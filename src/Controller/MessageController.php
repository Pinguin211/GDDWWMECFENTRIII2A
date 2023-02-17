<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function MessagePage(): Response
    {
        return $this->render('message.html.twig', [
            'title' => $_GET['title'] ?? 'Erreur',
            'message' => $_GET['message'] ?? "Revenir à l'accueil",
            'redirect_app' => $_GET['redirect_app'] ?? 'app_signup',
            'is_url' => $_GET['is_url'] ?? false,
        ]);
    }
}
