<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{

	protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->companyId = $options['companyId'];

        $builder
            ->add('nom', TextType::class, array(
        		'required' => true,
            	'label' => 'Nom',
        	))
            ->add('description', TextareaType::class, array(
        			'required' => 'true',
            	'label' => 'Description'
        	))
            ->add('tarifUnitaire', NumberType::class, array(
            		'required' => true,
            		'label' => 'Tarif unitaire (€)',
            		'scale' => 2,
            		'attr' => array('class' => 'produit-tarif')
     	   ))
            ->add('quantite', NumberType::class, array(
            		'required' => true,
            		'label' => 'Quantité',
            		'attr' => array('class' => 'produit-quantite')
            ))
             ->add('montant', NumberType::class, array(
            		'required' => true,
            		'label' => 'Montant (€)',
            		'scale' => 2,
             		'mapped' => false,
             		'disabled' => true,
             		'attr' => array('class' => 'produit-montant')
     	   ))
            ->add('remise', NumberType::class, array(
            		'required' => false,
            		'label' => 'Remise (€)',
            		'scale' => 2,
            		'attr' => array('class' => 'produit-remise')
     	   ))
           ->add('type', EntityType::class, array(
            		'class'=>'App:Settings',
            		'choice_label' => 'valeur',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('s')
            			->where('s.parametre = :parametre')
            			->andWhere('s.company = :company')
            			->andWhere('s.module = :module')
            			->setParameter('parametre', 'TYPE_PRODUIT')
            			->setParameter('company', $this->companyId)
            			->setParameter('module', 'CRM');
            		},
            		'required' => false,
            		'label' => 'Type',
            		'attr' => array('class' => 'produit-type')
            ))
            ->add('total', NumberType::class, array(
            		'required' => true,
            		'label' => 'Total (€)',
            		'scale' => 2,
            		'mapped' => false,
            		'disabled' => true,
            		'attr' => array('class' => 'produit-total')
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Produit',
            'type' => null,
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_produit';
    }
}
