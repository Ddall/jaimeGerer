<?php

namespace App\Form\CRM;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactureType extends AbstractType
{
	protected $userGestionId;
	protected $companyId;
    protected $compte;


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->userGestionId = $options['userGestionId'];
        $this->companyId = $options['companyId'];
        $this->compte = $options['compte'];

        $nomFacturation = null;
        $adresse = null;
        $adresseLigne2 = null;
        $codePostal = null;
        $ville = null;
        $pays = null;
        if($this->compte){
            $nomFacturation = $this->compte->getNomFacturation()?$this->compte->getNomFacturation():$this->compte->getNom();
            $adresse = $this->compte->getAdresseFacturation()?$this->compte->getAdresseFacturation():$this->compte->getAdresse();
            $adresseLigne2 = $this->compte->getAdresseFacturationLigne2()?$this->compte->getAdresseFacturationLigne2():'';
            $codePostal = $this->compte->getCodePostalFacturation()?$this->compte->getCodePostalFacturation():$this->compte->getCodePostal();
            $ville = $this->compte->getVilleFacturation()?$this->compte->getVilleFacturation():$this->compte->getVille();
            $pays = $this->compte->getPaysFacturation()?$this->compte->getPaysFacturation():$this->compte->getPays();
        }

        $builder
            ->add('objet', TextType::class, array(
        		'required' => true,
            	'label' => 'Objet'
        	))
            ->add('dateValidite', DateType::class, array('widget' => 'single_text',
    			'input' => 'datetime',
    			'format' => 'dd/MM/yyyy',
    			'attr' => array('class' => 'dateInput'),
    			'required' => true,
    			'label' => 'Date d\'échéance'
        	))
        	->add('dateCreation', DateType::class, array('widget' => 'single_text',
    			'input' => 'datetime',
    			'format' => 'dd/MM/yyyy',
    			'attr' => array('class' => 'dateInput dateCreationInput'),
    			'required' => true,
    			'label' => 'Date de la facture',
        	))
        	->add('numBCClient', TextType::class, array(
        		'required' => false,
        		'label' => 'N° bon de commande client'
        	))
           ->add('adresse', TextType::class, array(
        		'required' => true,
            	'label' => 'Adresse',
             	'attr' => array('class' => 'input-adresse'),
                'data' => $adresse
        	))
           ->add('adresseLigne2', TextType::class, array(
               'required' => false,
               'label' => 'Adresse (suite)',
               'attr' => array('class' => 'input-adresse-ligne2'),
               'data' => $adresseLigne2
           ))
           ->add('nomFacturation', TextType::class, array(
               'required' => true,
               'label' => 'Nom de l\'organisation pour la facturation',
               'attr' => array('class' => 'input-nom-facturation'),
               'data' => $nomFacturation
           ))
            ->add('codePostal', TextType::class, array(
        		'required' => true,
            	'label' => 'Code postal',
            	'attr' => array('class' => 'input-codepostal'),
                'data' => $codePostal
        	))
            ->add('ville', TextType::class, array(
        		'required' => true,
            	'label' => 'Ville',
            	'attr' => array('class' => 'input-ville'),
                'data' => $ville
        	))
            ->add('region', TextType::class, array(
        		'required' => true,
            	'label' => 'Région',
            	'attr' => array('class' => 'input-region'),
                'data' => $this->compte ? $this->compte->getRegion() : null
        	))
            ->add('pays', TextType::class, array(
        		'required' => true,
            	'label' => 'Pays',
            	'attr' => array('class' => 'input-pays'),
                'data' => $pays
        	))
            ->add('description', TextareaType::class, array(
        		'required' => false,
            	'label' => 'Commentaire'
        	))
            ->add('remise', NumberType::class, array(
        		'required' => false,
            	'label' => 'Remise',
            	'scale' => 2,
            		'attr' => array('class' => 'facture-remise')
        	))
        	->add('cgv', TextareaType::class, array(
        			'required' => false,
        			'label' => 'Conditions d\'utilisation'
        	))
            ->add('userGestion', EntityType::class, array(
       			'class'=> User::class,
       			'required' => true,
       			'label' => 'Gestionnaire de la facture',
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
             ->add('compte_name', TextType::class, array(
         		'required' => true,
         		'mapped' => false,
         		'label' => 'Organisation',
         		'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off')
             ))
             ->add('compte', HiddenType::class, array(
         		'required' => true,
         		'attr' => array('class' => 'entity-compte')
             ))
		    ->add('contact_name', TextType::class, array(
	        	'required' => true,
	        	'mapped' => false,
	        	'label' => 'Contact',
	        	'attr' => array('class' => 'typeahead-contact', 'autocomplete' => 'off')
		    ))
		    ->add('contact', HiddenType::class, array(
	        	'required' => true,
	        	'attr' => array('class' => 'entity-contact')
		    ))
           	->add('devis', EntityType::class, array(
       			'class'=> 'App\Entity\CRM\DocumentPrix',
       			'required' => false,
       			'label' => 'N° de devis',
       			'choice_label' => 'num',
       			'query_builder' => function(EntityRepository $er) {
       				return $er->createQueryBuilder('d')
       				->leftJoin('App\Entity\CRM\Compte', 'c', 'WITH', 'c.id = d.compte')
					->where('c.company = :company')
					->andWhere('d.type = :type')
       				->setParameter('type', 'DEVIS')
       				->setParameter('company', $this->companyId)
       				->orderBy('d.num', 'ASC');
       			},
//       			'empty_value' => 'Aucun'
           	))
           	->add('produits', CollectionType::class, array(
                'entry_type' => ProduitType::class,
                'entry_options' => array(
                    'companyId' => $this->companyId,
                ),
       			'allow_add' => true,
       			'allow_delete' => true,
       			'by_reference' => false,
       			'label_attr' => array('class' => 'hidden'),
//                'options' => array('type' => 'FACTURE')
           	))
           	->add('sousTotal', NumberType::class, array(
       			'required' => false,
       			'label' => 'Sous total',
       			'scale' => 2,
       			'mapped' => false,
       			'disabled' => true,
       			'attr' => array('class' => 'facture-sous-total')
           	))
           	->add('taxe', NumberType::class, array(
       			'required' => false,
       			'scale' => 2,
       			'label_attr' => array('class' => 'hidden'),
       			'attr' => array('class' => 'facture-taxe'),
       			'disabled' => true,
           	))
           	->add('taxePercent', PercentType::class, array(
       			'required' => false,
       			'type' => 'fractional',
       			'scale' => 2,
       			'label' => 'TVA',
       			'attr' => array('class' => 'facture-taxe-percent'),
           	))
			->add('facturationBelge', NumberType::class, array(
				'required' => false,
				'scale' => 2,
				'label_attr' => array('class' => 'hidden'),
				'attr' => array('class' => 'facturation-belgique'),
				'disabled' => true,
				'mapped' => false
			))
			->add('facturationBelgePercent', PercentType::class, array(
				'required' => false,
				'type' => 'fractional',
				'scale' => 2,
				'label' => 'Facturation Belgique',
				'attr' => array('class' => 'facturation-belgique-percent'),
			))
           	->add('totalHT', NumberType::class, array(
       			'required' => false,
       			'label' => 'Total HT',
       			'scale' => 2,
       			'mapped' => false,
       			'disabled' => true,
       			'attr' => array('class' => 'facture-total-ht')
           	))
           	->add('totalTTC', NumberType::class, array(
       			'required' => false,
       			'label' => 'Total TTC',
       			'scale' => 2,
       			'mapped' => false,
       			'disabled' => true,
       			'attr' => array('class' => 'facture-total-ttc')
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
                'attr' => array('class' => 'facture-analytique')
           	))
            ->add('bc_name', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'N° bon de commande interne',
                'attr' => array('class' => 'typeahead-bc', 'autocomplete' => 'off')
            ))
            ->add('bc_entity', HiddenType::class, array(
                'required' => true,
                'attr' => array('class' => 'entity-bc'),
                'mapped' => false
            ))
            ->add('inclureFrais', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
                'mapped' => false,
                'attr' => array(
                    'data-toggle' => 'toggle',
                    'data-on' => 'Oui',
                    'data-off' => 'Non',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger'
                ),
            ))
        ;


      }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\DocumentPrix',
            'userGestionId' => null,
            'companyId' => null,
            'compte' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_facture';
    }

}
