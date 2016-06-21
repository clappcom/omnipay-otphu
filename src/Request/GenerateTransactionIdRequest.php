<?php
namespace Clapp\OtpHu\Request;

use LSS\Array2XML;
use SimpleXMLElement;


class GenerateTransactionIdRequest extends AbstractRequest{

    protected $actionName = 'WEBSHOPTRANZAZONGENERALAS';

    public function getData(){
        $signedActionBody = Array2XML::createXML('StartWorkflow',
            [
                'TemplateName' => [
                    '@value' => $this->actionName,
                ],
                'Variables' => [
                    'isClientCode' => [
                        '@value' => 'WEBSHOP',
                    ],
                    'isPOSID' => [
                        '@value' => $this->getShopId()
                    ],
                    'isClientSignature' => [
                        '@attributes' => [
                            "algorithm" => "SHA512"
                        ],
                        '@value' => $this->generateSignature()
                    ],
                ],
            ]
        );
        return $this->createSoapEnvelope($this->actionName, $signedActionBody);
    }
    /**
     * aláírandó string összeállítása
     * ( 2.4.3.1 A digitális aláírás képzése )
     */
    protected function getSignatureData(){
        /**
         * - Egyedi tranzakció kérés esetén:
                - shop-azonosító
         */
        return $this->getShopId();
    }
    /**
     * transactionId kiszedése a response bodyból
     */
    public static function transformResponseBody($response){
        return (new SimpleXMLElement(base64_decode($response->xml()->xpath('//result')[0]->__toString())))->xpath('//id')[0]->__toString();
    }
}
