<?php

namespace App\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UploadReleveBancaireMappingType extends AbstractType
{
	
	protected $arr_headers;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->arr_headers = $options['arr_headers'];

    	$libelle = "";
    	$date = "";
    	$credit = "";
    	$debit = "";
    	foreach($this->arr_headers as $header){
    		
    		if(strstr($header, 'Libellé') || strstr($header, 'Libelle') ){
    			$libelle = $header;
    		} 
    		if(strstr($header, 'Date') && !strstr($header, 'valeur')){
    			$date = $header;
    		} 
    		if(strstr($header, 'Crédit') || strstr($header, 'Credit')){
    			$credit = $header;
    		}
    		if(strstr($header, 'Débit') || strstr($header, 'Debit')){
    			$debit = $header;
    		}
    	}

       		$builder
				->add('date', ChoiceType::class, array(
						'required' => true,
						'label' => 'Date',
						'choices' => $this->arr_headers,
						'data' => $date
				))
				->add('libelle', ChoiceType::class, array(
						'required' => true,
						'label' => 'Libellé',
						'choices' => $this->arr_headers,
						'data' => $libelle
				))
				->add('debit', ChoiceType::class, array(
						'required' => true,
						'label' => 'Débit',
						'choices' => $this->arr_headers,
						'data' => $debit
				))
				->add('credit', ChoiceType::class, array(
						'required' => true,
						'label' => 'Crédit',
						'choices' => $this->arr_headers,
						'data' => $credit
				))
				->add('submit',SubmitType::class, array(
						'label' => 'Importer',
						'attr' => array('class' => 'btn btn-success')
				));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'arr_headers' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_uploadrelevebancaire';
    }
}
