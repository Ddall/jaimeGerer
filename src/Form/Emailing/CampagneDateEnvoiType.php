<?php

namespace App\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampagneDateEnvoiType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateEnvoi', DateTimeType::class, array(
                'required' => true,
                'label' => 'Date d\'envoi',
                'input' => 'datetime',
                'widget' => 'choice',
                'minutes' => array(0,15,30,45),
                'years' => range(date('Y'), date('Y')+1),
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
        return 'appbundle_emailing_campagne_dateenvoi';
    }

}
