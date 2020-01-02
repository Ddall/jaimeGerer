<?php

namespace App\Form\CRM;

use App\Entity\Settings;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionCommercialeType extends AbstractType
{
	protected $userGestionId;
    protected $companyId;
    protected $devis;
	protected $compte;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();

        $this->userGestionId = $options['userGestionId'];
        $this->companyId = $options['companyId'];
        $this->devis = $options['devis'];
        $this->compte = $options['compte'];

        $builder
            ->add('nom', TextType::class, array(
        		'label' => 'Objet'
            ))
            ->add('userGestion', EntityType::class, array(
                'class' => User::class,
                'required' => true,
                'label' => 'Gestionnaire de l\'opportunite',
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.company = :company')
                    ->andWhere('u.enabled = :enabled')
                    ->orWhere('u.id = :id')
                    ->orderBy('u.firstname', 'ASC')
                    ->setParameter('company', $this->companyId)
                    ->setParameter('enabled', 1)
                    ->setParameter('id', $this->userGestionId);
                },
            ))
            ->add('date', DateType::class, array('widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
                'label' => 'Date',
            ))
            ->add('dateValidite', DateType::class, array('widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
                'label' => 'Date de validité',
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getDateValidite() : new \DateTime(date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d')))))
            ))
            ->add('compte_name', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'Organisation',
                'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off')
            ))
            ->add('compte', HiddenType::class, array(
                'required' => true,
                'attr' => array('class' => 'entity-compte'),
            ))
            ->add('contact_name', TextType::class, array(
                'required' => true,
                'mapped' => false,
                'label' => 'Contact',
                'attr' => array('class' => 'typeahead-contact', 'autocomplete' => 'off')
            ))
            ->add('contact', HiddenType::class, array(
                'required' => false,
                'attr' => array('class' => 'entity-contact'),
            ))
            ->add('adresse', TextType::class, array(
                'required' => true,
                'label' => 'Adresse',
                'attr' => array('class' => 'input-adresse'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getAdresse() : null
            ))
            ->add('codePostal', TextType::class, array(
                'required' => true,
                'label' => 'Code postal',
                'attr' => array('class' => 'input-codepostal'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getCodePostal() : null
            ))
            ->add('ville', TextType::class, array(
                'required' => true,
                'label' => 'Ville',
                'attr' => array('class' => 'input-ville'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getVille() : null
            ))
            ->add('region', TextType::class, array(
                'required' => true,
                'label' => 'Région',
                'attr' => array('class' => 'input-region'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getRegion() : null
            ))
            ->add('pays', TextType::class, array(
                'required' => true,
                'label' => 'Pays',
                'attr' => array('class' => 'input-pays'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getPays() : null
            ))
            ->add('origine', EntityType::class, array(
                'class'=> Settings::class,
                'choice_label' => 'valeur',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                    ->where('s.parametre = :parametre')
                    ->andWhere('s.company = :company')
                    ->orderBy('s.valeur', 'ASC')
                    ->setParameter('parametre', 'ORIGINE')
                    ->setParameter('company', $this->companyId);
                },
                'required' => false,
                'label' => 'Origine'
            ))
            ->add('priveOrPublic', ChoiceType::class, array(
                'label' => 'Privé ou public ?',
                'required' => true,
                'attr' => array('class' => 'input-priveOrPublic'),
                'choices' => array(
                    'PUBLIC' => 'Public',
                    'PRIVE' => 'Privé'
                ),
                'data' => $data->getPriveOrPublic() != null ?  $data->getPriveOrPublic() : ($this->compte ? $this->compte->getPriveOrPublic() : null)
            ))
            ->add('analytique', EntityType::class, array(
                'class'=> Settings::class,
                'required' => true,
                'label' => 'Analytique',
                'choice_label' => 'valeur',
                'attr' => array('class' => 'devis-analytique'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                    ->where('s.company = :company')
                    ->andWhere('s.module = :module')
                    ->andWhere('s.parametre = :parametre')
                    ->setParameter('company', $this->companyId)
                    ->setParameter('module', 'CRM')
                    ->setParameter('parametre', 'ANALYTIQUE');
                },
            ))
            ->add('appelOffre', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'class' => 'appel-offre-checkbox',
                    'data-toggle' => 'toggle',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-on' => 'Oui',
                    'data-off' => 'Non',
                    'data-size' => 'mini'
                )
            ))
             ->add('prescription', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'data-toggle' => 'toggle',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-on' => 'Oui',
                    'data-off' => 'Non',
                    'data-size' => 'mini'
                )
            ))
            ->add('nouveauCompte', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'class' => 'nouveau-compte-checkbox',
                    'data-toggle' => 'toggle',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-on' => 'Oui',
                    'data-off' => 'Non',
                    'data-size' => 'mini'
                )
            ))
            ->add('probabilite', EntityType::class, array(
                'class'=> Settings::class,
                'choice_label' => 'valeur',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                    ->where('s.parametre = :parametre')
                    ->andWhere('s.company = :company')
                    ->setParameter('parametre', 'OPPORTUNITE_STATUT')
                    ->setParameter('company', $this->companyId);
                },
                'required' => true,
                'label' => 'Probabilité',
                'attr' => array('class' => 'opp-probabilite')
            ))
            ->add('produits', CollectionType::class, array(
                'entry_type' => ProduitType::class,
                'entry_options' => array(
                    'companyId' => $this->companyId,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label_attr' => array('class' => 'hidden'),
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getProduits() : null
            ))
            ->add('sousTotal', NumberType::class, array(
                'required' => false,
                'label' => 'Sous total',
                'scale' => 2,
                'mapped' => false,
                'disabled' => true,
                'attr' => array('class' => 'devis-sous-total')
            ))
            ->add('remise', NumberType::class, array(
                'required' => false,
                'label' => 'Remise',
                'scale' => 2,
                'attr' => array('class' => 'devis-remise')
            ))
            ->add('taxe', NumberType::class, array(
                'required' => false,
                'scale' => 2,
                'label_attr' => array('class' => 'hidden'),
                'attr' => array('class' => 'devis-taxe'),
                'disabled' => true,
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getTaxe() : null
            ))
            ->add('taxePercent', PercentType::class, array(
                'required' => false,
                'scale' => 2,
                'label' => 'TVA',
                'attr' => array('class' => 'devis-taxe-percent'),
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getTaxePercent() : null
            ))
            ->add('totalHT', NumberType::class, array(
                'required' => false,
                'label' => 'Total HT',
                'scale' => 2,
                'mapped' => false,
                'disabled' => true,
                'attr' => array('class' => 'devis-total-ht')
            ))
            ->add('totalTTC', NumberType::class, array(
                'required' => false,
                'label' => 'Total TTC',
                'scale' => 2,
                'mapped' => false,
                'disabled' => true,
                'attr' => array('class' => 'devis-total-ttc')
            )) 
            ->add('cgv', TextareaType::class, array(
                'required' => false,
                'label' => 'Conditions d\'utilisation',
                'mapped' => false,
            ))
            ->add('description', TextareaType::class, array(
                'required' => false,
                'label' => 'Commentaire',
                'mapped' => false,
                'data' => $this->companyId == 1 ? ($this->devis->getDescription() !== null ? $this->devis->getDescription() : 'Les frais de déplacement, hébergement et restauration seront ajoutés en sus sur présentation de justificatifs.') : ''
            ))
            ->add('tempsCommercial', NumberType::class, array(
                'required' => false,
                'label' => 'Temps passé en commercial'
            ))
            ->add('fichier', FileType::class, array(
                'label' => 'Proposition commerciale',
                'required' => false
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
            'userGestionId' => null,
            'companyId' => null,
            'devis' => null,
            'compte' => null,
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
