<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Regime;
use App\Entity\Theme;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/menus')]
final class MenuController extends AbstractController
{
    #[Route('', name: 'api_menu_list', methods: ['GET'])]
    public function list(MenuRepository $menuRepository): JsonResponse
    {
        $menus = $menuRepository->findAll();

        $data = array_map(fn (Menu $menu) => $this->serializeMenu($menu), $menus);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_menu_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, MenuRepository $menuRepository): JsonResponse
    {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return $this->json(['message' => 'Menu introuvable'], 404);
        }

        return $this->json($this->serializeMenu($menu));
    }

    #[Route('', name: 'api_menu_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $validationError = $this->validatePayload($payload);
        if ($validationError !== null) {
            return $this->json(['message' => $validationError], 422);
        }

        $regime = $entityManager->getRepository(Regime::class)->find($payload['regime_id']);
        if (!$regime) {
            return $this->json(['message' => 'Régime introuvable'], 404);
        }

        $theme = $entityManager->getRepository(Theme::class)->find($payload['theme_id']);
        if (!$theme) {
            return $this->json(['message' => 'Thème introuvable'], 404);
        }

        $menu = new Menu();
        $menu->setTitre(trim($payload['titre']));
        $menu->setNombrePersonneMinimum((int) $payload['nombre_personne_minimum']);
        $menu->setPrixParPersonne((float) $payload['prix_par_personne']);
        $menu->setDescription(trim($payload['description']));
        $menu->setQuantiteRestante((int) $payload['quantite_restante']);
        $menu->setRegime($regime);
        $menu->setTheme($theme);

        foreach ($payload['plats'] as $platId) {
            $plat = $entityManager->getRepository(Plat::class)->find($platId);

            if (!$plat) {
                return $this->json(['message' => sprintf('Plat introuvable : %s', $platId)], 404);
            }

            $menu->addPlat($plat);
        }

        $entityManager->persist($menu);
        $entityManager->flush();

        return $this->json($this->serializeMenu($menu), 201);
    }

    #[Route('/{id}', name: 'api_menu_update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function update(
        int $id,
        Request $request,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return $this->json(['message' => 'Menu introuvable'], 404);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $validationError = $this->validatePayload($payload);
        if ($validationError !== null) {
            return $this->json(['message' => $validationError], 422);
        }

        $regime = $entityManager->getRepository(Regime::class)->find($payload['regime_id']);
        if (!$regime) {
            return $this->json(['message' => 'Régime introuvable'], 404);
        }

        $theme = $entityManager->getRepository(Theme::class)->find($payload['theme_id']);
        if (!$theme) {
            return $this->json(['message' => 'Thème introuvable'], 404);
        }

        $menu->setTitre(trim($payload['titre']));
        $menu->setNombrePersonneMinimum((int) $payload['nombre_personne_minimum']);
        $menu->setPrixParPersonne((float) $payload['prix_par_personne']);
        $menu->setDescription(trim($payload['description']));
        $menu->setQuantiteRestante((int) $payload['quantite_restante']);
        $menu->setRegime($regime);
        $menu->setTheme($theme);

        foreach ($menu->getPlat()->toArray() as $plat) {
            $menu->removePlat($plat);
        }

        foreach ($payload['plats'] as $platId) {
            $plat = $entityManager->getRepository(Plat::class)->find($platId);

            if (!$plat) {
                return $this->json(['message' => sprintf('Plat introuvable : %s', $platId)], 404);
            }

            $menu->addPlat($plat);
        }

        $entityManager->flush();

        return $this->json($this->serializeMenu($menu));
    }

    #[Route('/{id}', name: 'api_menu_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(
        int $id,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return $this->json(['message' => 'Menu introuvable'], 404);
        }

        $entityManager->remove($menu);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    private function validatePayload(array $payload): ?string
    {
        $requiredFields = [
            'titre',
            'nombre_personne_minimum',
            'prix_par_personne',
            'description',
            'quantite_restante',
            'regime_id',
            'theme_id',
            'plats',
        ];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $payload)) {
                return sprintf('Le champ "%s" est obligatoire', $field);
            }
        }

        if (!is_string($payload['titre']) || trim($payload['titre']) === '') {
            return 'Le titre est obligatoire';
        }

        if (!is_numeric($payload['nombre_personne_minimum']) || (int) $payload['nombre_personne_minimum'] < 1) {
            return 'Le nombre minimum de personnes doit être un entier positif';
        }

        if (!is_numeric($payload['prix_par_personne']) || (float) $payload['prix_par_personne'] < 0) {
            return 'Le prix par personne doit être un nombre positif';
        }

        if (!is_string($payload['description']) || trim($payload['description']) === '') {
            return 'La description est obligatoire';
        }

        if (!is_numeric($payload['quantite_restante']) || (int) $payload['quantite_restante'] < 0) {
            return 'La quantité restante doit être un entier positif ou nul';
        }

        if (!is_numeric($payload['regime_id'])) {
            return 'regime_id doit être un entier';
        }

        if (!is_numeric($payload['theme_id'])) {
            return 'theme_id doit être un entier';
        }

        if (!is_array($payload['plats'])) {
            return 'Le champ plats doit être un tableau d’identifiants';
        }

        return null;
    }

    private function serializeMenu(Menu $menu): array
    {
        $plats = [];

        foreach ($menu->getPlat() as $plat) {
            $plats[] = [
                'id' => $plat->getPlatId(),
                'titre' => $plat->getTitrePlat(),
            ];
        }

        return [
            'id' => $menu->getMenuId(),
            'titre' => $menu->getTitre(),
            'nombre_personne_minimum' => $menu->getNombrePersonneMinimum(),
            'prix_par_personne' => $menu->getPrixParPersonne(),
            'description' => $menu->getDescription(),
            'quantite_restante' => $menu->getQuantiteRestante(),
            'regime' => $menu->getRegime() ? [
                'id' => $menu->getRegime()->getRegimeId(),
                'libelle' => $menu->getRegime()->getLibelle(),
            ] : null,
            'theme' => $menu->getTheme() ? [
                'id' => $menu->getTheme()->getThemeId(),
                'libelle' => $menu->getTheme()->getLibelle(),
            ] : null,
            'plats' => $plats,
        ];
    }
}