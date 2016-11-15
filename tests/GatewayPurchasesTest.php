<?php

use Clapp\OtpHu\Gateway as OtpHuGateway;
use Omnipay\Common\AbstractGateway;
use Clapp\OtpHu\TransactionIdFactory;
use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;
use Illuminate\Validaton\ValidationException;
use Omnipay\Common\Exception\InvalidRequestException;
use Clapp\OtpHu\BadResponseException;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Client as HttpClient;
use Clapp\OtpHu\Response\UnknownShopIdResponse;

class GatewayPurchasesTest extends TestCase{

    public function testUnknownShopIdPurchase(){
        /**
         * invalid response for "HIANYZIKSHOPPUBLIKUSKULCS"
         */
        $unknownShopIdResponseBody = '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><env:Header></env:Header><env:Body env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><m:startWorkflowSynchResponse xmlns:m="java:hu.iqsoft.otp.mw.access"><return xmlns:n1="java:hu.iqsoft.otp.mw.access" xsi:type="n1:WorkflowState"><completed xsi:type="xsd:boolean">true</completed><endTime xsi:type="xsd:string">2016.11.15. 19:53:43</endTime><instanceId xsi:type="xsd:string">8YuEEWnJyo7x5169189134</instanceId><result xsi:type="xsd:base64Binary">PD94bWwgdmVyc2lvbj0nMS4wJyBlbmNvZGluZz0ndXRmLTgnPz48YW5zd2VyPjxyZXN1bHRzZXQ+PHJlY29yZD48cG9zaWQ+NzQxNzk8L3Bvc2lkPjx0cmFuc2FjdGlvbmlkPjU8L3RyYW5zYWN0aW9uaWQ+PC9yZWNvcmQ+PC9yZXN1bHRzZXQ+PG1lc3NhZ2VsaXN0PjxtZXNzYWdlPkhJQU5ZWklLU0hPUFBVQkxJS1VTS1VMQ1M8L21lc3NhZ2U+PC9tZXNzYWdlbGlzdD48L2Fuc3dlcj4=</result><startTime xsi:type="xsd:string">2016.11.15. 19:53:43</startTime><templateName xsi:type="xsd:string">WEBSHOPFIZETESINDITAS</templateName><timeout xsi:type="xsd:boolean">false</timeout></return></m:startWorkflowSynchResponse></env:Body></env:Envelope>';

        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, $unknownShopIdResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-','',$this->faker->uuid));
        $gateway->setTestMode(false);

        try{
            $request = $gateway->purchase([
                'amount' => '100.00',
                'currency' => 'HUF'
            ]);
            $response = $request->send();
        }catch(UnknownShopIdResponse $e){
            $this->setLastException($e);
        }
        $this->assertLastException(UnknownShopIdResponse::class);

        //$this->assertContainsOnly($request, $plugin->getReceivedRequests());
    }
    public function testSuccessfulPurchase(){
        /**
         * response for "SIKERESWEBSHOPFIZETESINDITAS"
         */
        $successfulPurchaseResponseBody = '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><env:Header></env:Header><env:Body env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><m:startWorkflowSynchResponse xmlns:m="java:hu.iqsoft.otp.mw.access"><return xmlns:n1="java:hu.iqsoft.otp.mw.access" xsi:type="n1:WorkflowState"><completed xsi:type="xsd:boolean">true</completed><endTime xsi:type="xsd:string">2016.11.15. 22:11:07</endTime><instanceId xsi:type="xsd:string">dIsgwSLRmGOx5169691306</instanceId><result xsi:type="xsd:base64Binary">PD94bWwgdmVyc2lvbj0nMS4wJyBlbmNvZGluZz0ndXRmLTgnPz48YW5zd2VyPjxyZXN1bHRzZXQ+PHJlY29yZD48cG9zaWQ+IzAyMjk5OTkxPC9wb3NpZD48dHJhbnNhY3Rpb25pZD4xN2RiOGZjNTRhMzczM2M0ODk4YTY3ZTBkYmJkODk5NjwvdHJhbnNhY3Rpb25pZD48L3JlY29yZD48L3Jlc3VsdHNldD48bWVzc2FnZWxpc3Q+PG1lc3NhZ2U+U0lLRVJFU1dFQlNIT1BGSVpFVEVTSU5ESVRBUzwvbWVzc2FnZT48L21lc3NhZ2VsaXN0PjwvYW5zd2VyPg==</result><startTime xsi:type="xsd:string">2016.11.15. 22:11:07</startTime><templateName xsi:type="xsd:string">WEBSHOPFIZETESINDITAS</templateName><timeout xsi:type="xsd:boolean">false</timeout></return></m:startWorkflowSynchResponse></env:Body></env:Envelope>';

        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, $successfulPurchaseResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-','',$this->faker->uuid));
        $gateway->setTestMode(false);

        try{
            $request = $gateway->purchase([
                'amount' => '100.00',
                'currency' => 'HUF'
            ]);
            $response = $request->send();
        }catch(UnknownShopIdResponse $e){
            $this->setLastException($e);
        }
        $this->assertEquals($gateway->getTransactionId(), $response->getTransactionId());

        return $response;
    }
    /**
     * @depends testSuccessfulPurchase
     */
    public function testSuccessfulPurchaseResponse($response){
        /**
         * ez mindig false, mert 3 szereplős fizetést használ az otp,
         * ami azt jelenti, hogy nem mi kérjük be a bankkártya adatokat, hanem az otp oldala,
         *
         * így a terhelés sem sikerülhet anélkül, hogy át ne irányítanánk az otp oldalára
         */
        $this->assertFalse($response->isSuccessful());

        $this->assertTrue($response->isRedirect());

        $this->assertNotEmpty($response->getRedirectUrl());

        //var_dump("REDIRECTED TO", $response->getRedirectUrl());
    }
    public function testPurchase(){
        return;
        $gateway = Omnipay::create("\\".OtpHuGateway::class);

        $gateway->setTransactionIdFactory(new TransactionIdFactory());

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        try {
            $response = $gateway->purchase([
                'amount' => '100.00',
                'currency' => 'HUF'
            ])->send();

            $response->getTransactionId(); // a reference generated by the payment gateway

            if ($response->isSuccessful()) {
                // payment was successful: update database
                /**
                 * ez sosem történhet meg, mert 3 szereplős fizetést használ az otp,
                 * ami azt jelenti, hogy nem mi kérjük be a bankkártya adatokat, hanem az otp oldala,
                 *
                 * így a terhelés sem sikerülhet anélkül, hogy át ne irányítanánk az otp oldalára
                 */
                print_r($response);
            } elseif ($response->isRedirect()) {
                // redirect to offsite payment gateway
                /**
                 * mindig redirectes választ fogunk kapni a ->puchase()-től, hiszen a háromszereplős fizetés miatt át kell irányítani a felhasználót az otp oldalára
                 */
                //$url = $response->getRedirectUrl();
                //$data = $response->getRedirectData(); // associative array of fields which must be posted to the redirectUrl

                echo 'REDIRECT NEEDED TO'."\n";
                $url = $response->getRedirectUrl();
                echo $url . "\n\n";


                //$response->redirect();
            } else {
                // payment failed: display message to customer
                /**
                 * az otp nem fogadta el a terhelési kérésünket
                 */
                echo $response->getMessage();
            }
        }
        catch(ValidationException $e){
            /**
             * hiányzó shopid, hiányzó vagy hibás private key, vagy hiányzó felhasználó adatok
             */
        }
        catch (Exception $e) {
            // internal error, log exception and display a generic message to the customer
            echo $e->getMessage();

            echo("\n\n".$e->getTraceAsString()."\n");
            exit("\n".'Sorry, there was an error processing your payment. Please try again later.');
        }

        return $response->getTransactionId();
    }
    /**
     * @depends testPurchase
     */
    public function testCompletePurchase($transactionId){
        return;
        $gateway = Omnipay::create("\\".OtpHuGateway::class);

        $gateway->setShopId("#02299991");
        $gateway->setPrivateKey(file_get_contents("#02299991.privKey.pem"));

        try {
            $response = $gateway->completePurchase([
                'transactionId' => $transactionId,
            ])->send();
            if ($response->isSuccessful()) {
                // payment was successful: update database
                echo 'SUCCESSFUL: ';
                print_r($response->getTransactionId());
            } else if ($response->isPending()){
                echo 'PENDING: ';
                print_r($response->getTransactionId());
            } else if ($response->isCancelled()){
                echo 'CANCELLED: ';
                print_r($response->getTransactionId());
            } else {
                // payment failed: display message to customer
                echo 'FAILED';
                echo $response->getMessage();
            }
        }
        catch (Exception $e) {
            // internal error, log exception and display a generic message to the customer
            //exit('Sorry, there was an error processing your payment. Please try again later.');
            throw $e;
        }
    }
}
