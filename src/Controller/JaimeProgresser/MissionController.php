<?php

namespace App\Controller\JaimeProgresser;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\JaimeProgresser\Mission;
use App\Repository\JaimeProgresser\MissionRepository;
use App\Form\JaimeProgresser\MissionType;
use App\Entity\CRM\OpportuniteRepository;

/**
 * Ce controller gère les actions relatives aux missions dans j'aime progresser
 *
 * @author Laura Gilquin pour Nicomak <gilquin@nicomak.eu>
 * 
 * @Route("/jaime-progresser/mission")
 */
class MissionController extends AbstractController
{

	/**
	 * Afficher la liste des missions
     * @return     Response    affiche la vue
     * 
	 * @Route("/liste", name="jaime_progresser_mission_liste")
	 */
	public function list(MissionRepository $missionRepository): Response
	{ 
		$missions = [];
		return $this->render('jaime_progresser/mission/liste.html.twig', [
			'missions' => $missionRepository->findBy([
				'archive' => false
			])
		]); 
	}

	/**
	 * Créer une nouvelle mission via un formulaire
     * @return     Response    affiche la vue ou redirige  
     * 
	 * @Route("/ajouter", name="jaime_progresser_mission_ajouter")
	 */
	public function ajouter(Request $request): Response
	{ 
		$mission = new Mission();
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        	$entityManager = $this->getDoctrine()->getManager();

        	$actionCommercialeRepository = $entityManager->getRepository('App:CRM\Opportunite');
			$mission->setActionCommerciale($actionCommercialeRepository->findOneById($form['projet_entity']->getData()));

            
            $entityManager->persist($mission);
            $entityManager->flush();

            $url = $this->generateUrl('jaime_progresser_mission_voir', [ 
                'id' => $mission->getId() 
            ]);

            switch( strtoupper($mission->getTypeMission()) ){
                case 'FORMATION':
                    $url = $this->generateUrl('formation_choose_type', [ 
                        'id' => $mission->getId() 
                    ]);
                    break;
            }

            return $this->redirect($url);
        }

        return $this->render('jaime_progresser/mission/ajouter.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
	}
	
	/**
    * Afficher les informations d'une mission
    * @param   Mission   $mission    mission à  afficher
    * @return  Response              affiche la vue
    *   
    * @Route("/{id}", name="jaime_progresser_mission_voir", methods={"GET"})
    */
   public function voir(Mission $mission): Response
   {
       return $this->render('jaime_progresser/mission/voir.html.twig', [
           'mission' => $mission,
       ]);
   }
}
