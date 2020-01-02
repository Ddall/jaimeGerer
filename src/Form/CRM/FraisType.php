<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FraisType extends AbstractType
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
                'attr' => array('class' => 'produit-type'),
               
            ))
            ->add('nom', TextType::class, array(
                'required' => true,
                'label' => 'Nom'
            ))
            ->add('description', TextareaType::class, array(
                'required' => 'true',
                'label' => 'Description'
            ))
            ->add('montantHT', NumberType::class, array(
               'required' => true,
               'label' => 'Montant HT (€)',
               'scale' => 2,
               'attr' => array('class' => 'montant-ht')
             ))
           ->add('tva', NumberType::class, array(
                'required' => true,
                'label' => 'TVA (€)',
                'scale' => 2,
                'attr' => array('class' => 'montant-tva')
            ))
            ->add('montantTTC', NumberType::class, array(
                'required' => true,
                'label' => 'Montant TTC (€)',
                'scale' => 2,
                'attr' => array('class' => 'montant-ttc')
            ))
            ->add('date', DateType::class, array('widget' => 'single_text',
              'input' => 'datetime',
              'format' => 'dd/MM/yyyy',
              'attr' => array('class' => 'dateInput'),
              'required' => true,
              'label' => 'Date'
            ))
        ;

       
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Frais',
            'companyId' => null,
            'type' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_frais';
    }
}
