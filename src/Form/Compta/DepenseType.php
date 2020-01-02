<?php

namespace App\Form\Compta;

use App\Form\DataTransformer\CompteToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepenseType extends AbstractType
{

	protected $companyId;
	private $manager;
	private $arr_opportuniteSousTraitances;
	private $depenseSousTraitances;

	public function __construct(EntityManagerInterface $manager)
	{
		$this->manager = $manager;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->companyId = $options['companyId'];
        $this->arr_opportuniteSousTraitances = $options['arr_opportuniteSousTraitances'];
        $this->depenseSousTraitances = $options['depenseSousTraitances'];

        $builder
             ->add('compte_name', TextType::class, array(
             		'required' => true,
             		'mapped' => false,
             		'label' => 'Fournisseur',
             		'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off')
             ))
             ->add('compte', HiddenType::class, array(
             		'required' => false,
             		'attr' => array('class' => 'entity-compte')
             ))
             ->add('analytique', EntityType::class, array(
             		'class'=> 'App\Entity\Settings',
             		'required' => true,
             		'label' => 'Analytique',
             		'choice_label' => 'valeur',
             		'query_builder' => function(EntityRepository $er) {
             			return $er->createQueryBuilder('s')
             			->where('s.company = :company')
             			->andWhere('s.module = :module')
             			->andWhere('s.parametre = :parametre')
             			->andWhere('s.valeur is not null')
             			->setParameter('company', $this->companyId)
             			->setParameter('module', 'CRM')
             			->setParameter('parametre', 'ANALYTIQUE');
             		},
             ))
             ->add('date', DateType::class, array('widget' => 'single_text',
             		'input' => 'datetime',
             		'format' => 'dd/MM/yyyy',
             		'attr' => array('class' => 'dateInput'),
             		'required' => true,
             		'label' => 'Date de la dépense'
             ))
			->add('libelle', TextType::class, array(
				'required' => true,
				'label' => 'Libellé'
			))
			->add('numFournisseur', TextType::class, array(
				'required' => false,
				'label' => 'Numéro de facture fournisseur'
			))
			->add('modePaiement', ChoiceType::class, array(
				'required' => true,
				'label' => 'Mode de paiement',
				'attr' => array('class' => 'select-mode-paiement'),
				'choices'  => array(
						'Espèce' => 'Espèce',
						'Chèque' => 'Chèque',
						'Virement' => 'Virement',
						'Paypal' => 'Paypal',
						'CB' => 'CB',
						'Prélèvement' => 'Prélèvement'
				),
			))
			->add('conditionReglement',ChoiceType::class, array(
				'required' => true,
				'label' => 'Condition de règlement',
				'attr' => array('class' => 'select-condition-reglement'),
				'choices'  => array(
					'reception' => 'A réception',
					'30' => '30 jours',
					'30finMois' => '30 jours fin de mois',
					'45' => '45 jours',
					'45finMois' => '30 jours fin de mois',
					'60' => '60 jours',
					'60finMois' => '60 jours fin de mois'
				),
			))
			->add('numCheque', TextType::class, array(
				'required' => false,
				'label' => 'Numéro du chèque',
				'attr' => array('class' => 'input-num-cheque'),
			))
			->add('lignes', CollectionType::class, array(
             		'entry_type' => LigneDepenseType::class,
             		'entry_options' => array(
             		    'companyId' => $this->companyId
                    ),
             		'allow_add' => true,
             		'allow_delete' => true,
             		'by_reference' => false,
             		'label_attr' => array('class' => 'hidden')
             ))
			->add('taxe', NumberType::class, array(
				'required' => false,
				'scale' => 2,
				//'label_attr' => array('class' => 'hidden'),
				'attr' => array('class' => 'depense-taxe'),
				'disabled' => true,
				'label' => 'TVA'
			))
			->add('totalHT', NumberType::class, array(
				'required' => false,
				'label' => 'Total HT',
				'scale' => 2,
				'mapped' => false,
				'disabled' => true,
				'attr' => array('class' => 'depense-total-ht')
			))
			->add('totalTTC', NumberType::class, array(
				'required' => false,
				'label' => 'Total TTC',
				'scale' => 2,
				'mapped' => false,
				'disabled' => true,
				'attr' => array('class' => 'depense-total-ttc')
			))
			->add('opportuniteSousTraitances', EntityType::class, array(
				 'class'=> 'App\Entity\CRM\opportuniteSousTraitance',
				 'required' => false,
				 'label' => 'Sous-traitance',
				 'choice_label' => 'NomEtMontant',
				 'choices' => $this->arr_opportuniteSousTraitances,
				 'multiple' => true,
				 'expanded' => false,
				 'attr' => array('class' => 'select-sous-traitances'),
				 'mapped' => false,
				 'data' => $this->depenseSousTraitances

			))
        ;

		$builder->get('compte')
            ->addModelTransformer(new CompteToIdTransformer($this->manager));

		// $builder->get('opportuniteSousTraitance')
		// 	->addModelTransformer(new SousTraitanceToIdTransformer($this->manager));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\Depense',
            'companyId' => null,
            'arr_opportuniteSousTraitances' => null,
            'depenseSousTraitances' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_depense';
    }
}
