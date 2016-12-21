<?php

namespace Clapp\OtpHu\Request;

use LSS\Array2XML;
use Guzzle\Http\Message\Response as GuzzleResponse;
use Clapp\OtpHu\Response\TransactionDetailsResponse;

class TransactionDetailsRequest extends AbstractRequest
{
    protected $actionName = 'WEBSHOPTRANZAKCIOLEKERDEZES';

    public function getData()
    {
        $variablesData = [
            'isClientCode' => 'WEBSHOP', // ?
            'isPOSID' => $this->getShopId(),
            'isTransactionID' => $this->getTransactionId(),
            'isMaxRecords' => '',
            'isStartDate' => '',
            'isEndDate' => '',
        ];

        $variables = [];

        foreach ($variablesData as $key => $value) {
            $variables[$key] = [
                '@value' => $value,
            ];
        }

        $variables['isClientSignature'] = [
           '@attributes' => [
                'algorithm' => 'SHA512',
            ],
            '@value' => $this->generateSignature(),
        ];

        $signedActionBody = Array2XML::createXML('StartWorkflow',
            [
                'TemplateName' => [
                    '@value' => $this->actionName,
                ],
                'Variables' => $variables,
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
        /**
         *  Fizetési tranzakciók ellenőrzése/lekérdezése esetén:.
         */
        $data = [
            $this->getShopId(),
            $this->getTransactionId(),
            '',
            '',
            '',
        ];

        return implode('|', $data);
    }

    public function transformResponse(GuzzleResponse $response)
    {
        return new TransactionDetailsResponse($this, $response->getBody());
    }
}
