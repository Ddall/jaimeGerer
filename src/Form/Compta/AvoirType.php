<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvoirType extends AbstractType
{

	protected $userGestionId;
	protected $companyId;
	protected $type;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->userGestionId = $options['userGestionId'];
        $this->companyId = $options['companyId'];
        $this->type = $options['type'];

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
             ->add('lignes', CollectionType::class, array(
             		'entry_type' => LigneAvoirType::class,
             		'entry_options' => array(
             		    'companyId' => $this->companyId,
                        'type' => $this->type,
                    ),
             		'allow_add' => true,
             		'allow_delete' => true,
             		'by_reference' => false,
             		'label_attr' => array('class' => 'hidden')
             ))
           ->add('userGestion', EntityType::class, array(
           			'class'=>'App:User',
           			'required' => true,
           			'label' => 'Gestionnaire de l\'avoir',
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
           	));

             if($this->type == 'CLIENT'){
             	$builder->add('facture', EntityType::class, array(
           			'class'=> 'App\Entity\CRM\DocumentPrix',
           			'required' => true,
           			'label' => 'Facture',
           			'attr' => array('class' => 'select-piece'),
           			'query_builder' => function(EntityRepository $er) {
										return $er->findNoRapprochement($this->companyId,true);
           			}
							));
             } else {
             	$builder->add('depense', EntityType::class, array(
             			'class'=> 'App\Entity\Compta\Depense',
             			'required' => true,
             			'label' => 'Dépense',
             			'attr' => array('class' => 'select-piece'),
             			'query_builder' => function(EntityRepository $er) {
										return $er->findNoRapprochement($this->companyId);
             			}
             	));
             }

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\Avoir',
            'userGestionId' => null,
            'companyId' => null,
            'type' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_avoir';
    }
}
