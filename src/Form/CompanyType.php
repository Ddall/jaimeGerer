<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CompanyType extends AbstractType
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
            	'label' => 'Raison sociale'
        	))
            ->add('telephone', TextType::class, array(
            	'required' => false,
            	//'default_region' => 'FR',
            	//'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone'
        	))
            ->add('fax',TextType::class, array(
            	'required' => false,
            	//'default_region' => 'FR',
            	//'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	))
            ->add('adresse', TextType::class, array(
        		'required' => false,
            	'label' => 'Adresse'
        	))
            ->add('codePostal', TextType::class, array(
        		'required' => false,
            	'label' => 'Code postal'
        	))
            ->add('ville', TextType::class, array(
        		'required' => false,
            	'label' => 'Ville'
        	))
            ->add('region', TextType::class, array(
        		'required' => false,
            	'label' => 'Région'
        	))
            ->add('pays', TextType::class, array(
        		'required' => false,
            	'label' => 'Pays'
        	))
        	->add('color', ColorType::class, array(
        			'required' => false,
        			'label' => 'Couleur principale',
        			'attr' => array('class' => 'colorpicker'),
//        			'picker_options' => array('palettes' => true),
        			'empty_data' => '#FFFFFF'
        	))
            ->add('siren', TextType::class, array(
                'required' => true,
                'label' => 'SIREN'
            ))
            ->add('zeroBounceApiKey', TextType::class, array(
                'required' => false,
                'label' => 'API Key ZeroBounce'
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Company'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_company';
    }
}
