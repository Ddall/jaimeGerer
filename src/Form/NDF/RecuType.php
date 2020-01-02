<?php

namespace App\Form\NDF;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecuType extends AbstractType
{
    protected $companyId;
    protected $fc;
    protected $ccDefaut;
    protected $deplacementVoiture;
    protected $user;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->companyId = $options['companyId'];
        $this->fc = $options['fc'];
        $this->ccDefaut = $options['ccDefaut'];
        $this->deplacementVoiture = $options['deplacementVoiture'];
        $this->user = $options['user'];

        $data = $builder->getData();
     
        $builder
            ->add('projet_name', TextType::class, array(
                'required' => false,
                'mapped' => false,
                'label' => 'Projet (numéro de bon de commande, nom du projet ou du client)',
                'attr' => array('class' => 'typeahead-projet', 'autocomplete' => 'off')
            ))
            ->add('projet_entity', HiddenType::class, array(
                'required' => false,
                'attr' => array('class' => 'entity-projet'),
                'mapped' => false
            ))
            ->add('refacturable', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'data-toggle' => 'toggle',
                    'data-on' => 'Oui',
                    'data-off' => 'Non',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-size' => 'small',
                    'class' => 'toggle-refacturable'
                ),
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
            ->add('analytique', EntityType::class, array(
       			'class'=>'App:Settings',
       			'required' => true,
       			'label' => 'Analytique',
       			'query_builder' => function (EntityRepository $er) {
       				return $er->createQueryBuilder('s')
       				     ->where('s.company = :company')
       				     ->andWhere('s.parametre = :parametre')
       				     ->setParameter('company', $this->companyId)
       				     ->setParameter('parametre', 'analytique')
                        ->orderBy('s.valeur', 'ASC');
       			},
                'data' => $this->fc
           	))
            ->add('compteComptable', EntityType::class, array(
                'class'=>'App:Compta\CompteComptable',
                'required' => true,
                'label' => 'Compte comptable',
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                      ->where('c.company = :company')
                      ->andWhere('c.num LIKE :num')
                      ->setParameter('company', $this->companyId)
                      ->setParameter('num', '6%')
                      ->orderBy('c.nom', 'ASC');
                },
                'data' => $data->getId() ? $data->getCompteComptable() : $this->ccDefaut
            ))
            ->add('save', SubmitType::class, array(
              'label' => 'Enregistrer et revenir à la liste des reçus',
            ));

            if($this->deplacementVoiture === false){
                $builder->add('fournisseur', TextType::class, array(
                    'label' => 'Fournisseur',
                    'required' => true
                ))
                ->add('date', DateType::class, array('widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'dateInput', 'autocomplete' => 'off'),
                    'required' => true,
                    'label' => 'Date du reçu'
                ));
            } else {
                $builder->add('trajet', TextType::class, array(
                    'label' => 'Trajet',
                    'required' => true
                ))
                ->add('date', DateType::class, array('widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'dateInput', 'autocomplete' => 'off'),
                    'required' => true,
                    'label' => 'Date du trajet'
                ))
                ->add('distance', IntegerType::class, array(
                    'label' => 'Distance (km)',
                    'required' => true
                ))
                ->add('marqueVoiture', TextType::class, array(
                    'label' => 'Marque du véhicule',
                    'required' => true,
                    'data' => $data->getId() ? $data->getMarqueVoiture() : $this->user->getMarqueVoiture()
                ))
                ->add('modeleVoiture', TextType::class, array(
                    'label' => 'Modèle du véhicule',
                    'required' => true,
                    'data' => $data->getId() ? $data->getModeleVoiture() : $this->user->getModeleVoiture()
                ))
                ->add('immatriculationVoiture', TextType::class, array(
                    'label' => 'Immatriculation du véhicule',
                    'required' => true,
                    'data' => $data->getId() ? $data->getImmatriculationVoiture() : $this->user->getImmatriculationVoiture()
                ))
                 ->add('puissanceVoiture', ChoiceType::class, array(
                    'required' => true,
                    'label' => 'Puissance fiscale du véhicule',
                    'choices' => array(
                        3 => '3 CV',
                        4 => '4 CV',
                        5 => '5 CV',
                        6 => '6 CV',
                        7 => '7 CV et plus'
                    ),
                    'data' => $data->getId() ? $data->getPuissanceVoiture() : $this->user->getPuissanceVoiture()
                ))
                ;
            }
           
          
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\NDF\Recu',
            'companyId' => null,
            'fc' => null,
            'ccDefaut' => null,
            'deplacementVoiture' => null,
            'user' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ndf_recu';
    }
}
