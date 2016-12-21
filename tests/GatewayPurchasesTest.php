<?php

use Clapp\OtpHu\Gateway as OtpHuGateway;
use Omnipay\Omnipay;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Client as HttpClient;
use Clapp\OtpHu\Response\UnknownShopIdResponse;
use Clapp\OtpHu\ServerErrorResponseException;

class GatewayPurchasesTest extends TestCase
{
    public function testUnknownShopIdPurchase()
    {
        /**
         * invalid response for "HIANYZIKSHOPPUBLIKUSKULCS".
         */
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$unknownShopIdResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-', '', $this->faker->uuid));
        $gateway->setReturnUrl('https://www.example.com/processing-your-payment');
        $gateway->setTestMode(false);

        try {
            $request = $gateway->purchase([
                'amount' => '100.00',
                'currency' => 'HUF',
            ]);
            $response = $request->send();
        } catch (UnknownShopIdResponse $e) {
            $this->setLastException($e);
        }
        $this->assertLastException(UnknownShopIdResponse::class);
        //$this->assertContainsOnly($request, $plugin->getReceivedRequests());
    }

    public function testUnknownServerError()
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(500));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-', '', $this->faker->uuid));
        $gateway->setReturnUrl('https://www.example.com/processing-your-payment');
        $gateway->setTestMode(false);

        try {
            $request = $gateway->purchase([
                'amount' => '100.00',
                'currency' => 'HUF',
            ]);
            $response = $request->send();
        } catch (ServerErrorResponseException $e) {
            $this->setLastException($e);
        }
        $this->assertLastException(ServerErrorResponseException::class);
    }

    /**
     * @expectedException \Clapp\OtpHu\BadResponseException
     */
    public function testBadResponseInvalidResponseBody()
    {
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, 'invalidsoapresponse'));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-', '', $this->faker->uuid));
        $gateway->setReturnUrl('https://www.example.com/processing-your-payment');
        $gateway->setTestMode(false);

        $request = $gateway->purchase([
            'amount' => '100.00',
            'currency' => 'HUF',
        ]);
        $response = $request->send();
    }

    /**
     * @expectedException \Clapp\OtpHu\BadResponseException
     */
    public function testBadResponseInvalidResponseMessage()
    {
        /**
         * response for "loremipsum" (invalid code).
         */
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$invalidStatusCodeSuccessfulPurchaseResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-', '', $this->faker->uuid));
        $gateway->setReturnUrl('https://www.example.com/processing-your-payment');
        $gateway->setTestMode(false);

        $request = $gateway->purchase([
            'amount' => '100.00',
            'currency' => 'HUF',
        ]);
        $response = $request->send();
    }

    public function testSuccessfulPurchase()
    {
        /**
         * response for "SIKERESWEBSHOPFIZETESINDITAS".
         */
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$successfulPurchaseResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create('\\'.OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-', '', $this->faker->uuid));
        $gateway->setReturnUrl('https://www.example.com/processing-your-payment');
        $gateway->setTestMode(false);

        $request = $gateway->purchase([
            'amount' => '100.00',
            'currency' => 'HUF',
        ]);
        $response = $request->send();

        $this->assertEquals($gateway->getTransactionId(), $response->getTransactionId());
        /*
         * ez mindig false, mert a tranzakció nem fejeződött még be, nem terhelődött az összeg
         */
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
        $this->assertEquals('GET', $response->getRedirectMethod());
        $this->assertEquals([], $response->getRedirectData());

        return $response;
    }
}
