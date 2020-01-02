<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Entity\User;
use App\Form\User\UserType;
use App\Service\Compta\CompteComptableService;
use FOS\UserBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var TokenGeneratorInterface
     */
    protected $tokenGenerator;

    public function __construct(UserManagerInterface $userManager, TokenGeneratorInterface $tokenGenerator) {
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
  * Enable or disable an user.
  *
  * @param  string  $id      User id
  * @param  bool $enabled    True to enable, false to disable
  * @return JsonResponse     Json response with new user status
  *
  * @Route("/user/enable/{id}/{enabled}",
  *   requirements = {"id" = "\d+","enabled" = "[0-1]+"},
  *   name="user_enable",
  *   options = {"expose" = true}
  * )
  */
  public function userEnableAction($id, $enabled)
  {
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository('App:User');
    $user = $repository->find($id);

    $user->setEnabled($enabled);
    $manager->flush();

    return new JsonResponse(array('enabled' => $enabled));
  }

  /**
  * Promote or demote user with admin role
  *
  * @param  string  $id      User id
  * @param  bool $promote    True to promote, false to demote
  * @return JsonResponse     Json response with new user role
  *
  * @Route("/user/promote-admin/{id}/{promote}",
  *   requirements = {"id" = "\d+","promote" = "[0-1]+"},
  *   name="user_promote_admin",
  *   options = {"expose" = true}
  * )
  */
  public function userPromoteAdminAction($id, $promote)
  {

    $user = $this->userManager->findUserBy(array('id'=>$id));

    if($promote == 1){
       $user->addRole('ROLE_ADMIN');
    } else {
       $user->removeRole('ROLE_ADMIN');
    }

    $this->userManager->updateUser($user);

    return new JsonResponse(array('promote' => $promote));
  }

  /**
   * Add an user to the logged user's company
   * @return Response Rendered view
   *
   * @Route("admin/utilisateurs/ajouter",
   *   name="admin_utilisateurs_ajouter"
   * )
   */
    public function utilisateursAjouterAction(CompteComptableService $compteComptableService, \Swift_Mailer $mailer, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $settingsActivationRepo = $em->getRepository('App:SettingsActivationOutil');

        $user = $this->userManager->createUser();

        $form = $this->createForm(UserType::class, $user, array(
           'company_id' => $this->getUser()->getCompany()->getId(),
        ));;

        $form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$user->setCompany( $this->getUser()->getCompany() );

            if($form['admin']->getData() == 'true'){
                $user->addRole('ROLE_ADMIN');
            }

            foreach($form['permissions']->getData() as $role){
                $user->addRole($role);
            }

            $password = substr($this->tokenGenerator->generateToken(), 0, 8); // 8 chars
            $user->setPlainPassword($password);

            $this->userManager->updateUser($user);

            $activationCompta = $settingsActivationRepo->findOneBy(array(
                'company' => $this->getUser()->getCompany(),
                'outil' => 'COMPTA',
            ));

            if($activationCompta){
                try{
                    $compteComptableNDF = $compteComptableService->createCompteComptableNDF($this->getUser()->getCompany(), $user);
                    $user->setCompteComptableNoteFrais($compteComptableNDF);

                    $settings = new Settings();
                    $settings->setParametre('COMPTE_COMPTABLE_NOTE_FRAIS');
                    $settings->setModule('COMPTA');
                    $settings->setType('LISTE');
                    $settings->setCompany($this->getUser()->getCompany());
                    $settings->setCompteComptable($compteComptableNDF);
                    $settings->setHelpText("Lesquels de ces comptes comptables concernent les notes de frais ?");
                    $settings->setTitre("Comptes comptables de vos notes de frais");
                    $settings->setCategorie("NOTE_FRAIS");
                    $settings->setValeur("");
                    $settings->setNoTVA(false);
                    $em->persist($settings);
                    $em->flush();

                } catch(\Exception $e){
                    throw $e;
                }

            }

            $this->userManager->updateUser($user);

            //envoi d'un email en interne
             $message = $this->renderView('admin/utilisateurs/admin_utilisateurs_welcome_email.html.twig', array(
                'user' => $user,
                'password' => $password,
            ));
            $mail = (new \Swift_Message)
                ->setSubject('Bienvenue sur J\'aime gérer '.$user->getFirstName().' !')
                ->setFrom('laura@jaime-gerer.com')
                ->setTo($user->getEmail())
                ->setBody($message, 'text/html');
            $mailer->send($mail);

			return $this->redirect($this->generateUrl(
					'admin_utilisateurs_liste'
			));
		}

        return $this->render('admin/utilisateurs/admin_utilisateurs_ajouter.html.twig', array(
            'form' => $form->createView(),
            'company' => $this->getUser()->getCompany(),
            'profile' => false
        ));
    }


    /**
     * Add an user to the logged user's company
     * @return Response Rendered view
     *
     * @Route("admin/utilisateurs/editer/{id}",
     *   name="admin_utilisateurs_editer"
     * )
     */
    public function utilisateursEditerAction(Request $request, User $user)
    {
        $form = $this->createForm(UserType::class, $user, array(
            'company_id' => $this->getUser()->getCompany()->getId(),
        ));;
        $form['permissions']->setData($user->getRoles());
        $form['admin']->setData($user->hasRole('ROLE_ADMIN'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if($form['admin']->getData() == 'true'){
                $user->addRole('ROLE_ADMIN');
            } else if($user->hasRole('ROLE_ADMIN') ) {
                 $user->removeRole('ROLE_ADMIN');
            }

            foreach($form['permissions']->getData() as $role){
                $user->addRole($role);
            }

            foreach($user->getRoles() as $formerRole){
                if($formerRole != 'ROLE_ADMIN'){
                    if(!in_array($formerRole, $form['permissions']->getData() )){
                        $user->removeRole($formerRole);
                    }
                }
            }

            $this->userManager->updateUser($user);

            return $this->redirect($this->generateUrl(
                'admin_utilisateurs_liste'
            ));
        }

        return $this->render('admin/utilisateurs/admin_utilisateurs_ajouter.html.twig', array(
            'form' => $form->createView(),
            'company' => $this->getUser()->getCompany(),
            'profile' => false
        ));
    }

    /**
     * Display the logged user profile
     * @return Response Rendered view
     *
     * @Route("profil/voir",
     *   name="profil_voir"
     * )
     */
    public function profilVoirAction()
    {
        return $this->render('user/user_profil_voir.html.twig', array(
            'user' => $this->getUser()
        ));
    }

     /**
     * Edit the logged user profile
     * @return Response Rendered view
     *
     * @Route("profil/editer",
     *   name="profil_editer"
     * )
     */
    public function profilEditerAction(Request $request)
    {
        $form = $this->createForm( UserType::class, $this->getUser(), array(
            'company_id' => $this->getUser()->getCompany()->getId(),
        ));

        $form->remove('admin');
        $form->remove('permissions');
        $form->remove('enabled');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->userManager->updateUser($this->getUser());

            return $this->render('user/user_profil_voir.html.twig', array(
                'user' => $this->getUser()
            ));
        }

        return $this->render('user/user_profil_editer.html.twig', array(
            'form' => $form->createView(),
            'company' => $this->getUser()->getCompany(),
            'profile' => true
        ));
    }

    /**
    * @Route("/wp-register", name="register_from_wordpress")
    */
    public function registerFromWordpressAction(Request $request, \Swift_Mailer $mailer, TwigSwiftMailer $fosMailer)
    {
        //autoriser le cross domain
        if(isset($_SERVER['HTTP_ORIGIN'])){
          switch ($_SERVER['HTTP_ORIGIN']) {
            case 'https://www.jaime-gerer.com':
              header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
              header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
              header('Access-Control-Max-Age: 1000');
              header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
              break;
          }
        }
        $posts = $request->request->all();

        //vérifier si l'utilisateur existe déjà
        $existingUser = $this->userManager->findUserByEmail($posts['email']);
        if($existingUser != null){
          throw new \Exception('This user already exists.');
        }

        $firstname = $posts['firstname'];
        $lastname = $posts['lastname'];
        $email = $posts['email'];
        $plainPassword = $posts['plainPassword'];
        $company = $posts['company'];
        $phone = $posts['phone'];

        //création du nouvel utilisateur
        $user = $this->userManager->createUser();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setPlainPassword($plainPassword);
        $user->setUsername($email);;
        $user->setPhone($phone);;
        $user->setEnabled(false);

        $user->addRole('ROLE_ADMIN');
        $user->addRole('ROLE_COMMERCIAL');
        $user->addRole('ROLE_COMPTA');
        $user->addRole('ROLE_COMMUNICATION');
        $user->addRole('ROLE_RH');

        //génération du token de confirmation et envoi du mail d'activation au nouvel utilisateur
        $user->setConfirmationToken($this->tokenGenerator->generateToken());
        $this->userManager->updateUser($user);
        $fosMailer->sendConfirmationEmailMessage($user);

        //envoi d'un email en interne
        $message=$firstname.' '.$lastname.' de l\'organisation '.$company.' : '.$email.' - '.$phone;
        $mail = (new \Swift_Message)
          ->setSubject('Youpi, un nouvel utilisateur s\'est inscrit sur J\'aime gérer ! ')
          ->setFrom('laura@jaime-gerer.com')
          ->setTo('laura@jaime-gerer.com')
          ->setBody($message, 'text/html');
        $mailer->send($mail);

        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
    * @Route("/user/upload/signature", name="user_upload_signature")
    */
    public function userUploadSignatureAction(Request $requestData)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $arr_files = $requestData->files->all();
        $file = $arr_files["files"][0];

        //enregistrement temporaire du fichier uploadé
        $filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
        $path =  $this->get('kernel')->getRootDir().'/../web/upload/signature/'.$user->getId().'/';
        $file->move($path, $filename);

        $oldSignature = null;
        if($user->getSignature() != null){
          $oldSignature = $user->getSignature();
        }

        $user->setSignature($filename);
        $em->persist($user);
        $em->flush();

        if($oldSignature) {
          unlink($path.$oldSignature);
        }

        $response = new JsonResponse();
        $response->setData(array(
            'filename' => $filename
        ));

        return $response;
    }


}
