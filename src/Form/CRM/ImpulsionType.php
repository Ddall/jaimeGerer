<?php

namespace App\Form\CRM;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImpulsionType extends AbstractType
{
	protected $userId;
	protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->userId = $options['userId'];
        $this->companyId = $options['companyId'];

        $builder
            ->add('user', EntityType::class, array(
           			'class'=>'App:User',
           			'required' => true,
           			'label_attr' => array('class' => 'hidden'),
           			'query_builder' => function (EntityRepository $er) {
           				return $er->createQueryBuilder('u')
           				->where('u.company = :company')
           				->andWhere('u.enabled = :enabled')
           				->orWhere('u.id = :id')
           				->orderBy('u.firstname', 'ASC')
           				->setParameter('company', $this->companyId)
           				->setParameter('enabled', 1)
           				->setParameter('id', $this->userId);
           			},
           	))
             ->add('contact_name', TextType::class, array(
                    'required' => true,
                    'mapped' => false,
                    'label' => 'doit contacter',
                    'attr' => array('class' => 'typeahead-contact'),
            ))
           	->add('contact', HiddenType::class, array(
           			'required' => true,
           			'attr' => array('class' => 'entity-contact'),
           	))
           	->add('date', DateType::class, array(
                'widget' => 'single_text',
                'label' => 'Le',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
            ))
           	->add('methodeContact', TextType::class, array(
                'label' => 'par',
                'required' => true
            ))
            ->add('infos', TextareaType::class, array(
                'label' => 'au sujet de',
                'required' => true
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Impulsion',
            'userId' => null,
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_impulsion';
    }
}
