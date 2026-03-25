<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/avis')]
final class AvisController extends AbstractController
{
    #[Route('', name: 'api_avis_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
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

        $commandeNumero = trim((string) ($payload['commande_numero'] ?? ''));
        $note = $payload['note'] ?? null;
        $description = trim((string) ($payload['description'] ?? ''));

        if ($commandeNumero === '' || $note === null || $description === '') {
            return $this->json([
                'message' => 'Les champs commande_numero, note et description sont obligatoires.'
            ], 422);
        }

        if (!is_numeric($note) || (int) $note < 1 || (int) $note > 5) {
            return $this->json([
                'message' => 'La note doit être un entier compris entre 1 et 5.'
            ], 422);
        }

        $commande = $entityManager->getRepository(Commande::class)->findOneBy([
            'numeroCommande' => $commandeNumero
        ]);

        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable.'], 404);
        }

        if ($commande->getUtilisateur()?->getUtilisateurId() !== $user->getUtilisateurId()) {
            return $this->json(['message' => 'Accès interdit à cette commande.'], 403);
        }

        if ($commande->getStatut() !== 'terminee') {
            return $this->json([
                'message' => 'Un avis ne peut être déposé que pour une commande terminée.'
            ], 422);
        }

        $avisExistant = $entityManager->getRepository(Avis::class)->findOneBy([
            'commande' => $commande
        ]);

        if ($avisExistant) {
            return $this->json([
                'message' => 'Un avis a déjà été déposé pour cette commande.'
            ], 409);
        }

        $avis = new Avis();
        $avis->setCommande($commande);
        $avis->setUtilisateur($user);
        $avis->setNote((int) $note);
        $avis->setDescription($description);
        $avis->setStatut('en_attente');
        $avis->setDateCreation(new \DateTimeImmutable());

        $entityManager->persist($avis);
        $entityManager->flush();

        return $this->json([
            'message' => 'Avis enregistré avec succès et en attente de validation.',
            'avis' => $this->serializeAvis($avis),
        ], 201);
    }

    #[Route('', name: 'api_avis_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $avis = $entityManager->getRepository(Avis::class)->findBy(
            ['statut' => 'valide'],
            ['dateCreation' => 'DESC']
        );

        $data = array_map(
            fn(Avis $avis) => $this->serializeAvis($avis),
            $avis
        );

        return $this->json($data);
    }

    private function serializeAvis(Avis $avis): array
    {
        return [
            'id' => $avis->getAvisId(),
            'note' => $avis->getNote(),
            'description' => $avis->getDescription(),
            'statut' => $avis->getStatut(),
            'date_creation' => $avis->getDateCreation()?->format('Y-m-d H:i:s'),
            'utilisateur' => $avis->getUtilisateur() ? [
                'id' => $avis->getUtilisateur()->getUtilisateurId(),
                'name' => $avis->getUtilisateur()->getName(),
                'firstname' => $avis->getUtilisateur()->getFirstname(),
            ] : null,
            'commande' => $avis->getCommande() ? [
                'numero_commande' => $avis->getCommande()->getNumeroCommande(),
            ] : null,
        ];
    }

#[Route('/{id}/validate', name: 'api_avis_validate', methods: ['PATCH'])]
#[IsGranted('ROLE_EMPLOYE')]
public function validateAvis(
    int $id,
    EntityManagerInterface $entityManager
): JsonResponse {
    $avis = $entityManager->getRepository(Avis::class)->find($id);

    if (!$avis) {
        return $this->json(['message' => 'Avis introuvable.'], 404);
    }

    $avis->setStatut('valide');
    $entityManager->flush();

    return $this->json([
        'message' => 'Avis validé avec succès.',
        'avis' => $this->serializeAvis($avis),
    ]);
}

#[Route('/{id}/reject', name: 'api_avis_reject', methods: ['PATCH'])]
#[IsGranted('ROLE_EMPLOYE')]
public function rejectAvis(
    int $id,
    EntityManagerInterface $entityManager
): JsonResponse {
    $avis = $entityManager->getRepository(Avis::class)->find($id);

    if (!$avis) {
        return $this->json(['message' => 'Avis introuvable.'], 404);
    }

    $avis->setStatut('refuse');
    $entityManager->flush();

    return $this->json([
        'message' => 'Avis refusé avec succès.',
        'avis' => $this->serializeAvis($avis),
    ]);
}
#[Route('/moderation', name: 'api_avis_moderation_list', methods: ['GET'])]
#[IsGranted('ROLE_EMPLOYE')]
public function moderationList(
    Request $request,
    EntityManagerInterface $entityManager
): JsonResponse {
    $statut = $request->query->get('statut', 'en_attente');

    $statutsAutorises = ['en_attente', 'valide', 'refuse'];

    if (!in_array($statut, $statutsAutorises, true)) {
        return $this->json(['message' => 'Statut invalide.'], 400);
    }

    $avis = $entityManager->getRepository(Avis::class)->findBy(
        ['statut' => $statut],
        ['dateCreation' => 'DESC']
    );

    $data = array_map(
        fn(Avis $avis) => $this->serializeAvis($avis),
        $avis
    );

    return $this->json($data);
}
}