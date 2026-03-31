<?php

namespace App\Security;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('X-AUTH-TOKEN');

        if (!$apiToken) {
            throw new AuthenticationException('No API token provided');
        }

        return new SelfValidatingPassport(
            new UserBadge($apiToken, function (string $apiToken) {
                $user = $this->utilisateurRepository->findOneBy([
                'apiToken' => $apiToken
            ]);

            if (!$user) {
                throw new AuthenticationException('Invalid API token');
            }

            if (!$user->isActive()) {
                throw new AuthenticationException('User account is disabled');
            }

            return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            [
                'message' => 'Authentication failed',
                'error' => $exception->getMessage()
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
}