<?php


namespace App\Service\CRM;

use App\Entity\CRM\Contact;
use App\Entity\CRM\Prospection;
use App\Entity\CRM\ProspectionInfos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;


class ProspectionService
{

    protected $em;
    protected $propectionRepo;
    protected $contactRepo;
    protected $propectionInfosRepo ;
    protected $compteRepo ;

    /**
     * ProspectionService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->propectionRepo =  $this->em->getRepository('App:CRM\Prospection');
        $this->contactRepo =  $this->em->getRepository('App:CRM\Contact');
        $this->propectionInfosRepo =  $this->em->getRepository('App:CRM\ProspectionInfos');
        $this->compteRepo =  $this->em->getRepository('App:CRM\Compte');
    }


    public function changeProspectionList(Prospection $prospectionList, $user){

        $date = new \DateTime(date('y-m-d'));
        $prospectionList->setDateEdition($date);
        $prospectionList->setUserEdition($user);

        try{

            $this->em->persist($prospectionList);
            $this->em->flush();

            return "ok";

        }catch(Exception $e){
            return "Something went wrong!";
        }

    }

    public function deleteProspectionList(Prospection $prospectionList){

        try{

            $this->em->remove($prospectionList);
            $this->em->flush();

            return "ok";

        }catch(Exception $e){
            return "Something went wrong!";
        }

    }

    public function checkProspectDeleted($arr_contacts, Prospection $prospectionList){

        //check if prospect already exist
        $arr_errors = [];
        $arr_prospectionInfos = $this->propectionInfosRepo->findBy(array('prospection' => $prospectionList));
        $arr_contacts_id = [];

        foreach ($arr_contacts as $index => $contact) {
            array_push($arr_contacts_id,$contact["id"]);
        }
        foreach ($arr_prospectionInfos as $index => $arr_prospectionInfo){
             if(array_search($arr_prospectionInfo->getContact()->getId(),$arr_contacts_id) === false){
                 try{
                     if($arr_prospectionInfo->getContact()->getIsOnlyProspect() == true){
                         $this->em->remove($arr_prospectionInfo->getContact());
                     }
                     $this->em->remove($arr_prospectionInfo);
                     $this->em->flush();
                 }
                 catch(Exception $e){
                       array_push($arr_errors, $arr_prospectionInfo->getContact());
                 }
             }
        }

        return $arr_errors;
    }

    public function checkIfProspectList($arr_contacts, $user, Prospection $prospectionList){

        $results =  ["Errors" => []];

        //check if prospect already exists
        foreach ($arr_contacts as $index => $contact) {
            if($contact["nom"] !== "" || $contact["prenom"] !== ""){
                if($contact["id"] === "" || $contact["id"] === null){
                    $newContact = new Contact();
                    $newContact->setNom($contact["nom"]);
                    $newContact->setPrenom($contact["prenom"]);
                    $newContact->setTitre($contact["titre"]);
                    $newContact->setTelephoneFixe($contact["telephoneFixe"]);
                    $newContact->setTelephonePortable($contact["telephonePortable"]);
                    $newContact->setEmail($contact["email"]);
                    $newContact->setAdresse($contact["adresse"]);
                    $newContact->setVille($contact["ville"]);
                    $newContact->setCodePostal($contact["codePostal"]);
                    $newContact->setRegion($contact["region"]);
                    $newContact->setPays($contact["pays"]);
                    $newContact->setIsOnlyProspect(true);
                    $addResult = $this->addProspectionContacts([$newContact], $user, $prospectionList, $contact["compte"], $contact["url"]);
                    if($addResult != []){
                        $results["Errors"][] = $addResult;
                    }
                }
                else{

                    if(array_key_exists("url", $contact)  == false ){
                        $contact["url"] = "";
                    }

                    $newContact = $this->contactRepo->findOneById(intval($contact['id']));
                    if($newContact != null) {
                        $newContact->setNom($contact["nom"]);
                        $newContact->setPrenom($contact["prenom"]);
                        $newContact->setTitre($contact["titre"]);
                        $newContact->setTelephoneFixe($contact["telephoneFixe"]);
                        $newContact->setTelephonePortable($contact["telephonePortable"]);
                        $newContact->setEmail($contact["email"]);
                        $newContact->setAdresse($contact["adresse"]);
                        $newContact->setVille($contact["ville"]);
                        $newContact->setCodePostal($contact["codePostal"]);
                        $newContact->setRegion($contact["region"]);
                        $newContact->setPays($contact["pays"]);

                        $changeResult = $this->addProspectionContacts([$newContact], $user, $prospectionList, $contact["compte"], $contact["url"]);
                        if ($changeResult != []) {
                            $results["Errors"][] = $changeResult;
                        }
                    }
                }
            }
        }

        return $results ;

    }

    public function addProspectionContacts($arr_contacts, $user, Prospection $prospectionList, $companyProspect = null, $urlProspect = null){


        $arr_error = [];

        foreach($arr_contacts as $contact){


            $prospectionInfos = $this->propectionInfosRepo->findOneBy(array(
                'contact' => $contact,
                'prospection' => $prospectionList
            ));

            if($prospectionInfos == null){
                $date = new \DateTime(date('y-m-d'));
                $contact->setUserCreation($user);
                $contact->setUserGestion($user);
                $contact->setDateCreation($date);

                $prospectionInfos = new ProspectionInfos();

                $prospectionInfos->setContact($contact);
                $prospectionInfos->setProspection($prospectionList);

            }
            if($companyProspect !== null  ){
                $prospectionInfos->setCompany($companyProspect);
            }
            if($urlProspect !== null  ){
                $prospectionInfos->setUrl($urlProspect);
            }


            try{
                $this->em->persist($contact);
                $this->em->persist($prospectionInfos);
                $this->em->flush();
            }catch(Exception $e){
                array_push($arr_error,  [$contact->getId(), $contact->getNom()]);
            }
        }
        return $arr_error;

    }

}
