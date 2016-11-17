<?php
namespace Clapp\OtpHu\Response;

use Omnipay\Common\Message\AbstractResponse;
use SimpleXMLElement;
use Omnipay\Common\Message\RequestInterface;
use Clapp\OtpHu\BadResponseException;
use Clapp\OtpHu\TransactionFailedException;
use Exception;

class TransactionDetailsResponse extends AbstractResponse{

    protected $transaction;

    public function __construct(RequestInterface $request, $data){
        parent::__construct($request, $data);


        $this->transaction = $this->parseTransaction($data);

    }
    /**
     * transaction adatainak kiszedése a raw response-ból
     */
    protected function parseTransaction($data){
        $payload = base64_decode((new SimpleXMLElement($data))->xpath('//result')[0]->__toString());
        $transaction = (array)((new SimpleXMLElement($payload))->xpath('//record')[0]);

        if (empty($transaction)){
            throw new BadResponseException($data);
        }
        if ($this->isCompleted($transaction)){
            if (empty($transaction['responsecode'])){
                throw new BadResponseException($data);
            }
            /*$responseCode = $transaction['responsecode'];
            if (intval($responseCode) > 10){
                throw new TransactionFailedException("", $responseCode);
            }*/
        }
        return $transaction;
    }
    public function getTransaction(){
        return $this->transaction;
    }
    /**
     * @override
     */
    public function getTransactionId(){
        return $this->transaction['transactionid'];
    }
    /**
     * lezárult-e a tranzakció (akár sikeresen, akár sikertelenül)
     */
    protected function isCompleted($transaction = null){
        if (empty($transaction)) $transaction = $this->transaction;
        if (empty($transaction)) throw new Exception("no transaction details found");
        $values = [
            "FELDOLGOZVA"
        ];
        return in_array($transaction['state'], $values);
    }
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful(){
        if (empty($this->transaction)) throw new Exception("no transaction details found");

        if ($this->isCompleted()){
            if (intval($this->transaction['responsecode']) <= 10){
                return true;
            }
        }
        return false;
    }
    /**
     * Is the response rejected?
     */
    public function isRejected(){
        if (empty($this->transaction)) throw new Exception("no transaction details found");

        if ($this->isCompleted()){
            if (intval($this->transaction['responsecode']) > 10){
                return true;
            }
        }
        return false;
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
