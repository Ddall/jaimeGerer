<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpportuniteRepartitionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if($options['isEdition']){
            $builder->add('date', DateType::class, array(
                'years' => range(date('Y')-2, date('Y')+10),
                'required' => true,
                'input' => 'datetime',
                'widget' => 'choice',
            ));
        } else {
            $builder->add('date', DateType::class, array(
                'years' => range(date('Y')-2, date('Y')+10),
                'required' => true,
                'input' => 'datetime',
                'widget' => 'choice',
                'data' => new \DateTime(date('d-m-Y'))
            ));
        }
        
         
        $builder->add('montantMonetaire', NumberType::class, array(
            'required' => true,
            'attr' => array('class' => 'align-right')
        ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\OpportuniteRepartition',
            'isEdition' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_opportuniterepartition';
    }
}
