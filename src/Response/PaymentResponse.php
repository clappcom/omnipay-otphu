<?php
namespace Clapp\OtpHu\Response;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;
use SimpleXMLElement;
use Clapp\OtpHu\BadResponseException;
use Clapp\OtpHu\Response\UnknownShopIdResponse;
use Exception;

class PaymentResponse extends AbstractResponse implements RedirectResponseInterface{
    /**
     * https://www.otpbankdirekt.hu/webshop/do/webShopVasarlasInditas?posId={0}&azonosito={1}&nyelvkod={2}
     */
    protected $redirectUrl = "https://www.otpbankdirekt.hu/webshop/do/webShopVasarlasInditas";

    protected $messageString = null;

    public static $validMessageStrings = [
        "SIKERESWEBSHOPFIZETESINDITAS"
    ];

    public function __construct(RequestInterface $request, $data){
        parent::__construct($request, $data);

        //var_dump($data."");

        $this->messageString = $this->parseResponseData($data);
    }
    protected function parseResponseData ($data){
        try {
            /**
             * try to parse the successful response message
             */
            $payload = base64_decode((new SimpleXMLElement($data))->xpath('//result')[0]->__toString());
            $messageString = (new SimpleXMLElement($payload))->xpath('//message')[0]->__toString();
        }catch(Exception $e){
            /**
             * we cannot parse the response
             */
            $messageString = null;
        }

        if (in_array($messageString, self::$validMessageStrings)){
            return $messageString;
        }

        /**
         * we have an invalid $messageString
         *
         * create the proper exception based on the value of $messageString
         */
        switch($messageString){
            case 'HIANYZIKSHOPPUBLIKUSKULCS':
                throw new UnknownShopIdResponse($data);
            break;
            default:
                throw new BadResponseException($data);
            break;
        }
    }

    /**
     * sikeresen kifizette-e az összeget a felhasználó
     * nem, mivel ez mindig redirectel, hiszen majd a banki felületen fog fizetni
     */
    public function isSuccessful(){
        return false;
    }
    /**
     * Does the response require a redirect?
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return true;
    }
    /**
     * Gets the redirect target url.
     *
     * @return string
     */
    public function getRedirectUrl(){
        $params = [
            'posId' => $this->getRequest()->getShopId(),
            'azonosito' => $this->getRequest()->getTransactionId(),
            'nyelvkod' => $this->getRequest()->getLanguage(),
        ];

        return $this->redirectUrl . "?" . http_build_query($params);
    }

    /**
     * Get the required redirect method (either GET or POST).
     *
     * @return string
     */
    public function getRedirectMethod(){
        return "GET";
    }

    /**
     * Gets the redirect form data array, if the redirect method is POST.
     *
     * @return array
     */
    public function getRedirectData(){
        return [];
    }
    /**
     * Get the transaction ID as generated by the merchant website.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getRequest()->getTransactionId();
    }
}
