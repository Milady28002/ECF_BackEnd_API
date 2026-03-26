<?php

namespace App\Controller;

use App\Entity\ImageGalerie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/galerie')]
final class ImageGalerieController extends AbstractController
{
    #[Route('', name: 'api_galerie_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $images = $entityManager->getRepository(ImageGalerie::class)->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        $data = array_map(
            fn(ImageGalerie $image) => $this->serializeImage($image),
            $images
        );

        return $this->json($data);
    }

    #[Route('', name: 'api_galerie_create', methods: ['POST'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $titre = trim((string) ($payload['titre'] ?? ''));
        $url = trim((string) ($payload['url'] ?? ''));
        $categorie = trim((string) ($payload['categorie'] ?? ''));

        if ($titre === '' || $url === '' || $categorie === '') {
            return $this->json([
                'message' => 'Les champs titre, url et categorie sont obligatoires.'
            ], 422);
        }

        $categoriesAutorisees = ['sale', 'sucre', 'cocktail'];

        if (!in_array($categorie, $categoriesAutorisees, true)) {
            return $this->json([
                'message' => 'Catégorie invalide.'
            ], 422);
        }

        $image = new ImageGalerie();
        $image->setTitre($titre);
        $image->setUrl($url);
        $image->setCategorie($categorie);
        $image->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($image);
        $entityManager->flush();

        return $this->json($this->serializeImage($image), 201);
    }

    #[Route('/{id}', name: 'api_galerie_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $image = $entityManager->getRepository(ImageGalerie::class)->find($id);

        if (!$image) {
            return $this->json(['message' => 'Image introuvable'], 404);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (array_key_exists('titre', $payload)) {
            $titre = trim((string) $payload['titre']);

            if ($titre === '') {
                return $this->json(['message' => 'Le titre ne peut pas être vide.'], 422);
            }

            $image->setTitre($titre);
        }

        if (array_key_exists('url', $payload)) {
            $url = trim((string) $payload['url']);

            if ($url === '') {
                return $this->json(['message' => 'L’url ne peut pas être vide.'], 422);
            }

            $image->setUrl($url);
        }

        if (array_key_exists('categorie', $payload)) {
            $categorie = trim((string) $payload['categorie']);
            $categoriesAutorisees = ['sale', 'sucre', 'cocktail'];

            if (!in_array($categorie, $categoriesAutorisees, true)) {
                return $this->json(['message' => 'Catégorie invalide.'], 422);
            }

            $image->setCategorie($categorie);
        }

        $entityManager->flush();

        return $this->json($this->serializeImage($image));
    }

    #[Route('/{id}', name: 'api_galerie_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EMPLOYE')]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $image = $entityManager->getRepository(ImageGalerie::class)->find($id);

        if (!$image) {
            return $this->json(['message' => 'Image introuvable'], 404);
        }

        $entityManager->remove($image);
        $entityManager->flush();

        return $this->json(['message' => 'Image supprimée avec succès.']);
    }

    private function serializeImage(ImageGalerie $image): array
    {
        return [
            'id' => $image->getImageId(),
            'titre' => $image->getTitre(),
            'url' => $image->getUrl(),
            'categorie' => $image->getCategorie(),
            'created_at' => $image->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}