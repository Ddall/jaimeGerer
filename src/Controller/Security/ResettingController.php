<?php
namespace App\Controller\Security;

use FOS\UserBundle\Controller\ResettingController as ResettingControllerBase;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class ResettingController extends ResettingControllerBase
{
    protected $eventDispatcher;
    protected $formFactory;
    protected $userManager;
    protected $tokenGenerator;
    protected $mailer;

    /**
     * @required
     */
    public function setParameters(UserManagerInterface $userManager, FormFactoryInterface $formFactory) {
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;

    }

    /**
     * Reset user password
     */
    public function resetAction(Request $request, $token)
    {

        dump($this->userManager);
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        if (!$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->redirectToRoute('fos_user_resetting_request');
        }

        $form = $this->get('fos_user.resetting.form.type');
        $formHandler = $this->get('fos_user.resetting.form.factory');
        $process = $formHandler->process($user);

        if ($process) {
            $this->addFlash('fos_user_success', 'resetting.flash.success');

            // Manually log in the user
            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
            $context = $this->get('security.token_storage');
            $context->setToken($token);

            // Fire the login event manually
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            return $this->redirectToRoute('homepage');
        }

        return $this->render('FOSUserBundle:Resetting:reset.html.twig', array(
            'token' => $token,
            'form' => $form->createView(),
        ));
    }



}
