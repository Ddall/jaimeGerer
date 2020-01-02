<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        	->add('parametre', TextType::class, array(
        			'required' => true,
        			'label' => 'Parametre',
        			'attr' => array('class' => 'hidden'),
        			'label_attr' => array('class' => 'hidden')
        	))
        	->add('module', TextType::class, array(
        			'required' => true,
        			'label' => 'Module',
        			'attr' => array('class' => 'hidden'),
        			'label_attr' => array('class' => 'hidden')
        	));	
        	
        	$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    }
    
    public function onPreSetData(FormEvent $event){
    	$settings = $event->getData();
    	$form = $event->getForm();
    	
    	if($settings->getType() == 'TEXTE'){
    		$form->add('valeur', TextareaType::class, array(
    				'required' => true,
    				'label' => 'Valeur',
    				'label_attr' => array('class' => 'hidden')
    		));
    	} else if($settings->getType() == 'IMAGE'){
    		$form->add('file', FileType::class, array(
    				'label'	=> 'Image',
    				'required' => true
    		));
    	} else {
    		$form->add('valeur', TextType::class, array(
    				'required' => true,
    				'label' => 'Valeur',
    				'label_attr' => array('class' => 'hidden')
    		));

    	}
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Settings'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_settings';
    }
}
