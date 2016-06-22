<?php
namespace Clapp\OtpHu\Response;

use Omnipay\Common\Message\AbstractResponse;
use SimpleXMLElement;
use Omnipay\Common\Message\RequestInterface;
use Clapp\OtpHu\BadResponseException;

class TransactionDetailsResponse extends AbstractResponse{

    protected $transaction;

    public function __construct(RequestInterface $request, $data){
        parent::__construct($request, $data);

        try {
            $payload = base64_decode((new SimpleXMLElement($data))->xpath('//result')[0]->__toString());
            $this->transaction = (new SimpleXMLElement($payload))->xpath('//record')[0];

            $this->transaction = (array)$this->transaction;
        }catch(Exception $e){
            throw new BadResponseException($data);
        }
        if (empty($this->transaction)){
            throw new BadResponseException($data);
        }
    }
    /**
     * @override
     */
    public function getTransactionId(){
        return $this->transaction['transactionid'];
    }
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful(){
        if (empty($this->transaction)) throw new Exception("no transaction details found");
        print_r($this->transaction); exit;
        $values = [
            "FELDOLGOZVA"
        ];
        return in_array($this->transaction['state'], $values);
    }
    /**
     * Is the transaction cancelled by the user?
     *
     * @return boolean
     */
    public function isCancelled()
    {
        if (empty($this->transaction)) throw new Exception("no transaction details found");
        $values = [
            "VEVOOLDAL_VISSZAVONT",
            "VEVOOLDAL_TIMEOUT",
            "BOLTOLDAL_TIMEOUT", //?
        ];
        return in_array($this->transaction['state'], $values);
    }
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isPending()
    {
        if (empty($this->transaction)) throw new Exception("no transaction details found");
        $values = [
            "FELDOLGOZAS_ALATT",
            "VEVOOLDAL_INPUTVARAKOZAS",
            "LEZARAS_ALATT", //?
            "BOLTOLDAL_LEZARASVARAKOZAS", //?
        ];
        return in_array($this->transaction['state'], $values);
    }
}
