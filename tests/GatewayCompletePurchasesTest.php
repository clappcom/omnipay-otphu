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

        $response = $gateway->completePurchase([
            'transactionId' => 'myTransactionId',
        ])->send();
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
    public function testCompletePurchaseCancelled(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCancelledResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

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
    public function testCompletePurchaseRejected(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsRejectedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

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
    public function testCompletePurchaseCompleted($transactionId = null){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCompletedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

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
    public function testTransactionDetailsAliasFunction($transactionId = null){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsCompletedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

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
}
