<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemiseChequeType extends AbstractType
{

	protected $companyId;
	protected $arr_cheque_pieces;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->companyId = $options['companyId'];
        $this->arr_cheque_pieces = $options['arr_cheque_pieces'];

        $builder
            ->add('total', NumberType::class, array(
           			'required' => false,
           			'label' => 'Total (€)',
           			'scale' => 2,
           			'mapped' => false,
           			'disabled' => true,
           			'attr' => array('class' => 'remise-cheque-total-input')
           	))
            ->add('compteBancaire', EntityType::class, array(
        			'required' => true,
        			'class' => 'App:Compta\CompteBancaire',
        			'label' => 'Compte bancaire',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
        				->andWhere('c.company = :company')
        				->setParameter('company', $this->companyId);
        			},
        			'attr' => array('class' => 'compte-select')
        	))
        	->add('date', DateType::class, array('widget' => 'single_text',
        			'input' => 'datetime',
        			'format' => 'dd/MM/yyyy',
        			'attr' => array('class' => 'dateInput dateCreationInput'),
        			'required' => true,
        			'label' => 'Date de la remise de chèque',
        	))
        	->add('cheques', CollectionType::class, array(
        			'entry_type' => ChequeType::class,
        			'entry_options' => array(
        			    'companyId' => $this->companyId,
                        'arr_cheque_pieces' => $this->arr_cheque_pieces,
                    ),
        			'allow_add' => true,
        			'allow_delete' => true,
        			'by_reference' => false,
        			'label_attr' => array('class' => 'hidden'),
        	))
        	->add('submit',SubmitType::class, array(
        			'label' => 'Enregistrer',
        			'attr' => array('class' => 'btn btn-success')
        	))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\RemiseCheque',
            'companyId' => null,
            'arr_cheque_pieces' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_remisecheque';
    }
}
