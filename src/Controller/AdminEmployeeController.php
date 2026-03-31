<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/employees', name: 'app_api_admin_employees_')]
class AdminEmployeeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/admin/employees',
        summary: "Créer un compte employé"
    )]
    public function createEmployee(
        Request $request,
        MailerInterface $mailer
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        if (
            !is_array($data) ||
            empty($data['firstName']) ||
            empty($data['lastName']) ||
            empty($data['email']) ||
            empty($data['password'])
        ) {
            return new JsonResponse(
                ['message' => 'Email et mot de passe obligatoires.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $email = trim((string) $data['email']);
        $password = (string) $data['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(
                ['message' => 'Adresse email invalide.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$this->isPasswordStrong($password)) {
            return new JsonResponse(
                ['message' => 'Mot de passe trop faible.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $existingUser = $this->manager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser) {
            return new JsonResponse(
                ['message' => 'Cet email est déjà utilisé.'],
                Response::HTTP_CONFLICT
            );
        }

        $employeeRole = $this->manager
            ->getRepository(Role::class)
            ->findOneBy(['libelle' => 'ROLE_EMPLOYE']);

        if (!$employeeRole) {
            return new JsonResponse(
                ['message' => 'Le rôle EMPLOYE est introuvable en base.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Création employé
        $employee = new Utilisateur();
        $employee->setName(trim((string) $data['lastName']));
        $employee->setFirstname(trim((string) $data['firstName']));
        $employee->setEmail($email);
        $employee->setTelephone('Non renseigne');
        $employee->setVille('Non renseignee');
        $employee->setPays('France');
        $employee->setAdressePostale('Non renseignee');
        $employee->setPassword(
            $this->passwordHasher->hashPassword($employee, $password)
        );
        $employee->setApiToken(bin2hex(random_bytes(32)));
        $employee->setIsActive(true);
        $employee->addRole($employeeRole);

        $this->manager->persist($employee);
        $this->manager->flush();

        // Envoi du mail
        try {
            $emailMessage = (new Email())
                ->from('no-reply@vite-gourmand.fr')
                ->to($employee->getEmail())
                ->subject('Votre compte employé a été créé')
               
                ->html(sprintf(
    '
                    <body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
                        <div style="max-width:600px; margin:auto; background:#ffffff; padding:30px; border-radius:12px;">
                            
                            <h2 style="color:#7bbd2f;">Vite &amp; Gourmand</h2>
                            <h1 style="color:#008cff;">Compte employé créé</h1>

                            <p>Bonjour %s,</p>

                            <p>Un compte employé a été créé pour vous.</p>

                            <p><strong>Email :</strong> %s</p>

                            <p>
                                Pour obtenir votre mot de passe, merci de vous rapprocher de l’administrateur.
                            </p>

                            <p>À bientôt,<br>L’équipe Vite &amp; Gourmand</p>

                            <p style="font-size:12px;color:#888;">
                                Si vous n’êtes pas à l’origine de cette demande, contactez l’administrateur.
                            </p>

                        </div>
                    </body>
                    ',
                    htmlspecialchars($employee->getFirstname() ?? 'Utilisateur'),
                    htmlspecialchars($employee->getEmail())
                ));

            $mailer->send($emailMessage);
        } catch (\Throwable $e) {
            return new JsonResponse(
                [
                    'message' => 'Employé créé mais erreur lors de l’envoi du mail',
                    'error' => $e->getMessage()
                ],
                Response::HTTP_CREATED
            );
        }

        return new JsonResponse(
            [
                'message' => 'Compte employé créé avec succès.',
                'employee' => [
                    'id' => $employee->getUtilisateurId(),
                    'email' => $employee->getEmail(),
                    'roles' => $employee->getRoles(),
                    'is_active' => $employee->isActive(),
                ]
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function listEmployees(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->manager
            ->getRepository(Utilisateur::class)
            ->findAll();

        $employees = array_filter($users, function (Utilisateur $user) {
            return in_array('ROLE_EMPLOYE', $user->getRoles(), true);
        });

        $data = array_map(function (Utilisateur $employee) {
            return [
                'id' => $employee->getUtilisateurId(),
                'name' => $employee->getName(),
                'firstName' => $employee->getFirstname(),
                'email' => $employee->getEmail(),
                'is_active' => $employee->isActive(),
                'roles' => $employee->getRoles(),
            ];
        }, $employees);

        return new JsonResponse(array_values($data), Response::HTTP_OK);
    }

    #[Route('/{id}/toggle', name: 'toggle', methods: ['PATCH'])]
    public function toggleEmployee(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $employee = $this->manager
            ->getRepository(Utilisateur::class)
            ->find($id);

        if (!$employee) {
            return new JsonResponse(
                ['message' => 'Employé introuvable.'],
                Response::HTTP_NOT_FOUND
            );
        }

        if (!in_array('ROLE_EMPLOYE', $employee->getRoles(), true)) {
            return new JsonResponse(
                ['message' => 'Ce compte n’est pas un employé.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $employee->setIsActive(!$employee->isActive());
        $this->manager->flush();

        return new JsonResponse([
            'message' => $employee->isActive()
                ? 'Compte employé réactivé avec succès.'
                : 'Compte employé désactivé avec succès.',
            'is_active' => $employee->isActive(),
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