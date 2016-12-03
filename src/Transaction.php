<?php
namespace Clapp\OtpHu;

use SimpleXMLElement;
use Exception;
use InvalidArgumentException;
use Carbon\Carbon;

/**
 * wrapper class for transaction details
 */
class Transaction{
    /*
    {
       "transactionid":"17728f8f9f82313494043bb7224b2f6c",
       "posid":"#02299991",
       "state":"VEVOOLDAL_VISSZAVONT",
       "responsecode":"VISSZAUTASITOTTFIZETES",
       "shopinformed":"true",
       "startdate":"20161116235449",
       "enddate":"20161117000150",
       "params":{
          "input":{
             "backurl":"http:\/\/www.google.com",
             "exchange":"HUF",
             "zipcodeneeded":"false",
             "narrationneeded":"false",
             "mailaddressneeded":"false",
             "countyneeded":"FALSE",
             "nameneeded":"false",
             "languagecode":"hu",
             "countryneeded":"FALSE",
             "amount":"100",
             "settlementneeded":"false",
             "streetneeded":"false",
             "consumerreceiptneeded":"FALSE",
             "consumerregistrationneeded":"FALSE"
          },
          "output":{

          }
       }
    }
     */

     protected $rawTransaction = null;
     protected $transaction = null;

     public static $possibleRejectionErrorCodes = [
         '055' => 'A megadott kártya blokkolt.',
         '058' => 'A megadott kártyaszám érvénytelen',
         '070' => 'A megadott kártyaszám érvénytelen, a BIN nem létezik',

         '901' => 'A megadott kártya lejárt',
         '051' => 'A megadott kártya lejárt',

         '902' => 'A megadott kártya letiltott',
         '059' => 'A megadott kártya letiltott',
         '072' => 'A megadott kártya letiltott',

         '057' => 'A megadott kártya elvesztett kártya',
         '903' => 'A megadott bankkártya nem aktív',

         '097' => 'A megadott kártyaadatok hibásak',
         '069' => 'A megadott kártyaadatok hibásak',

         '074' => 'A megadott kártyaadatok hibásak, vagy nincs elég fedezet',
         '076' => 'A megadott kártyaadatok hibásak, vagy nincs elég fedezet',
         '050' => 'A megadott kártyaadatok hibásak, vagy nincs elég fedezet',
         '200' => 'A megadott kártyaadatok hibásak, vagy nincs elég fedezet',
         '089' => 'A megadott kártyaadatok hibásak, vagy nincs elég fedezet',

         '206' => 'A megadott kártya az üzletági követelményeknek nem felel meg',
         '056' => 'A megadott kártyaszám ismeretlen',
         '909' => 'A megadott kártya terhelése nem lehetséges',

         '204' => 'A megadott kártya terhelése a megadott összeggel nem lehetséges (jellemzően vásárlási limittúllépés miatt)',
         '082' => 'A megadott kártya terhelése a megadott összeggel nem lehetséges (jellemzően vásárlási limittúllépés miatt)',

         '205' => 'Érvénytelen összegű vásárlás',
     ];

    public function __construct($rawTransaction = null){
        $this->setRawTransaction($rawTransaction);
    }

    public function setRawTransaction($rawTransaction = null){
        $this->rawTransaction = $rawTransaction;
        try{
            $this->transaction = json_decode(json_encode($rawTransaction));
        }catch(Exception $e){
            $this->transaction = null;
        }
    }
    public function getRawTransaction(){
        return $this->rawTransaction;
    }

    protected function getDate($dateString){
        return Carbon::createFromFormat('YmdHis', $dateString);
    }

    public function getStartDate(){
        if (empty($this->transaction)) throw new Exception("no transaction details found");
        if (!empty($this->transaction->startdate) && is_string($this->transaction->startdate)){
            return $this->getDate($this->transaction->startdate);
        }else {
            return null;
        }
    }
    public function getEndDate(){
        if (empty($this->transaction)) throw new Exception("no transaction details found");
        if (!empty($this->transaction->enddate) && is_string($this->transaction->enddate)){
            return $this->getDate($this->transaction->enddate);
        }else {
            return null;
        }
    }

    public static function fromXml($xmlString){
        $payload = base64_decode((new SimpleXMLElement($xmlString))->xpath('//result')[0]->__toString());
        $rawTransaction = (array)((new SimpleXMLElement($payload))->xpath('//record')[0]);
        return new self($rawTransaction);
    }

    public function getRejectionReasonCode(){
        if (empty($this->rawTransaction['responsecode'])) return null;
        return $this->rawTransaction['responsecode'];
    }

    public function getRejectionReasonMessage(){
        if (empty($this->rawTransaction['responsecode'])) return null;
        try {
            return $this->translateRejectionCodeToMessage($this->rawTransaction['responsecode']);
        }catch(InvalidArgumentException $e){
            return null;
        }
    }
    public function getTransactionId(){
        if (empty($this->transaction)) throw new Exception("no transaction details found");
        return $this->transaction->transactionid;
    }
    /**
     * lezárult-e a tranzakció (akár sikeresen, akár sikertelenül)
     */
    public function isCompleted(){
        if (empty($this->transaction)) throw new Exception("no transaction details found");
        $values = [
            "FELDOLGOZVA"
        ];
        return in_array($this->transaction->state, $values);
    }
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful(){
        if (empty($this->rawTransaction)) throw new Exception("no transaction details found");

        if ($this->isCompleted()){
            if (intval($this->rawTransaction['responsecode']) <= 10){
                return true;
            }
        }
        return false;
    }
    /**
     * Is the response rejected?
     */
    public function isRejected(){
        if (empty($this->rawTransaction)) throw new Exception("no transaction details found");

        if ($this->isCompleted()){
            if (intval($this->rawTransaction['responsecode']) > 10){
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
        if (empty($this->rawTransaction)) throw new Exception("no transaction details found");
        $values = [
            "VEVOOLDAL_VISSZAVONT",
            "VEVOOLDAL_TIMEOUT",
            "BOLTOLDAL_TIMEOUT", //?
        ];
        return in_array($this->rawTransaction['state'], $values);
    }
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isPending()
    {
        if (empty($this->rawTransaction)) throw new Exception("no transaction details found");
        $values = [
            "FELDOLGOZAS_ALATT",
            "VEVOOLDAL_INPUTVARAKOZAS",
            "LEZARAS_ALATT", //?
            "BOLTOLDAL_LEZARASVARAKOZAS", //?
        ];
        return in_array($this->rawTransaction['state'], $values);
    }

    protected function translateRejectionCodeToMessage($code){
        if (!isset(self::$possibleRejectionErrorCodes[$code])){
            throw new InvalidArgumentException('unknown rejection code');
        }
        return self::$possibleRejectionErrorCodes[$code];
    }

}
