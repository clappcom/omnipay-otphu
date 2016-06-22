<?php
namespace Clapp\OtpHu;

use Guzzle\Http\Exception\ServerErrorResponseException as BaseServerErrorResponseException;
use SimpleXMLElement;
use Exception;
use Guzzle\Http\Exception\BadResponseException;

/**
 * amikor http 200 a response, de a base64-elt body-ban hibaüzenet van
 */
class ServerErrorResponseException extends BaseServerErrorResponseException{
    public function __construct($message = "", $code = 0, $previous = null){
        try {
            /**
             * az otp-s response-okban ilyen furán van benne az exception message
             *
             * megpróbáljuk kiszedni belőle
             */
            $message = (new SimpleXMLElement($message))->xpath('//faultstring')[0]->__toString();
        }catch(\Exception $e){}

        parent::__construct($message, $code, $previous);
    }

    public static function fromBase(BadResponseException $e){
        $ret = new static($e->getResponse()->getBody(), $e->getCode(), $e);
        $ret->setResponse($e->getResponse());
        return $ret;
    }
}
