<?php

namespace App\Controller\Emailing\mautic;


use App\Entity\Emailing\MauticApiKey;
use App\Form\Emailing\mautic\MauticChangeCredentialsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class EmailingController extends AbstractController
{

    /**
     * @Route("/emailing/erase/session", name="session_erase")
     */
    public function stats(){


        $this->get('session')->set('accessTokenData', null);
        $this->get('session')->set('auth', null);
        $this->get('session')->set('actionInit', null);
        $this->get('session')->set('rapportId', null);


        return new Response();

    }

    /**
     * @Route("/emailing/home", name="emailing_homepage")
     */
    public function indexAction(Request $request)
    {

        //$mauticCredentials = $this->_checkDataBaseCredentials();


        // $mauticService = $this->get('mautic');
        // try{
        //     $mauticService->authorization($mauticCredentials);
        // } catch(\Exception $e){
        //     throw $e;
        // }

        // if($this->get('session')->get('redirection')){
        //     if($this->get('session')->get('rapportId')){
        //         return $this->redirect($this->generateUrl($this->get('session')->get('redirection'), array("id" => $this->get('session')->get('rapportId'))));
        //     }
        //     return $this->redirect($this->generateUrl($this->get('session')->get('redirection')));
        // }


        $accessTokenData = $this->get('session')->get('accessTokenData');
        $date = date('d/m H:i:s', ($accessTokenData['expires']/60)*60);

        return $this->render('emailing/mautic/home.html.twig', array(
            'token' => $accessTokenData,
            'date' => $date
        ));
    }

    /**
     * @Route("/emailing/setting/change/mautic/credential", name="emailing_change_credentials")
     */
    public function changeMauticCredential(Request $request){

        $em = $this->getDoctrine()->getManager();
        $mauticCredentialsRepo = $em->getRepository('App:Emailing\MauticApiKey');
        $company = $this->getUser()->getCompany();
        $mauticCredentials = $mauticCredentialsRepo->findOneByCompany($company);

        $options = [];
        $form = $this->createForm(MauticChangeCredentialsType::class, $options);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $secretKey = $form->get('secretKey')->getData();
            $publicKey = $form->get('publicKey')->getData();

            if($mauticCredentials == null){
                $mauticCredentials = new MauticApiKey();

            }
            $mauticCredentials->setCompany($company);
            $mauticCredentials->setSecretKey($secretKey);
            $mauticCredentials->setPublicKey($publicKey);
            $em->persist($mauticCredentials);
            $em->flush();
        }

        return $this->render('emailing/mautic/settings/MauticChangeCredentialView.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/emailing/contact/create/segment", name="emailing_create_contact_segment")
     */
    public function createSegment(Request $request){

        $this->_checkMauticSessionAction('emailing_create_contact_batch');

        $jsonContacts = $request->getContent();

        $data = json_decode($jsonContacts);
        $contacts = json_decode($data->data);

        $name = $data->name;
        $comment = $data->comment;

        $arr_contacts = [];


        for($i=0; $i<count($contacts); $i++){
            $arr_contacts[] = array(
                'firstname' => $contacts[$i]->prenom,
                'lastname' => $contacts[$i]->nom,
                'position' => $contacts[$i]->titre,
                'email' => $contacts[$i]->email,
                'company' => $contacts[$i]->compte,
                'city' => $contacts[$i]->ville,
            );
        }



        $mauticService = $this->get('mautic');

        $segmentId = $mauticService->createSegment($name,$comment);
        $contactsId = $mauticService->createContactBatch($arr_contacts);

        $mauticService->addContactsToSegment($segmentId,$contactsId);

        return new JsonResponse($jsonContacts);

    }


    private function _checkMauticSessionAction($page){
        if($this->get('session')->get('accessTokenData') === null){
            $this->get('session')->set('redirection', $page);
            return $this->redirect($this->generateUrl('emailing_homepage'));
        }
    }

    private function _checkDataBaseCredentials(){
        $em = $this->getDoctrine()->getManager();
        $mauticCredentialsRepo = $em->getRepository('App:Emailing\MauticApiKey');
        $company = $this->getUser()->getCompany();
        $mauticCredentials = $mauticCredentialsRepo->findOneByCompany($company);

        if($mauticCredentials=== null){
            return $this->redirect($this->generateUrl('emailing_change_credentials'));
        }
        else{
            return $mauticCredentials;
        }
    }

}
