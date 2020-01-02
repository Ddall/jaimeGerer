<?php

namespace App\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompteImportType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       	$builder ->add('file', FileType::class, array(
						'label'	=> 'Fichier',
						'required' => true,
						'attr' => array('class' => 'file-upload')
					))
					->add('submit',SubmitType::class, array(
						'label' => 'Suite',
						'attr' => array('class' => 'btn btn-success')
					));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_compteimport';
    }
}
