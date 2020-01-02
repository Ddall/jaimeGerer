<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpportuniteType extends AbstractType
{
	protected $userGestionId;
	protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->userGestionId = $options['userGestionId'];
        $this->companyId = $options['companyId'];

        $builder
            ->add('nom', TextType::class, array(
        		'label' => 'Nom de l\'opportunité'
        	   ))
            ->add('montant',MoneyType::class, array(
            	'required' => true,
            	'label' => 'Montant HT',
            	'attr' => array('class' => 'opp-montant')
        	   ))
            ->add('probabilite', EntityType::class, array(
        		'class'=>'App:Settings',
        		'choice_label' => 'valeur',
        		'query_builder' => function (EntityRepository $er) {
        			return $er->createQueryBuilder('s')
        			->where('s.parametre = :parametre')
        			->andWhere('s.company = :company')
        			->setParameter('parametre', 'OPPORTUNITE_STATUT')
        			->setParameter('company', $this->companyId);
        		},
        		'required' => true,
        		'label' => 'Probabilité',
        		'attr' => array('class' => 'opp-probabilite')
            ))
    	    ->add('type', ChoiceType::class, array(
    	  		'label' => 'Type',
    	  		'choices' => array(
    	  				'Existing Business' => 'Compte existant',
    	  				'New Business' => 'Nouveau compte',
    	  		),
    	  		'required' => true
        	))
		    ->add('compte_name', TextType::class, array(
        		'required' => true,
        		'mapped' => false,
        		'label' => 'Organisation',
        		'attr' => array('class' => 'typeahead-compte')
	        ))

	        ->add('compte', HiddenType::class, array(
        		'required' => true,
        		'attr' => array('class' => 'entity-compte'),
	        ))
	        ->add('contact_name', TextType::class, array(
        		'required' => false,
        		'mapped' => false,
        		'label' => 'Contact',
        		'attr' => array('class' => 'typeahead-contact')
	        ))

	        ->add('contact', HiddenType::class, array(
        		'required' => false,
        		'attr' => array('class' => 'entity-contact'),
	        ))
	        ->add('userGestion', EntityType::class, array(
       			'class'=>'App:User',
       			'required' => true,
       			'label' => 'Gestionnaire de l\'opportunite',
       			'query_builder' => function (EntityRepository $er) {
   				return $er->createQueryBuilder('u')
       				->where('u.company = :company')
       				->andWhere('u.enabled = :enabled')
       				->orWhere('u.id = :id')
       				->orderBy('u.firstname', 'ASC')
       				->setParameter('company', $this->companyId)
       				->setParameter('enabled', 1)
       				->setParameter('id', $this->userGestionId);
   				},
           ))
   		   ->add('origine', EntityType::class, array(
   				'class'=>'App:Settings',
   				'choice_label' => 'valeur',
   				'query_builder' => function (EntityRepository $er) {
   					return $er->createQueryBuilder('s')
   					->where('s.parametre = :parametre')
   					->andWhere('s.company = :company')
   					->orderBy('s.valeur', 'ASC')
   					->setParameter('parametre', 'ORIGINE')
   					->setParameter('company', $this->companyId);
   				},
   				'required' => false,
   				'label' => 'Origine'
       		))
       		->add('caAttendu',MoneyType::class, array(
   				'mapped' => false,
   				'label' => 'Chiffre d\'affaires attendu',
   				'disabled' => true,
   				'attr' => array('class' => 'opp-ca-attendu')
       		))
			->add('appelOffre', CheckboxType::class, array(
				'label' => 'Appel d\'offre',
				'required' => false,
			))
            ->add('priveOrPublic', ChoiceType::class, array(
                'label' => 'Privé ou public ?',
                'required' => true,
                'choices' => array(
                    'PUBLIC' => 'Public',
                    'PRIVE' => 'Privé'
                )
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
					->setParameter('company', $this->companyId)
					->setParameter('module', 'CRM')
					->setParameter('parametre', 'ANALYTIQUE');
				},
			))
            ->add('date', DateType::class, array(
                'years' => range(date('Y')-2, date('Y')+10),
                'days' => array(1),
                'required' => true,
                'input' => 'datetime',
                'widget' => 'choice',
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Opportunite',
            'userGestionId' => null,
            'companyId' => null,
        ));

    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_opportunite';
    }
}
