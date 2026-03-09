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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    #[OA\Post(
        path: '/api/registration',
        summary: "Inscription d'un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'Sylvie'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Martin'),
                    new OA\Property(property: 'email', type: 'string', example: 'sylvie@mail.fr'),
                    new OA\Property(property: 'password', type: 'string', example: 'Test123!'),
                    new OA\Property(property: 'telephone', type: 'string', example: '0600000000')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur inscrit",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'string'),
                        new OA\Property(property: 'apiToken', type: 'string'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: "Email déjà utilisé"
            )
        ]
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

        $existingUser = $this->manager
            ->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return new JsonResponse(
                ['message' => 'Cet email est déjà utilisé'],
                Response::HTTP_CONFLICT
            );
        }

        $user = new Utilisateur();
        $user->setFirstname($data['firstName']);
        $user->setName($data['lastName']);
        $user->setEmail($data['email']);
        $user->setTelephone($data['telephone']);
        $user->setVille($data['ville'] ?? 'Non renseignee');
        $user->setPays($data['pays'] ?? 'France');
        $user->setAdressePostale($data['adressePostale'] ?? 'Non renseignee');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $data['password'])
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
        summary: "Connexion d'un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'sylvie@mail.fr'),
                    new OA\Property(property: 'password', type: 'string', example: 'Test123!')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion reussie",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'string'),
                        new OA\Property(property: 'apiToken', type: 'string'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Identifiants invalides"
            )
        ]
    )]
    public function login(#[CurrentUser] ?Utilisateur $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(
                ['message' => 'Missing credentials'],
                Response::HTTP_UNAUTHORIZED
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

    #[Route('/account/me', name: 'me', methods: ['GET'])]
    #[OA\Get(
        path: '/api/account/me',
        summary: "Recuperer les informations du compte",
        security: [
            ["ApiToken" => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Informations utilisateur"
            )
        ]
    )]

    public function me(): JsonResponse
    {
        $user = $this->getUser();
        $responseData = $this->serializer->serialize($user, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/account/edit', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/account/edit',
        summary: "Modifier son compte utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'Nouveau prenom')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Utilisateur modifie avec succes"
            )
        ]
    )]
    public function edit(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            Utilisateur::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $this->getUser()],
        );

        if (isset($request->toArray()['password'])) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPassword())
            );
        }

        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}