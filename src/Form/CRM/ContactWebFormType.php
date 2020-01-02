<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContactWebFormType extends AbstractType
{
	
	protected $userGestionId;
	protected $companyId;
	protected $request;

	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->userGestionId = $options['userGestionId'];
        $this->companyId = $options['companyId'];
        $this->request = $options['request'];

		//~ var_dump(FormEvent::getData()); exit;
    	$arr_delaiNum = array();
    	for($i=1; $i<13; $i++){
    		$arr_delaiNum[$i] = $i;
    	}
    	
    	$arr_delaiUnit= array(
    			'DAY' => 'jours', 
    			'WEEK' => 'semaines', 
    			'MONTH' => 'mois');

        $builder
            ->add('nom_formulaire', TextType::class, array(
        		'required' => true,
            	'label' => 'Nom du formulaire'
        	))
        	->add('returnUrl', UrlType::class, array(
        			'required' => true,
        			'label' => 'URL de destination'
        	))
            ->add('nomCompte', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Nom de l\'organisation',
        	))
            ->add('prenomContact', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Prénom du contact'
        	))
            ->add('nomContact', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Nom du Contact'
        	))
            ->add('adresse', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Adresse'
        	))
            ->add('codePostal', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Code postal'
        	))
            ->add('ville', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Ville'
        	))
            ->add('region', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Région'
        	))
            ->add('pays', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Pays'
        	))
            ->add('telephoneFixe', CheckboxType::class, array(
            	'required' => false,
            //	'default_region' => 'FR',
            //	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone fixe'
        	))
            ->add('telephonePortable',CheckboxType::class, array(
            	'required' => false,
          //  	'default_region' => 'FR',
          //  	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Tél. portable pro'
        	))
            ->add('email', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'Email'
        	))
            ->add('fax', CheckboxType::class, array(
            	'required' => false,
           // 	'default_region' => 'FR',
           // 	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	))
            ->add('url', CheckboxType::class, array(
        		'required' => false,
            	'label' => 'URL du site web',
        	))

 
 
            ->add('carteVoeux', CheckboxType::class, array(
        		'required' => false,
  				'label' => 'Carte de voeux'
        	))
            ->add('newsletter', CheckboxType::class, array(
        		'required' => false,
  				'label' => 'Newsletter'
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
            ->add('gestionnaireSuivi', EntityType::class, array(
           			'class'=>'App:User',
           			'required' => false,
           			'label' => 'Gestionnaire du suivi',
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
			->add('envoyerEmail', ChoiceType::class, array(
				'choices' => array(
					'1' => 'Envoyer un email au nouveau contact',
					'0' => 'Ne pas envoyer d\'email au nouveau contact'
				),
				'multiple' => false,
				'expanded' => true,
				'required' => true,
				'data'     => '1',
				'disabled' => true
			))
			->add('expediteur', TextType::class, array(
				'required' => false,
				'label' => 'Emetteur de l\'email',
				'disabled' => true
			))
			->add('corpsEmail', TextareaType::class, array(
				'required' => false,
				'label' => 'Corps de l\'email',
				'disabled' => true
			))
			->add('objetEmail', TextType::class, array(
				'required' => false,
				'label' => 'Objet de l\'email',
				'disabled' => true
			))
			->add('copieEmail', CheckboxType::class, array(
				'required' => false,
				'label' => 'Envoyer une copie au gestionnaire de contact',
				'disabled' => true
			))
			->add('delaiNum', IntegerType::class, array(
           		'label' => 'Contacter tous les',
           		'required' => false,
     		))
           	->add('delaiUnit', ChoiceType::class, array(
           			'choices' => $arr_delaiUnit,
           			'label_attr' => array('class' => 'invisible'),
           			'required' => false,
           	));
           	
                         
             $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
             $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
             $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostBind'));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\ContactWebForm',
            'attr' => array('id' => 'ContactWebForm'),
            'userGestionId' => null,
            'companyId' => null,
            'request' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_contactwebform';
    }
    
    public function onPreSetData(FormEvent $event){
    	$contact = $event->getData();
    	$form = $event->getForm();
//~ var_dump($form->getChildren()); exit;
//~ var_dump($contact); exit;
    	$arr_themes = array();
    	$arr_services = array();
    	$arr_types = null;
    	foreach($contact->getSettings() as $setting){
    		if($setting->getModule() == 'CRM' && $setting->getParametre() == 'THEME_INTERET'){
    			$arr_themes[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'SERVICE_INTERET'){
    			$arr_services[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'TYPE'){
    			$arr_types[] = $setting;
    		}
    	}
    	$posts = $this->request->request->get($form->getName());
		$fieldsToRename = array('nomCompte' => 'Nom de l\'organisation', 
								'prenomContact' => 'Prénom du Contact',  
								'nomContact' => 'Nom du Contact',  
								'adresse' => 'Adresse',  
								'codePostal' => 'Code postal',  
								'ville' => 'Ville',  
								'region' => 'Région',  
								'pays' => 'Pays',  
								'telephoneFixe' => 'Téléphone fixe',  
								'telephonePortable' => 'Tél. portable pro',  
								'email' => 'Email',  
								'fax' => 'Fax',  
								'url' => 'URL du site web');
		if( $contact->getEmail() )
		{
			$form->remove('envoyerEmail');
			$form->remove('expediteur');
			$form->remove('corpsEmail');
			$form->remove('objetEmail');
			$form->remove('copieEmail');
			$form
			->add('envoyerEmail', ChoiceType::class, array(
			'choices' => array(
				'1' => 'Envoyer un email au nouveau contact',
				'0' => 'Ne pas envoyer d\'email au nouveau contact'
			),
			'multiple' => false,
			'expanded' => true,
			'required' => true,
			'data'     => '1'
			))
			->add('expediteur', TextType::class, array(
				'required' => false,
				'label' => 'Emetteur de l\'email'
			))
			->add('corpsEmail', TextareaType::class, array(
				'required' => false,
				'label' => 'Corps de l\'email'
			))
			->add('objetEmail', TextType::class, array(
				'required' => false,
				'label' => 'Objet de l\'email'
			))
			->add('copieEmail', CheckboxType::class, array(
				'required' => false,
				'label' => 'Envoyer une copie au gestionnaire de contact'
			));

		}
		foreach( $fieldsToRename as $k=>$v )
		{
			if( $contact->getId() > 0 )
			{
				//~ var_dump($form[$v]); 
				//~ echo $v."<br>";
				//~ var_dump($posts[$v]); echo "<br><br>";
				//~ $form[$k]['data'] = array($v);
				$methodGet = 'get'.ucfirst($k);
				eval("\$var = \$contact->$methodGet();");
				if( $var != '' )
				{
				//~ var_dump($var);
					$form->remove($k);
					$form->add($k, CheckboxType::class, array(
								'required' => false,
								'label' => $v,
								'mapped' => false,
								'data' => true,
						));
					//~ $form->add($k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
								//~ 'required' => false,
								//~ 'label' => false,
								//~ 'expanded' => true,
								//~ 'multiple' => true,
								//~ 'choices' => array($v => $v),
								//~ 'mapped' => false,
								//~ 'data' => array($v),
						//~ ));
					$form->add('new_value_'.$k, \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
								'required' => false,
								'label' => false,
								'mapped' => false,
								'data' => $var,
								//~ 'attr' => array('class' => ''),
						));
				}
				else
				{
					//~ var_dump(is_null($var)); exit;
					$var = $v;
					$form->remove($k);
					$form->add($k, CheckboxType::class, array(
								'required' => false,
								'label' => $var,
								'mapped' => false,
								'data' => false,
						));
					$form->add('new_value_'.$k, \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
								'required' => false,
								'label' => false,
								'mapped' => false,
								'data' => $var,
								//~ 'attr' => array('class' => ''),
						));
				}
				//~ $form->remove($v);
				//~ $form->add($v, \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
						//~ 'required' => false,
						//~ 'label' => false,
						//~ 'data' => is_array($posts[$v]) ? $posts[$v][0] : $posts[$v],
					//~ ));
			}
			else
			{
				$form->add('new_value_'.$k, \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
							'required' => false,
							'label' => false,
							'mapped' => false,
							'data' => $v,
							//~ 'attr' => array('class' => ''),
					));
			}
		//~ exit;
		}
    	//~ nomCompte
    	//~ var_dump($this->request->request->get($form->getName())); exit;
    	if( is_array($posts) && count($posts) > 0 )
    	{
			foreach( $fieldsToRename as $k=>$v )
			{
				$form->remove('new_value_'.$k);
				if( isset($posts[$k]) )
				{
					//~ echo $v."<br>";
					//~ var_dump($posts[$v]); echo "<br><br>";
					//~ $form->remove($k);
					//~ $form->add($k, \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
							//~ 'required' => false,
							//~ 'label' => false,
							//~ 'data' => is_array($posts[$k]) ? $posts[$k][0] : $posts[$k],
						//~ ));
					$form->add('new_value_'.$k, \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
							'required' => false,
							'label' => false,
							'mapped' => false,
							'data' => is_array($posts[$k]) ? $posts[$k][0] : $posts[$k],
						));
				}
				else
				{
					$form->add('new_value_'.$k, \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
							'required' => false,
							'label' => false,
							'mapped' => false,
							'data' => is_array($posts['new_value_'.$k]) ? $posts['new_value_'.$k][0] : $posts['new_value_'.$k],
						));
					
				}
			}
			//~ var_dump($posts); exit;
			//~ exit;
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
    	
    	foreach($form->get('themes_interet')->getData() as $theme){
    		$contact->addSetting($theme);
    	}
    	
    	foreach($form->get('services_interet')->getData() as $service){
    		$contact->addSetting($service);
    	}
    	foreach($form->get('types')->getData() as $type){
    		$contact->addSetting($type);
    	}

    	//~ $form->remove('nomCompte');
    	//~ $form->add('nomCompte', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
        		//~ 'required' => false,
            	//~ 'label' => false,
            	//~ 'data' => 'hich',
//~ 
        	//~ ));
		//~ var_dump($contact); exit;
    }

    public function onPostBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        //~ var_dump($form->isValid()); exit;
        
		//~ $fieldsToRename = array('nomCompte', 'prenomContact',  'nomContact',  'adresse',  'codePostal',  'ville',  'region',  'pays',  'telephoneFixe',  'telephonePortable',  'email',  'fax',  'url');
		$fieldsToRename = array('nomCompte' => 'Nom de l\'organisation', 
								'prenomContact' => 'Prénom du Contact',  
								'nomContact' => 'Nom du Contact',  
								'adresse' => 'Adresse',  
								'codePostal' => 'Code postal',  
								'ville' => 'Ville',  
								'region' => 'Région',  
								'pays' => 'Pays',  
								'telephoneFixe' => 'Téléphone fixe',  
								'telephonePortable' => 'Tél. portable pro',  
								'email' => 'Email',  
								'fax' => 'Fax',  
								'url' => 'URL du site web');
    	$envoyerMail = array('envoyerEmail', 'expediteur', 'corpsEmail', 'objetEmail', 'copieEmail');
		$posts = $this->request->request->get($form->getName());
    	//~ var_dump($posts);
		//~ var_dump($form->isValid()); exit;
		if( $form->isValid() )
		{
			if( isset($posts['email']) )
			{
				foreach( $envoyerMail as $v )
				{
					$NewVal = isset($posts[$v]) && $posts[$v] != '' ? $posts[$v] : NULL;
					$methodSet = 'set'.ucfirst($v);
					//~ var_dump($NewVal);
					eval("\$data->$methodSet(\$NewVal);");
				}
			}
			foreach( $fieldsToRename as $k=>$v )
			{
				$NewVal = isset($posts[$k]) ? ( $posts['new_value_'.$k] != '' ? $posts['new_value_'.$k] : $v ) : NULL;
				$methodSet = 'set'.ucfirst($k);
				//~ var_dump($NewVal);
				eval("\$data->$methodSet(\$NewVal);");					
			}
		}
		$event->setData($data);
    }

}
