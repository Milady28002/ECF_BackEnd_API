<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use App\Entity\CommandeStatutHistorique;
use App\Entity\Avis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Service\MongoStatsService;

#[Route('/api/commandes')]
final class CommandeController extends AbstractController
{
    private MongoStatsService $mongoStatsService;

    public function __construct(MongoStatsService $mongoStatsService)
    {
        $this->mongoStatsService = $mongoStatsService;
    }

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

        if ($nombrePersonnes <= 0) {
            return $this->json(['message' => 'Nombre de personnes invalide'], 422);
        }

        if ($nombrePersonnes < $menu->getNombrePersonneMinimum()) {
            return $this->json([
                'message' => sprintf(
                    'Le nombre minimum de personnes pour ce menu est de %d',
                    $menu->getNombrePersonneMinimum()
                )
            ], 422);
        }

        if ($menu->getQuantiteRestante() <= 0) {
            return $this->json([
                'message' => 'Ce menu n’est plus disponible',
                'code' => 'OUT_OF_STOCK'
            ], 422);
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
            $this->mongoStatsService->saveCommandeStat([
                'commande_numero' => $commande->getNumeroCommande(),
                'menu_id' => $menu->getMenuId(),
                'menu_titre' => $menu->getTitre(),
                'date_commande' => $commande->getDateCommande()?->format('Y-m-d'),
                'date_prestation' => $commande->getDatePrestation()?->format('Y-m-d'),
                'nombre_personnes' => $commande->getNombrePersonnes(),
                'prix_menu' => round((float) $commande->getPrixMenu(), 2),
                'prix_livraison' => round((float) $commande->getPrixLivraison(), 2),
                'chiffre_affaire' => round((float) ($commande->getPrixMenu() + $commande->getPrixLivraison()), 2),
                'statut' => $commande->getStatut(),
            ]);
        } catch (\Throwable $e) {
            // À logger plus tard si besoin
        }

        try {
            $email = (new Email())
                ->from('no-reply@vite-gourmand.fr')
                ->to($user->getEmail())
                ->subject('Confirmation de votre commande')
                ->html(sprintf(
                    '
                    <body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
                      <div style="max-width:600px; margin:0 auto; background:#ffffff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                        <h2 style="color:#7bbd2f; margin-top:0;">Vite &amp; Gourmand</h2>
                        <h1 style="color:#008cff;">Commande confirmée</h1>

                        <p>Bonjour %s,</p>
                        <p>Votre commande a bien été enregistrée.</p>

                        <div style="background:#f8f8f8; padding:15px; border-radius:8px; margin:15px 0;">
                          <p><strong>Numéro :</strong> %s</p>
                          <p><strong>Date :</strong> %s</p>
                          <p><strong>Heure :</strong> %s</p>
                          <p><strong>Adresse :</strong> %s</p>
                          <p><strong>Total :</strong> %.2f €</p>
                        </div>

                        <p>Merci pour votre confiance 🙏</p>
                      </div>
                    </body>
                    ',
                    htmlspecialchars((string) $user->getFirstname()),
                    htmlspecialchars((string) $commande->getNumeroCommande()),
                    $commande->getDatePrestation()->format('d/m/Y'),
                    htmlspecialchars((string) $commande->getHeureLivraison()),
                    htmlspecialchars((string) $commande->getAdresseLivraison()),
                    $commande->getPrixMenu() + $commande->getPrixLivraison()
                ));

            // $mailer->send($email); decommenter lorsque je pourrai utiliser de nouveau mailtrap
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
        try {
            $menu = $this->getCommandeMenu($commande);

            if ($menu) {
                $this->mongoStatsService->updateCommandeStat(
                    $commande->getNumeroCommande(),
                    [
                        'menu_id' => $menu->getMenuId(),
                        'menu_titre' => $menu->getTitre(),
                        'date_commande' => $commande->getDateCommande()?->format('Y-m-d'),
                        'date_prestation' => $commande->getDatePrestation()?->format('Y-m-d'),
                        'nombre_personnes' => $commande->getNombrePersonnes(),
                        'prix_menu' => round((float) $commande->getPrixMenu(), 2),
                        'prix_livraison' => round((float) $commande->getPrixLivraison(), 2),
                        'chiffre_affaire' => round((float) ($commande->getPrixMenu() + $commande->getPrixLivraison()), 2),
                        'statut' => $commande->getStatut(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Optionnel : logger plus tard
        }

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

        try {
            $this->mongoStatsService->updateCommandeStat(
                $commande->getNumeroCommande(),
                [
                    'statut' => $commande->getStatut(),
                    'prix_menu' => 0,
                    'prix_livraison' => 0,
                    'chiffre_affaire' => 0,
                ]
            );
        } catch (\Throwable $e) {
                // Optionnel : logger plus tard
        }

        return $this->json($this->serializeCommande($commande));
    }

    #[Route('/employe/{id}/cancel', name: 'api_commande_employe_cancel', methods: ['PATCH'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function employeeCancel(
        string $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
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

        try {
            $this->mongoStatsService->updateCommandeStat(
                $commande->getNumeroCommande(),
                [
                    'statut' => $commande->getStatut(),
                    'prix_menu' => 0,
                    'prix_livraison' => 0,
                    'chiffre_affaire' => 0,
                ]
            );
        } catch (\Throwable $e) {
            // Optionnel : logger plus tard
        }

        try {
            $client = $commande->getUtilisateur();

            if ($client && $client->getEmail()) {
                $email = (new Email())
                    ->from('no-reply@vite-gourmand.fr')
                    ->to($client->getEmail())
                    ->subject('Annulation de votre commande')
                    ->html(sprintf(
                        '
                        <body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
                          <div style="max-width:600px; margin:0 auto; background:#ffffff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                            <h2 style="color:#7bbd2f; margin-top:0;">Vite &amp; Gourmand</h2>
                            <h1 style="color:#b42318;">Commande annulée</h1>

                            <p>Bonjour %s,</p>
                            <p>Votre commande <strong>%s</strong> a été annulée par notre équipe.</p>

                            <div style="background:#f8f8f8; padding:15px; border-radius:8px; margin:15px 0;">
                              <p><strong>Motif :</strong> %s</p>
                              <p><strong>Mode de contact utilisé :</strong> %s</p>
                            </div>

                            <p>Nous restons à votre disposition pour toute question.</p>
                          </div>
                        </body>
                        ',
                        htmlspecialchars((string) $client->getFirstname()),
                        htmlspecialchars((string) $commande->getNumeroCommande()),
                        htmlspecialchars($motifAnnulation),
                        htmlspecialchars($modeContact)
                    ));

                $mailer->send($email);
            }
        } catch (\Throwable $e) {
            // Optionnel : logger plus tard
        }

        return $this->json($this->serializeCommande($commande));
    }

    #[Route('/employe/{id}/status', name: 'api_commande_employe_status', methods: ['PATCH'], requirements: ['id' => 'CMD[0-9A-Z]+'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function updateStatus(
        string $id,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
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


        if ($nouveauStatut === 'retour_materiel' && !$commande->isPretMateriel()) {
            return $this->json([
                'message' => 'Ce statut n’est possible que si du matériel a été prêté au client.'
            ], 422);
        }

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

       try {
            $mongoData = [
                'statut' => $commande->getStatut(),
            ];

            if ($commande->getStatut() === 'annulee') {
                $mongoData['prix_menu'] = 0;
                $mongoData['prix_livraison'] = 0;
                $mongoData['chiffre_affaire'] = 0;
            } else {
                $mongoData['prix_menu'] = round((float) $commande->getPrixMenu(), 2);
                $mongoData['prix_livraison'] = round((float) $commande->getPrixLivraison(), 2);
                $mongoData['chiffre_affaire'] = round((float) ($commande->getPrixMenu() + $commande->getPrixLivraison()), 2);
            }

            $this->mongoStatsService->updateCommandeStat(
                $commande->getNumeroCommande(),
                $mongoData
            );
        } catch (\Throwable $e) {
            // Optionnel : logger plus tard
        }

        try {
            $client = $commande->getUtilisateur();

            if ($client && $client->getEmail()) {
                $subject = null;
                $title = null;
                $message = null;
                $color = '#008cff';

                if ($nouveauStatut === 'acceptee') {
                    $subject = 'Votre commande a été acceptée';
                    $title = 'Commande acceptée';
                    $message = 'Bonne nouvelle : votre commande a bien été acceptée par notre équipe.';
                } elseif ($nouveauStatut === 'en_preparation') {
                    $subject = 'Votre commande est en préparation';
                    $title = 'Commande en préparation';
                    $message = 'Votre commande est actuellement en préparation.';
                } elseif ($nouveauStatut === 'en_livraison') {
                    $subject = 'Votre commande est en livraison';
                    $title = 'Commande en livraison';
                    $message = 'Votre commande est en cours de livraison.';
                } elseif ($nouveauStatut === 'livree') {
                    $subject = 'Votre commande a été livrée';
                    $title = 'Commande livrée';
                    $message = 'Votre commande a bien été livrée.';
                } elseif ($nouveauStatut === 'retour_materiel') {
                    $subject = 'En attente du retour de matériel';
                    $title = 'En attente du retour de matériel';
                    $message = 'Votre commande est terminée, mais nous sommes actuellement en attente du retour du matériel prêté.';
                    $color = '#d39e00';
                } elseif ($nouveauStatut === 'terminee') {
                    $subject = 'Votre commande est terminée';
                    $title = 'Commande terminée';
                    $message = 'Votre commande est maintenant terminée. Merci pour votre confiance.';
                }

                if ($subject && $title && $message) {
                $commandeUrl = sprintf(
                    'http://127.0.0.1:3001/#/commande-detail?id=%s',
                    urlencode((string) $commande->getNumeroCommande())
                );

            $ctaHtml = '';

            if ($nouveauStatut === 'terminee') {
                $ctaHtml = sprintf(
                    '
                    <p style="margin: 25px 0 10px;">
                    Vous pouvez consulter votre commande et laisser un avis en cliquant ci-dessous :
                    </p>

                    <p style="margin: 20px 0 30px;">
                    <a href="%s"
                        style="display:inline-block; padding:14px 22px; background-color:#7bbd2f; color:#ffffff; text-decoration:none; border-radius:8px; font-weight:bold;">
                        Voir ma commande
                    </a>
                    </p>
                    ',
                    htmlspecialchars($commandeUrl, ENT_QUOTES, 'UTF-8')
                );
            }

            $extraContent = '';

            if ($nouveauStatut === 'retour_materiel') {
                $extraContent = '
                    <div style="background:#fff8e6; border:1px solid #f0d58a; padding:15px; border-radius:8px; margin:20px 0;">
                        <p style="margin-top:0;"><strong>Retour du matériel prêté</strong></p>
                        <p>
                            Du matériel vous a été prêté dans le cadre de votre commande.
                        </p>
                        <p>
                            Nous vous invitons à prendre contact avec notre société afin d’organiser sa restitution.
                        </p>
                        <p>
                            Conformément à nos conditions générales de vente, si le matériel n’est pas restitué sous
                            <strong>10 jours ouvrés</strong>, des frais de <strong>600 €</strong> pourront vous être facturés.
                        </p>
                    </div>
                ';
            }

            $email = (new Email())
                ->from('no-reply@vite-gourmand.fr')
                ->to($client->getEmail())
                ->subject($subject)
                ->html(sprintf(
                    '
                    <body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
                    <div style="max-width:600px; margin:0 auto; background:#ffffff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                        <h2 style="color:#7bbd2f; margin-top:0;">Vite &amp; Gourmand</h2>
                        <h1 style="color:%s;">%s</h1>

                        <p>Bonjour %s,</p>
                        <p>%s</p>

                        <div style="background:#f8f8f8; padding:15px; border-radius:8px; margin:15px 0;">
                        <p><strong>Numéro :</strong> %s</p>
                        <p><strong>Date :</strong> %s</p>
                        <p><strong>Heure :</strong> %s</p>
                        </div>

                        %s

                        %s

                        <p>Merci pour votre confiance 🙏</p>
                    </div>
                    </body>
                    ',
                    htmlspecialchars($color),
                    htmlspecialchars($title),
                    htmlspecialchars((string) $client->getFirstname()),
                    htmlspecialchars($message),
                    htmlspecialchars((string) $commande->getNumeroCommande()),
                    $commande->getDatePrestation()?->format('d/m/Y') ?? 'Non renseignée',
                    htmlspecialchars((string) $commande->getHeureLivraison()),
                    $ctaHtml,
                    $extraContent
                ));

            file_put_contents(
                __DIR__ . '/../../public/test-mail-terminee.html',
                $email->getHtmlBody()
            );
            $mailer->send($email);
        }
            }
        } catch (\Throwable $e) {
            // Optionnel : logger plus tard
        }

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

        $avisDejaLaisse = false;

        foreach ($commande->getAvis() as $avis) {
            if ($avis->getUtilisateur()?->getUtilisateurId() === $commande->getUtilisateur()?->getUtilisateurId()) {
                $avisDejaLaisse = true;
                break;
            }
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
            'avis_deja_laisse' => $avisDejaLaisse,
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