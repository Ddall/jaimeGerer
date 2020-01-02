<?php

namespace App\Form\Compta;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadReleveBancaireType extends AbstractType
{

	protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->companyId = $options['companyId'];

    	$arr_formats = array(
    		'd/m/Y' => 'jour/mois/année (4 chiffres)',
    		'd/m/y'=> 'jour/mois/année (2 chiffres)',
    		'd-m-Y' => 'jour-mois-année (4 chiffres)',
    		'd-m-y'=> 'jour-mois-année (2 chiffres)',
    	);

       	$builder ->add('compteBancaire', EntityType::class, array(
        			'required' => true,
        			'class' => 'App:Compta\CompteBancaire',
        			'label' => 'Compte bancaire',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
        				->andWhere('c.company = :company')
        				->setParameter('company', $this->companyId);
        			},
        			'attr' => array('class' => 'compte-select')
        			))
        			->add('solde', NumberType::class, array(
        					'label'	=> 'Solde du compte (€)',
        					'required' => true,
        					'attr' => array('class' => 'input-solde'),
        					'precision' => 2
        			))
        			->add('dateFormat', ChoiceType::class, array(
        					'label'	=> 'Format des dates du fichier importé',
        					'required' => true,
        					'choices' => $arr_formats
        			))
						->add('file', FileType::class, array(
			        'label' => 'Fichier',
			        'required' => true,
							'attr' => array('class' => 'file-upload'),
			        'constraints' => array(
	            	new File(array(
	                 'mimeTypes' => array(
	                     'text/csv',
											 'text/plain'
	                 ),
	                 'mimeTypesMessage' => 'Vous devez choisir un fichier .csv',
	         				)
	      				)
							)
						))
						->add('submit',SubmitType::class, array(
							'label' => 'Suite',
							'attr' => array('class' => 'btn btn-success')
						));
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'companyId' => null,
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
