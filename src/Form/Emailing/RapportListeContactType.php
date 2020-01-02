<?php

namespace App\Form\Emailing;

use App\Form\CRM\RapportFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RapportListeContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, array(
        		'label' => 'Nom de la liste',
            	'required' => true,
            	'attr' => array(
            		'class' => 'input-xxl'
           	 	)
        	))
            ->add('description', TextareaType::class, array(
        		'label' => 'Description',
            	'required' => true,
            	'attr' => array(
            		'class' => 'textarea-xxl'
            	)
        	))
        	->add('filtres', CollectionType::class, array(
                'entry_type' => RapportFilterType::class,
                'entry_options' => array(
                    'type' => 'contact'
                ),
				'label' => 'Filtres',
				'allow_add' => true,
				'allow_delete' => true,
				'by_reference' => false,
				'label_attr' => array('class' => 'hidden'),
				'mapped' => false
			));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Rapport'
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
