<?php

namespace App\Controller\Emailing;


use App\Entity\Emailing\Campagne;
use App\Entity\Emailing\CampagneContact;
use App\Util\DependancyInjectionTrait\MailgunServiceTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MailgunTestController extends AbstractController
{


    use MailgunServiceTrait;

	/**
	 * @Route("/emailing/test/api", name="emailing_test_api")
	 */ 
	public function testAPI(){

		$em = $this->getDoctrine()->getManager();

		//set campaign details
		$campagne = new Campagne();
		$campagne->setNom('Dev - Newsletter');
		$campagne->setObjet('Newsletter Nicomak');
		$campagne->setNomExpediteur('Nicomak');
		$campagne->setEmailExpediteur('contact@nicomak.eu');
		$campagne->setDateCreation(new \DateTime(date('Y-m-d')));
		$campagne->setUserCreation($this->getUser());
		$campagne->setDateEnvoi(new \DateTime(date('Y-m-d')));

		//set HTML
		$path = $this->get('kernel')->getRootDir().'/../web/files/emailing/';
		$html = file_get_contents($path.'newsletter.html');
		$campagne->setHtml($html);

		$em->persist($campagne);

		//add contacts
		$rapportRepo = $em->getRepository('App:CRM\Rapport');
		$rapport = $rapportRepo->find(435);
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('App:CRM\RapportFilter');
		$arr_filters = $filterRepo->findByRapport($rapport);
		$contactRepo = $this->getDoctrine()->getManager()->getRepository('App:CRM\Contact');
		$arr_contacts = $contactRepo->createQueryAndGetResult($arr_filters, $this->getUser()->getCompany(), true);

		foreach($arr_contacts as $contact){
			$campagneContact = new CampagneContact();
			$campagneContact->setContact($contact);
			$campagne->addCampagneContact($campagneContact);
		}

		$em->persist($campagne);
		$em->flush();

		$this->mailgunService->sendViaAPI($campagne);

	 	return new Response();
	}

	/**
	 * @Route("/emailing/test/webhook", name="emailing_test_webhook")
	 */
	public function testWebhook(Request $request){
		
		$response = new Response();

		$content = json_decode($request->getContent(), true);

		//check the signature
		$signature = $content['signature'];
		if ( $this->mailgunService->checkWebhookSignature($signature['token'], $signature['timestamp'], $signature['signature'] ) === false ) {
       		$response->setStatusCode('401');
			return $response;
		}

		$eventData = $content['event-data'];
		if( $this->mailgunService->saveWebhookEvent($eventData) === false ){
			$response->setStatusCode('500');
			return $response;
		}

		return $response;
		
	}

}
