<?php

namespace App\Form;

use App\Entity\User;
use App\Service\RolesInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('recruiter', CheckboxType::class,
                ['mapped' => false, 'required' => false])
            ->add('cgu', CheckboxType::class,
                ['mapped' => false])
            ->add('Inscription', SubmitType::class)
        ;
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event)
        {
            $event->getData()->addRole($event->getForm()->get('recruiter')->getData() ? RolesInterface::ROLE_RECRUTER : RolesInterface::ROLE_CANDIDATE);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
