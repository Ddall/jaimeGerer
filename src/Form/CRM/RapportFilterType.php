<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RapportFilterType extends AbstractType
{

	protected $type;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    	$arr_champs = array();
    	switch($options['type']){

    		case 'compte':
    			$arr_champs = array(
    					'nom' => 'Nom',
    					'adresse' => 'Adresse',
    					'ville' => 'Ville',
    					'code_postal' => 'Code postal',
    					'region' => 'Region',
    					'pays' => 'Pays',
    					'telephone' => 'Téléphone',
    					'fax' => 'Fax',
    					'url' => 'Site web',
                        'secteurActivite' => 'Secteur d\'activité'
    			);
    			break;

    	case 'contact':
                $arr_champs = array(
                    'prenom' => 'Prenom',
                    'nom' => 'Nom',
                    'titre' => 'Titre',
                    'compte' => 'Organisation',
                    'adresse' => 'Adresse',
                    'ville' => 'Ville',
                    'codePostal' => 'Code postal',
                    'region' => 'Region',
                    'pays' => 'Pays',
                    'telephoneFixe' => 'Téléphone fixe',
                    'telephonePortable' => 'Tél. portable pro',
                    'fax' => 'Fax',
                    'email' => 'Email',
                    'TYPE' => 'Type de relation commerciale',
                    'ORIGINE' => 'Origine',
                    'RESEAU' => 'Reseau',
                    'carteVoeux' => 'Carte de voeux',
                    'newsletter' => 'Newsletter',
                    'rejetNewsletter' => 'Ne pas envoyer newsletter',
                    'rejetEmail' => 'Ne pas envoyer emailings',
                    'THEME_INTERET' => 'Thèmes d\'intérêt',
                    'SERVICE_INTERET' => 'Services d\'intérêt',
                    'SECTEUR' => 'Secteur d\'activité',
                    'dateCreation' => 'Date de création',
                    'dateEdition' => 'Date de modification',
                    'bounce' => 'Bounce'

                );
                break;

			case 'prospection':
    				$arr_champs = array(
    				'prenom' => 'Prenom',
    				'nom' => 'Nom',
    				'titre' => 'Titre',
    				'adresse' => 'Adresse',
    				'ville' => 'Ville',
    				'code_postal' => 'Code postal',
    				'region' => 'Region',
    				'pays' => 'Pays',
    				'telephoneFixe' => 'Téléphone fixe',
    				'telephonePortable' => 'Tél. portable pro',
    				'fax' => 'Fax',
    				'email' => 'Email',
    				'TYPE' => 'Type de relation commerciale',
    				'ORIGINE' => 'Origine',
    				'RESEAU' => 'Reseau',
    				'carteVoeux' => 'Carte de voeux',
    				'newsletter' => 'Newsletter',
    				'THEME_INTERET' => 'Thèmes d\'intérêt',
    				'SERVICE_INTERET' => 'Services d\'intérêt',
                    'SECTEUR' => 'Secteur d\'activité'
    				);
    			break;
    		case 'devis':
    			$arr_champs = array(
    				'objet' => 'Objet',
    				'num' => 'Num',
    				'adresse' => 'Adresse',
    				'ville' => 'Ville',
    				'code_postal' => 'Code postal',
    				'region' => 'Region',
    				'pays' => 'Pays',
    				'date_validite' => 'Date de validité',
    				'dateCreation' => 'Date de création',
    				'dateEdition' => 'Date de modification',
    				'Produit|nom' => 'Produit',
    				'Produit|tarifUnitaire*quantite' => 'Montant',
    				'Produit|remise' => 'Remise',
    			);
    			break;
    		case 'facture':
    			$arr_champs = array(
	    			'objet' => 'Objet',
	    			'num' => 'Num',
	    			'adresse' => 'Adresse',
	    			'ville' => 'Ville',
	    			'code_postal' => 'Code postal',
	    			'region' => 'Region',
	    			'pays' => 'Pays',
	    			'date_validite' => 'Date de validité',
	    			'dateCreation' => 'Date de création',
	    			'dateEdition' => 'Date de modification',
	    			'Produit|nom' => 'Produit',
	    			'Produit|tarifUnitaire*quantite' => 'Montant',
	    			'Produit|remise' => 'Remise',
    		  );
    			break;
    		case 'opportunite':
    			$arr_champs = array(
    				'nom' => 'Nom',
    				'montant' => 'Montant',
    				'echeance' => 'Echeance',
    				'type' => 'Type',
    				'origine' => 'Origine',
    				'probabilite' => 'Probabilite',
    				'dateCreation' => 'Date de création',
    				'dateEdition' => 'Date de modification',
    			);
    			break;
    	}
        $arr_champs = array_flip($arr_champs);

    	$arr_action = array(
    			'EQUALS' => 'Est',
    			'NOT_EQUALS' => 'N\'est pas',
    			'CONTAINS' => 'Contient',
    			'NOT_CONTAINS' => 'Ne contient pas',
    			'BEGINS_WITH' => 'Commence par',
    			'ENDS_WITH' => 'Finit par',
    			'EMPTY' => 'Est vide',
    			'NOT_EMPTY' => 'N\'est pas vide',
    			'IS_TRUE' => 'Est vrai',
    			'IS_FALSE' => 'Est faux',
    			'MORE_THAN' => '>',
                'LESS_THAN' => '<',
                'MORE_OR_EQUALS' => '>=',
    			'LESS_OR_EQUALS' => '<='
    	);
        $arr_action = array_flip($arr_action);

    	$arr_andor = array(
    			'AND' => 'Et',
    			'OR' => 'Ou',
    	);
        $arr_andor = array_flip($arr_andor);

    	$builder
    		->add('andor', ChoiceType::class, array(
    			'choices' => $arr_andor,
    			'required' => true,
    			'label_attr' => array('class' => 'hidden'),
    			'attr' => array('class' => 'select_andor')
    		))
	    	->add('champ', ChoiceType::class, array(
	    	        'attr' => array('class'=> 'settings','onClick'=> 'change(event)'),
	    			'choices' => $arr_champs,
	    			'required' => true,
	    			'label_attr' => array('class' => 'hidden')

	    	))
	    	->add('action', ChoiceType::class, array(
	    			'choices' => $arr_action,
	    			'required' => true,
	    			'label_attr' => array('class' => 'hidden'),
	    			'attr' => array('class' => 'select_action'),

	    	))
	    	->add('valeur', TextType::class, array(
	    			'required' => false,
	    			'label_attr' => array('class' => 'hidden'),
	    			'attr' => array('class' => 'input_valeur '),
	    	))
            ->add('endGroup', HiddenType::class, array(
                'attr' => array('class' => 'end-group-hidden'),
                'empty_data' => 0
            ))
        ;
    }

 /**
     * @param ExceptionInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    	$resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\RapportFilter',
            'type' => null,
    	));

    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_compte_filters';
    }
}
