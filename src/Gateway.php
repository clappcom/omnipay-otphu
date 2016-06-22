<?php

namespace Clapp\OtpHu;

use Omnipay\Common\AbstractGateway;
use Clapp\OtpHu\Request\GenerateTransactionIdRequest;
use Clapp\OtpHu\Request\PaymentRequest;
use Clapp\OtpHu\Request\TransactionDetailsRequest;
use Clapp\OtpHu\Response\GenerateTransactionIdResponse;
use SimpleXMLElement;
use InvalidArgumentException;



class Gateway extends AbstractGateway{

    protected $endpoint = "https://www.otpbankdirekt.hu/mwaccesspublic/mwaccess";

    protected $transactionIdFactory = null;

    public function __construct(ClientInterface $httpClient = null, HttpRequest $httpRequest = null){
        parent::__construct($httpClient, $httpRequest);

        $this->setParameter('endpoint', $this->endpoint);
    }

    public function getName(){
        return "otphu";
    }

    public function purchase($options){
        $transactionId = $this->getTransactionId($options);
         if (!empty($transactionId)){
            $this->setTransactionId($transactionId);
        }
        /**
         * generáltassunk az OTP-vel transactionId-t, ha nem lenne nekünk
         */
        if (empty($transactionId)){
            if (empty($this->transactionIdFactory)){
                throw new InvalidArgumentException('missing factory for auto generating transaction_id');
            }
            $transactionId = $this->transactionIdFactory->generateTransactionId(array_merge($options, $this->getParameters()));
        }
        $this->setTransactionId($transactionId);

        $request = $this->createRequest("\\".PaymentRequest::class, array_merge($options, $this->getParameters()));

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint',
            'transactionId'
        );

        return $request;
    }

    public function completePurchase($options){
        $transactionId = $this->getTransactionId($options);
        if (!empty($transactionId)){
            $this->setTransactionId($transactionId);
        }

        $request = $this->createRequest("\\".TransactionDetailsRequest::class, $this->getParameters());

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint',
            'transactionId'
        );
        return $request;
    }
    /**
     * override, hogy ha van shop_id-nk, akkor az menjen át a shop id getter függvényén
     * ez azért fontos, mert az OTP-nél a testmode abban nyilvánul meg, hogy a shop_id egy "#" karakterrel kezdődik
     */
    public function getParameters(){
        $params = parent::getParameters();
        if (isset($params['shop_id'])) $params['shop_id'] = $this->getShopId();
        return $params;
    }

    public function setTransactionIdFactory(TransactionIdFactory $factory){
        $this->transactionIdFactory = $factory;
        return $this;
    }
    public function getTransactionIdFactory(){
        return $this->transactionIdFactory;
    }

    public function setShopId($value){
        return $this->setParameter("shop_id", $value);
    }
    public function getShopId(){
        $value = $this->getParameter("shop_id");
        /**
         * testmode-ban van előtte "#" karakter, production módban nincsen
         */
        if (!empty($value)){
            $value = ltrim($value, "#");
        }
        if ($this->getTestMode()){
            $value = "#".$value;
        }
        return $value;
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
    public function getTransactionId($options = []){
        $transactionId = $this->getParameter("transactionId");
        if (!empty($options)){
            if (!empty($options['transactionId'])){
                $transactionId = $options['transactionId'];
            }
            if (!empty($options['transaction_id'])){
                $transactionId = $options['transaction_id'];
            }
        }
        return $transactionId;
    }
}
