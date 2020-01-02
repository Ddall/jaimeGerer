<?php

namespace App\Form\CRM;

use App\Entity\CRM\Compte;
use App\Entity\CRM\Contact;
use App\Service\CRM\ContactService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ContactFusionnerType extends AbstractType
{
	
    protected $contactA;
    protected $contactB;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->contactA = $options['contactA'];
        $this->contactB = $options['contactB'];

        $this->builder = $builder;
        // Same fields then into ContactService $fieldsToCheck. However, not got them from there as we not necessary add them all the same way
        if ($this->doDisplayField('compte')) {
            $builder->add('compte', EntityType::class, [
                'class' => Compte::class,
                'choice_label' => 'nom',
                'expanded' => true,
                'constraints' => new NotNull(),
                'choices' => [
                    $this->contactA->getCompte(),
                    $this->contactB->getCompte(),
                ],
            ]);
        }        
        $this->addChoicesField('prenom');
        $this->addChoicesField('nom');
        $this->addChoicesField('telephonePortable');
        $this->addChoicesField('email');
        $this->addChoicesField('email2');
        $this->addChoicesField('adresse');
        $this->addChoicesField('codePostal');
        $this->addChoicesField('ville');
        $this->addChoicesField('region');
        $this->addChoicesField('pays');
        $this->addChoicesField('titre');
        $this->addChoicesField('fax');
        $this->addChoicesField('telephoneFix');
        $this->addChoicesField('telephoneAutres');
        $this->addChoicesField('civilite');
        $this->addChoicesField('reseau', 'valeur');
        $this->addChoicesField('origine', 'valeur');
    }
    
    /**
     * Add a field to the form, if required
     * 
     * @param string $field
     * @param string $keyField
     */
    private function addChoicesField($field, $keyField = null)
    {
        if ($this->doDisplayField($field)) {
            $method = 'get' . ucfirst($field);
            $keyMethod = $keyField ? 'get' . ucfirst($keyField) : null;
            if (!$keyMethod || !method_exists(Contact::class, $keyMethod)) {
                $keyMethod = $method;
            }
            $this->builder->add($field, ChoiceType::class, [
                'choices' => [
                    $this->contactA->$keyMethod() => $this->contactA->$method(),
                    $this->contactB->$keyMethod() => $this->contactB->$method(),
                ],
                'expanded' => true,
                'constraints' => new NotNull(),
            ]);
        }
    }

    /**
     * Return true if a given field must be displayed in the form
     * 
     * @param string $field
     * 
     * @return boolean
     */
    private function doDisplayField($field)
    {
        return ContactService::needToChooseField($this->contactA, $this->contactB, $field);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Contact',
            'contactA' => null,
            'contactB' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_contact_fusionner';
    }
}
