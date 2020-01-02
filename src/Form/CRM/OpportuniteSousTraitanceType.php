<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpportuniteSousTraitanceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $opportuniteSousTraitance = $builder->getData();

        $builder
            ->add('sousTraitant', TextType::class, array(
                'label' => 'Nom du sous-traitant',
                'required' => true
            ))
            ->add('typeForfait', ChoiceType::class, array(
                'choices' => array(
                    'GLOBAL' => 'Forfait global',
                    'JOUR' => 'Forfait jour'
                ),
                'expanded' => true,
                'multiple' => false,
                'label' => 'Type de forfait',
                'required' => true,
                'attr' => array('class' => 'type-forfait')
            ))
            ->add('montantGlobalMonetaire', NumberType::class, array(
                'required' => false,
                'label' => 'Montant du forfait',
                'attr' => array('class' => 'montant')
            ))
            ->add('tarifJourMonetaire', NumberType::class, array(
                'required' => false,
                'label' => 'Tarif par jour',
                'attr' => array('class' => 'montant')
            ))
            ->add('nbJours', NumberType::class, array(
                'required' => false,
                'label' => 'Nombre de jours',
                'attr' => array('class' => 'montant')
            ))
            ->add('repartitions', CollectionType::class, array(
       			'entry_type' => SousTraitanceRepartitionType::class,
       			'entry_options' => array(
       			    'opportunite' => $opportuniteSousTraitance->getOpportunite(),
                ),
       			'allow_add' => true,
       			'allow_delete' => true,
       			'by_reference' => false,
				'label_attr' => array('class' => 'hidden')
           	))
            ->add('add', SubmitType::class, array(
                'label' => 'Ajouter un autre sous-traitant',
                'attr' => array(
                  'class' => 'btn btn-info',
                )
            ))
            ->add('fraisRefacturables', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'data-toggle' => 'toggle',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-on' => 'Oui',
                    'data-off' => 'Non',
                    'class' => 'toggle-frais'
                )
            ))
            ->add('htPrixNet', ChoiceType::class, array(
                'choices' => array(
                    'HT' => 'HT',
                    'Prix net' => 'Prix net'
                ),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => 'HT ou Prix net ?'
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Terminer',
                'attr' => array(
                  'class' => 'btn btn-success',

                )
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\OpportuniteSousTraitance'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_opportunitesoustraitance';
    }
}
