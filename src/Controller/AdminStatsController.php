<?php

namespace App\Controller;

use App\Service\MongoStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/stats', name: 'api_admin_stats_')]
final class AdminStatsController extends AbstractController
{
    public function __construct(
        private MongoStatsService $mongoStatsService
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(Request $request): JsonResponse
    {
        $menuId = $request->query->get('menu_id');
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        $statut = $request->query->get('statut', 'hors_annulees');

        $statutsAutorises = [
            'hors_annulees',
            'annulees',
            'toutes',
            'en_attente',
            'acceptee',
            'en_preparation',
            'en_livraison',
            'livree',
            'retour_materiel',
            'terminee',
            'annulee',
        ];

        if (!in_array($statut, $statutsAutorises, true)) {
            return $this->json([
                'message' => 'Filtre de statut invalide'
            ], 400);
        }

        $stats = $this->mongoStatsService->getStats(
            $menuId !== null && $menuId !== '' ? (int) $menuId : null,
            $dateDebut ?: null,
            $dateFin ?: null,
            $statut
        );

        return $this->json($stats);
    }

    #[Route('/evolution', name: 'evolution', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function evolution(Request $request): JsonResponse
    {
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');
        $statut = $request->query->get('statut', 'hors_annulees');

        $statutsAutorises = [
            'hors_annulees',
            'annulees',
            'toutes',
            'en_attente',
            'acceptee',
            'en_preparation',
            'en_livraison',
            'livree',
            'retour_materiel',
            'terminee',
            'annulee',
        ];

        if (!in_array($statut, $statutsAutorises, true)) {
            return $this->json([
                'message' => 'Filtre de statut invalide'
            ], 400);
        }

        $stats = $this->mongoStatsService->getEvolutionStats(
            $dateDebut ?: null,
            $dateFin ?: null,
            $statut
        );

        return $this->json($stats);
    }
}