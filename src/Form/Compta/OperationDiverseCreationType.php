<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationDiverseCreationType extends AbstractType
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
            ->add('date', DateType::class, array('widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'dateInput'),
                    'required' => true,
                    'label' => 'Date de l\'opération'
             ))
            ->add('libelle', TextType::class, array(
                'required' => true,
                'label' => 'Libellé'
            ))
            ->add('debit', NumberType::class, array(
                'mapped' => false
            ))
            ->add('credit', NumberType::class, array(
                'mapped' => false
            ))
            ->add('compteComptableDebit', EntityType::class, array(
    			'required' => false,
    			'class' => 'App:Compta\CompteComptable',
    			'label' => 'Compte comptable',
                'mapped' => false,
    			'query_builder' => function (EntityRepository $er) {
    				return $er->createQueryBuilder('c')
    				->andWhere('c.company = :company')
    				->setParameter('company', $this->companyId)
    				->orderBy('c.num', 'ASC');
    			}
        	))
            ->add('compteComptableCredit', EntityType::class, array(
                'required' => false,
                'class' => 'App:Compta\CompteComptable',
                'label' => 'Compte comptable',
                'mapped' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                    ->andWhere('c.company = :company')
                    ->setParameter('company', $this->companyId)
                    ->orderBy('c.num', 'ASC');
                }
            ))
            ->add('commentaire', TextType::class, array(
                'required' => false,
                'label' => 'Commentaire'
            ))
            ->add('pieceType', ChoiceType::class, array(
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => array(
                    'NONE' => 'Aucune pièce',
                    'FACTURE' => 'Facture',
                    'DEPENSE' => 'Dépense',
                    'AVOIR-CLIENT' => 'Avoir client',
                    'AVOIR-FOURNISSEUR' => 'Avoir fournisseur'
                ),
                'mapped' => false,
                'label' => 'Type de pièce',
                'attr' => array('class' => 'piece-type')
            ))
            ->add('piece', TextType::class, array(
                'required' => false,
                'label' => 'Pièce (numéro)',
                'mapped' => false,
                'attr' => array('class' => 'typeahead-piece', 'autocomplete' => 'off'),
                'disabled' => true
            ))
            ->add('facture', EntityType::class, array(
                'attr' => array('class' => 'entity-facture hidden'),
                'class' => 'App:CRM\DocumentPrix',
                'required' => false,
                'label_attr'=> array('class' => 'hidden')
            ))
            ->add('depense', EntityType::class, array(
                'attr' => array('class' => 'entity-depense hidden'),
                'class' => 'App:Compta\Depense',
                'required' => false,
                'label_attr'=> array('class' => 'hidden')
            ))
            ->add('avoir', EntityType::class, array(
                'attr' => array('class' => 'entity-avoir hidden'),
                'class' => 'App:Compta\Avoir',
                'required' => false,
                'label_attr'=> array('class' => 'hidden')
            ))
            ->add('submit',SubmitType::class, array(
                    'label' => 'Enregistrer',
                    'attr' => array('class' => 'btn btn-success')
            ));
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\OperationDiverse',
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_operationdiverse';
    }
}
