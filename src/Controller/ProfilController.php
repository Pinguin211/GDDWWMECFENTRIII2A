<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\RolesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {

        return $this->render('profil/index.html.twig', [
            'error' => false,
            'last_email' => '',
            'email' => 'hello word',
        ]);
    }

    #[Route('/profil_get_user')]
    public function getUserInformation(EntityManagerInterface $entityManager): Response
    {
        $id = $_POST['id'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = $entityManager->getRepository(User::class)->findOneBy(['id'=> $id]);
        if ($user)
        {
            if ($user->getPassword() !== $password)
                return new Response('Bad password', 401);
            return new JsonResponse(json_encode(self::getProfiles($user, $entityManager)));
        }
        else
            return new Response('User not found', 404);

    }
    private static function getProfiles(User $user, EntityManagerInterface $entityManager)
    {
        return [
            'candidate' => self::getProfile($user, $entityManager, 'Candidate'),
            'recruter' => self::getProfile($user, $entityManager, 'Recruter'),
            'consultant' => $user->haveRole(RolesInterface::ROLE_CONSULTANT),
            'admin' => $user->haveRole(RolesInterface::ROLE_ADMIN)
        ];

    }
    private static function getProfile(User $user, EntityManagerInterface $entityManager, string $profile)
    {
        $func = "get$profile";
        $profile = $user->$func($entityManager);
        if (!$profile)
            return $profile;
        else
            return $profile->getValueAsArray();
    }
}
