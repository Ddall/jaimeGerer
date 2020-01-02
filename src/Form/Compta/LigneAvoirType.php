<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneAvoirType extends AbstractType
{
	protected $companyId;
	protected $type;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->companyId = $options['companyId'];
        $this->type = $options['type'];

        $builder
         ->add('nom', TextType::class, array(
      		'required' => true,
        	'label' => 'Nom',
      	))
        ->add('montant', NumberType::class, array(
        		'required' => true,
        		'label' => 'Montant HT (€)',
        		'scale' => 2,
        		'attr' => array('class' => 'produit-montant')
     	   ))
     	   ->add('taxe', NumberType::class, array(
     	   		'required' => false,
     	   		'scale' => 2,
     	   		'label' => 'TVA (€)',
     	   		'attr' => array('class' => 'produit-taxe')
     	   ))
     	   ->add('totalTTC', NumberType::class, array(
     	   		'required' => false,
     	   		'label' => 'Total TTC (€)',
     	   		'scale' => 2,
     	   		'mapped' => false,
     	   		'attr' => array('class' => 'produit-total')
     	   ));

     	   if($this->type == "CLIENT"){

	     	   	$builder->add('compteComptable', EntityType::class, array(
	     	   			'class'=>'App:Compta\CompteComptable',
	     	   			'query_builder' => function (EntityRepository $er) {
	     	   				return $er->createQueryBuilder('c')
	     	   				->where('c.company = :company')
	     	   				->orderBy('c.nom', 'ASC')
	     	   				->andWhere('c.num LIKE :num')
	     	   				->setParameter('company', $this->companyId)
	     	   				->setParameter('num', '7%');
	     	   			},
	     	   			'required' => false,
	     	   			'label' => 'Compte comptable',
	     	   			'attr' => array('class' => 'select-compte-comptable')
	     	   	));
     	   } else {

     	   	$builder->add('compteComptable', EntityType::class, array(
            		'class'=>'App:Compta\CompteComptable',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('c')
            			->where('c.company = :company')
            			->orderBy('c.nom', 'ASC')
            			->andWhere('c.num LIKE :num')
            			->setParameter('company', $this->companyId)
            			->setParameter('num', '6%');
            		},
            		'required' => false,
            		'label' => 'Compte comptable',
            		'attr' => array('class' => 'select-compte-comptable')
            ));

     	  }



    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\LigneAvoir',
            'companyId' => null,
            'type' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_lignedepense';
    }
}
