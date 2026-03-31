<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    #[OA\Post(
        path: '/api/registration',
        summary: "Inscription d'un utilisateur"
    )]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (
            empty($data['firstName']) ||
            empty($data['lastName']) ||
            empty($data['email']) ||
            empty($data['password']) ||
            empty($data['telephone'])
        ) {
            return new JsonResponse(
                ['message' => 'Champs obligatoires manquants'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $email = trim((string) $data['email']);
        $password = (string) $data['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(
                ['message' => 'Adresse email invalide'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$this->isPasswordStrong($password)) {
            return new JsonResponse(
                [
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $existingUser = $this->manager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser) {
            return new JsonResponse(
                ['message' => 'Cet email est déjà utilisé'],
                Response::HTTP_CONFLICT
            );
        }

        $user = new Utilisateur();
        $user->setFirstname(trim((string) $data['firstName']));
        $user->setName(trim((string) $data['lastName']));
        $user->setEmail($email);
        $user->setTelephone(trim((string) $data['telephone']));
        $user->setVille($data['ville'] ?? 'Non renseignee');
        $user->setPays($data['pays'] ?? 'France');
        $user->setAdressePostale($data['adressePostale'] ?? 'Non renseignee');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );
        $user->setApiToken(bin2hex(random_bytes(32)));

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            [
                'user' => $user->getUserIdentifier(),
                'apiToken' => $user->getApiToken(),
                'roles' => $user->getRoles(),
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: "Connexion d'un utilisateur"
    )]
    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: "Connexion d'un utilisateur"
    )]
    public function login(#[CurrentUser] ?Utilisateur $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(
                ['message' => 'Missing credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if (!$user->isActive()) {
            return new JsonResponse(
                ['message' => 'Ce compte est désactivé.'],
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$user->getApiToken()) {
            $user->setApiToken(bin2hex(random_bytes(32)));
            $this->manager->flush();
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/forgot-password',
        summary: 'Demande de réinitialisation du mot de passe'
    )]
    public function forgotPassword(
        Request $request,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || empty($data['email'])) {
            return new JsonResponse(
                ['message' => 'Email obligatoire'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->manager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.'
            ]);
        }

        $token = bin2hex(random_bytes(32));

        $user->setResetToken($token);
        $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));

        $this->manager->flush();

        $resetLink = 'http://127.0.0.1:3001/#/reset-password?token=' . $token;

        try {
            $email = (new Email())
                ->from('no-reply@vite-gourmand.fr')
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->html(sprintf(
                    '
                    <body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
                    <div style="max-width:600px; margin:0 auto; background:#ffffff; padding:30px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                        <h2 style="color:#7bbd2f; margin-top:0;">Vite &amp; Gourmand</h2>
                        <h1 style="color:#008cff;">Réinitialisation du mot de passe</h1>

                        <p>Bonjour %s,</p>
                        <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                        <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>

                        <p style="margin:24px 0;">
                        <a href="%s" style="background:#7bbd2f; color:#fff; text-decoration:none; padding:12px 20px; border-radius:8px; display:inline-block;">
                            Réinitialiser mon mot de passe
                        </a>
                        </p>

                        <p>Ce lien expire dans 1 heure.</p>
                        <p>Si vous n’êtes pas à l’origine de cette demande, vous pouvez ignorer cet email.</p>
                    </div>
                    </body>
                    ',
                    htmlspecialchars((string) $user->getFirstname()),
                    htmlspecialchars($resetLink)
                ));

            $mailer->send($email);
        } catch (\Throwable $e) {
            return new JsonResponse(
                ['message' => 'Erreur lors de l’envoi de l’email'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse([
            'message' => 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.'
        ]);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/reset-password',
        summary: 'Réinitialisation du mot de passe'
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (
            !is_array($data) ||
            empty($data['token']) ||
            empty($data['password'])
        ) {
            return new JsonResponse(
                ['message' => 'Token et mot de passe obligatoires'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $token = $data['token'];
        $newPassword = $data['password'];

        $user = $this->manager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['resetToken' => $token]);

        if (!$user) {
            return new JsonResponse(
                ['message' => 'Token invalide'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (
            !$user->getResetTokenExpiresAt() ||
            $user->getResetTokenExpiresAt() < new \DateTime()
        ) {
            return new JsonResponse(
                ['message' => 'Le lien de réinitialisation a expiré'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

        if (!preg_match($passwordRegex, $newPassword)) {
            return new JsonResponse(
                ['message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $newPassword)
        );

        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $this->manager->flush();

        return new JsonResponse([
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }

    #[Route('/account', name: 'account', methods: ['GET'])]
    public function account(#[CurrentUser] ?Utilisateur $utilisateur): JsonResponse
    {
        if (!$utilisateur) {
            return $this->json([
                'message' => 'Utilisateur non authentifié'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'telephone' => $utilisateur->getTelephone(),
        ]);
    }

    private function isPasswordStrong(string $password): bool
    {
        return (bool) preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
            $password
        );
    }
}