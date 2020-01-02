<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Shtumi\UsefulBundle\Form\Type\AjaxAutocompleteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CompteType extends AbstractType
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

        $compte = $builder->getData();

        $builder
            ->add('nom', TextType::class, array(
        		'label' => 'Nom de l\'organisation'
        	))
            ->add('telephone',TextType::class, array(
            	'required' => false,
            	'label' => 'Téléphone'
        	))
            ->add('fax', TextType::class, array(
            	'required' => false,
            	'label' => 'Fax'
        	))
            ->add('url', UrlType::class, array(
            	'required' => false,
            	'label' => 'URL du site web',
                'attr'   =>  array(
                    'class' => "urlId",
                )
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
            ->add('nomFacturation', TextType::class, array(
                'required' => false,
                'label' => 'Nom (facturation)',
            ))
            ->add('adresseFacturation', TextType::class, array(
                'required' => false,
                'label' => 'Adresse (facturation)',
            ))
            ->add('adresseFacturationLigne2', TextType::class, array(
                'required' => false,
                'label' => 'Adresse suite (facturation)',
            ))
            ->add('codePostalFacturation', TextType::class, array(
                'required' => false,
                'label' => 'Code postal (facturation)',
            ))
            ->add('villeFacturation', TextType::class, array(
                'required' => false,
                'label' => 'Ville (facturation)',
            ))
            ->add('paysFacturation', TextType::class, array(
                'required' => false,
                'label' => 'Pays (facturation)' ,        
            ))
            ->add('description', TextareaType::class, array(
            	'required' => false,
            	'label' => 'Description'
        	))
            ->add('compteParent', AjaxAutocompleteType::class, array(
            		'entity_alias'=>'comptes',
            		'required' => false,
            		'label' => 'Organisation parente'
           	))
            ->add('secteurActivite', EntityType::class, array(
                'class'=>'App:Settings',
                'choice_label' => 'valeur',
                'required' => true,
                'label' => 'Secteur d\'activité',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.parametre = :parametre')
                        ->andWhere('s.company = :company')
                        ->andWhere('s.module = :module')
                        ->setParameter('parametre', 'SECTEUR')
                        ->setParameter('module', 'CRM')
                        ->setParameter('company', $this->companyId)
                        ->orderBy('s.valeur');
                }
            ))
           	->add('userGestion', EntityType::class, array(
           			'class'=>'App:User',
           			'required' => true,
           			'label' => 'Gestionnaire de l\'organisation',
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
            ->add('updateContacts', CheckboxType::class, array(
                'label' => 'Mettre à jour les contacts avec la nouvelle adresse',
                'mapped' => false,
                'required' => false
            ))
            ->add('adresseFacturationDifferente', CheckboxType::class, array(
                'label' => 'Adresse de facturation différente',
                'mapped' => false,
                'required' => false,
                'attr' => array('class' => 'checkbox-adresse-facturation'),
                'data' => $compte->getAdresseFacturation()?true:false
            ))
            ->add('priveOrPublic', ChoiceType::class, array(
                'label' => 'Privé ou public ?',
                'required' => false,
                'choices' => array(
                    'PUBLIC' => 'Public',
                    'PRIVE' => 'Privé'
                )
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Compte',
            'userGestionId' => null,
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_compte';
    }

}
