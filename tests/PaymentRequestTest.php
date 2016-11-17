<?php

use Clapp\OtpHu\Request\PaymentRequest;
use Guzzle\Http\Client as HttpClient;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class PaymentRequestTest extends TestCase{

    public function testLanguageSettings(){
        $request = new PaymentRequest(new HttpClient(), new HttpRequest());

        $request->setLanguage('hu');

        $this->assertEquals('hu', $request->getLanguage());
    }
    public function testCurrencySettings(){
        $request = new PaymentRequest(new HttpClient(), new HttpRequest());

        $request->setCurrency('EUR');

        $this->assertEquals(2, $request->getCurrencyDecimalPlaces());

        $request->setCurrency('HUF');

        $this->assertEquals(0, $request->getCurrencyDecimalPlaces());
    }
}
