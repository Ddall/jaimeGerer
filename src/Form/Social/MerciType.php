<?php

namespace App\Form\Social;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MerciType extends AbstractType
{
    protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->companyId = $options['companyId'];
        $merci = $builder->getData();

        if(strtolower($merci->getType()) == "externe"){
            $builder
                ->add('fromText', TextType::class, array(
                    'label' => 'J\'ai été remercié par',
                    'required' => true
                ));
        } else {
             $builder
                ->add('to', EntityType::class, array(
                    'class'=>'App:User',
                    'required' => true,
                    'label' => 'Je dis merci à',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                        ->where('u.company = :company')
                        ->andWhere('u.enabled = :enabled')
                        ->andWhere('u.competCom = true')
                        ->orderBy('u.firstname', 'ASC')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('enabled', 1);
                    },
                ));
        }

        $builder
            ->add(\Symfony\Component\Form\Extension\Core\Type\TextType::class, TextType::class, array(
                'label' => 'Pour',
                'required' => true
            ))
             ->add('submit', SubmitType::class, array(
                'label' => 'Youpi !',
                'attr' => array('class' => 'btn btn-success'),
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Social\Merci',
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_social_merci';
    }
}
