<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use App\Entity\CommandeStatutHistorique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api/commandes')]
final class CommandeController extends AbstractController
{
    #[Route('', name: 'api_commande_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
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

        if (!$this->isValidAdresseLivraison($adresseLivraison)) {
            return $this->json([
                'message' => 'L’adresse de livraison doit contenir un numéro, une voie, un code postal et une ville.'
            ], 422);
        }

        $prixMenu = $menu->getPrixParPersonne() * $nombrePersonnes;
        $minimum = $menu->getNombrePersonneMinimum();

        if ($nombrePersonnes >= $minimum + 5) {
            $prixMenu = $prixMenu * 0.9;
        }

        $pretMateriel = isset($payload['pret_materiel']) ? (bool) $payload['pret_materiel'] : false;
        $restitutionMateriel = isset($payload['restitution_materiel']) ? (bool) $payload['restitution_materiel'] : false;

   
        $prixLivraison = $this->calculatePrixLivraison($adresseLivraison);

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

        $historique = new CommandeStatutHistorique();
        $historique->setCommande($commande);
        $historique->setAncienStatut('creation');
        $historique->setNouveauStatut('en_attente');
        $historique->setDateChangement(new \DateTimeImmutable());
        $historique->setUtilisateur($user);

        $entityManager->persist($commande);
        $entityManager->persist($historique);
        $entityManager->flush();

            try {
            $email = (new Email())
                ->from('no-reply@vite-gourmand.fr')
                ->to($user->getEmail())
                ->subject('Confirmation de votre commande')
                ->html(sprintf(
    '
                    <h1 style="color:#008cff;">Commande confirmée</h1>

                    <p>Bonjour %s,</p>
                    <p>Votre commande a bien été enregistrée.</p>

                    <p><strong>Numéro :</strong> %s</p>
                    <p><strong>Date :</strong> %s</p>
                    <p><strong>Heure :</strong> %s</p>
                    <p><strong>Adresse :</strong> %s</p>

                    <p style="font-size:18px;">
                        <strong>Total : %.2f €</strong>
                    </p>

                    <p>Merci pour votre confiance.</p>
                    ',
                    htmlspecialchars((string) $user->getFirstname()),
                    htmlspecialchars((string) $commande->getNumeroCommande()),
                    $commande->getDatePrestation()->format('d/m/Y'),
                    $commande->getHeureLivraison(),
                    htmlspecialchars((string) $commande->getAdresseLivraison()),
                    $commande->getPrixMenu() + $commande->getPrixLivraison()
                ));

            $mailer->send($email);
                } catch (\Throwable $e) {
            // Optionnel : logger plus tard
        }

        return $this->json($this->serializeCommande($commande), 201);
    }
   
    #[Route('', name: 'api_commande_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $statut = $request->query->get('statut');
        $client = trim((string) $request->query->get('client', ''));

        $statutsAutorises = [
            'en_attente',
            'acceptee',
            'en_preparation',
            'en_livraison',
            'livree',
            'retour_materiel',
            'terminee',
            'annulee',
        ];

        if ($statut && !in_array($statut, $statutsAutorises, true)) {
            return $this->json(['message' => 'Statut invalide'], 400);
        }

        $qb = $entityManager
            ->getRepository(Commande::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.utilisateur', 'u')
            ->addSelect('u')
            ->addOrderBy('c.dateCommande', 'DESC');

        if (
            !$this->isGranted('ROLE_EMPLOYE')
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            $qb->andWhere('c.utilisateur = :user')
                ->setParameter('user', $user);
        }

        if ($statut) {
            $qb->andWhere('c.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($client !== '' && ($this->isGranted('ROLE_EMPLOYE') || $this->isGranted('ROLE_ADMIN'))) {
            $qb->andWhere(
                'LOWER(u.name) LIKE :client
                OR LOWER(u.firstname) LIKE :client
                OR LOWER(u.email) LIKE :client'
            )
            ->setParameter('client', '%' . mb_strtolower($client) . '%');
        }

        $commandes = $qb->getQuery()->getResult();

        $data = array_map(
            fn(Commande $commande) => $this->serializeCommande($commande),
            $commandes
        );

        return $this->json($data);
    }


        #[Route('/{id}', name: 'api_commande_show', methods: ['GET'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
        #[IsGranted('ROLE_USER')]
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

            if (
                $commande->getUtilisateur()?->getUtilisateurId() !== $user->getUtilisateurId()
                && !$this->isGranted('ROLE_EMPLOYE')
            ) {
                return $this->json(['message' => 'Accès interdit'], 403);
            }

            return $this->json($this->serializeCommande($commande));
        }

        #[Route('/{id}', name: 'api_commande_update', methods: ['PATCH'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
        #[IsGranted('ROLE_USER')]
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

                if (!$this->isValidAdresseLivraison($adresseLivraison)) {
                    return $this->json([
                        'message' => 'L’adresse de livraison doit contenir un numéro, une voie, un code postal et une ville.'
                    ], 422);
                }

                $commande->setAdresseLivraison($adresseLivraison);
                $commande->setPrixLivraison($this->calculatePrixLivraison($adresseLivraison));
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
        #[IsGranted('ROLE_USER')]
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
    #[IsGranted('ROLE_EMPLOYE')]
    public function employeeCancel(
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
    #[IsGranted('ROLE_EMPLOYE')]
    public function updateStatus(
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

        $historique = new CommandeStatutHistorique();
        $historique->setCommande($commande);
        $historique->setAncienStatut($statutActuel);
        $historique->setNouveauStatut($nouveauStatut);
        $historique->setDateChangement(new \DateTimeImmutable());
        $historique->setUtilisateur($user);

        $commande->setStatut($nouveauStatut);

        $entityManager->persist($historique);
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

    

        private function isValidAdresseLivraison(string $adresse): bool
    {
        $adresse = trim($adresse);

        // Format attendu :
        // 18 rue Pompon, 33600 Pessac
        return (bool) preg_match('/^\d+\s+.+,\s*\d{5}\s+.+$/u', $adresse);
    }

    private function calculatePrixLivraison(string $adresseLivraison): float
    {
        $adresse = mb_strtolower(trim($adresseLivraison));

        if (str_contains($adresse, 'bordeaux') || str_contains($adresse, '33000')) {
            return 0.0;
        }

        $distanceKm = 10;

        if (str_contains($adresse, 'merignac') || str_contains($adresse, '33700')) {
            $distanceKm = 8;
        } elseif (str_contains($adresse, 'pessac') || str_contains($adresse, '33600')) {
            $distanceKm = 7;
        } elseif (str_contains($adresse, 'talence') || str_contains($adresse, '33400')) {
            $distanceKm = 6;
        } elseif (str_contains($adresse, 'eysines') || str_contains($adresse, '33320')) {
            $distanceKm = 9;
        } elseif (str_contains($adresse, 'blanquefort') || str_contains($adresse, '33290')) {
            $distanceKm = 12;
        } elseif (str_contains($adresse, 'le bouscat') || str_contains($adresse, '33110')) {
            $distanceKm = 5;
        }

        return 5 + ($distanceKm * 0.59);
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
        
        $historiqueStatuts = [];

        foreach ($commande->getHistoriquesStatut() as $historique) {
            $historiqueStatuts[] = [
                'ancien_statut' => $historique->getAncienStatut(),
                'nouveau_statut' => $historique->getNouveauStatut(),
                'date_changement' => $historique->getDateChangement()?->format('Y-m-d H:i:s'),
                'utilisateur' => $historique->getUtilisateur() ? [
                    'id' => $historique->getUtilisateur()->getUtilisateurId(),
                    'name' => $historique->getUtilisateur()->getName(),
                    'firstname' => $historique->getUtilisateur()->getFirstname(),
                    'email' => $historique->getUtilisateur()->getEmail(),
                ] : null,
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
            'historique_statuts' => $historiqueStatuts,
        ];
    }
}