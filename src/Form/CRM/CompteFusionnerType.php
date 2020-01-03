<?php
namespace App\Form\CRM;

use App\Entity\Compta\CompteComptable;
use App\Entity\CRM\Compte;
use App\Service\CRM\CompteService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class CompteFusionnerType extends AbstractType
{

    protected $compteA;
    protected $compteB;
    protected $builder;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->compteA = $options['compteA'];
        $this->compteB = $options['compteB'];

        $this->builder = $builder;
        // Same fields then into CompteService $fieldsToCheck. However, not got them from there as we not necessary add them all the same way
        $this->addChoicesField('nom');
        $this->addChoicesField('telephone');
        $this->addChoicesField('adresse');
        $this->addChoicesField('ville');
        $this->addChoicesField('codePostal');
        $this->addChoicesField('region');
        $this->addChoicesField('pays');
        $this->addChoicesField('url');
        $this->addChoicesField('fax');
        $this->addChoicesField('codeEvoliz');
        $this->addChoicesField('priveOrPublic', 'priveOrPublicToString');

        if ($this->doDisplayField('compteComptableClient')) {
            $builder->add('compteComptableClientToKeep', EntityType::class, [
                'mapped' => false,
                'class' => CompteComptable::class,
                'choice_label' => 'nom',
                'expanded' => true,
                'constraints' => new NotNull(),
                'choices' => [
                    $this->compteA->getCompteComptableClient(),
                    $this->compteB->getCompteComptableClient(),
                ],
            ]);
        }
        if ($this->doDisplayField('compteComptableFournisseur')) {
            $builder->add('compteComptableFournisseurToKeep', EntityType::class, [
                'mapped' => false,
                'class' => CompteComptable::class,
                'choice_label' => 'nom',
                'expanded' => true,
                'constraints' => new NotNull(),
                'choices' => [
                    $this->compteA->getCompteComptableFournisseur(),
                    $this->compteB->getCompteComptableFournisseur(),
                ],
            ]);
        }
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
            if (!$keyMethod || !method_exists(Compte::class, $keyMethod)) {
                $keyMethod = $method;
            }
            $this->builder->add($field, ChoiceType::class, [
                'choices' => [
                    $this->compteA->$keyMethod() => $this->compteA->$method(),
                    $this->compteB->$keyMethod() => $this->compteB->$method(),
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
        return CompteService::needToChooseField($this->compteA, $this->compteB, $field);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CRM\Compte',
            'compteA' => null,
            'compteB' => null,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'appbundle_crm_compte_fusionner';
    }
}
