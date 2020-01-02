<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class OpportuniteWonPlanPaiementType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $actionCommerciale = $builder->getData();

        $builder
            ->add('type', ChoiceType::class, array(
                'mapped' => false,
                'choices' => array(
                    'COMMANDE' => '100 % à la commande',
                    'FIN' => '100 % à la fin du projet',
                    'CUSTOM_PERCENT' => 'Personnalisé (pourcentage)',
                    'CUSTOM_MONTANT' => 'Personnalisé (montant)',
                ),
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'type-select'),
                'data' => $actionCommerciale->getModePaiement()
            ))
           	->add('planPaiements', CollectionType::class, array(
       			'type' => PlanPaiementType::class,
       			'allow_add' => true,
       			'allow_delete' => true,
       			'by_reference' => false,
				'label_attr' => array('class' => 'hidden'),
                'data' => $actionCommerciale->getPlansPaiementsCustom()
           	))
           ->add('submit', SubmitType::class, array(
                'label' => 'Valider',
                'attr' => array(
                    'class' => 'btn btn-success',
                    'disabled' => true,
                )
            ))
        ;

      }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Opportunite'
        ));

    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_opportunite_won_plan_paiement';
    }

}
