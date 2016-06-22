<?php
namespace Clapp\OtpHu;

use Guzzle\Http\Exception\BadResponseException as BaseBadResponseException;
use SimpleXMLElement;
use Exception;

/**
 * amikor http 200 a response, lezárult a tranzakció, de hibakódot kaptunk
 */
class TransactionFailedException extends BaseBadResponseException{
    public function __construct($message = "", $code = 0, $previous = null){
        try {
            if (empty($message)){
                $message = $this->translateCodeToMessage($code);
            }
        }catch(\Exception $e){}

        parent::__construct($message, $code, $previous);
    }

    protected $possibleErrorCodes = [
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

    protected function translateCodeToMessage($code){
        return $this->possibleErrorCodes[$code];
    }
}
