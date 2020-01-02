<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContactFromProspectionType extends AbstractType
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

        $arr_delaiNum = array();
        for($i=1; $i<13; $i++){
            $arr_delaiNum[$i] = $i;
        }
        
        $arr_delaiUnit= array(
                'DAY' => 'jours',
                'WEEK' => 'semaines', 
                'MONTH' => 'mois');

        $builder
            ->add('prenom', TextType::class, array(
        		'required' => true,
            	'label' => 'Prénom'
        	))
            ->add('nom', TextType::class, array(
        		'required' => true,
            	'label' => 'Nom'
        	))
            ->add('telephoneFixe', TextType::class, array(
            	'required' => false,
            //	'default_region' => 'FR',
            //	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone fixe'
        	))
            ->add('telephonePortable',TextType::class, array(
            	'required' => false,
          //  	'default_region' => 'FR',
          //  	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Tél. portable pro'
        	))
    			->add('telephoneAutres',TextType::class, array(
    				'required' => false,
    				//  	'default_region' => 'FR',
    				//  	'format' => PhoneNumberFormat::INTERNATIONAL,
    				'label' => 'Tél. (autre)'
    			))
          ->add('email', EmailType::class, array(
            		'required' => false,
              	'label' => 'Email'
            	))
    			->add('email2', EmailType::class, array(
    				'required' => false,
    				'label' => 'Email 2'
    			))
            ->add('adresse', TextType::class, array(
        		'required' => false,
            	'label' => 'Adresse'
        	))
          ->add('codePostal', TextType::class, array(
        		'required' => false,
          	'label' => 'Code postal'
        	))
          ->add('ville', TextType::class, array(
        		'required' => false,
            	'label' => 'Ville'
        	))
          ->add('region', TextType::class, array(
        		'required' => false,
            	'label' => 'Région'
        	))
          ->add('pays', TextType::class, array(
        		'required' => false,
            	'label' => 'Pays'
        	))
          ->add('description', TextareaType::class, array(
        		'required' => false,
            	'label' => 'Description'
        	))
          ->add('titre', TextType::class, array(
        		'required' => false,
            	'label' => 'Titre'
        	))
          ->add('fax', TextType::class, array(
            	'required' => false,
           // 	'default_region' => 'FR',
           // 	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	))
          ->add('carteVoeux', CheckboxType::class, array(
        		'required' => false,
  				'label' => 'Carte de voeux'
        	))
          ->add('newsletter', CheckboxType::class, array(
        		'required' => false,
  				'label' => 'Newsletter'
        	))
		   ->add('compte_name', TextType::class, array(
				'required' => true,
				'mapped' => false,
				'label' => 'Organisation',
				'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off' )
			))
			->add('compte', HiddenType::class, array(
				'required' => true,
				'attr' => array('class' => 'entity-compte'),
                'data_class' => 'Proxies\__CG__\App\Entity\CRM\Compte'
			))
			->add('reseau', EntityType::class, array(
        		'class'=>'App:Settings',
        		'choice_label' => 'valeur',
        		'query_builder' => function (EntityRepository $er) {
        			return $er->createQueryBuilder('s')
        			->where('s.parametre = :parametre')
        			->andWhere('s.company = :company')
        			->setParameter('parametre', 'RESEAU')
        			->setParameter('company', $this->companyId);
        		},
        		'required' => false,
        		'label' => 'Réseau'
            ))
            ->add('origine', EntityType::class, array(
        		'class'=>'App:Settings',
        		'choice_label' => 'valeur',
        		'query_builder' => function (EntityRepository $er) {
        			return $er->createQueryBuilder('s')
        			->where('s.parametre = :parametre')
        			->andWhere('s.company = :company')
        			->setParameter('parametre', 'ORIGINE')
        			->setParameter('company', $this->companyId);
        		},
        		'required' => false,
        		'label' => 'Origine'
            ))
            ->add('userGestion', EntityType::class, array(
       			'class'=>'App:User',
       			'required' => true,
       			'label' => 'Gestionnaire du contact',
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
           	->add('addressPicker', TextType::class, array(
   				'label' => 'Veuillez saisir l\'adresse ici',
   				'mapped' => false,
   				'required' => false
           	))
            ->add('delaiNum', IntegerType::class, array(
                'label' => 'Contacter tous les',
                'required' => false,
                'mapped' => false
            ))
            ->add('delaiUnit', ChoiceType::class, array(
                    'choices' => $arr_delaiUnit,
                    'label_attr' => array('class' => 'invisible'),
                    'required' => true,
                    'mapped' => false
            ));
                         
            $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
            $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Contact',
            'userGestionId' => null,
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_contact';
    }
    
    public function onPreSetData(FormEvent $event){
    	$contact = $event->getData();
    	$form = $event->getForm();
    	
    	$arr_themes = array();
    	$arr_services = array();
    	$arr_types = null;
        $arr_secteurs = array();
    	foreach($contact->getSettings() as $setting){
    		if($setting->getModule() == 'CRM' && $setting->getParametre() == 'THEME_INTERET'){
    			$arr_themes[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'SERVICE_INTERET'){
    			$arr_services[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'TYPE'){
    			$arr_types[] = $setting;
    		}
            else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'SECTEUR'){
                $arr_secteurs[] = $setting;
            }
    	}

    	$form->add('themes_interet', EntityType::class, array(
             		'class'=>'App:Settings',
             		'choice_label' => 'valeur',
             		'query_builder' => function (EntityRepository $er) {
             			return $er->createQueryBuilder('s')
             			->where('s.parametre = :parametre')
             			->andWhere('s.module = :module')
             			->andWhere('s.company = :company')
             			->setParameter('parametre', 'THEME_INTERET')
             			->setParameter('module', 'CRM')
             			->setParameter('company', $this->companyId)
             			->orderBy('s.valeur');
             		},
             		'required' => false,
             		'multiple' => true,
             		'label' => 'Thèmes d\'intérêt',
             		'mapped' => false,
             		'data' => $arr_themes
             ));
    	
        $form->add('services_interet', EntityType::class, array(
                    'class'=>'App:Settings',
                    'choice_label' => 'valeur',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                        ->where('s.parametre = :parametre')
                        ->andWhere('s.company = :company')
                        ->andWhere('s.module = :module')
                        ->setParameter('parametre', 'SERVICE_INTERET')
                        ->setParameter('module', 'CRM')
                        ->setParameter('company', $this->companyId)
                        ->orderBy('s.valeur');
                    },
                    'required' => false,
                    'multiple' => true,
                    'label' => 'Services d\'intérêt',
                     'empty_data'  => null,
                     'mapped' => false,
                     'data' => $arr_services
            )
        );

        $form->add('secteur', EntityType::class, array(
            'class'=>'App:Settings',
            'choice_label' => 'valeur',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->where('s.parametre = :parametre')
                    ->andWhere('s.company = :company')
                    ->andWhere('s.module = :module')
                    ->setParameter('parametre', 'SECTEUR')
                    ->setParameter('module', 'CRM')
                    ->setParameter('company', $this->companyId)
                    ->orderBy('s.valeur');
            },
            'required' => false,
            'multiple' => true,
            'label' => 'Secteur d\'activité',
            'empty_data'  => null,
            'mapped' => false,
            'data' => $arr_secteurs
        ));

    	 $form->add('types', EntityType::class, array(
    	 		'class'=>'App:Settings',
    	 		'choice_label' => 'valeur',
    	 		'query_builder' => function (EntityRepository $er) {
    	 			return $er->createQueryBuilder('s')
    	 			->where('s.parametre = :parametre')
    	 			->andWhere('s.module = :module')
    	 			->andWhere('s.company = :company')
    	 			->setParameter('parametre', 'TYPE')
    	 			->setParameter('module', 'CRM')
    	 			->setParameter('company', $this->companyId)
    	 			->orderBy('s.valeur');
    	 		},
    	 		'required' => false,
    	 		'multiple' => true,
    	 		'label' => 'Type de relation commerciale',
    	 		'empty_data'  => null,
    	 		'mapped' => false,
    	 		'data' => $arr_types
    	 ));
    }
    
    public function onPostSubmit(FormEvent $event){
    	
    	$contact = $event->getData();
    	$form = $event->getForm();
    	
    	$contact->removeSettings();
    	//~ var_dump($form->get('themes_interet')->getData());
    	//~ var_dump($form->get('services_interet')->getData());
    	//~ var_dump($form->get('types')->getData());
    	foreach($form->get('themes_interet')->getData() as $theme){
			//~ var_dump($theme);
    		$contact->addSetting($theme);
    	}
    	
    	foreach($form->get('services_interet')->getData() as $service){
			//~ var_dump($service);
    	//~ exit;
    		$contact->addSetting($service);
    	}

        foreach($form->get('secteur')->getData() as $secteur){
            //~ var_dump($service);
            //~ exit;
            $contact->addSetting($secteur);
        }

    	foreach($form->get('types')->getData() as $type){
    		$contact->addSetting($type);
    	}
    }

}
