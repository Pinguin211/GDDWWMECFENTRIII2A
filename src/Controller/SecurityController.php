<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Recruter;
use App\Entity\User;
use App\Form\SignupType;
use App\Service\RolesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request,
                          EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher,
                            RolesInterface $roles)
    {
        //Login form
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();
        return $this->signup($request, $entityManager, $hasher, $roles, $error, $lastEmail);
    }

    #[Route(path: '/', name: 'app_signup')]
    public function signup(Request $request, EntityManagerInterface $entityManager,
                           UserPasswordHasherInterface $hasher, RolesInterface $roles,
                            $error = false, $lastEmail = '')
    {
        //Signup form
        $user = new User();
        $form = $this->createForm(SignupType::class, $user);
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
        }

        //Stats
        $nb_offers = $entityManager->createQuery('SELECT COUNT(o) FROM App\Entity\Offer o')->getOneOrNullResult()[1];
        $nb_candidates = $entityManager->createQuery('SELECT COUNT(c) FROM App\Entity\Candidate c')->getOneOrNullResult()[1];
        $nb_applies = $entityManager->createQuery('SELECT COUNT(a) FROM App\Entity\AppliedCandidate a')->getOneOrNullResult()[1];

        return $this->render('security/signup.html.twig', [
            'form' => $form->createView(),
            'nb_offers' => $nb_offers,
            'nb_candidates' => $nb_candidates,
            'nb_applies' => $nb_applies,
            ]);
    }
}
