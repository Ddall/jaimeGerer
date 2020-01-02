<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneJournalCorrectionType extends AbstractType
{

    protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->companyId = $options['companyId'];

        $builder
        ->add('compteComptable', EntityType::class, array(
            'class'=>'App:Compta\CompteComptable',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('c')
              ->where('c.company = :company')
              ->setParameter('company', $this->companyId)
              ->orderBy('c.num');
            },
            'required' => true,
            'label' => 'Compte comptable',
            'attr' => array('class' => 'select-compte-comptable'),
            'mapped' => false
        ))
        ->add('compteNotInList', CheckboxType::class, array(
          'label' => 'Le compte comptable n\'existe pas encore',
          'attr' => array('class' => 'checkbox-not-in-list'),
          'mapped' => false,
          'required'=> false
        ))
        ->add('comptePrefixe', ChoiceType::class, array(
          'choices' => array(
            '401' => '401',
            '411' => '411',
          ),
          'mapped' => false,
          'required'=> false
        ))
        ->add('compteNum', TextType::class, array(
          'attr' => array('class' => 'input-compte-num'),
          'mapped' => false,
          'required'=> false
        ))
        ->add('compteNom', TextType::class, array(
          'attr' => array('class' => 'input-compte-nom'),
          'label' => 'Nom du compte',
          'mapped' => false,
          'required'=> false
        ))
        ->add('corriger', SubmitType::class, array(
          'label' => 'Corriger directement',
          'attr' => array('class' => 'btn btn-success submit-button')
        ))
        ->add('creerOD', SubmitType::class, array(
          'label' => 'Corriger via une OD',
          'attr' => array('class' => 'btn btn-info btn-xs submit-button')
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\JournalBanque',
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_comptecomptablecorrection';
    }
}
