<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Viaje;
use App\Entity\ViajeSolicitud;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ViajeSolicitudType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*$builder
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('viaje', EntityType::class, [
                'class' => Viaje::class,
                'choice_label' => 'id',
            ])
            ->add('pasajero', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;*/
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ViajeSolicitud::class,
        ]);
    }
}
