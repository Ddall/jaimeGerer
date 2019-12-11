<?php

namespace Nicomak\UserBundle\Entity;

class UserAdmin
{

	/**
	 * {@inheritdoc}
	 */
    protected $baseRoutePattern = 'utilisateurs';
	protected $baseRouteName = 'AppBundle\Entity\SuperEntityAdmin';
    
	/**
        * {@inheritdoc}
        */
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('company_section')
                ->add('company')
            ->end()
        ;
    }
	
}