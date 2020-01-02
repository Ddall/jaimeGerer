<?php

namespace App\Form\User;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{

    protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->companyId = $options['company_id'];

        $builder
            ->add('firstName', TextType::class, array(
                'required' => true,
                'label' => 'Prénom'
            ))
            ->add('lastName', TextType::class, array(
                'required' => true,
                'label' => 'Nom'
            ))
            ->add('email', EmailType::class, array(
                'required' => true,
                'label' => 'Adresse email'
            ))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'label' => ' ',
                'attr' => array(
                    'data-toggle'=> "toggle",
                    'data-onstyle'=> "success",
                    'data-offstyle'=> "danger",
                    'data-on'=> "Oui",
                    'data-off'=> "Non"
                )
            ))
            ->add('admin', CheckboxType::class, array(
                'required' => false,
                'label' => ' ',
                'mapped' => false,
                'attr' => array(
                    'data-toggle'=> "toggle",
                    'data-onstyle'=> "success",
                    'data-offstyle'=> "danger",
                    'data-on'=> "Oui",
                    'data-off'=> "Non"
                )
            ))
            ->add('permissions', ChoiceType::class, array(
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'label' => 'Peut utiliser :',
                'choices' => array(
                    'ROLE_COMMERCIAL' => 'J\'aime le commercial',
                    'ROLE_COMPTA' => 'J\'aime la compta',
                    'ROLE_COMMUNICATION' => 'J\'aime communiquer',
                    //'ROLE_RH' => 'J\'aime le recrutement',
                    'ROLE_NDF' => 'J\'aime les notes de frais',
                    'ROLE_TIMETRACKER' => 'J\'aime compter mon temps',
                )
            ))
            ->add('tauxHoraire', IntegerType::class, array(
                'required' => false,
                'label' => 'Taux horaire'
            ))
            ->add('compteComptableNoteFrais', EntityType::class, array(
                'class'=>'App:Compta\CompteComptable',
                'required' => false,
                'label' => 'Compte comptable pour les notes de frais',
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                      ->where('c.company = :company')
                      ->andWhere('c.num LIKE :num')
                      ->setParameter('company', $this->companyId)
                      ->setParameter('num', '62510%')
                      ->orderBy('c.nom', 'ASC');
                },
            ))
            ->add('modeleVoiture', TextType::class, array(
                'required' => false,
                'label' => 'Modèle'
            ))
            ->add('marqueVoiture', TextType::class, array(
                'required' => false,
                'label' => 'Marque'
            ))
            ->add('immatriculationVoiture', TextType::class, array(
                'required' => false,
                'label' => 'Immatriculation'
            ))
            ->add('puissanceVoiture', ChoiceType::class, array(
                'required' => false,
                'label' => 'Puissance fiscale',
                'choices' => array(
                    3 => '3 CV',
                    4 => '4 CV',
                    5 => '5 CV',
                    6 => '6 CV',
                    7 => '7 CV et plus'
                )
            ))
            ->add('submit', SubmitType::class, array(
              'label' => 'Enregistrer',
              'attr' => array('class' => 'btn btn-success')
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\User',
            'company_id' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user';
    }
}
