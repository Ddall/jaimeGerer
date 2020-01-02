<?php

namespace App\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadHistoriqueDepenseMappingType extends AbstractType
{
	
	protected $arr_headers;
	protected $filename;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->arr_headers = $options['arr_headers'];
        $this->filename = $options['filename'];

    	$compte = "";
    	$num = "";
    	$date = "";
    	$dateCreation = "";
    	$etat = "";
    	$user = "";
    	$analytique = "";
    	$modePaiement = "";
    	$taxe = "";
    	
    	$produitNom = "";
    	$produitTarif = "";
    	$produitTaxe = "";
    	$produitCompteComptable = "";
    	
    	foreach($this->arr_headers as $header){
    		
    		
    		if(stristr($header, 'fournisseur')){
    			$compte = $header;
    		}
    		if(stristr($header, 'date')){
    			$date = $header;
    		}
    		if(stristr($header, 'création')){
    			$dateCreation = $header;
    		}
    		
    		if(stristr($header, 'tva')){
    			if(stristr($header, 'total')){
    				$tva = $header;
    			} else{
    				$tauxTVA = $header;
    			}
    		}
    		if(stristr($header, 'remise')){
    			$remise = $header;
    		}
    		
    		if(stristr($header, 'état') || stristr($header, 'etat')){
    			$etat = $header;
    		}
    		
    		if(stristr($header, 'métier') || stristr($header, 'metier')){
    			$analytique = $header;
    		}
    		
    		if(stristr($header, 'ht')){
    			$produitTarif = $header;
    		}
    		
    		if(stristr($header, 'créateur')){
    			$user = $header;
    		}
    		
    		if(stristr($header, 'paiement')){
    			$modePaiement = $header;
    		}
    		
    		if(stristr($header, 'numéro') || stristr($header, 'n°') || stristr($header, 'référence') || stristr($header, 'reference')){
    			$num = $header;
    		}
    		
    		if(stristr($header, 'classification') ){
    			$produitNom = $header;
    			$produitCompteComptable = $header;
    		}
    		if(stristr($header, 'HT') || stristr($header, 'montant') ){
    			$produitTarif = $header;
    		}
    		if(stristr($header, 'taxe') || stristr($header, 'TVA') ){
    			$produitTaxe = $header;
    			$taxe = $header;
    		}
    		if(stristr($header, 'HT') || stristr($header, 'montant') ){
    			$produitTarif = $header;
    		}
    	}

    	$arr_formats = array(
    			'd/m/Y' => 'jour/mois/année (4 chiffres)',
    			'd/m/y'=> 'jour/mois/année (2 chiffres)',
    			'd-m-Y' => 'jour-mois-année (4 chiffres)',
    			'd-m-y'=> 'jour-mois-année (2 chiffres)',
    	);
    	
       	$builder
	       	->add('dateFormat', ChoiceType::class, array(
	       			'label'	=> 'Format des dates du fichier importé',
	       			'required' => true,
	       			'choices' => $arr_formats
	       	))
			->add('filepath', HiddenType::class, array(
					'data' => $this->filename,
			
			))
			->add('compte', ChoiceType::class, array(
					'required' => true,
					'label' => 'Fournisseur',
					'choices' => $this->arr_headers,
					'data' => $compte
			))
			->add('num', ChoiceType::class, array(
					'required' => true,
					'label' => 'Numéro de dépense',
					'choices' => $this->arr_headers,
					'data' => $num
			))
			->add('date', ChoiceType::class, array(
					'required' => true,
					'label' => 'Date de la dépense',
					'choices' => $this->arr_headers,
					'data' => $date
			))
			->add('dateCreation', ChoiceType::class, array(
					'required' => true,
					'label' => 'Date de création',
					'choices' => $this->arr_headers,
					'data' => $dateCreation
			))
			->add('etat', ChoiceType::class, array(
					'required' => true,
					'label' => 'Etat',
					'choices' => $this->arr_headers,
					'data' => $etat
			))
			->add('modePaiement', ChoiceType::class, array(
					'required' => true,
					'label' => 'Mode de paiement',
					'choices' => $this->arr_headers,
					'data' => $modePaiement
			))
			->add('user', ChoiceType::class, array(
					'required' => false,
					'label' => 'Créateur',
					'choices' => $this->arr_headers,
					'data' => $user
			))
			->add('taxe', ChoiceType::class, array(
					'required' => true,
					'label' => 'Taxe',
					'choices' => $this->arr_headers,
					'data' => $taxe
			))
			->add('analytique', ChoiceType::class, array(
					'required' => true,
					'label' => 'Analytique',
					'choices' => $this->arr_headers,
					'data' => $analytique
			))
			->add('produitNom', ChoiceType::class, array(
					'required' => true,
					'label' => 'Nom',
					'choices' => $this->arr_headers,
					'data' => $produitNom
			))
			->add('produitTarif', ChoiceType::class, array(
					'required' => true,
					'label' => 'Tarif',
					'choices' => $this->arr_headers,
					'data' => $produitTarif
			))
			->add('produitTaxe', ChoiceType::class, array(
					'required' => true,
					'label' => 'Taxe',
					'choices' => $this->arr_headers,
					'data' => $produitTaxe
			))
			->add('produitCompteComptable', ChoiceType::class, array(
					'required' => true,
					'label' => 'Compte comptable',
					'choices' => $this->arr_headers,
					'data' => $produitCompteComptable
			))
			->add('submit',SubmitType::class, array(
					'label' => 'Suite',
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
        ));
    }
    
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_uploadhistoriquedepensemapping';
    }
}
