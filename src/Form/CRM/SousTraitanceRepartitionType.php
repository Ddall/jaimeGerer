<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SousTraitanceRepartitionType extends AbstractType
{
    protected $opportunite;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->opportunite = $options['opportunite'];
        $builder
            ->add('date', DateType::class, array(
                'years' => range($this->opportunite->getRepartitionStartDate()->format('Y'), $this->opportunite->getRepartitionEndDate()->format('Y')),
                'required' => true,
                'input' => 'datetime',
                'widget' => 'choice',
            ))
            ->add('montantMonetaire', NumberType::class, array(
                'required' => true,
                'attr' => array('class' => 'align-right montant')
            ))
            ->add('fraisMonetaire', NumberType::class, array(
                'required' => false,
                'attr' => array('class' => 'align-right montant')
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\SousTraitanceRepartition',
            'opportunite' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_soustraitancerepartition';
    }
}
