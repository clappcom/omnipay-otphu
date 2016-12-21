<?php

namespace Clapp\OtpHu\Request;

use LSS\Array2XML;
use Guzzle\Http\Message\Response as GuzzleResponse;
use Clapp\OtpHu\Response\GenerateTransactionIdResponse;

class GenerateTransactionIdRequest extends AbstractRequest
{
    protected $actionName = 'WEBSHOPTRANZAZONGENERALAS';

    public function getData()
    {
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
                        '@value' => $this->getShopId(),
                    ],
                    'isClientSignature' => [
                        '@attributes' => [
                            'algorithm' => 'SHA512',
                        ],
                        '@value' => $this->generateSignature(),
                    ],
                ],
            ]
        );

        return $this->createSoapEnvelope($this->actionName, $signedActionBody);
    }

    /**
     * aláírandó string összeállítása
     * ( 2.4.3.1 A digitális aláírás képzése ).
     */
    protected function getSignatureData()
    {
        /*
         * - Egyedi tranzakció kérés esetén:
         *       - shop-azonosító
         */
        return $this->getShopId();
    }

    public function transformResponse(GuzzleResponse $response)
    {
        return new GenerateTransactionIdResponse($this, $response->getBody());
    }
}
