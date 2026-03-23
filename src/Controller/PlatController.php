<?php

namespace App\Controller;

use App\Entity\Plat;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/plats')]
final class PlatController extends AbstractController
{
    #[Route('', name: 'api_plat_list', methods: ['GET'])]
    public function list(PlatRepository $repo): JsonResponse
    {
        $plats = $repo->findAll();

        $data = array_map([$this, 'toArray'], $plats);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_plat_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getOne(int $id, PlatRepository $repo): JsonResponse
    {
        $plat = $repo->find($id);

        if (!$plat) {
            return $this->json(['message' => 'Plat introuvable'], 404);
        }

        return $this->json($this->toArray($plat));
    }

    #[Route('', name: 'api_plat_create', methods: ['POST'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $titre = $payload['titre_plat'] ?? $payload['titrePlat'] ?? $payload['titre'] ?? null;
        $typePlat = $payload['type_plat'] ?? null;
        $imageUrl = $payload['image_url'] ?? null;

        if (!$titre || !is_string($titre) || mb_strlen(trim($titre)) < 1) {
            return $this->json(['message' => 'Le titre du plat est obligatoire'], 422);
        }

        if ($typePlat === null || !in_array((string) $typePlat, ['1', '2', '3'], true)) {
            return $this->json(['message' => 'Le type de plat doit être 1 (entrée), 2 (plat) ou 3 (dessert)'], 422);
        }

        if ($imageUrl !== null && $imageUrl !== '' && !is_string($imageUrl)) {
            return $this->json(['message' => 'image_url doit être une chaîne de caractères ou null'], 422);
        }

        $plat = new Plat();
        $plat->setTitrePlat(trim($titre));
        $plat->setTypePlat((string) $typePlat);
        $plat->setImageUrl(is_string($imageUrl) && trim($imageUrl) !== '' ? trim($imageUrl) : null);

        $em->persist($plat);
        $em->flush();

        return $this->json($this->toArray($plat), 201);
    }

    #[Route('/{id}', name: 'api_plat_update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function update(int $id, Request $request, PlatRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $plat = $repo->find($id);

        if (!$plat) {
            return $this->json(['message' => 'Plat introuvable'], 404);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (array_key_exists('titre_plat', $payload) || array_key_exists('titrePlat', $payload) || array_key_exists('titre', $payload)) {
            $titre = $payload['titre_plat'] ?? $payload['titrePlat'] ?? $payload['titre'] ?? null;

            if (!$titre || !is_string($titre) || mb_strlen(trim($titre)) < 1) {
                return $this->json(['message' => 'Le titre du plat est obligatoire'], 422);
            }

            $plat->setTitrePlat(trim($titre));
        }

        if (array_key_exists('type_plat', $payload)) {
            $typePlat = $payload['type_plat'];

            if ($typePlat === null || !in_array((string) $typePlat, ['1', '2', '3'], true)) {
                return $this->json(['message' => 'Le type de plat doit être 1 (entrée), 2 (plat) ou 3 (dessert)'], 422);
            }

            $plat->setTypePlat((string) $typePlat);
        }

        if (array_key_exists('image_url', $payload)) {
            $imageUrl = $payload['image_url'];

            if ($imageUrl !== null && $imageUrl !== '' && !is_string($imageUrl)) {
                return $this->json(['message' => 'image_url doit être une chaîne de caractères ou null'], 422);
            }

            $plat->setImageUrl(is_string($imageUrl) && trim($imageUrl) !== '' ? trim($imageUrl) : null);
        }

        $em->flush();

        return $this->json($this->toArray($plat));
    }

    #[Route('/{id}', name: 'api_plat_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, PlatRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $plat = $repo->find($id);

        if (!$plat) {
            return $this->json(['message' => 'Plat introuvable'], 404);
        }

        $em->remove($plat);
        $em->flush();

        return $this->json(null, 204);
    }

    private function toArray(Plat $plat): array
    {
        return [
            'id' => $plat->getPlatId(),
            'titre_plat' => $plat->getTitrePlat(),
            'type_plat' => $plat->getTypePlat(),
            'image_url' => $plat->getImageUrl(),
        ];
    }
}