<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevisType extends AbstractType
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
            ->add('objet', TextType::class, array(
        		'required' => true,
            	'label' => 'Objet'
        	))
            ->add('dateValidite', DateType::class, array('widget' => 'single_text',
        			'input' => 'datetime',
        			'format' => 'dd/MM/yyyy',
        			'attr' => array('class' => 'dateInput'),
        			'required' => true,
        			'label' => 'Date de validité'
        	))
             ->add('adresse', TextType::class, array(
        		'required' => true,
            	'label' => 'Adresse',
             	'attr' => array('class' => 'input-adresse'),
        	))
            ->add('codePostal', TextType::class, array(
        		'required' => true,
            	'label' => 'Code postal',
            	'attr' => array('class' => 'input-codepostal'),
        	))
            ->add('ville', TextType::class, array(
        		'required' => true,
            	'label' => 'Ville',
            	'attr' => array('class' => 'input-ville'),
        	))
    			->add('etat', ChoiceType::class, array(
    				'label' => 'Etat du devis',
    				'choices' => array(
    						'DRAFT' => 'Brouillon',
    						'SENT' => 'Envoyé',
    						'WON' => 'Gagné',
    						'LOST' => 'Perdu'
    					),
    			))
            ->add('region', TextType::class, array(
        		'required' => true,
            	'label' => 'Région',
            	'attr' => array('class' => 'input-region'),
        	))
            ->add('pays', TextType::class, array(
        		'required' => true,
            	'label' => 'Pays',
            	'attr' => array('class' => 'input-pays'),
        	))
            ->add('description', TextareaType::class, array(
        		'required' => false,
            	'label' => 'Commentaire'
        	))
            ->add('remise', NumberType::class, array(
        		'required' => false,
            	'label' => 'Remise',
            	'scale' => 2,
            		'attr' => array('class' => 'devis-remise')
        	))
        	->add('cgv', TextareaType::class, array(
        			'required' => false,
        			'label' => 'Conditions d\'utilisation'
        	))
            ->add('userGestion', EntityType::class, array(
           			'class'=>'App:User',
           			'required' => true,
           			'label' => 'Gestionnaire du devis',
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
           	->add('produits', CollectionType::class, array(
                    'entry_type' => ProduitType::class,
                    'entry_options' => array(
                        'companyId' => $this->companyId,
                    ),
           			'allow_add' => true,
           			'allow_delete' => true,
           			'by_reference' => false,
           			'label_attr' => array('class' => 'hidden')
           	))
           	->add('sousTotal', NumberType::class, array(
           			'required' => false,
           			'label' => 'Sous total',
           			'scale' => 2,
           			'mapped' => false,
           			'disabled' => true,
           			'attr' => array('class' => 'devis-sous-total')
           	))
           	->add('taxe', NumberType::class, array(
           			'required' => false,
           			'scale' => 2,
           			'label_attr' => array('class' => 'hidden'),
           			'attr' => array('class' => 'devis-taxe'),
           			'disabled' => true,
           	))
           	->add('taxePercent', PercentType::class, array(
           			'required' => false,
           			'scale' => 2,
           			'label' => 'TVA',
           			'attr' => array('class' => 'devis-taxe-percent'),
           	))
           	->add('totalHT', NumberType::class, array(
           			'required' => false,
           			'label' => 'Total HT',
           			'scale' => 2,
           			'mapped' => false,
           			'disabled' => true,
           			'attr' => array('class' => 'devis-total-ht')
           	))
           	->add('totalTTC', NumberType::class, array(
           			'required' => false,
           			'label' => 'Total TTC',
           			'scale' => 2,
           			'mapped' => false,
           			'disabled' => true,
           			'attr' => array('class' => 'devis-total-ttc')
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
                'attr' => array('class' => 'devis-analytique')
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
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_devis';
    }

}
