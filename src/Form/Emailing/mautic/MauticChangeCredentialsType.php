<?php

namespace App\Form\Emailing\mautic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MauticChangeCredentialsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('secretKey', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'Secret Key'
            ))
            ->add('publicKey', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'Public Key'
            ))
            ->add('Enregistrer', SubmitType::class, array());

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_credentials';
    }
}
