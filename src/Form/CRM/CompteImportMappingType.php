<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompteImportMappingType extends AbstractType
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

    	$nom = "";
    	$adresse = "";
    	$codePostal = "";
    	$ville = "";
    	$region = "";
    	$pays = "";
    	$telephone = "";
    	$fax = "";
    	$url = "";
    	$description = "";
    	
//     	foreach($this->arr_headers as $header){
    		
//     		if(stristr($header, 'objet')){
//     			$objet = $header;
//     		} 
//     		if(stristr($header, 'numéro') || stristr($header, 'n°')){
//     			$num = $header;
//     		} 
//     		if(stristr($header, 'client')){
//     			$compte = $header;
//     		}
//     		if(stristr($header, 'date')){
//     			$date = $header;
//     		}
//     		if(stristr($header, 'échéance') || stristr($header, 'echeance') || stristr($header, 'validité') || stristr($header, 'validite')){
//     			$echeance = $header;
//     		}
//     		if(stristr($header, 'tva')){
//     			if(stristr($header, 'total')){
//     				$tva = $header;
//     			} else{
//     				$tauxTVA = $header;
//     			}
//     		}
//     		if(stristr($header, 'remise')){
//     			$remise = $header;
//     		}
//     		if(stristr($header, 'description')){
//     			$description = $header;
//     		}
//     		if(stristr($header, 'état') || stristr($header, 'etat')){
//     			$etat = $header;
//     		}
    		
//     		if(stristr($header, 'métier') || stristr($header, 'metier')){
//     			$produitType = $header;
//     		}
//     		if(stristr($header, 'désignation') || stristr($header, 'designation')){
//     			$produitDescription = $header;
//     		}
//     		if(stristr($header, 'ht')){
//     			$produitTarif = $header;
//     		}
//     		if(stristr($header, 'quantité') || stristr($header, 'qté')){
//     			$produitQuantite = $header;
//     		}
//     		if(stristr($header, 'créateur')){
//     			$user = $header;
//     		}
//     	}

       	$builder
			->add('filepath', HiddenType::class, array(
					'data' => $this->filename,
			
			))
// 			->add('objet', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Objet',
// 					'choices' => $this->arr_headers,
// 					'data' => $objet
// 			))
// 			->add('num', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Numéro de facture',
// 					'choices' => $this->arr_headers,
// 					'data' => $num
// 			))
// 			->add('compte', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Compte',
// 					'choices' => $this->arr_headers,
// 					'data' => $compte
// 			))
// 			->add('date', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Date de la facture',
// 					'choices' => $this->arr_headers,
// 					'data' => $date
// 			))
// 			->add('echeance', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Echeance',
// 					'choices' => $this->arr_headers,
// 					'data' => $echeance
// 			))
// 			->add('tva', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Montant TVA',
// 					'choices' => $this->arr_headers,
// 					'data' => $tva
// 			))
// 			->add('tauxTVA', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Taux TVA',
// 					'choices' => $this->arr_headers,
// 					'data' => $tauxTVA
// 			))
// 			->add('remise', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Remise',
// 					'choices' => $this->arr_headers,
// 					'data' => $remise
// 			))
// 			->add('description', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => false,
// 					'label' => 'Description',
// 					'choices' => $this->arr_headers,
// 					'data' => $description
// 			))
// 			->add('etat', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Etat',
// 					'choices' => $this->arr_headers,
// 					'data' => $etat
// 			))
// 			->add('user', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => false,
// 					'label' => 'Créateur',
// 					'choices' => $this->arr_headers,
// 					'data' => $user
// 			))
// 			->add('produitType', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Type',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitType
// 			))
// 			->add('produitNom', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Nom',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitNom
// 			))
// 			->add('produitDescription', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Description',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitDescription
// 			))
// 			->add('produitTarif', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Tarif',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitTarif
// 			))
// 			->add('produitQuantite', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
// 					'required' => true,
// 					'label' => 'Quantité',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitQuantite
// 			))
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
            'data_class' => 'App\Entity\CRM\Compte',
            'arr_headers' => null,
            'filename' => null,
        ));
    }
    
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_importcomptemapping';
    }
}
