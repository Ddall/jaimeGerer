<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ChequeType extends AbstractType
{
    protected $arr_cheque_pieces;
    protected $companyId;
	protected $autre;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->arr_cheque_pieces = $options['arr_cheque_pieces'];
        $this->companyId = $options['companyId'];

        $builder
            ->add('nomBanque', TextType::class, array(
          		'required' => false,
              	'label' => 'Banque'
            ))
            ->add('num', TextType::class, array(
          		'required' => false,
              	'label' => 'N° chèque'
            ))
            ->add('select', ChoiceType::class, array(
               	'choices' => $this->arr_cheque_pieces,
          		'required' => true,
               	'multiple' => true,
               	'expanded' => false,
               	'mapped' => false,
          		'attr' => array('class' => 'select-piece'),
               	'label' => 'Pièces',
            ))
            ->add('autre', CheckboxType::class, array(
                'label' => 'Autre',
                'attr' => array('class' => 'checkbox-autre'),
                'mapped' => false,
                'required' => false,
            ))
            ->add('libelle', TextType::class, array(
                'required' => false,
                'label' => 'Libellé',
                'mapped' => false,
                'attr' => array('class' => 'input-libelle'),
            ))
            ->add('emetteur', TextType::class, array(
                'required' => false,
                'label' => 'Emetteur',
                'mapped' => false,
                'attr' => array('class' => 'input-emetteur'),
            ))
            ->add('compteComptableTiers', EntityType::class, array(
                'class'=>'App:Compta\CompteComptable',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.company = :company')
                        ->andWhere('c.num  LIKE :num4 ')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('num4', "4%")
                        ->orderBy('c.num');
                },
                'required' => false,
                'label' => 'Compte comptable du tiers',
                'attr' => array('class' => 'select-cc'),
                'mapped' => false,
            ))
            ->add('compteComptable', EntityType::class, array(
                'class'=>'App:Compta\CompteComptable',
                'choice_label' => 'nom',
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
                },
                'required' => false,
                'label' => 'Compte comptable',
                'attr' => array('class' => 'select-cc'),
                'mapped' => false,
            ))
           ->add('montant', NumberType::class, array(
     	   		'required' => true,
     	   		'label' => 'Montant (€)',
     	   		'scale' => 2,
     	   		'mapped' => false,
     	   		'disabled' => true,
           	    'attr' => array('class' => 'input-montant')
      	   ));
        ;

       
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Compta\Cheque',
            'arr_cheque_pieces' => null,
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_cheque';
    }
}
