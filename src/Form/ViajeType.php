<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Viaje;
use App\Repository\LocationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ViajeType extends AbstractType
{
    public function __construct(
        private Security $security
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        $minDate = new \DateTime('+1 hour');
        $maxDate = new \DateTime('+1 month');        

        $builder
            ->add('fechaHora', DateTimeType::class, [
            'widget' => 'single_text',
            //'data' => $minDate, // valor por defecto opcional
            'attr' => [
                'min' => $minDate->format('Y-m-d\TH:i'),
                'max' => $maxDate->format('Y-m-d\TH:i'),
            ],
        ])
            ->add('plazas', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                ],
                'data' => 2,   
            ])
            /*->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt')*/
            ->add('startLocation', EntityType::class, [
       
                'class' => Location::class,
                'choice_label' => 'address',
                'placeholder' => 'Seleccionar localización *',
                'label' => 'Localización de Origen',
                'query_builder' => fn(LocationRepository $repo) =>
                    $repo->createQueryBuilderByUserOrNull($user),                 
            ])
            ->add('endLocation', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'address',
                'placeholder' => 'Seleccionar localización *',
                'label' => 'Localización de Destino',
                'query_builder' => fn(LocationRepository $repo) =>
                    $repo->createQueryBuilderByUserOrNull($user),                 
            ])
            /*->add('conductor', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Viaje::class,
        ]);
    }
}
