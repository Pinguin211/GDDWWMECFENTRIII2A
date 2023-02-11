<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Department;
use App\Entity\Location;
use App\Entity\Offer;
use App\Entity\Region;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OfferType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('week_hours', IntegerType::class)
            ->add('net_salary', IntegerType::class)
            ->add('description', TextareaType::class)
            ->add('location_type', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Votre adresse' => 1,
                    'Ville' => 2,
                    'Département' => 3,
                    'Region' => 4
                ]
            ])
            ->add('location_id', ChoiceType::class, [
                'mapped' => false, 'choices' => ['Votre Adresse' => 0],
                'constraints' => [new NotBlank()]
            ])
            ->add('Soumettre', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $loc_id = $data['location_id'] ?? 0;
                $loc_type = $data['location_type'];
                $pass = false;
                if ($loc_type > 1) {
                    switch ($loc_type) {
                        case 2:
                            $class = City::class;
                            break;
                        case 3:
                            $class = Department::class;
                            break;
                        case 4:
                            $class = Region::class;
                            break;
                    }
                    if ($this->entityManager->getRepository($class)->findOneBy(['id' => $loc_id]))
                        $pass = true;
                } else if ($loc_type === 0)
                    $pass = true;
                if ($pass && $loc_id) {
                    $event->getForm()->add('location_id', ChoiceType::class, ['choices' => [$loc_id => $loc_id], 'mapped' => false]);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
        ]);
    }
}
