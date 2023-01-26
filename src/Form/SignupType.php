<?php

namespace App\Form;

use App\Entity\User;
use App\Service\RolesInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SignupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(["max" => 255, 'maxMessage' => "L'email doit contenir au maximum 255 caractères"]),
                ]
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'max' => 32,
                        'minMessage' => 'Le mot de passe doit contenir au minimum 8 caractères',
                        'maxMessage' => 'Le mot de passe doit contenir au maximum 32 caractères',
                    ]),
                    new Regex('^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*^', "Le mot de passe doit contenir au minimum 1 minuscule, 1 majuscule et 1 chiffre")
                ],
                    ])
            ->add('roles', HiddenType::class,
                ['empty_data' => []])
            ->add('recruiter', CheckboxType::class,
                ['mapped' => false, 'required' => false])
            ->add('cgu', CheckboxType::class,
                ['mapped' => false])
            ->add('Inscription', SubmitType::class)
        ;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event)
        {
            $event->getData()->addRole($event->getForm()->get('recruiter')->getData() ? RolesInterface::ROLE_RECRUITER : RolesInterface::ROLE_CANDIDATE);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
