<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompteBancaireType extends AbstractType
{
	protected $companyId;
	protected $solde;
	protected $soldeDebutAnnee;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->companyId = $options['companyId'];
        $this->solde =  $options['solde'];
        $this->soldeDebutAnnee =  $options['soldeDebutAnnee'];

        $builder
            ->add('nom', TextType::class, array(
        		'required' => true,
            	'label' => 'Nom du compte dans le journal de banque'	
       	 	))
            ->add('nomComplet', TextType::class, array(
                'required' => true,
                'label' => 'Nom complet du compte'  
            ))
       	 	->add('num', TextType::class, array(
       	 			'required' => true,
       	 			'label' => 'Numéro de compte'
       	 	))
            ->add('bic', TextType::class, array(
        		'required' => true,
            	'label' => 'BIC'
        	))
            ->add('iban', TextType::class, array(
        		'required' => true,
            	'label' => 'IBAN'
        	))
            ->add('domiciliation', TextType::class, array(
        		'required' => true,
            	'label' => 'Domiciliation'
        	))
            ->add('solde', NumberType::class, array(
        		'required' => true,
            	'label' => 'Solde actuel du compte (€)',
            	'mapped' => false,
            	'data' => $this->solde
        	))
        	->add('soldeDebutAnnee', NumberType::class, array(
        			'required' => true,
        			'label' => 'Solde du compte (€) au 1er janvier de l\'année en cours',
        			'mapped' => false,
        			'data' => $this->soldeDebutAnnee
        	))
            ->add('compteComptable', EntityType::class, array(
        			'required' => false,
        			'class' => 'App:Compta\CompteComptable',
        			'label' => 'Compte comptable',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
        				->where('c.company = :company')
        				->andWhere('c.num LIKE :num')
        				->setParameter('company', $this->companyId)
        				->setParameter('num', "512%");
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
            'data_class' => 'App\Entity\Compta\CompteBancaire',
            'companyId' => null,
            'solde' => null,
            'soldeDebutAnnee' => null,
        ));

    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_comptebancaire';
    }
}
