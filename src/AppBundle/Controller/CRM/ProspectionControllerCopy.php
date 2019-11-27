<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityRepository;

use AppBundle\Controller\CRM\ContactController;
use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Impulsion;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\Settings;
use AppBundle\Entity\CRM\Rapport;
use AppBundle\Entity\CRM\PriseContact;

use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\CRM\RapportFilterType;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\SettingsType;
use AppBundle\Form\CRM\RapportType;
use AppBundle\Form\CRM\ProspectionImporterMappingType;
use AppBundle\Form\CRM\ContactFromProspectionType;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

use AppBundle\Entity\CRM\Prospection;
use AppBundle\Form\CRM\ProspectionType;
use AppBundle\Entity\CRM\ProspectionInfos;

use PHPExcel;
use PHPExcel_IOFactory;


class ProspectionControllerCopy extends Controller
{
    /**
     * @Route("/crm/prospection/liste", name="crm_prospection_liste")
     */
    public function prospectionListeAction()
    {
        return $this->render('crm/prospection/crm_prospection_liste.html.twig');
    }

    /**
     * @Route("/crm/prospection/liste/ajax", name="crm_prospection_liste_ajax")
     */
    public function prospectionListeAjaxAction()
    {

        $requestData = $this->getRequest();
        $arr_sort = $requestData->get('order');
        $arr_cols = $requestData->get('columns');

        $col = $arr_sort[0]['column'];

        $repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Prospection');
        $repositoryInfo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ProspectionInfos');

        $arr_search = $requestData->get('search');

        $list = $repository->findForList(
            $this->getUser()->getCompany(),
            $requestData->get('length'),
            $requestData->get('start'),
            $arr_cols[$col]['data'],
            $arr_sort[0]['dir'],
            $arr_search['value']
        );

        foreach( $list as $k=>$v )
        {
            $data = $repositoryInfo->findByProspection($v["id"]);
            $list[$k]['nbreContacts'] = count($data);
        }

        $response = new JsonResponse();
        $response->setData(array(
            'draw' => intval( $requestData->get('draw') ),
            'recordsTotal' => $repository->count($this->getUser()->getCompany()),
            'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), $arr_search['value']),
            'aaData' => $list,
        ));
        return $response;
    }

    /**
     * @Route("/crm/prospection/voir/{id}", name="crm_prospection_voir")
     */
    public function prospectionVoirAction(Prospection $prospection)
    {
        $repositoryInfo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ProspectionInfos');
        $arr_data = $repositoryInfo->findByProspection($prospection->getId());

        return $this->render('crm/prospection/crm_prospection_voir.html.twig', array(
            'prospection' => $prospection,
            'nbre_contact' => count($arr_data)
        ));
    }

    /**
     * @Route("/crm/prospection/changeListeTitle/{id}", name="crm_prospection_changeListeTitle")
     */
    public function changeListTitle($id,Request $request)
    {
        $repo = $this->getDoctrine()->getManager();
        $prospection = $repo->getRepository('AppBundle:CRM\Prospection')->find($id);
        $newTitle = $request->request->get('title');

        $prospection->setNom($newTitle);

        $repo->persist($prospection);
        $repo->flush();

        return 1;
    }


    /**
     * @Route("/crm/prospection/supprimer/{id}", name="crm_prospection_supprimer")
     */
    public function prospectionSupprimerAction(Prospection $prospection)
    {

        //TODO sup dans service ?
        $repositoryInfo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ProspectionInfos');
        $form = $this->createFormBuilder()->getForm();

        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactsProspects = $repositoryInfo->findByProspection($prospection->getId());

            $em = $this->getDoctrine()->getManager();

            if( $contactsProspects != [] )
            {
                foreach ($contactsProspects as $index => $contactsProspect) {
                    if($contactsProspect->getContact()->getIsOnlyProspect() == true){
                        $em->remove($contactsProspect->getContact());
                    }
                }
            }
            $em->remove($prospection);
            $em->flush();
            return $this->redirect($this->generateUrl(
                'crm_prospection_liste'
            ));
        }

        return $this->render('crm/prospection/crm_prospection_supprimer.html.twig', array(
            'form' => $form->createView(),
            'prospection' => $prospection
        ));
    }

    /**
     * @Route("/crm/prospection/ajouter", name="crm_prospection_ajouter")
     */
    public function prospectionAjouterAction()
    {
        $prospection = new Prospection();

        $form = $this->createForm(
            new ProspectionType(),
            $prospection
        );

        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prospection->setDateCreation(new \DateTime(date('Y-m-d')));
            $prospection->setUserCreation($this->getUser());
            $prospection->setCompany($this->getUser()->getCompany());
            $prospection->setNbreAffichage(20);
            $em = $this->getDoctrine()->getManager();
            $em->persist($prospection);
            $em->flush();

            $response = new JsonResponse();
            $response->setData(array(
                'success' => true,
                'redirect' => $this->generateUrl( 'crm_prospection_gerer_liste',	array('id' => $prospection->getId()) ),
            ));

            return $response;
        }

        return $this->render('crm/prospection/crm_prospection_ajouter_modal.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/crm/prospection/gerer_liste/{id}", name="crm_prospection_gerer_liste")
     */
    public function prospectionGererListeAction(Prospection $prospection)
    {


        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);


        $repositoryInfo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ProspectionInfos');
        $prospections = $repositoryInfo->findByProspection($prospection->getId());


        $arr_data = array();
        $arr_obj = array();

        $arr_headers = array();
        $arr_columns = array();


        $arr_headers_keys = array(
            'prenom' => 'Prénom',
            'nom' =>	'Nom',
            'compte' => 'Organisation',
            'titre' => 'Titre',
            'telephoneFixe' =>	'Telephone',
            'telephonePortable' =>	'Telephone portable',
            'email' =>	'Email',
            'adresse' => 'Adresse',
            'ville' =>	'Ville',
            'codePostal' =>	'Code postal',
            'region' =>	'Region',
            'pays' =>	'Pays',
            'url' => 'Site web',
        );

        foreach($arr_headers_keys as $col => $header){

            $arr_headers[] = $header;
            $arr_columns[] = $this->getHTMLRender($col);
        }

        $data_obj = array();

        foreach ($prospections as $index => $prospect) {


            if ($prospect->getContact()->getCompte() !== null && $prospect->getUrl() == null){
                $url= $prospect->getContact()->getCompte()->getUrl();
            }
            else{
                $url = $prospect->getUrl();
            }
            if ($prospect->getContact()->getCompte() !== null && $prospect->getCompany() == null){
                $compte = $prospect->getContact()->getCompte()->getNom();
            }
            else{
                $compte = $prospect->getCompany();
            }


            $obj = (object) array(
                "id" => $prospect->getContact()->getId(),
                "prenom" => $prospect->getContact()->getPrenom(),
                "nom" => $prospect->getContact()->getNom(),
                "compte" => $compte,
                "titre" => $prospect->getContact()->getTitre(),
                "telephoneFixe" => $prospect->getContact()->getTelephoneFixe(),
                "telephonePortable" => $prospect->getContact()->getTelephonePortable(),
                "email" => $prospect->getContact()->getEmail(),
                "adresse" => $prospect->getContact()->getAdresse(),
                "ville" => $prospect->getContact()->getVille(),
                "region" => $prospect->getContact()->getRegion(),
                "codePostal" => $prospect->getContact()->getCodePostal(),
                "pays" => $prospect->getContact()->getPays(),
                "url" => $url,
                "last_seen" => $prospect->getDernierContact(),
                "note" => $prospect->getNote(),
            );

            $data_obj[] = $obj;

        }



        return $this->render('crm/prospection/crm_prospection_gerer_liste.html.twig', array(
            'arr_obj' => $data_obj,
            'arr_headers' => $arr_headers,
            'arr_columns' => $arr_columns,
            'prospection' => $prospection,
            'hide_tiny' => true,
        ));
        return 1;
    }


    /**
    * @Route("/crm/prospection/rapport/enregistrer/{id}", name="crm_prospection_rapport_enregistrer")
    */
    public function prospectionRapportEnregistrerAction(Prospection $prospection)
    {

        $request = $this->getRequest();

        $data = json_decode($request->request->get('data'), true);

        $prospectionSevice  = $this->get('appbundle.prospection');

        $responseDelete  = $prospectionSevice->checkProspectDeleted($data,$prospection);
        $responseList = $prospectionSevice->checkIfProspectList($data, $this->getUser(), $prospection);

        $response = array_merge($responseList,$responseDelete)
;
        return new JsonResponse($response);

    }

    /**
     * @Route("/crm/prospection/rapport/ajouter/{id}", name="crm_prospection_rapport_ajouter")
     */
    public function prospectionRapportAjouterAction(Prospection $prospection)
    {
        $em = $this->getDoctrine()->getManager();
        $filterRepo = $em->getRepository('AppBundle:CRM\RapportFilter');
        $contactRepo =  $em->getRepository('AppBundle:CRM\Contact');
        $prospectionInfosRepo =  $em->getRepository('AppBundle:CRM\ProspectionInfos');
        $prospects = $prospectionInfosRepo->findByProspection($prospection);

        $arr_filters = $filterRepo->findByProspection($prospection);
        $rapport = new Rapport();

        $form = $this->createForm(new RapportType('contact'), $rapport)
            ->remove('nom')
            ->remove('description')
            ->add('filters', 'collection', array(
                'type' => new RapportFilterType('contact'),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label_attr' => array('class' => 'hidden'),
                'mapped' => false
            ));

        $form->get('filters')->setData($arr_filters);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $arr_new_filters =  $form->getData()->getFiltres();

            foreach ($arr_new_filters as $index => $new_filter) {
                array_push($arr_filters, $new_filter);
            }



            foreach($arr_filters as $filter){
                if($filter->getId() == null){
                    if($filter->getValeur() !== "" && $filter->getValeur() !== null){

                        $filter->setProspection($prospection);
                        $rapport->setNom("prospection");
                        $rapport->setType("prospection");
                        $rapport->setModule("CRM");
                        $rapport->setDateCreation(new \DateTime(date('Y-m-d')));
                        $rapport->setUserCreation($this->getUser());
                        $em->persist($rapport);
                        $em->persist($filter);
                        $em->flush();
                    }
                }

            }

            $newContacts = $contactRepo->createQueryAndGetResult($arr_filters, $this->getUser()->getCompany());

            $oldProspects = [];
            $newProspects = [];

            foreach ($prospects as $index => $prospect) {
                array_push($oldProspects,$prospect->getContact()->getId());
            }

            foreach ($newContacts as $index => $newContact) {
               if(array_search($newContact->getId(), $oldProspects) === false){
                    array_push($newProspects, $newContact);
               }
            }

            $prospectionService  = $this->get('appbundle.prospection');



            //enregistrement des contacts comme etant des prospects
            $prospectionService->addProspectionContacts($newProspects, $this->getUser(), $prospection);


            return $this->redirect($this->generateUrl(
                'crm_prospection_gerer_liste',
                array('id' => $prospection->getId())
            ));

        }

        return $this->render('crm/prospection/crm_prospection_rapport_ajouter.html.twig', array(
            'form' => $form->createView(),
            'prospection' => $prospection,
        ));

    }


    /**
     * @Route("/crm/prospection/rapport/row_maj/{id}", name="crm_prospection_rapport_row_maj")
     */
    public function prospectionRapportRowMajAction(Prospection $prospection)
    {
        $request = $this->getRequest();
        $data = $request->request->get('data');
        $em = $this->getDoctrine()->getManager();

        $contactRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
        $prospectionInfosRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ProspectionInfos');
        $contact = $contactRepo->find($data['id']);
        
        $infos = $prospectionInfosRepo->findOneBy(array('contact' => $contact, 'prospection' => $prospection ));
        $compte = $contact->getCompte();

        $infos->setCompany($data['compte']);
        $infos->setUrl($data['url']);

        $contact->setPrenom($data['prenom']);
        $contact->setNom($data['nom']);
        $contact->setAdresse($data['adresse']);
        $contact->setVille($data['ville']);
        $contact->setCodePostal($data['codePostal']);
        $contact->setRegion($data['region']);
        $contact->setPays($data['pays']);
        $contact->setTelephoneFixe($data['telephoneFixe']);
        $contact->setTelephonePortable($data['telephonePortable']);
        $contact->setEmail($data['email']);
        $contact->setTitre($data['titre']);

        $em->persist($contact);
        $em->persist($compte);
        $em->flush();

        $response = new JsonResponse();
        $response->setData('ok');
        return $response;
    }


    /**
     * @Route(" /crm/prospection/phoning/{id}", name="crm_prospection_phoning")
     */
    public function prospectionPhoningAction(Prospection $prospection)
    {

        $em = $this->getDoctrine()->getManager();
        $prospectionInfosRepo =  $em->getRepository('AppBundle:CRM\ProspectionInfos');
        $prisecontactRepo = $em->getRepository('AppBundle:CRM\PriseContact');

        $prospects = $prospectionInfosRepo->findByProspection($prospection);

        $data = [];

        foreach ($prospects as $index => $prospect) {

            if ($prospect->getContact()->getCompte() !== null && $prospect->getUrl() == null){
                $url= $prospect->getContact()->getCompte()->getUrl();
            }
            else{
                $url = $prospect->getUrl();
            }
            if ($prospect->getContact()->getCompte() !== null && $prospect->getCompany() == null){
                $compte = $prospect->getContact()->getCompte()->getNom();
            }
            else{
                $compte = $prospect->getCompany();
            }
            $blacklistToday = ($prospect->getBlacklistToday() == new \DateTime(date('Y-m-d'))) ? "yes" : null;

            $obj = (object) array(
                "id" => $prospect->getContact()->getId(),
                "prenom" => $prospect->getContact()->getPrenom(),
                "nom" => $prospect->getContact()->getNom(),
                "compte" => $compte,
                "titre" => $prospect->getContact()->getTitre(),
                "telephoneFixe" => $prospect->getContact()->getTelephoneFixe(),
                "telephonePortable" => $prospect->getContact()->getTelephonePortable(),
                "email" => $prospect->getContact()->getEmail(),
                "adresse" => $prospect->getContact()->getAdresse(),
                "ville" => $prospect->getContact()->getVille(),
                "region" => $prospect->getContact()->getRegion(),
                "codePostal" => $prospect->getContact()->getCodePostal(),
                "pays" => $prospect->getContact()->getPays(),
                "url" => $url,
                "last_seen" => $prospection->getDateLastOpen(),
                "date_tentative" => $prospect->getDernierContact(),
                "blackliste" => $prospect->getBlacklist(),
                "blacklisteToday" => $blacklistToday,
                "tentative" => $prospect->getNbreContacts(),
                "note" => $prospect->getNote(),
                "onlyProspect" => $prospect->getContact()->getIsOnlyProspect()
            );

            $arr_priseContact = $prisecontactRepo->findByContact($prospect->getContact()->getId());
            $arr_jsonPriseContact = [];
            foreach ($arr_priseContact as $index => $priseContact) {
                $priseContact =  ["date" => $priseContact->getDate()->format('d/m/Y'), "description" => $priseContact->getDescription()];
                array_push($arr_jsonPriseContact, json_encode($priseContact));
            }
            $obj->prises_contact = $arr_jsonPriseContact;

            $data[] = $obj;
        }


        return $this->render('crm/prospection/crm_prospection_phoning.html.twig', array(
            'data' => json_encode($data),
            'prospection' => $prospection,
            'date_serveur' => \DateTime::createFromFormat('U',time())->format('d/m/Y'),
        ));

    }


    /**
     * @Route("/crm/prospection/rapport/maj_row_rapport/{id}", name="crm_prospection_maj_row_rapport")
     */
    public function prospectionRapportMajRowRapportAction( Prospection $prospection)
    {
        $em = $this->getDoctrine()->getManager();
        $contactRepo = $em->getRepository('AppBundle:CRM\Contact');
        $infoRepo = $em->getRepository('AppBundle:CRM\ProspectionInfos');

        $request = $this->getRequest();
        $data = $request->get('rowData');
        $note = $request->get('noteContent');

        $contact =  $contactRepo->find($data["id"]);
        $infos = $infoRepo->findOneBy(
            array(
                'contact' => $contact,
                'prospection' => $prospection
            )
        );

        $date = null;
        if(gettype($data["date_tentative"])  !== 'string' ){
            $date = $data["date_tentative"]["date"];
        }
        else{
           // $date = date("Y-d-m", strtotime( $data["date_tentative"]));
            $date = date_create_from_format("d/m/Y", $data["date_tentative"]);
        }

        $contact->setNom($data["nom"]);
        $contact->setPrenom($data["prenom"]);
        $contact->setTitre($data["titre"]);
        $contact->setTelephoneFixe($data["telephoneFixe"]);
        $contact->setTelephonePortable($data["telephonePortable"]);
        $contact->setEmail($data["email"]);
        $contact->setAdresse($data["adresse"]);
        $contact->setRegion($data["region"]);
        $contact->setPays($data["pays"]);
        $contact->setVille($data["ville"]);
        $contact->setCodePostal($data["codePostal"]);

        $blacklistToday = ($data["blacklisteToday"] == "yes") ? new \DateTime(date('Y-m-d')) : null ;
        $blacklist =  ($data["blackliste"] == 1) ? true : false ;

        $infos->setNbreContacts($data["tentative"]);
        $infos->setDernierContact($date);
        $infos->setBlacklist($blacklist);
        $infos->setBlacklistToday($blacklistToday);
        $infos->setUrl($data["url"]);
        $infos->setCompany($data["compte"]);

        $em->persist($contact);
        $em->persist($infos);

        $em->flush();

        return new Response(1);

    }

    /**
     * @Route("/crm/prospection/rapport/save_note", name="crm_save_note", options={"expose"=true})
     */
    public function prospectionRapportSaveNotes()
    {

        $em = $this->getDoctrine()->getManager();
        $contactRepo = $em->getRepository('AppBundle:CRM\Contact');

        $request = $this->getRequest();
        $note = $request->get('noteContent');

        $id = intval($request->get('contactId')["id"]);


        $contact =  $contactRepo->find($id);

        $date = new \DateTime();

        $priseContact = new PriseContact();
        $priseContact->setContact($contact);
        $priseContact->setDate($date);
        $priseContact->setDescription($note);
        $priseContact->setUser($this->getUser());
        $priseContact->setType('PHONE');
        $priseContact->setAvoir(NULL);

        $em->persist($priseContact);
        $em->flush();

        return new Response(1);
    }

    /**
     * @Route("/crm/prospection/ajouter_contact/{id}", name="crm_prospection_ajouter_contact", options={"expose"=true})
     */
    public function prospectionAjouterContactAction(Contact $contact)
    {
        $em = $this->getDoctrine()->getManager();
        $prospectionInfosRepo =  $em->getRepository('AppBundle:CRM\ProspectionInfos');
        $compteRepo =  $em->getRepository('AppBundle:CRM\Compte');
        $settingsRepo = $em->getRepository('AppBundle:Settings');

        $form = $this->createForm(
            new ContactFromProspectionType(
                    $contact->getUserGestion()->getId(),
                    $this->getUser()->getCompany()->getId()
            ),
            $contact
        );

        if($contact->getCompte() == null){
            $prospectInfos = $prospectionInfosRepo->findOneByContact($contact);
            $compteName = $prospectInfos->getCompany();

            $compte = $compteRepo->findOneBy(array(
                'company' => $this->getUser()->getCompany(),
                'nom' => $compteName
            ));

            if($compte == null){
                $compte = new Compte();
                $compte->setNom($compteName);
                $compte->setUserCreation($this->getUser());
                $compte->setUserGestion($this->getUser());
                $compte->setDateCreation(new \DateTime(date('Y-m-d')));
                $compte->setCompany($this->getUser()->getCompany());
                $em->persist($compte);
                $em->flush();
            }

            $contact->setCompte($compte);
           
        }

         if($contact->getCompte()){

            $secteurActivite =  $contact->getCompte()->getSecteurActivite();
            $form->remove('compte-name');
            $form->remove('compte');
            $form->remove('secteur');
            $form->add('compte_name', 'text', array(
                'required' => true,
                'mapped' => false,
                'label' => 'Organisation',
                'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off' ),
                'data' => $contact->getCompte()->getNom()
            ))
            ->add('compte', 'hidden', array(
                'required' => true,
                'attr' => array('class' => 'entity-compte'),
                'data' => $contact->getCompte()->getId()
            ))
            ->add('secteur', 'entity', array(
                'class'=>'AppBundle:Settings',
                'property' => 'Valeur',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.parametre = :parametre')
                        ->andWhere('s.company = :company')
                        ->andWhere('s.module = :module')
                        ->setParameter('parametre', 'SECTEUR')
                        ->setParameter('module', 'CRM')
                        ->setParameter('company', $this->getUser()->getcompany()->getId())
                        ->orderBy('s.valeur');
                },
                'required' => false,
                'multiple' => true,
                'label' => 'Secteur d\'activité',
                'empty_data'  => null,
                'mapped' => false,
                'data' => array($secteurActivite)
            ));

        }

        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $contact->setCompte($compteRepo->findOneById($data->getCompte()));

            $contact->setUserCreation($this->getUser());
            $contact->setDateCreation(new \DateTime(date('Y-m-d')));
            $contact->setUserGestion($this->getUser());

            $contact->setIsOnlyProspect(false);
            $em->persist($contact);

            $delaiNum = $form->get('delaiNum')->getData();
            if($delaiNum){
                $impulsion = new Impulsion();
                $impulsion->setContact($contact);
                $impulsion->setUser($this->getUser());
                $impulsion->setDelaiNum($delaiNum);
                $impulsion->setDelaiUnit($form->get('delaiUnit')->getData());
                $impulsion->setDateCreation(new \DateTime(date('Y-m-d')));
                $em->persist($impulsion);
            }

            $em->flush();

            return new Response(1);
        }

        return $this->render('crm/prospection/crm_prospection_ajouter_contact_modal.html.twig', array(
            'form' => $form->createView(),
            'contact'=> $contact
        ));

    }


    /**
     * @Route("/crm/prospection/nombre_affichage/maj_row_rapport/{id}", name="crm_prospection_nombre_affichage")
     */
    public function prospectionNbreAffichageAction(Prospection $prospection)
    {
        $request = $this->getRequest();
        $posts = $request->request->all();
        $nbre_affichage = $posts['nbreAffichage'] > 0 ? $posts['nbreAffichage'] : 10;
        $em = $this->getDoctrine()->getManager();
        $prospection->setNbreAffichage($nbre_affichage);
        $em->persist($prospection);
        $em->flush();
        echo 1; exit;
    }

    /**
     * @Route("/crm/prospection/change_rapports", name="crm_change_rapport")
     */
    public function changeRapports(Request $request)
    {
        $jsonContacts = $request->getContent();

        $ajaxData = json_decode($jsonContacts);

        $id = $ajaxData->id;



        $em = $this->getDoctrine()->getManager();

        $rapportRepo = $em->getRepository('AppBundle:CRM\Rapport');
        $rapport = $rapportRepo->find(intval($id));


        $prospection = new Prospection();
        $prospection->setNom($rapport->getNom());
        $prospection->setCompany($this->getUser()->getCompany());
        $prospection->setUserCreation($this->getUser());
        $prospection->setDateCreation(new \DateTime('now'));

        $em->persist($prospection);


        $data = json_decode($rapport->getData());

        $newData = [];

        foreach ($data as $index => $item) {
            $newItem = (array)$item;
            array_push($newData,$newItem);
        }


        $prospectionService  = $this->get('appbundle.prospection');

        //enregistrement des contacts comme etant des prospects
        $prospectionService->checkIfProspectList($newData, $this->getUser(), $prospection);


        return new JsonResponse("ok");
    }






    public function stats($var)
    {
        $var = (array)$var;

        global $nbre_personne, $nbre_qualifie, $nbre_blackliste, $total_tentative;
        if( isset($var['blackliste']) && $var['blackliste'] ) $nbre_blackliste++;
        if( isset($var['onlyProspect']) && $var['onlyProspect'] ) $nbre_qualifie++;
        if( isset($var['tentative']) && $var['tentative'] )
        {
            $nbre_personne++;
            $total_tentative+=$var['tentative'];
        }
    }


    // Statistiques


    /**
     * @Route("/crm/prospection/stats/{id}", name="crm_prospection_stats")
     */
    public function prospectionStatsAction(Prospection $prospection)
    {
        global $nbre_personne, $nbre_qualifie, $nbre_blackliste, $total_tentative;

        $repositoryInfo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ProspectionInfos');
        $prospections = $repositoryInfo->findByProspection($prospection->getId());

        $rapport = array();

        foreach ($prospections as $index => $prospect) {


            if ($prospect->getContact()->getCompte() !== null && $prospect->getUrl() == null){
                $url= $prospect->getContact()->getCompte()->getUrl();
            }
            else{
                $url = $prospect->getUrl();
            }
            if ($prospect->getContact()->getCompte() !== null && $prospect->getCompany() == null){
                $compte = $prospect->getContact()->getCompte()->getNom();
            }
            else{
                $compte = $prospect->getCompany();
            }


            $obj = (object) array(
                "id" => $prospect->getContact()->getId(),
                "prenom" => $prospect->getContact()->getPrenom(),
                "nom" => $prospect->getContact()->getNom(),
                "compte" => $compte,
                "titre" => $prospect->getContact()->getTitre(),
                "telephoneFixe" => $prospect->getContact()->getTelephoneFixe(),
                "telephonePortable" => $prospect->getContact()->getTelephonePortable(),
                "email" => $prospect->getContact()->getEmail(),
                "adresse" => $prospect->getContact()->getAdresse(),
                "ville" => $prospect->getContact()->getVille(),
                "region" => $prospect->getContact()->getRegion(),
                "codePostal" => $prospect->getContact()->getCodePostal(),
                "pays" => $prospect->getContact()->getPays(),
                "url" => $url,
                "last_seen" => $prospection->getDateLastOpen(),
                "date_tentative" => $prospect->getDernierContact(),
                "blackliste" => $prospect->getBlacklist(),
                "tentative" => $prospect->getNbreContacts(),
                "note" => $prospect->getNote(),
                "onlyProspect" => $prospect->getContact()->getIsOnlyProspect());

            $rapport[] = $obj;

        }



        $nbre_personne = 0;
        $nbre_qualifie = 0;
        $nbre_blackliste = 0;
        $total_tentative = 0;

        $arr_data = array();
        if( count($rapport) > 0 )
            $arr_data = $rapport;
        array_map(array($this, 'stats'), $arr_data);
        //~ echo $nbre_personne.'<br>'.$nbre_qualifie.'<br>'.$nbre_blackliste.'<br>'.$total_tentative;
        //~ exit;
        $total = count($arr_data);
        //~ var_dump($arr_data);
        return $this->render('crm/prospection/crm_prospection_stats_modal.html.twig', array(
            'total' => $total,
            'nbre_personne' => $nbre_personne,
            'nbre_qualifie' => $nbre_qualifie,
            'nbre_blackliste' => $nbre_blackliste,
            'total_tentative' => $total_tentative,
            'prospection' => $prospection
        ));
    }





    private function _rapportProcessData($arr_obj){

        $arr_processed_data = array();
        $phoneUtil = PhoneNumberUtil::getInstance();
        foreach($arr_obj as $contact){

            $s_telephone_fixe = "";
            $s_telephone_portable = "";
            $s_fax = "";

            $arr_data = array(
                'id' => $contact->getId(),
                'prenom' => $contact->getPrenom(),
                'nom' => $contact->getNom(),
                'compte' => $contact->getCompte()->getNom(),
                'titre' => $contact->getTitre(),
                'adresse' => $contact->getAdresse(),
                'ville' => $contact->getVille(),
                'codePostal' => $contact->getCodePostal(),
                'region' => $contact->getRegion(),
                'pays' => $contact->getPays(),
                'telephoneFixe' => $contact->getTelephoneFixe(),
                'telephonePortable' => $contact->getTelephonePortable(),
                'email' => $contact->getEmail(),
                'url' => $contact->getCompte()->getUrl() != '' ? '<a href="'.$contact->getCompte()->getUrl().'" target="_blank">'.$contact->getCompte()->getUrl().'</a>' : '',
            );


            $arr_processed_data[] = $arr_data;

        }


        return $arr_processed_data;
    }

    private function getHTMLRender($col) {

        $arr_readOnly_cols = array(
            'gestionnaire', 'type', 'origine', 'reseau', 'carte_voeux', 'services_interet', 'themes_interet', 'num', 'contact'
        );
        if (in_array($col, $arr_readOnly_cols)) {
            return array('data' => $col, 'readOnly' => true, 'renderer' => 'html');
        }
        else {
            return array('data' => $col, 'renderer' => 'html');
        }
    }


}