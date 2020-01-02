<?php

namespace App\Form\NDF;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteFraisType extends AbstractType
{
    protected $arr_recus;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->arr_recus = $options['arr_recus'];

        $ndf = $builder->getData();

        $builder
          ->add('recus', ChoiceType::class, array(
            'mapped' => false,
            'label' => 'Choisir les reçus',
            'choices' => $this->arr_recus,
            'multiple' => true,
            'attr' => array('class' => 'select-recus'),
            'data' => $ndf->getRecusId()
          ))
          ->add('signatureEmploye', CheckboxType::class, array(
              'label'    => 'Je certifie que ces informations sont exactes et signe la note de frais.',
              'required' => false,
          ))
          ->add('draft', SubmitType::class, array(
            'label' => 'Enregistrer comme brouillon'
          ))
          ->add('validate', SubmitType::class, array(
            'label' => 'Enregistrer et transmettre à la compta'
          ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\NDF\NoteFrais',
            'arr_recus' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ndf_notefrais';
    }
}
