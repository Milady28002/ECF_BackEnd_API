<?php

namespace App\Controller;

use App\Entity\Horaire;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/horaires', name: 'app_api_horaires_')]
class HoraireController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/horaires',
        summary: 'Lister les horaires de l’entreprise'
    )]
    public function list(HoraireRepository $horaireRepository): JsonResponse
    {
        $horaires = $horaireRepository->findAllOrderedByJour();

        $data = array_map(function (Horaire $horaire) {
            return [
                'id' => $horaire->getId(),
                'jour' => $horaire->getJour(),
                'heure_ouverture' => $horaire->getHeureOuverture(),
                'heure_fermeture' => $horaire->getHeureFermeture(),
            ];
        }, $horaires);

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/horaires/{id}',
        summary: 'Modifier un horaire',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ]
    )]
    public function update(
        int $id,
        Request $request,
        HoraireRepository $horaireRepository,
        EntityManagerInterface $manager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $horaire = $horaireRepository->find($id);

        if (!$horaire) {
            return $this->json([
                'message' => 'Horaire introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !is_array($data)) {
            return $this->json([
                'message' => 'Données invalides.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['jour']) && !empty(trim($data['jour']))) {
            $horaire->setJour(trim($data['jour']));
        }

        if (isset($data['heure_ouverture']) && !empty(trim($data['heure_ouverture']))) {
            $horaire->setHeureOuverture(trim($data['heure_ouverture']));
        }

        if (isset($data['heure_fermeture']) && !empty(trim($data['heure_fermeture']))) {
            $horaire->setHeureFermeture(trim($data['heure_fermeture']));
        }

        $manager->flush();

        return $this->json([
            'message' => 'Horaire mis à jour avec succès.',
            'horaire' => [
                'id' => $horaire->getId(),
                'jour' => $horaire->getJour(),
                'heure_ouverture' => $horaire->getHeureOuverture(),
                'heure_fermeture' => $horaire->getHeureFermeture(),
            ]
        ], Response::HTTP_OK);
    }
}