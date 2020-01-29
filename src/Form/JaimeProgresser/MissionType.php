<?php

namespace App\Form\JaimeProgresser;

use App\Entity\JaimeProgresser\Mission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
             ->add('typeMission', EntityType::class, [
                'class' => 'App:JaimeProgresser\TypeMission',
                'label' => 'Type',
                'required' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('themes', EntityType::class, [
                'class' => 'App:JaimeProgresser\Theme',
                'label' => 'Thèmes',
                'required' => false,
                'expanded' => true,
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
