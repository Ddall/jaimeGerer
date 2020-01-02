<?php
/**
 * Contrôleur du bundle PaypalBundle
 * @package         Nicomak
 * @subpackage      PaypalBundle
 * @category        Controller
 * @author          Gilles Ortheau
 */
namespace App\Controller\Paypal;

use App\Entity\Paypal\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaypalDefaultController extends AbstractController
{

    protected $email;
    protected $confirm_route;
    protected $cancel_route;
    protected $item_name;
    protected $item_number;
    protected $quantity;
    protected $debug;

    /**
     * PaypalDefaultController constructor.
     *
     * @param string      $email
     * @param string      $confirm_route
     * @param string      $cancel_route
     * @param string      $env
     * @param string|null $item_name
     * @param string|null $item_number
     * @param int         $quantity
     */
    public function __construct(
        string $email,
        string $confirm_route,
        string $cancel_route,
        string $env = 'dev',
        ?string $item_name = null,
        ?string $item_number = null,
        int $quantity = 1
    ) {
        $this->email = $email;
        $this->confirm_route = $confirm_route;
        $this->cancel_route = $cancel_route;
        $this->item_name = $item_name;
        $this->item_number = $item_number;
        $this->quantity = $quantity;

        $this->debug = (bool)($env == 'prod'); // @upgrade-note changed this to detect prod||dev and set debug on accordingly

    }


    /**
     * Appel de paypal
     * @param   Payment     $payment
     * @return  Response
     * @Route("paypal/paiement/{ref}", name="paypal_call")
     */
    public function CallAction(Payment $payment)
    {
        $this->get('logger')->debug('[PaypalController] ' . __CLASS__ . '::' . __FUNCTION__);

        $itemName = ($payment->getItemName() ? $payment->getItemName() :
                $this->item_name);
        $itemNumber = ($payment->getItemNumber() ? $payment->getItemNumber() :
                $this->item_number);
        $quantity = ($payment->getQuantity() ? $payment->getQuantity() :
                $this->quantity);
        $url = ($this->debug ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' :
                'https://www.paypal.com/cgi-bin/webscr');

        return $this->render('paypal/call.html.twig', array(
                'urlPaypal'     => $url,
                'email'         => $this->email,
                'itemName'      => $itemName,
                'itemNumber'    => $itemNumber,
                'amount'        => $payment->getAmount(),
                'quantity'      => $quantity,
                'ref'           => $payment->getRef(),
                'urlNotify'     => $this->generateUrl('paypal_validate',
                                                      array('ref' => $payment->getRef()),
                                                      true),
                'urlReturn'     => $this->generateUrl($this->confirm_route,
                                                      array('ref' => $payment->getRef()),
                                                      true),
                'urlCancel'     => $this->generateUrl($this->cancel_route,
                                                      array('ref' => $payment->getRef()),
                                                      true),
        ));

    }


    /**
     * Action de confirmation de payement
     * @param   Payment $payment
     * @return  Response
     * @Route("paypal/validate/{ref}", name="paypal_validate")
     */
    public function ValidateAction(Request $request, Payment $payment)
    {
        $this->get('logger')->debug('[PaypalController] ' . __CLASS__ . '::' . __FUNCTION__);

        if ($this->checkPayment($payment, $request) && !$payment->isValid()) {
            $payment->validate();
            $payment->getCompany()->addCredits($payment->getAmount());
            $this->getDoctrine()->getManager()->flush($payment);
            $this->getDoctrine()->getManager()->flush($payment->getCompany());
        }

        return new Response();
    }

    /**
     * Vérifie que le paiement a bien été payé sur Paypal
     * @param Payment $payment
     * @return bool
     */
    private function checkPayment(Payment $payment, Request $request)
    {
        $this->get('logger')->debug('[PaypalController] ' . __CLASS__ . '::' . __FUNCTION__);

        if (!$this->checkParams($payment, $request)) {
            return false;
        }
        return $this->checkPaypal();
    }

    /**
     * Vérifie que les données reçues correspondent au paiement
     * @param Payment $payment
     * @return bool
     */
    private function checkParams(Payment $payment, Request $request)
    {
        $this->get('logger')->debug('[PaypalController] ' . __CLASS__ . '::' . __FUNCTION__);

        // Vérification de l'adresse email
        if (!$request->get('business')) {
            $this->get('logger')->error("[PaypalController] Aucune adresse email reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if(trim($request->get('business')) != trim($this->email)) {
            $this->get('logger')->error("[PaypalController] L'adresse email reçue dans la notification Paypal " .
                                        $request->get('business') . " ne correspond pas à l'adresse email " .
                                        $this->email);
            return false;

        }
        // Vérification du prix payé
        if (!$request->get('mc_gross')) {
            $this->get('logger')->error("[PaypalController] Aucune information de prix reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if ((float)$request->get('mc_gross') != $payment->getAmount()) {
            $this->get('logger')->error('[PaypalController] Le prix payé ' . (float)$request->get('mc_gross') .
                                        ' ne correspond pas au prix à payer ' . $payment->getAmount());
            return false;
        }

        // Vérification du statut
        if (!$request->get('payment_status')) {
            $this->get('logger')->error("[PaypalController] Aucune information de statut reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if (strtoupper($request->get('payment_status')) != 'COMPLETED') {
            $this->get('logger')->error('[PaypalController] Le statut ' . $request->get('payment_status') .
                                        ' n\'est pas correct');
            return false;
        }

        // Vérification du champs custom
        if (!$request->get('custom')) {
            $this->get('logger')->error("[PaypalController] Aucun référence de paiement reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if ($request->get('custom') != $payment->getRef()) {
            $this->get('logger')->error('[PaypalController] La référence de Paypal ' . $request->get('custom') .
                                        ' ne correspond pas à la référence du paiement ' . $payment->getRef());
            return false;
        }

        return true;
    }

    /**
     * Vérifie que les données reçues viennent bien de Paypal
     * @return bool
     */
    private function checkPaypal()
    {
        $this->get('logger')->debug('[PaypalController] ' . __CLASS__ . '::' . __FUNCTION__);

        // Appel de Paypal pour vérifier que les informations reçues sont correctes
        $request = 'cmd=_notify-validate';
        foreach ($_REQUEST as $key => $value) {
            $request .= '&' . $key . '=' . urlencode($value);
        }
        $host = ($this->debug ? 'www.sandbox.paypal.com' : 'www.paypal.com');

        if (!($fp = fsockopen ($host, 80, $errno, $errstr, 30))) {
            $this->get('logger')->error('[PaypalController] Erreur de connexion à Paypal nº' . $errno . ': ' . $errstr);
            return false;
        }
        $this->get('logger')->debug('[PaypalController] Connexion à Paypal OK');
        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n" .
                  "host: $host\r\n" .
                  "Content-Type: application/x-www-form-urlencoded\r\n" .
                  "Content-Length: " . strlen($request) . "\r\n\r\n";
        $this->get('logger')->debug('[PaypalController] Envoi de la requête ' . $request);
        fputs($fp, $header . $request);
        while (!feof($fp)) {
            $result = fgets($fp, 1024);
            $this->get('logger')->debug('[PaypalController] ' . $result);
            switch (trim($result)) {
                case 'VERIFIED':
                    return true;
                case 'INVALID':
                    $this->get('logger')->error('[PaypalController] Erreur de vérification par Paypal pour la requête ' .
                                                $request);
                    return false;
            }
        }
        $this->get('logger')->error('[PaypalController] Impossible de vérifier auprès de Paypal la requête ' . $request);
        return false;
    }
}
