<?php

namespace App\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampagneContenuType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array(
            	'required' => true,
            	'label' => 'Contenu (fichier HTML)',
                'mapped' => false,
                'attr' => array('class' => 'file-input')
        	))
            ->add('preview', SubmitType::class, array(
                'label' => 'Aperçu',
                'attr' => array('class' => 'btn btn-info preview btn-lg')
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Suite',
                'attr' => array('class' => 'btn btn-success btn-lg')
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
        return 'appbundle_emailing_campagne_contenu';
    }

}
