<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
    	if($this->getUser() != null){

    		if($this->getUser()->getCompany() == null){
    			//company non paramétrée : paramétrage
    			return $this->redirect($this->generateUrl('admin_company_edit'));
    		} else {
    			//homepage
       	    	return $this->render('default/index.html.twig');
    		}
    	} else {
    		return $this->redirect('login');
    	}
    }

    /**
     * @Route("/importer", name="importer")
     */
    public function importerAction(){

    	return $this->render('crm/settings/crm_settings_importer.html.twig');
    }



}
