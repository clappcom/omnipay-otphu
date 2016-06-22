<?php
namespace Clapp\OtpHu\Request;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use LSS\Array2XML;
use DomDocument;
use Clapp\OtpHu\BadResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException as GuzzleServerErrorResponseException;
use Clapp\OtpHu\ServerErrorResponseException;
use Guzzle\Http\Message\Response as GuzzleResponse;


abstract class AbstractRequest extends BaseAbstractRequest{

    protected function createSoapEnvelope($actionName, DomDocument $signedActionBody){
        $signedActionBody->encoding = "ISO-8859-2";

        $dom = Array2XML::createXML('soapenv:Envelope',
            [
            '@attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:xsd' => 'http://www.w3.org/2001/XMLSchema',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
            'soapenv:Body' => [
                'ns1:startWorkflowSynch' => [
                    '@attributes' => [
                        'soapenv:encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
                        'xmlns:ns1' => 'urn:MWAccess',
                    ],
                    'arg0' => [
                        '@attributes' => [
                            'xsi:type' => 'xsd:string',
                        ],
                        '@value' => $actionName
                    ],
                    'arg1' => [
                        '@attributes' => [
                            'xsi:type' => 'xsd:string',
                        ],
                        '@value' => htmlspecialchars($signedActionBody->saveXML(),ENT_QUOTES, 'UTF-8')
                    ],
                ]
            ]
        ]);

        $xml = $dom->saveXML();
        //hack
        $xml = str_replace('&amp;', '&', $xml);
        return $xml;
    }
    /**
     * aláírás generálása
     */
    protected function generateSignature(){
        $data = $this->getSignatureData();
        $signature = null;
        $key = openssl_get_privatekey($this->getPrivateKey());
        if (openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA512) === false) throw new \Exception('unable to generate signature');
        return bin2hex($signature);
    }

    public function transformResponse(GuzzleResponse $response){
        return $response;
    }

    /**
     * transactionId generáltatása az otp szerverével
     */
    public function sendData($data){

        $request = $this->httpClient->post(
            $this->getEndpoint(),
            [
                'content-type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'urn:startWorkflowSynch',
            ],
            $data
        );

        try {
            $response = $request->send();
            return $this->transformResponse($response);
        }catch(GuzzleServerErrorResponseException $e){
            throw ServerErrorResponseException::fromBase($e);
        }
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
    public function setEndpoint($value){
        return $this->setParameter("endpoint", $value);
    }
    public function getEndpoint(){
        return $this->getParameter("endpoint");
    }
}
