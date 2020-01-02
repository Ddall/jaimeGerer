<?php

namespace App\Form\Emailing;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampagneDestinatairesType extends AbstractType
{
    private $company;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->company = $options['company'];

        $builder
            ->add('rapport', EntityType::class, array(
                'class'=>'App:CRM\Rapport',
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                    ->where('r.type = :type')
                    ->andWhere('r.module = :module')
                    ->andWhere('r.company = :company')
                    ->orderBy('r.nom', 'ASC')
                    ->setParameter('type', 'contact')
                    ->setParameter('module', 'CRM')
                    ->setParameter('company', $this->company);
                },
                'required' => true,
                'label' => 'Rapport',
                'mapped' => false,
                'attr' => array('class' => 'rapport-select')
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Suite',
                'attr' => array('class' => 'btn btn-success btn-lg submit', 'disabled' => true)
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Emailing\Campagne',
            'company' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_emailing_campagne_destinataires';
    }

}
