<?php

namespace App\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampagneType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, array(
            	'required' => true,
            	'label' => 'Nom de la campagne'
        	))
            ->add('objet', TextType::class, array(
            	'required' => true,
            	'label' => 'Objet de l\'email'
        	)) 
            ->add('nomExpediteur', TextType::class, array(
            	'required' => true,
            	'label' => 'Nom de l\'expéditeur'
        	))
            ->add('emailExpediteur', EmailType::class, array(
            	'required' => true,
            	'label' => 'Email de l\'expéditeur'
        	))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Emailing\Campagne'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_emailing_campagne';
    }

}
