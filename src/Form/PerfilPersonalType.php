<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\PerfilPersonal;
use App\Entity\User;
use App\Repository\LocationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PerfilPersonalType extends AbstractType
{
    public function __construct(
        private Security $security
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        
        $builder
            ->add('nombre', null, [
                'label' => 'Nombre'
            ])
            ->add('Apellidos', null, [
                'label' => 'Apellidos'
            ])
            ->add('fechaNacimiento', null, [
                'widget' => 'single_text',
                'label' => 'Fecha de nacimiento'
            ])
            ->add('telefono', null, [
                'label' => 'Teléfono'
            ])
            /*->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])*/
            ->add('homeLocation', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'address', // máis amigable que id
                'required' => false,          // permite null
                'placeholder' => 'Seleccionar localización *',
                'label' => 'Localización Personal',
                'query_builder' => fn(LocationRepository $repo) =>
                    $repo->createQueryBuilderByUser($user),                
            ])
            ->add('workLocation', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'address', 
                'required' => false,
                'placeholder' => 'Seleccionar localización *',
                'label' => 'Localización de Trabajo/Estudio',
                'query_builder' => fn(LocationRepository $repo) =>
                    $repo->createQueryBuilderByUserOrNull($user),                 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PerfilPersonal::class,
        ]);
    }
}
