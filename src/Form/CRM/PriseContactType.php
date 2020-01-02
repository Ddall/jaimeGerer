<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriseContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$arr_types = array(
    		'PHONE' => 'Téléphone',
    		'RDV' => 'Rendez-vous',
    		'EMAIL' => 'Email',
    		'LETTRE' => 'Courrier',
    		'SOCIAL' => 'Réseaux sociaux',
    	);
    	
        $builder
            ->add('type', ChoiceType::class, array(
        		'choices' => $arr_types,
            	'required' => true,
            	'label' => 'Comment avez-vous pris contact ?'
        ))
            ->add('date', DateType::class, array('widget' => 'single_text',
        			'input' => 'datetime',
        			'format' => 'dd/MM/yyyy',
        			'attr' => array('class' => 'dateInput'),
        			'required' => true,
        			'label' => 'Date de la prise de contact',
            		'data' => new \DateTime()
        	))
            ->add('description', TextareaType::class, array(
        		'required' => true,
            	'label' => 'Description'
        	))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\PriseContact'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_prisecontact';
    }
}
