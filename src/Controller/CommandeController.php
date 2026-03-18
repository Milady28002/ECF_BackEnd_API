<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/commandes')]
final class CommandeController extends AbstractController
{
    #[Route('', name: 'api_commande_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $requiredFields = [
            'menu_id',
            'nombre_personnes',
            'date_prestation',
            'heure_livraison',
            'adresse_livraison',
        ];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $payload)) {
                return $this->json(['message' => sprintf('Le champ "%s" est obligatoire', $field)], 422);
            }
        }

        $menu = $entityManager->getRepository(Menu::class)->find($payload['menu_id']);

        if (!$menu) {
            return $this->json(['message' => 'Menu introuvable'], 404);
        }

        $nombrePersonnes = (int) $payload['nombre_personnes'];

        if ($nombrePersonnes < $menu->getNombrePersonneMinimum()) {
            return $this->json([
                'message' => sprintf(
                    'Le nombre minimum de personnes pour ce menu est de %d',
                    $menu->getNombrePersonneMinimum()
                )
            ], 422);
        }

        if ($menu->getQuantiteRestante() <= 0) {
            return $this->json(['message' => 'Ce menu n’est plus disponible'], 422);
        }

        try {
            $datePrestation = new \DateTime($payload['date_prestation']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'date_prestation invalide'], 422);
        }

        $heureLivraison = trim((string) $payload['heure_livraison']);

        if ($heureLivraison === '') {
            return $this->json(['message' => 'heure_livraison est obligatoire'], 422);
        }

        $adresseLivraison = trim((string) $payload['adresse_livraison']);

        if ($adresseLivraison === '') {
            return $this->json(['message' => 'adresse_livraison est obligatoire'], 422);
        }

        $prixMenu = $menu->getPrixParPersonne() * $nombrePersonnes;
        $minimum = $menu->getNombrePersonneMinimum();

        if ($nombrePersonnes >= $minimum + 5) {
            $prixMenu = $prixMenu * 0.9;
        }

        $prixLivraison = isset($payload['prix_livraison']) ? (float) $payload['prix_livraison'] : 0.0;
        $pretMateriel = isset($payload['pret_materiel']) ? (bool) $payload['pret_materiel'] : false;
        $restitutionMateriel = isset($payload['restitution_materiel']) ? (bool) $payload['restitution_materiel'] : false;

        $commande = new Commande();
        $commande->setNumeroCommande('CMD' . strtoupper(substr(uniqid(), -6)));
        $commande->setDateCommande(new \DateTime());
        $commande->setDatePrestation($datePrestation);
        $commande->setHeureLivraison($heureLivraison);
        $commande->setAdresseLivraison($adresseLivraison);
        $commande->setPrixMenu($prixMenu);
        $commande->setPrixLivraison($prixLivraison);
        $commande->setNombrePersonnes($nombrePersonnes);
        $commande->setStatut('en_attente');
        $commande->setPretMateriel($pretMateriel);
        $commande->setRestitutionMateriel($restitutionMateriel);
        $commande->setUtilisateur($user);
        $commande->addMenu($menu);

        $menu->setQuantiteRestante($menu->getQuantiteRestante() - 1);

        $entityManager->persist($commande);
        $entityManager->flush();

        return $this->json($this->serializeCommande($commande), 201);
    }

    #[Route('/me', name: 'api_commande_my_list', methods: ['GET'])]
    public function myOrders(
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commandes = $entityManager->getRepository(Commande::class)->findBy(
            ['utilisateur' => $user],
            ['dateCommande' => 'DESC']
        );

        $data = array_map
            (fn (Commande $commande) => $this->serializeCommande($commande),
            $commandes
        );

        return $this->json($data);
    }

    #[Route('', name: 'api_commande_list', methods: ['GET'])]
    public function list(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        if (!in_array('ROLE_EMPLOYE', $user->getRoles(), true) && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $statut = $request->query->get('statut');

        if ($statut) {
            $commandes = $entityManager
                ->getRepository(Commande::class)
                ->findBy(['statut' => $statut], ['dateCommande' => 'DESC']);
        } else {
            $commandes = $entityManager
                ->getRepository(Commande::class)
                ->findBy([], ['dateCommande' => 'DESC']);
        }

        $data = array_map(fn (Commande $commande) => $this->serializeCommande($commande), $commandes);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_commande_show', methods: ['GET'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
    public function show(
        string $id,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commande = $entityManager->getRepository(Commande::class)->findOneBy([
            'numeroCommande' => $id
        ]);

        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable'], 404);
        }

        if ($commande->getUtilisateur()?->getUtilisateurId() !== $user->getUtilisateurId()) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        return $this->json($this->serializeCommande($commande));
    }

    #[Route('/{id}', name: 'api_commande_update', methods: ['PATCH'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
    public function update(
        string $id,
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commande = $entityManager->getRepository(Commande::class)->findOneBy([
            'numeroCommande' => $id
        ]);

        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable'], 404);
        }

        if ($commande->getUtilisateur()?->getUtilisateurId() !== $user->getUtilisateurId()) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        if ($commande->getStatut() !== 'en_attente') {
            return $this->json([
                'message' => 'La commande ne peut plus être modifiée une fois prise en charge'
            ], 422);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $menu = $this->getCommandeMenu($commande);

        if (!$menu) {
            return $this->json(['message' => 'Aucun menu lié à cette commande'], 422);
        }

        if (array_key_exists('menu_id', $payload)) {
            return $this->json([
                'message' => 'Le menu ne peut pas être modifié'
            ], 422);
        }

        if (array_key_exists('nombre_personnes', $payload)) {
            $nombrePersonnes = (int) $payload['nombre_personnes'];

            if ($nombrePersonnes < $menu->getNombrePersonneMinimum()) {
                return $this->json([
                    'message' => sprintf(
                        'Le nombre minimum de personnes pour ce menu est de %d',
                        $menu->getNombrePersonneMinimum()
                    )
                ], 422);
            }

            $prixMenu = $menu->getPrixParPersonne() * $nombrePersonnes;
            $minimum = $menu->getNombrePersonneMinimum();

            if ($nombrePersonnes >= $minimum + 5) {
                $prixMenu = $prixMenu * 0.9;
            }

            $commande->setNombrePersonnes($nombrePersonnes);
            $commande->setPrixMenu($prixMenu);
        }

        if (array_key_exists('date_prestation', $payload)) {
            try {
                $commande->setDatePrestation(new \DateTime($payload['date_prestation']));
            } catch (\Exception $e) {
                return $this->json(['message' => 'date_prestation invalide'], 422);
            }
        }

        if (array_key_exists('heure_livraison', $payload)) {
            $heureLivraison = trim((string) $payload['heure_livraison']);

            if ($heureLivraison === '') {
                return $this->json(['message' => 'heure_livraison invalide'], 422);
            }

            $commande->setHeureLivraison($heureLivraison);
        }

        if (array_key_exists('adresse_livraison', $payload)) {
            $adresseLivraison = trim((string) $payload['adresse_livraison']);

            if ($adresseLivraison === '') {
                return $this->json(['message' => 'adresse_livraison invalide'], 422);
            }

            $commande->setAdresseLivraison($adresseLivraison);
        }

        if (array_key_exists('prix_livraison', $payload)) {
            $commande->setPrixLivraison((float) $payload['prix_livraison']);
        }

        if (array_key_exists('pret_materiel', $payload)) {
            $commande->setPretMateriel((bool) $payload['pret_materiel']);
        }

        if (array_key_exists('restitution_materiel', $payload)) {
            $commande->setRestitutionMateriel((bool) $payload['restitution_materiel']);
        }

        $entityManager->flush();

        return $this->json($this->serializeCommande($commande));
    }

    #[Route('/{id}/cancel', name: 'api_commande_cancel', methods: ['PATCH'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
    public function cancel(
        string $id,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commande = $entityManager->getRepository(Commande::class)->findOneBy([
            'numeroCommande' => $id
        ]);

        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable'], 404);
        }

        if ($commande->getUtilisateur()?->getUtilisateurId() !== $user->getUtilisateurId()) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        if ($commande->getStatut() === 'annulee') {
            return $this->json([
                'message' => 'La commande est déjà annulée'
            ], 422);
        }

        if ($commande->getStatut() !== 'en_attente') {
            return $this->json([
                'message' => 'La commande ne peut plus être annulée une fois prise en charge'
            ], 422);
        }

        $commande->setStatut('annulee');

        $menu = $this->getCommandeMenu($commande);
        if ($menu) {
            $menu->setQuantiteRestante($menu->getQuantiteRestante() + 1);
        }

        $entityManager->flush();

        return $this->json($this->serializeCommande($commande));
    }

    #[Route('/employe/{id}/cancel', name: 'api_commande_employe_cancel', methods: ['PATCH'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
    public function employeeCancel(
        string $id,
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        if (!in_array('ROLE_EMPLOYE', $user->getRoles(), true) && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $commande = $entityManager->getRepository(Commande::class)->findOneBy([
            'numeroCommande' => $id
        ]);

        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable'], 404);
        }

        if ($commande->getStatut() === 'annulee') {
            return $this->json(['message' => 'La commande est déjà annulée'], 422);
        }

        if ($commande->getStatut() === 'terminee') {
            return $this->json(['message' => 'Une commande terminée ne peut pas être annulée'], 422);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $modeContact = isset($payload['mode_contact_annulation']) ? trim((string) $payload['mode_contact_annulation']) : '';
        $motifAnnulation = isset($payload['motif_annulation']) ? trim((string) $payload['motif_annulation']) : '';

        if ($modeContact === '') {
            return $this->json(['message' => 'Le mode de contact est obligatoire'], 422);
        }

        if ($motifAnnulation === '') {
            return $this->json(['message' => 'Le motif d’annulation est obligatoire'], 422);
        }

        $commande->setModeContactAnnulation($modeContact);
        $commande->setMotifAnnulation($motifAnnulation);
        $commande->setStatut('annulee');

        $menu = $this->getCommandeMenu($commande);
        if ($menu) {
            $menu->setQuantiteRestante($menu->getQuantiteRestante() + 1);
        }

        $entityManager->flush();

        return $this->json($this->serializeCommande($commande));
    }

    #[Route('/employe/{id}/status', name: 'api_commande_employe_status', methods: ['PATCH'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
    public function updateStatus(
        string $id,
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        if (!in_array('ROLE_EMPLOYE', $user->getRoles(), true) && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['message' => 'Accès interdit'], 403);
        }

        $commande = $entityManager->getRepository(Commande::class)->findOneBy([
            'numeroCommande' => $id
        ]);

        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable'], 404);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (!isset($payload['statut'])) {
            return $this->json(['message' => 'Le statut est obligatoire'], 422);
        }

        $transitionsAutorisees = [
            'en_attente' => ['acceptee', 'annulee'],
            'acceptee' => ['en_preparation', 'annulee'],
            'en_preparation' => ['en_livraison', 'annulee'],
            'en_livraison' => ['livree'],
            'livree' => ['retour_materiel', 'terminee'],
            'retour_materiel' => ['terminee'],
            'terminee' => [],
            'annulee' => [],
        ];

        $statutActuel = $commande->getStatut();
        $nouveauStatut = $payload['statut'];

        if (!isset($transitionsAutorisees[$statutActuel])) {
            return $this->json(['message' => 'Statut actuel invalide'], 422);
        }

        if (!in_array($nouveauStatut, $transitionsAutorisees[$statutActuel], true)) {
            return $this->json(['message' => 'Transition de statut non autorisée'], 422);
        }

        $commande->setStatut($payload['statut']);

        $entityManager->flush();

        return $this->json($this->serializeCommande($commande));
    }

    private function getCommandeMenu(Commande $commande): ?Menu
    {
        foreach ($commande->getMenu() as $menu) {
            return $menu;
        }

        return null;
    }

    private function serializeCommande(Commande $commande): array
    {
        $menus = [];

        foreach ($commande->getMenu() as $menu) {
            $menus[] = [
                'id' => $menu->getMenuId(),
                'titre' => $menu->getTitre(),
                'prix_par_personne' => $menu->getPrixParPersonne(),
                'description' => $menu->getDescription(),
                'image' => $menu->getImage(),
            ];
        }

        return [
            'numero_commande' => $commande->getNumeroCommande(),
            'date_commande' => $commande->getDateCommande()?->format('Y-m-d'),
            'date_prestation' => $commande->getDatePrestation()?->format('Y-m-d'),
            'heure_livraison' => $commande->getHeureLivraison(),
            'adresse_livraison' => $commande->getAdresseLivraison(),
            'prix_menu' => $commande->getPrixMenu(),
            'prix_livraison' => $commande->getPrixLivraison(),
            'prix_total' => $commande->getPrixMenu() + $commande->getPrixLivraison(),
            'nombre_personnes' => $commande->getNombrePersonnes(),
            'statut' => $commande->getStatut(),
            'motif_annulation' => $commande->getMotifAnnulation(),
            'mode_contact_annulation' => $commande->getModeContactAnnulation(),
            'pret_materiel' => $commande->isPretMateriel(),
            'restitution_materiel' => $commande->isRestitutionMateriel(),
            'utilisateur' => $commande->getUtilisateur() ? [
                'id' => $commande->getUtilisateur()->getUtilisateurId(),
                'name' => $commande->getUtilisateur()->getName(),
                'firstname' => $commande->getUtilisateur()->getFirstname(),
                'email' => $commande->getUtilisateur()->getEmail(),
                'telephone' => $commande->getUtilisateur()->getTelephone(),
            ] : null,
            'menus' => $menus,
        ];
    }

}