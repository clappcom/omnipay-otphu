<?php

use Clapp\OtpHu\Gateway as OtpHuGateway;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Omnipay\Omnipay;

class GatewayCompletePurchasesTest extends TestCase
{
    public function testCompletePurchasePending()
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsPendingResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        $response = $gateway->completePurchase([
            'transactionId' => 'myTransactionId',
        ])->send();

        $this->assertNotNull($response->getTransactionId());

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRejected());

        $this->assertFalse($response->getTransaction()->isSuccessful());
        $this->assertTrue($response->getTransaction()->isPending());
        $this->assertFalse($response->getTransaction()->isCancelled());
        $this->assertFalse($response->getTransaction()->isRejected());

        $this->assertNull($response->getTransaction()->getRejectionReasonMessage());
    }

    public function testCompletePurchaseCancelled()
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCancelledResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        $response = $gateway->completePurchase([
            'transactionId' => 'myTransactionId',
        ])->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isCancelled());
        $this->assertFalse($response->isRejected());

        $this->assertFalse($response->getTransaction()->isSuccessful());
        $this->assertFalse($response->getTransaction()->isPending());
        $this->assertTrue($response->getTransaction()->isCancelled());
        $this->assertFalse($response->getTransaction()->isRejected());

        $this->assertNull($response->getTransaction()->getRejectionReasonMessage());
    }

    public function testCompletePurchaseRejected()
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsRejectedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        $response = $gateway->completePurchase([
            'transactionId' => 'myTransactionId',
        ])->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertTrue($response->isRejected());

        $this->assertFalse($response->getTransaction()->isSuccessful());
        $this->assertFalse($response->getTransaction()->isPending());
        $this->assertFalse($response->getTransaction()->isCancelled());
        $this->assertTrue($response->getTransaction()->isRejected());

        $this->assertNotNull($response->getTransaction()->getRejectionReasonMessage());
    }

    public function testCompletePurchaseCompleted($transactionId = null)
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCompletedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        $response = $gateway->completePurchase([
            'transactionId' => 'myTransactionId',
        ])->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRejected());

        $this->assertTrue($response->getTransaction()->isSuccessful());
        $this->assertFalse($response->getTransaction()->isPending());
        $this->assertFalse($response->getTransaction()->isCancelled());
        $this->assertFalse($response->getTransaction()->isRejected());

        $this->assertNull($response->getTransaction()->getRejectionReasonMessage());
    }

    /**
     * @expectedException Clapp\OtpHu\BadResponseException
     */
    public function testCompletePurchaseCompletedButMissingResponseCode()
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, $this->generateResponseBody([
            'answer' => [
                'resultset' => [
                    'record' => [
                        'transactionid' => '268396fda0543089aceb23cc35331eb9',
                        'posid'         => '#02299991',
                        'state'         => 'FELDOLGOZVA',
                        //"responsecode"=>"000",
                        'shopinformed'=> 'true',
                        'startdate'   => '20161117002756',
                        'enddate'     => '20161117003021',
                        'params'      => [
                            'input'=> [
                                'backurl'                   => 'http=>//www.google.com',
                                'exchange'                  => 'HUF',
                                'zipcodeneeded'             => 'false',
                                'narrationneeded'           => 'false',
                                'mailaddressneeded'         => 'false',
                                'countyneeded'              => 'FALSE',
                                'nameneeded'                => 'false',
                                'languagecode'              => 'hu',
                                'countryneeded'             => 'FALSE',
                                'amount'                    => '100',
                                'settlementneeded'          => 'false',
                                'streetneeded'              => 'false',
                                'consumerreceiptneeded'     => 'FALSE',
                                'consumerregistrationneeded'=> 'FALSE',
                            ],
                            'output'=> [
                                'authorizationcode'=> '405298',
                            ],
                        ],
                    ],
                ],
                'messagelist'=> [
                    'message'=> 'SIKER',
                ],
            ],
        ])));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        $response = $gateway->completePurchase([
            'transactionId' => 'myTransactionId',
        ])->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRejected());

        $this->assertTrue($response->getTransaction()->isSuccessful());
        $this->assertFalse($response->getTransaction()->isPending());
        $this->assertFalse($response->getTransaction()->isCancelled());
        $this->assertFalse($response->getTransaction()->isRejected());

        $this->assertNull($response->getTransaction()->getRejectionReasonMessage());
    }

    public function testTransactionDetailsAliasFunction($transactionId = null)
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCompletedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        $response = $gateway->transactionDetails([
            'transactionId' => 'myTransactionId',
        ])->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRejected());

        $this->assertTrue($response->getTransaction()->isSuccessful());
        $this->assertFalse($response->getTransaction()->isPending());
        $this->assertFalse($response->getTransaction()->isCancelled());
        $this->assertFalse($response->getTransaction()->isRejected());

        $this->assertNull($response->getTransaction()->getRejectionReasonMessage());
    }

    /**
     * @expectedException Clapp\OtpHu\BadResponseException
     */
    public function testInvalidTransactionDetailsResponse()
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, 'foobar'));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());

        $response = $gateway->transactionDetails([
            'transactionId' => 'myTransactionId',
        ])->send();
    }
}
