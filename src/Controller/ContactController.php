<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'app_api_')]
class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact', methods: ['POST'])]
    #[OA\Post(
        path: '/api/contact',
        summary: 'Envoyer un message de contact'
    )]
    public function sendContactMessage(
        Request $request,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json([
                'error' => 'Données invalides.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $titre = trim($data['titre'] ?? '');
        $email = trim($data['email'] ?? '');
        $message = trim($data['message'] ?? '');

        if (empty($titre) || empty($email) || empty($message)) {
            return $this->json([
                'error' => 'Tous les champs sont obligatoires.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Adresse email invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $mail = (new Email())
                ->from('formulaire@vitegourmand.fr')
                ->to('contact@vitegourmand.fr')
                ->replyTo($email)
                ->subject('Nouveau message de contact : ' . $titre)
                ->html('
                    <h2>Nouveau message de contact</h2>
                    <p><strong>Titre :</strong> ' . htmlspecialchars($titre) . '</p>
                    <p><strong>Email :</strong> ' . htmlspecialchars($email) . '</p>
                    <p><strong>Message :</strong></p>
                    <p>' . nl2br(htmlspecialchars($message)) . '</p>
                ');

            $mailer->send($mail);

            return $this->json([
                'message' => 'Votre message a bien été envoyé.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Impossible d\'envoyer le message.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}