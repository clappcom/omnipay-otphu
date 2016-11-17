<?php

namespace Clapp\OtpHu;

use Clapp\OtpHu\Request\GenerateTransactionIdRequest;

class TransactionIdFactory extends Gateway{

    public function generateTransactionId($parameters){
        $request = $this->getGenerateTransactionIdRequest($parameters);
        return $request->send();
    }

    protected function getGenerateTransactionIdRequest($parameters){
        $request = $this->createRequest("\\".GenerateTransactionIdRequest::class, $parameters);

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint'
        );
        return $request;
    }
}
