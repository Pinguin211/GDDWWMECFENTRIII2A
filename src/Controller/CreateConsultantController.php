<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Recruter;
use App\Entity\User;
use App\Form\CreateConsultantType;
use App\Service\PathInterface;
use App\Service\RolesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class CreateConsultantController extends AbstractController
{
    #[Route('/create_consultant')]
    public function create_consultant(Request $request, EntityManagerInterface $entityManager,
                           UserPasswordHasherInterface $hasher, RolesInterface $roles,)
    {

        if (!($admin = $this->getUser()) || !$roles->is_admin($admin))
            return $this->redirectToRoute('app_message', ['title' => 'Erreur 404', 'message' => "Cette page n'existe pas", 'redirect_app' => 'app_annonces']);

        //Signup form
        $user = new User();
        $form = $this->createForm(CreateConsultantType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()));
            $entityManager->persist($user);
            $entityManager->flush();
            if ($roles->is_recruter($user))
                $entityManager->persist(new Recruter($user));
            if ($roles->is_candidate($user))
                $entityManager->persist(new Candidate($user));
            $entityManager->flush();
            return $this->redirectToRoute('app_message', ['title' => 'Consultant bien ajouté', 'message' => "Le nouveaux consultants peu désormais se connecter", 'redirect_app' => 'app_profil']);
        }

        return $this->render('create_consultant/index.html.twig', [
            'form' => $form->createView(),
            ]);
    }
}
