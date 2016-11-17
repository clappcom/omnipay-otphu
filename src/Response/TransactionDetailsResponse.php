<?php
namespace Clapp\OtpHu\Response;

use Omnipay\Common\Message\AbstractResponse;
use SimpleXMLElement;
use Omnipay\Common\Message\RequestInterface;
use Clapp\OtpHu\BadResponseException;
use Clapp\OtpHu\TransactionFailedException;
use Exception;
use InvalidArgumentException;

class TransactionDetailsResponse extends AbstractResponse{

    protected $transaction;

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
    public function getRejectionReasonMessage(){
        if (empty($this->transaction['responsecode'])) return null;
        try {
            return $this->translateRejectionCodeToMessage($this->transaction['responsecode']);
        }catch(InvalidArgumentException $e){
            return null;
        }
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

    protected function translateRejectionCodeToMessage($code){
        if (!isset(self::$possibleRejectionErrorCodes[$code])){
            throw new InvalidArgumentException('unknown rejection code');
        }
        return self::$possibleRejectionErrorCodes[$code];
    }
}
