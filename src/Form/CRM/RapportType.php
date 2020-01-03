<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RapportType extends AbstractType
{

    protected $type;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->type = $options['type'];

        $builder
            ->add('nom', TextType::class, array(
        		'label' => 'Nom du rapport',
            	'required' => true,
            	'attr' => array(
            		'class' => 'input-xxl'
           	 	)
        	))
            ->add('description', TextareaType::class, array(
        		'label' => 'Description',
            	'required' => false,
            	'attr' => array(
            		'class' => 'textarea-xxl'
            	)
        	))
            ->add('filtres', CollectionType::class, array(
                'entry_type' => RapportFilterType::class,
                'entry_options' => array(
                    'type' => $options['type']
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label_attr' => array('class' => 'hidden'),
        ));

        if($this->type == "contact"){
            $builder ->add('emailing', CheckboxType::class, array(
                'label' => 'Cette liste sert à faire un emailing (retirer les adresses email vides, bounces, ne pas contacter)',
                'required' => false
            ));
            $builder ->add('excludeWarnings', CheckboxType::class, array(
                'label' => 'Retirer les warning bounces',
                'required' => false
            ));
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Rapport',
            'type' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_rapport';
    }
}
