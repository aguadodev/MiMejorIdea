<?php

namespace App\Form;

use App\Entity\Receta;
use App\Entity\Ingrediente;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\IngredienteRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetaType extends AbstractType
{
    private IngredienteRepository $ingredienteRepository;
    
    public function __construct(IngredienteRepository $ingredienteRepository)
    {
        $this->ingredienteRepository = $ingredienteRepository;
    }
        
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo')
            ->add('ingredientes', EntityType::class, [
                'class' => Ingrediente::class,
                'query_builder' => function (IngredienteRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.nombre', 'ASC');
                },
                'placeholder' => 'Ingredientes?',
                'autocomplete' => true,
                'multiple' => true,
                'required'   => false,
                'tom_select_options' => ["create" => true,
                                         "allowEmptyOption" => true],
                
            ])   
                  
           /* ->add('ingredientes', IngredienteAutocompleteField::class)   */
            ->add('notas')
            //->addEventSubscriber(new RecetaAddNewIngredientListener())
            ->addEventListener(FormEvents::PRE_SUBMIT,[$this,'onPreSubmit'])
        //    ->add('user')
        ;
    }

    public function onPreSubmit(FormEvent $event) : void
    {
        $data = $event->getData();

        // Recorre los ingredientes y crea aquellos que no existan en la base de datos
        if (isset($data['ingredientes'])) {
            $i = 0;
            foreach($data['ingredientes'] as $ingrediente)
            {
                if (!$this->ingredienteRepository->find($ingrediente)){
                    $ingredienteBD = $this->ingredienteRepository->findOneByNombre($ingrediente);
                    if (isset($ingredienteBD)){
                        $nuevoIngrediente = $ingredienteBD;
                    } else {
                        //crear ingrediente
                        $nuevoIngrediente = new Ingrediente();
                        $nuevoIngrediente->setNombre($ingrediente);
                        $this->ingredienteRepository->add($nuevoIngrediente,true);
                    }

                    $data['ingredientes'][$i] = $nuevoIngrediente->getId();
                    $event->setData($data);
                }
                $i++;
            }
    
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Receta::class,
            // Sanea el código HTML que el usuario introduce en el textArea WYSIWYG
            'sanitize_html' => true,
        ]);
    }
}
