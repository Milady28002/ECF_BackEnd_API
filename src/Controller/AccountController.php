<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/account')]
final class AccountController extends AbstractController
{
    #[Route('/me', name: 'api_account_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        return $this->json([
            'id' => $user->getUtilisateurId(),
            'name' => $user->getName(),
            'firstname' => $user->getFirstname(),
            'email' => $user->getEmail(),
            'adressePostale' => $user->getAdressePostale(),
            'ville' => $user->getVille(),
            'pays' => $user->getPays(),
            'telephone' => $user->getTelephone(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/edit', name: 'api_account_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        #[CurrentUser] ?Utilisateur $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (isset($data['firstName'])) {
            $user->setFirstname(trim((string) $data['firstName']));
        }

        if (isset($data['lastName'])) {
            $user->setName(trim((string) $data['lastName']));
        }

        if (isset($data['telephone'])) {
            $user->setTelephone(trim((string) $data['telephone']));
        }

        if (isset($data['adressePostale'])) {
            $user->setAdressePostale(trim((string) $data['adressePostale']));
        }

        if (isset($data['ville'])) {
            $user->setVille(trim((string) $data['ville']));
        }

        if (isset($data['pays'])) {
            $user->setPays(trim((string) $data['pays']));
        }

        if (!empty($data['password'])) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $data['password'])
            );
        }

        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}