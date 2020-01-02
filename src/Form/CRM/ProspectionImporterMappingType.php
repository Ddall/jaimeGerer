<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProspectionImporterMappingType extends AbstractType
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

		$compte = 'B';
		$adresse = 'C';
		$telephone_fixe = 'D';
		$activite = 'E';
		$nom = 'F';
		$prenom = 'G';
		$type = 'H';
		 
		foreach( $this->filename as $k=>$v )
		{
        $builder
            ->add('compte'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
            	'label' => 'Organisation',
				'choices' => $this->arr_headers[$k],
				'data' => $compte
        	))
            ->add('nom'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
            	'label' => 'Nom',
				'choices' => $this->arr_headers[$k],
				'data' => $nom
        	))
            ->add('prenom'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
            	'label' => 'Prénom',
				'choices' => $this->arr_headers[$k],
				'data' => $prenom
        	))
            ->add('adresse'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
            	'label' => 'Adresse',
				'choices' => $this->arr_headers[$k],
				'data' => $adresse
        	))
            ->add('telephoneFixe'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
            	'label' => 'Téléphone fixe',
				'choices' => $this->arr_headers[$k],
				'data' => $telephone_fixe
        	))
            ->add('activite'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
            	'label' => 'Activité',
				'choices' => $this->arr_headers[$k],
				'data' => $activite
        	))
            ->add('type'.$k, \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
        		'required' => true,
            	'label' => 'Type',
				'choices' => $this->arr_headers[$k],
				'data' => $type
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
            'form_index' => 0,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_importprospectionmapping';
    }
}
