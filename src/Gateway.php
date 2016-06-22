<?php

namespace Clapp\OtpHu;

use Omnipay\Common\AbstractGateway;
use Clapp\OtpHu\Request\GenerateTransactionIdRequest;
use Clapp\OtpHu\Request\PaymentRequest;
use Clapp\OtpHu\Response\GenerateTransactionIdResponse;
use SimpleXMLElement;



class Gateway extends AbstractGateway{

    protected $endpoint = "https://www.otpbankdirekt.hu/mwaccesspublic/mwaccess";

    public function __construct(ClientInterface $httpClient = null, HttpRequest $httpRequest = null){
        parent::__construct($httpClient, $httpRequest);

        $this->setParameter('endpoint', $this->endpoint);
    }

    public function getName(){
        return "otphu";
    }

    public function purchase($options){
        $generateTransactionIdResponse = $this->generateTransactionId();
        $this->setTransactionId($generateTransactionIdResponse->getTransactionId());

        $request = $this->createRequest("\\".PaymentRequest::class, array_merge($options, $this->getParameters()));

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint',
            'transactionId'
        );

        return $request;
    }

    protected function generateTransactionId(){
        $request = $this->createRequest("\\".GenerateTransactionIdRequest::class, $this->getParameters());

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint'
        );
        return $request->send();
    }

    public function completePurchase($options){

    }

    public function setShopId($value){
        return $this->setParameter("shop_id", $value);
    }
    public function getShopId(){
        return $this->getParameter("shop_id");
    }
    public function setPrivateKey($value){
        return $this->setParameter("private_key", $value);
    }
    public function getPrivateKey(){
        return $this->getParameter("private_key");
    }
    public function setTransactionId($value){
        return $this->setParameter("transactionId", $value);
    }
    public function getTransactionId(){
        return $this->getParameter("transactionId");
    }
}
