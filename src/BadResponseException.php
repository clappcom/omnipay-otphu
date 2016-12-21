<?php

namespace Clapp\OtpHu;

use Exception;
use Guzzle\Http\Exception\BadResponseException as BaseBadResponseException;
use SimpleXMLElement;

/**
 * amikor http 200 a response, de a base64-elt body-ban hibaüzenet van.
 */
class BadResponseException extends BaseBadResponseException
{
    public function __construct($message = '', $code = 0, $previous = null)
    {
        try {
            /**
             * az otp-s response-okban ilyen furán van benne az exception message.
             *
             * megpróbáljuk kiszedni belőle
             */
            $payload = base64_decode((new SimpleXMLElement($message))->xpath('//result')[0]->__toString());
            $message = (new SimpleXMLElement($payload))->xpath('//message')[0]->__toString();
        } catch (\Exception $e) {
        }

        parent::__construct($message, $code, $previous);
    }
}
