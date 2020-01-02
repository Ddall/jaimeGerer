<?php

namespace App\Form\TimeTracker;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TempsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder
            ->add('date', DateType::class, array('widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
                'label' => 'Date',
            ))
            ->add('duree', NumberType::class, array(
                'required' => true,
                'label' => 'Temps passé'
            ))
            ->add('activite', TextType::class, array(
                'required' => false,
                'label' => 'Activité'
            ))
            ->add('projet_name', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'Projet',
                'attr' => array('class' => 'typeahead-projet', 'autocomplete' => 'off')
            ))
            ->add('projet_entity', HiddenType::class, array(
                'required' => true,
                'attr' => array('class' => 'entity-projet'),
                'mapped' => false
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\TimeTracker\Temps'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_timetracker_temps';
    }
}
