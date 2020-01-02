<?php

namespace App\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MouvementBancaireType extends AbstractType
{
	protected $mouvementBancaire;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->mouvementBancaire = $options['mouvementBancaire'];

        $builder
           ->add('date', DateType::class, array('widget' => 'single_text',
             		'input' => 'datetime',
             		'format' => 'dd/MM/yyyy',
             		'attr' => array('class' => 'dateInput'),
             		'required' => true,
             		'label' => 'Date',
           			'data' => $this->mouvementBancaire->getDate()
             ))
             ->add('montant', NumberType::class, array(
            		'required' => true,
            		'label' => 'Montant (€)',
            		'scale' => 2,
            		'attr' => array('class' => 'montant')
     	   ))
            ->add('libelle', TextType::class, array(
             		'required' => true,
            		'label' => 'Libellé',
            		'data' => $this->mouvementBancaire->getLibelle()
             ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\MouvementBancaire',
            'mouvementBancaire' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_mouvementbancaire';
    }
}
