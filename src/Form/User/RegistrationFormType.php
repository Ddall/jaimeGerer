<?php
namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->remove('username');
		$builder->remove('email');
		$builder->remove('plainPassword');
	
		$builder
			->add('firstname', TextType::class, array(
					'label' => 'Prénom')
			)
            ->add('lastname', TextType::class, array(
            		'label' => 'Nom')
            )
             ->add('email', EmailType::class, array(
            		'label' => 'Email')
            )
             ->add('plainPassword', PasswordType::class, array(
             	'label' => 'Mot de passe'
             ))
             ->add('username', TextType::class, array(
             		'label' => 'Identifiant' 
             ));
	}

	public function getParent()
	{
		return 'fos_user_registration';
	}

	/**
	 * @return string
	 */
	public function getBlockPrefix()
	{
		return 'appbundle_user_registration';
	}
}
