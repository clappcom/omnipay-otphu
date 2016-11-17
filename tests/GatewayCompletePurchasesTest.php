<?php

use Clapp\OtpHu\Gateway as OtpHuGateway;
use Omnipay\Omnipay;
use Clapp\OtpHu\BadResponseException;

use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Client as HttpClient;

use Clapp\OtpHu\TransactionFailedException;

class GatewayCompletePurchasesTest extends TestCase{

    public function testCompletePurchasePending(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsPendingResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-','',$this->faker->uuid));

        $response = $gateway->completePurchase([
            'transactionId' => $gateway->getTransactionId(),
        ])->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRejected());
    }
    public function testCompletePurchaseCancelled(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCancelledResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-','',$this->faker->uuid));

        $response = $gateway->completePurchase([
            'transactionId' => $gateway->getTransactionId(),
        ])->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isCancelled());
        $this->assertFalse($response->isRejected());
    }
    public function testCompletePurchaseRejected(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsRejectedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-','',$this->faker->uuid));


        $response = $gateway->completePurchase([
            'transactionId' => $gateway->getTransactionId(),
        ])->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertTrue($response->isRejected());
    }
    public function testCompletePurchaseCompleted($transactionId = null){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCompletedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTransactionId(str_replace('-','',$this->faker->uuid));

        $response = $gateway->completePurchase([
            'transactionId' => $gateway->getTransactionId(),
        ])->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isRejected());
    }
}
