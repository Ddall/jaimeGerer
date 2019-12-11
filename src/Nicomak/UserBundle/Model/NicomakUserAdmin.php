<?php

namespace Nicomak\UserBundle\Admin;


class NicomakUserAdmin
{
    /**
        * {@inheritdoc}
        */
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('new_section')
                ->add('company')
                // ...
            ->end()
        ;
    }
}
