<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffectationDiverseType extends AbstractType
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
        		'label' => 'Libellé',
            	'required' => true
       	 	))
            ->add('recurrent', ChoiceType::class, array(
            	'choices' => array(1 => 'Récurrent', 0 => 'Non récurrent'),
            	'label' => 'Récurrent',
            	'expanded' => true,
            	'required' => true
            ))
            ->add('filtre_comptes', ChoiceType::class, array(
            		'choices' => array(
            			471 => 'Je ne sais pas',
            		),
            		'label' => 'Type d\'affectation',
            		'expanded' => true,
            		'required' => false,
            		'multiple' => false,
            		'mapped' => false,
            		'attr' => array('class' => 'radio-filtre-comptes'),
            		'empty_value' => 'Je sais !'
            ))
            ->add('type', HiddenType::class);

			if($this->type == "ACHAT"){
				$builder->add('compteComptable', EntityType::class, array(
		        			'required' => false,
		        			'class' => 'App:Compta\CompteComptable',
		        			'label' => 'Compte comptable',
		            		'attr' => array('class' => 'select-compte-comptable'),
		        			'query_builder' => function (EntityRepository $er) {
		        				return $er->createQueryBuilder('c')
		        				->where('c.company = :company')
		        				->andWhere('c.num NOT LIKE :num2 and c.num NOT LIKE :num401 and c.num NOT LIKE :num411 and c.num NOT LIKE :num7')
		        				->setParameter('company', $this->companyId)
		        				->setParameter('num2', "2%")
		        				->setParameter('num401', "401%")
		        				->setParameter('num411', "411%")
		        				->setParameter('num7', "7%")
		        				->orderBy('c.num');
		        			}
				));
    		} else {
    			$builder->add('compteComptable', EntityType::class, array(
    					'required' => false,
    					'class' => 'App:Compta\CompteComptable',
    					'label' => 'Compte comptable',
    					'attr' => array('class' => 'select-compte-comptable'),
    					'query_builder' => function (EntityRepository $er) {
    						return $er->createQueryBuilder('c')
    						->where('c.company = :company')
    						->andWhere('c.num NOT LIKE :num2 and c.num NOT LIKE :num401 and c.num NOT LIKE :num411 and c.num NOT LIKE :num6')
    						->setParameter('company', $this->companyId)
    						->setParameter('num2', "2%")
    						->setParameter('num401', "401%")
    						->setParameter('num411', "411%")
                            ->setParameter('num6', "6%")
    						->orderBy('c.num');
    					}
    			));
    		}

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\AffectationDiverse',
            'companyId' => null,
            'type' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_affectationdiverse';
    }
}
