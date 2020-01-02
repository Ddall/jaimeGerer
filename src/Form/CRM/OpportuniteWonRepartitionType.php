<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpportuniteWonRepartitionType extends AbstractType
{

    protected $isEdition;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isEdition = $options['isEdition'];

        $builder
           	->add('opportuniteRepartitions', CollectionType::class, array(
                'entry_type' => OpportuniteRepartitionType::class,
                'entry_options' => array(
                    'isEdition' => $this->isEdition,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                            'label_attr' => array('class' => 'hidden')
           	));

        if( $this->isEdition == false ){
            $builder->add('sousTraitance', CheckboxType::class, array(
                'mapped' => false,
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'data-toggle' => 'toggle',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-on' => 'Oui',
                    'data-off' => 'Non'
                ),
            ));
        }
            
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
            'data_class' => 'App\Entity\CRM\Opportunite',
            'isEdition' => null
        ));

    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_opportunite_won_repartition';
    }

}
