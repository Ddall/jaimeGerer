<?php

namespace App\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompteComptableType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('num', TextType::class, array(
        		'required' => true,
            	'label' => 'Numéro (8 caractères maximum)',
                'attr' => array('maxlength' => 8)
        	))
            ->add('nom', TextType::class, array(
        		'required' => true,
            	'label' => 'Libellé',
        	))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\CompteComptable'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_comptecomptable';
    }
}
