<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactImporterMappingType extends AbstractType
{
	
	protected $arr_headers;
	protected $filename;
	protected $form_index;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->arr_headers = $options['arr_headers'];
        $this->filename = $options['filename'];
        $this->form_index = $options['form_index'];

		$nom = 'A';
		$prenom = 'B';
		$titre = 'C';
        $reseau = 'D';
        $compte = 'E';
        $telephone_fixe = 'F';
        $telephone_portable = 'G';
        $telephoneAutres = 'H';
        $fax = 'I';
        $email = 'J';
        $email2 = 'K';
        $adresse = 'L';
        $codePostal = 'M';
        $ville = 'N';
        $region = 'O';
        $pays = 'P';
        $description = 'Q';
        $carteVoeux = 'R';
        $newsLetter = 'S';
        $origine = 'T';
        $serviceInteret = 'U';
        $themeInteret = 'V';
        $secteurActivite = 'W';


		$arr_formats = array(
				'd/m/Y' => 'jour/mois/année (4 chiffres)',
				'd/m/y'=> 'jour/mois/année (2 chiffres)',
				'd-m-Y' => 'jour-mois-année (4 chiffres)',
				'd-m-y'=> 'jour-mois-année (2 chiffres)',
		);
 
		foreach( $this->filename as $k=>$v )
		{
        $builder
			->add('dateFormat'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
					'label'	=> 'Format des dates',
					'required' => true,
					'choices' => $arr_formats
			))
            ->add('prenom'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Prénom',
				'choices' => $this->arr_headers[$k],
				'data' => $prenom
        	))
            ->add('nom'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Nom',
				'choices' => $this->arr_headers[$k],
				'data' => $nom
        	))
            ->add('pays'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Pays',
				'choices' => $this->arr_headers[$k],
				'data' => $pays
        	))
            ->add('reseau'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Réseau',
				'choices' => $this->arr_headers[$k],
				'data' => $reseau
        	))
            ->add('compte'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Nom de l\'organisation',
				'choices' => $this->arr_headers[$k],
				'data' => $compte
        	))
            ->add('titre'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Titre/fonction',
				'choices' => $this->arr_headers[$k],
				'data' => $titre
        	))
            ->add('description'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Description',
				'choices' => $this->arr_headers[$k],
				'data' => $description
        	))
            ->add('email'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Email',
				'choices' => $this->arr_headers[$k],
				'data' => $email
        	))
            ->add('telephoneFixe'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Téléphone fixe',
				'choices' => $this->arr_headers[$k],
				'data' => $telephone_fixe
        	))
            ->add('telephonePortable'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Tél. portable pro',
				'choices' => $this->arr_headers[$k],
				'data' => $telephone_portable
        	))
            ->add('adresse'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Adresse',
                'choices' => $this->arr_headers[$k],
                'data' => $adresse
            ))
            ->add('telephoneAutres'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Tel. (autre)',
                'choices' => $this->arr_headers[$k],
                'data' => $telephoneAutres
            ))
            ->add('fax'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Fax',
                'choices' => $this->arr_headers[$k],
                'data' => $fax
            ))
            ->add('email2'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Mail(2)',
                'choices' => $this->arr_headers[$k],
                'data' => $email2
            ))
            ->add('codePostal'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Code postal',
                'choices' => $this->arr_headers[$k],
                'data' => $codePostal
            ))
            ->add('ville'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Ville',
                'choices' => $this->arr_headers[$k],
                'data' => $ville
            ))
            ->add('region'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Region',
                'choices' => $this->arr_headers[$k],
                'data' => $region
            ))
            ->add('carteVoeux'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Carte de voeux',
                'choices' => $this->arr_headers[$k],
                'data' => $carteVoeux
            ))
            ->add('newsletter'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'News letter',
                'choices' => $this->arr_headers[$k],
                'data' => $newsLetter
            ))


            ->add('origine'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Origine',
                'choices' => $this->arr_headers[$k],
                'data' => $origine
            ))
            ->add('serviceInteret'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Service d\'interêt',
                'choices' => $this->arr_headers[$k],
                'data' => $serviceInteret
            ))
            ->add('themeInteret'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Thème d\'interêt',
                'choices' => $this->arr_headers[$k],
                'data' => $themeInteret
            ))
            ->add('secteurActivite'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Secteur d\'activité',
                'choices' => $this->arr_headers[$k],
                'data' => $secteurActivite
            ))
			->add('filepath'.$k, \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
					'label' => $v['nom_original'],
					'data' => $k,
			));
		}
			$builder
			->add('submit',SubmitType::class, array(
					'label' => 'Importer les contacts',
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
            'arr_headers' => null,
            'filename' => null,
            'form_index' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_importcontactemapping';
    }
}
