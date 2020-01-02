<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationDiverseType extends AbstractType
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
            ->add('compteComptable', EntityType::class, array(
    			'required' => false,
    			'class' => 'App:Compta\CompteComptable',
    			'label' => 'Compte comptable',
    			'query_builder' => function (EntityRepository $er) {
    				return $er->createQueryBuilder('c')
    				->andWhere('c.company = :company')
    				->setParameter('company', $this->companyId)
    				->orderBy('c.num', 'ASC');
    			}
        	));
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\OperationDiverse',
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_operationdiverse';
    }
}
