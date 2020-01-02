<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneDepenseType extends AbstractType
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
            	'label' => 'Libellé',
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
     	   ))
            ->add('compteComptable', EntityType::class, array(
            		'class'=>'App:Compta\CompteComptable',
            		'choice_label' => 'nameAndNum',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('c')
            			->where('c.company = :company')
            			->andWhere('c.num LIKE :num OR c.num LIKE :compteAttente OR c.num LIKE :immo')
            			->setParameter('company', $this->companyId)
            			->setParameter('num', '6%')
            			->setParameter('compteAttente', '471')
                        ->setParameter('immo', '2%')
            			->orderBy('c.nom');
            		},
            		'required' => false,
            		'label' => 'Compte comptable',
            		'attr' => array('class' => 'produit-type')
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\LigneDepense',
            'companyId' => null,
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
