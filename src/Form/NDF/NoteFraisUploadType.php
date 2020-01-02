<?php

namespace App\Form\NDF;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteFraisUploadType extends AbstractType
{
	protected $companyId;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->companyId = $options['companyId'];

  		$arr_mois = array();
  		for($i=1; $i<=12; $i++){
  			$month = str_pad($i, 2, "0", STR_PAD_LEFT);
  			$arr_mois[$month] = $month;
  		}
  		$arr_annees = array(2016 => 2016, 2017 => 2017);

        $builder
            ->add('month', ChoiceType::class, array(
	        	'label' => '',
	            'required' => true,
	            'choices' => $arr_mois,

        	))
             ->add('year', ChoiceType::class, array(
	        	'label' => '',
	            'required' => true,
	            'choices' => $arr_annees,

        	))
        	->add('compteComptable', EntityType::class, array(
        			'class'=>'App:Compta\CompteComptable',
        			'choice_label' => 'nom',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
								->leftJoin('App:Settings', 's', 'WITH', 's.compteComptable = c.id')
        				->where('c.company = :company')
        				->andWhere('c.num LIKE :num')
								->andWhere('s.parametre LIKE :parametre')
        				->setParameter('company', $this->companyId)
        				->setParameter('num', '421%')
						->setParameter('parametre', 'COMPTE_COMPTABLE_NOTE_FRAIS')
        				->orderBy('c.nom');
        			},
        			'required' => true,
        			'label' => 'Compte comptable',
        	))
            ->add('user', EntityType::class, array(
        		'required' => true,
          	'label' => 'Salarié',
          	'mapped' => true,
						'class'=>'App:User',
						'query_builder' => function (EntityRepository $er) {
							return $er->createQueryBuilder('u')
								->where('u.company = :company')
								->setParameter('company', $this->companyId);
						},
       		))
       		->add('file', FileType::class, array(
       				'label'	=> 'Fichier',
       				'required' => true,
       				'attr' => array('class' => 'file-upload'),
       				'mapped' => false
       		))
            ->add('submit',SubmitType::class, array(
            		'label' => 'Enregistrer',
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
            'data_class' => 'App\Entity\NDF\NoteFrais',
            'companyId' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_compta_notefrais';
    }
}
