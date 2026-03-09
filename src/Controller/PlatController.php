<?php

namespace App\Controller;

use App\Entity\Plat;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/plats')]
final class PlatController extends AbstractController
{
    #[Route('', name: 'api_plat_list', methods: ['GET'])]
    public function list(PlatRepository $repo): JsonResponse
    {
        $plats = $repo->findAll();

        // On renvoie un JSON "safe" (évite les soucis de relations/circular refs)
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
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $titre = $payload['titrePlat'] ?? null;

        if (!$titre || !is_string($titre) || mb_strlen(trim($titre)) < 1) {
            return $this->json(['message' => 'Le titre du plat est obligatoire'], 422);
        }

        $plat = new Plat();

        $plat->setTitrePlat(trim($titre));

        // photo optionnelle (base64) : "photo_base64"
        // Si tu n’en as pas besoin maintenant, tu peux ignorer cette partie.
        $photoBase64 = $payload['photo_base64'] ?? null;
        if (is_string($photoBase64) && $photoBase64 !== '') {
            $binary = base64_decode($photoBase64, true);
            if ($binary === false) {
                return $this->json(['message' => 'photo_base64 invalide (base64 attendu)'], 422);
            }
            // Adapte si ton champ photo est string/blob etc.
            $plat->setPhoto($binary);
        }

        $em->persist($plat);
        $em->flush();

        return $this->json($this->toArray($plat), 201);
    }

    #[Route('/{id}', name: 'api_plat_update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
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

        if (array_key_exists('photo_base64', $payload)) {
            $photoBase64 = $payload['photo_base64'];
            if ($photoBase64 === null || $photoBase64 === '') {
                $plat->setPhoto(null);
            } elseif (is_string($photoBase64)) {
                $binary = base64_decode($photoBase64, true);
                if ($binary === false) {
                    return $this->json(['message' => 'photo_base64 invalide (base64 attendu)'], 422);
                }
                $plat->setPhoto($binary);
            } else {
                return $this->json(['message' => 'photo_base64 doit être une string ou null'], 422);
            }
        }

        $em->flush();

        return $this->json($this->toArray($plat));
    }

    #[Route('/{id}', name: 'api_plat_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
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
        $id = $plat->getPlatId();
        $titre = $plat->getTitrePlat();

        // Photo: pour éviter d’envoyer un blob énorme, on renvoie juste un booléen
        $hasPhoto = false;
        if (method_exists($plat, 'getPhoto')) {
            $p = $plat->getPhoto();
            $hasPhoto = $p !== null && $p !== '';
        }

        return [
            'id' => $id,
            'titre_plat' => $titre,
            'has_photo' => $hasPhoto,
        ];
    }
}