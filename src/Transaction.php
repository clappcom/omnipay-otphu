<?php
namespace Clapp\OtpHu;

use SimpleXMLElement;
use Exception;
use InvalidArgumentException;
use Carbon\Carbon;

/**
 * Wrapper class for transaction details.
 */
class Transaction{
    /**
     * @var array|null the raw transaction details provided by the gateway
     */
    protected $rawTransaction = null;
    /**
     * example:
     *
     * ```javascript
     * {
     *    "transactionid":"17728f8f9f82313494043bb7224b2f6c",
     *    "posid":"#02299991",
     *    "state":"VEVOOLDAL_VISSZAVONT",
     *    "responsecode":"VISSZAUTASITOTTFIZETES",
     *    "shopinformed":"true",
     *    "startdate":"20161116235449",
     *    "enddate":"20161117000150",
     *    "params":{
     *       "input":{
     *          "backurl":"http:\/\/www.google.com",
     *          "exchange":"HUF",
     *          "zipcodeneeded":"false",
     *          "narrationneeded":"false",
     *          "mailaddressneeded":"false",
     *          "countyneeded":"FALSE",
     *          "nameneeded":"false",
     *          "languagecode":"hu",
     *          "countryneeded":"FALSE",
     *          "amount":"100",
     *          "settlementneeded":"false",
     *          "streetneeded":"false",
     *          "consumerreceiptneeded":"FALSE",
     *          "consumerregistrationneeded":"FALSE"
     *       },
     *       "output":{
     *
     *       }
     *    }
     * }
     * ```
     *
     * @var array|null easier to use version of $rawTransaction - only `StdObject`s and `Array`s
     */
    protected $formattedTransaction = null;
    /**
     * @var array List of human readable meanings for each error code string from the gateway
     */
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
    /**
     * Create a new instance to parse a raw transaction object
     * @param array $rawTransaction the transaction details from the gateway
     * @return void
     */
    public function __construct($rawTransaction = null){
        $this->setRawTransaction($rawTransaction);
    }
    /**
     * set the raw transaction details and parse it into $formattedTransaction
     *
     * @param array $rawTransaction the transaction details from the gateway
     * @return void
     */
    public function setRawTransaction($rawTransaction = null){
        $this->rawTransaction = $rawTransaction;
        try{
            $this->formattedTransaction = json_decode(json_encode($rawTransaction));
        }catch(Exception $e){
            $this->formattedTransaction = null;
        }
    }
    /**
     * get the raw transaction details
     * @return array raw transaction details from the gateway
     */
    public function getRawTransaction(){
        return $this->rawTransaction;
    }
    /**
     * parse a date string from the format used in the gateway to a Carbon instance
     * @param  string $dateString a datetime string in the format used by the gateway
     * @return Carbon date instance
     */
    protected function getDate($dateString){
        return Carbon::createFromFormat('YmdHis', $dateString);
    }
    /**
     * get the starting date of the first transaction of the results
     * @return Carbon|null date instance
     */
    public function getStartDate(){
        if (empty($this->formattedTransaction)) throw new Exception("no transaction details found");
        if (!empty($this->formattedTransaction->startdate) && is_string($this->formattedTransaction->startdate)){
            return $this->getDate($this->formattedTransaction->startdate);
        }else {
            return null;
        }
    }
    /**
     * get the ending date of the last transaction of the results
     * @return Carbon|null date instance
     */
    public function getEndDate(){
        if (empty($this->formattedTransaction)) throw new Exception("no transaction details found");
        if (!empty($this->formattedTransaction->enddate) && is_string($this->formattedTransaction->enddate)){
            return $this->getDate($this->formattedTransaction->enddate);
        }else {
            return null;
        }
    }
    /**
     * initialize a new Transaction instance from an xml string provided by the gateway
     * @param  string $xmlString the xml string provided by the gateway
     * @return Transaction transaction details instance
     */
    public static function fromXml($xmlString){
        $payload = base64_decode((new SimpleXMLElement($xmlString))->xpath('//result')[0]->__toString());
        $rawTransaction = (array)((new SimpleXMLElement($payload))->xpath('//record')[0]);
        return new self($rawTransaction);
    }
    /**
     * get the rejection code provided by the gateway
     * @return string|null rejection code from the gateway
     */
    public function getRejectionReasonCode(){
        if (empty($this->rawTransaction['responsecode'])) return null;
        return $this->rawTransaction['responsecode'];
    }
    /**
     * get the human readable version of the rejection reason
     * @return string|null human readable version of the rejection reason
     */
    public function getRejectionReasonMessage(){
        if (empty($this->rawTransaction['responsecode'])) return null;
        try {
            return $this->translateRejectionCodeToMessage($this->rawTransaction['responsecode']);
        }catch(InvalidArgumentException $e){
            return null;
        }
    }
    /**
     * get the transaction id
     * @return string transaction id
     */
    public function getTransactionId(){
        if (empty($this->formattedTransaction)) throw new Exception("no transaction details found");
        return $this->formattedTransaction->transactionid;
    }
    /**
     * is this transaction completed (not pending)?
     * @return boolean whether or not the transaction is completed
     */
    public function isCompleted(){
        if (empty($this->formattedTransaction)) throw new Exception("no transaction details found");
        $values = [
            "FELDOLGOZVA"
        ];
        return in_array($this->formattedTransaction->state, $values);
    }
    /**
     * Is this transaction successful?
     *
     * @return boolean whether or not the transaction is completed and successful
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
     * Is this transaction rejected?
     *
     * @return boolean whether or not the transaction is completed, but rejected
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
     * Is this transaction cancelled by the user?
     *
     * @return boolean whether or not the transaction is completed, but cancelled by the user
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
     * Is this transaction still pending?
     *
     * @return boolean whether or not the transaction is not completed and still pending
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
    /**
     * translate a rejection code to a human readable string
     *
     * @throws InvalidArgumentException if the $code is not found in $possibleRejectionErrorCodes
     * @return string human readable rejection string
     */
    protected function translateRejectionCodeToMessage($code){
        if (!isset(self::$possibleRejectionErrorCodes[$code])){
            throw new InvalidArgumentException('unknown rejection code');
        }
        return self::$possibleRejectionErrorCodes[$code];
    }

}
