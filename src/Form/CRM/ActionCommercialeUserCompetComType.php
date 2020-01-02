<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ActionCommercialeUserCompetComType extends AbstractType
{

    private $actionCommerciale;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->actionCommerciale = $builder->getData();

        $builder
            ->add('userCompetCom', EntityType::class, array(
                'class'=>'App:User',
                'required' => true,
                'label' => 'A qui revient cette affaire ?',
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.company = :company')
                    ->andWhere('u.enabled = :enabled')
                    ->andWhere('u.competCom = true')
                    ->orWhere('u.id = :id')
                    ->orderBy('u.firstname', 'ASC')
                    ->setParameter('company', $this->actionCommerciale->getUserCreation()->getCompany())
                    ->setParameter('enabled', 1)
                    ->setParameter('id', $this->actionCommerciale->getUserCompetCom());
                },
            ))
             ->add('submit', SubmitType::class, array(
                'label' => 'Valider',
                'attr' => array('class' => 'btn btn-success'),
            ));
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
        return 'appbundle_crm_opportunite';
    }
}
