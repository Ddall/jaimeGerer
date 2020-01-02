<?php

namespace App\Form\Compta;

use App\Entity\CRM\Compte;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContactType extends AbstractType
{
	
	protected $userGestionId;
	protected $companyId;
	protected $formAction;
    protected $type;
	

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->userGestionId = $options['userGestionId'];
        $this->companyId = $options['companyId'];
        $this->formAction = $options['formAction'];
        $this->type = $options['type'];

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
                    'value' => 'http://'
                )
        	))
            ->add('adresse', TextType::class, array(
            	'required' => true,
            	'label' => 'Adresse'
        	))
            ->add('codePostal', TextType::class, array(
            	'required' => true,
            	'label' => 'Code postal'
        	))
            ->add('ville', TextType::class, array(
            	'required' => true,
            	'label' => 'Ville'
        	))
            ->add('region', TextType::class, array(
            	'required' => true,
            	'label' => 'Région'
        	))
            ->add('pays', TextType::class, array(
            	'required' => true,
            	'label' => 'Pays'        	
            ))
            ->add('description', TextareaType::class, array(
            	'required' => false,
            	'label' => 'Description'
        	))
            ->add('compteParent', EntityType::class, array(
            		'class' => Compte::class,
            		'required' => false,
            		'label' => 'Organisation parente'
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
            ->add('addressPicker', TextType::class, array(
                'label'     => 'Veuillez renseigner l\'adresse ici',
                'mapped'    => false,
                'required'  => false,
                'attr' => array('class' => '.adresspicker')
            ));

            if($this->type == "CLIENT"){
                $builder->add('compteComptableClient', EntityType::class, array(
                    'class'=>'App:Compta\CompteComptable',
                    'choice_label' => 'nom',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                        ->where('c.company = :company')
                        ->andWhere('c.num LIKE :num')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('num', '411%')
                        ->orderBy('c.nom');
                    },
                    'required' => false,
                    'label' => 'Compte comptable client',
                    'attr' => array('class' => 'select-compte-comptable')
              ));
            } else {
                $builder->add('compteComptableFournisseur', EntityType::class, array(
                    'class'=>'App:Compta\CompteComptable',
                    'choice_label' => 'nom',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                        ->where('c.company = :company')
                        ->andWhere('c.num LIKE :num')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('num', '401%')
                        ->orderBy('c.nom');
                    },
                    'required' => false,
                    'label' => 'Compte comptable fournisseur',
                     'attr' => array('class' => 'select-compte-comptable')
                ));
            }
            $builder->add('secteurActivite', EntityType::class, array(
                'class'=>'App:Settings',
                'choice_label' => 'valeur',
                'required' => false,
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
            ));
            
           	if( $this->formAction )
				$builder->setAction($this->formAction);
	
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
            'formAction' => null,
            'type' => null,
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
