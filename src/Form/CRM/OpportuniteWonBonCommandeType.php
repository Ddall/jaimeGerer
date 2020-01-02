<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class OpportuniteWonBonCommandeType extends AbstractType
{


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           	->add('bonsCommande', CollectionType::class, array(
           			'type' => new BonCommandeType(),
           			'allow_add' => true,
           			'allow_delete' => true,
           			'by_reference' => false,
								'label_attr' => array('class' => 'hidden')
           	));
            
        $builder
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
        return 'appbundle_crm_opportunite_won_bon_commande';
    }

}
