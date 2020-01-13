<?php

namespace App\Service\CRM;

use App\Service\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Twig\Environment;

class DevisService {

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UtilsService
     */
    protected $utilsService;

    /**
     * @var string|string
     */
    protected $kernelRootDir;

    /**
     * @var Environment
     */
    protected $templatingService;

    /**
     * @var Pdf
     */
    protected $knpSnappyPDF;

    /**
     * DevisService constructor.
     *
     * @param string                 $kernelRootDir
     * @param EntityManagerInterface $em
     * @param UtilsService           $utilsService
     * @param Environment            $templatingService
     * @param Pdf                    $knpSnappyPDF
     */
    public function __construct(string $kernelRootDir = '', EntityManagerInterface $em, UtilsService $utilsService, Environment $templatingService, Pdf $knpSnappyPDF)
    {
        $this->em = $em;
        $this->utilsService = $utilsService;
        $this->kernelRootDir = $kernelRootDir;
        $this->templatingService = $templatingService;
        $this->knpSnappyPDF = $knpSnappyPDF;
    }


    public function win($devis){

        $devis->win();
        $this->em->persist($devis);
        $this->em->flush();

        return $devis;

     }

     public function lose($devis){

        $devis->lose();
        $this->em->persist($devis);
        $this->em->flush();

        return $devis;

    }

    public function setNum($devis){
        
        $settingsRepository = $this->em->getRepository('App:Settings');
        $settingsNum = $settingsRepository->findOneBy(array('company' => $devis->getuserCreation()->getCompany(), 'module' => 'CRM', 'parametre' => 'NUMERO_DEVIS'));
        $currentNum = $settingsNum->getValeur();

        $prefixe = date('Y').'-';
        if($currentNum < 10){
            $prefixe.='00';
        } else if ($currentNum < 100){
            $prefixe.='0';
        }
        $devis->setNum($prefixe.$currentNum);
        $currentNum++;
        $settingsNum->setValeur($currentNum);
        $this->em->persist($settingsNum);

        return $devis;
    }

    public function createFromOpportunite($devis, $opportunite){

        $devis->setCompte($opportunite->getCompte());
        $devis->setContact($opportunite->getContact());
        $devis->setDateCreation($opportunite->getDateCreation());
        $devis->setUserCreation($opportunite->getuserCreation());
        $devis->setObjet($opportunite->getNom());
        $devis->setUserGestion($opportunite->getUserGestion());
        $devis->setAnalytique($opportunite->getAnalytique());

        return $devis;
    }

    public function createDevisPDF($devis)
    {

        $settingsRepository = $this->em->getRepository('App:Settings');
        $footerDevis = $settingsRepository->findOneBy(array('company' => $devis->getUserCreation()->getCompany(), 'module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_DEVIS'));

        $contactAdmin = $settingsRepository->findOneBy(array('company' => $devis->getUserCreation()->getCompany(), 'module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF'));

        $html = $this->templatingService->render('crm/devis/crm_devis_exporter.html.twig',array(
                'devis' => $devis,
                'footer' => $footerDevis,
                'contact_admin' => $contactAdmin->getValeur(),
        ));

        $filename = 'devis_'.$devis->getNum().'.pdf';

        $pdfFolder = $this->kernelRootDir.'/../public/files/crm/'.$devis->getUserCreation()->getCompany()->getId().'/devis/';
        $nomClient = $this->utilsService->removeSpecialChars($devis->getCompte()->getNom());
        $fileName =$pdfFolder.$devis->getNum().'.'.$nomClient.'.pdf';

        $this->knpSnappyPDF->generateFromHtml($html, $fileName, array('javascript-delay' => 60), true);

        return $fileName;
    }
}
