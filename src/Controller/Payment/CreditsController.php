<?php
/**
 * Contrôleur de la gestion des crédits
 * @package         App
 * @subpackage      Payment
 * @category        Controller
 * @author          Gilles Ortheau
 */
namespace App\Controller\Payment;

use App\Entity\Paypal\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreditsController extends AbstractController
{
    const SIMPLE_SENDING_PRICE      = 1.3;
    const RECOMMANDED_SENDING_PRICE = 6.8;

    const SIMPLE_SENDING_LABEL      = 'Envoi courrier simple';
    const RECOMMANDED_SENDING_LABEL = 'Envoi en recommandé avec AR';

    static private $pricesList = array(
            self::SIMPLE_SENDING_LABEL      => self::SIMPLE_SENDING_PRICE,
            self::RECOMMANDED_SENDING_LABEL => self::RECOMMANDED_SENDING_PRICE
    );


    /**
     * Affiche la modal de présentation des crédits
     * @return Response
     * @Route("achat/credits/presentation",
     *   name="payment_credits_display",
     *   options={"expose"=true}
     * )
     */
    public function DisplayAction()
    {
        $this->get('logger')->debug('[GOR] ' . __CLASS__ . '::' . __FUNCTION__);
        return $this->render('payment/credits/display.html.twig', array('prices' => self::$pricesList));
    }

    /**
     * Affiche la page de choix du montant de crédits à acheter
     * @param   Request     $request
     * @return  Response
     * @Route("achat/credits/choix", name="payment_credits_choose")
     */
    public function ChooseAction(Request $request)
    {
        $this->get('logger')->debug('[GOR] ' . __CLASS__ . '::' . __FUNCTION__);

        $this->get('logger')->debug('[GOR] Requete: ' . var_export($request->getMethod(), true));
        if ($request && 'POST' === $request->getMethod()) {
            $this->get('logger')->debug('[GOR] OK');

            $amount = (float)round($request->get('amount'), 2);

            if ($amount > 0) {
                $payment = new Payment($amount, $this->getUser()->getCompany());
                $this->getEM()->persist($payment);
                $this->getEM()->flush($payment);

                return $this->redirectToRoute('paypal_call', array('ref' => $payment->getRef()));
            }
        }
        return $this->render('payment/credits/choose.html.twig', array('prices' => self::$pricesList));
    }


    /**
     * Affiche la page de confirmation d'achat de crédits
     *
     * @param \App\Entity\Paypal\Payment $payment
     * @return  Response
     * @Route("achat/credits/confirmation/{ref}", name="payment_credits_confirm")
     */
    public function ConfirmAction(Payment $payment)
    {
        $this->get('logger')->debug('[GOR] ' . __CLASS__ . '::' . __FUNCTION__);

        $payment->confirm();
        $this->getEM()->persist($payment);
        $this->getEM()->flush($payment);
        $amount = $payment->getAmount();
        $nbSend = array();
        foreach (self::$pricesList as $label => $price) {
            $nbSend[$label] = floor($amount / $price);
        }
        return $this->render('payment/credits/confirm.html.twig', array(
                'prices' => self::$pricesList,
                'amount' => $amount,
                'nbSend' => $nbSend));
    }

    /**
     * Affiche la page d'annulation d'achat de crédits
     * @param   Payment $payment
     * @return  Response
     * @Route("achat/credits/annulation/{ref}", name="payment_credits_cancel")
     */
    public function CancelAction(Payment $payment)
    {
        $this->get('logger')->debug('[GOR] ' . __CLASS__ . '::' . __FUNCTION__);

        $payment->cancel();
        $this->getEM()->persist($payment);
        $this->getEM()->flush($payment);
        $amount = $payment->getAmount();
        return $this->render('payment/credits/cancel.html.twig', array('amount' => $amount));
    }

    /**
     * Retourne l'Entity Manager
     * @return \Doctrine\Persistence\ObjectManager
     */
    private function getEM()
    {
        return $this->getDoctrine()->getManager();
    }
}
