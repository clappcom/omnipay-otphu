<?php

use Clapp\OtpHu\TransactionIdFactory;

use Guzzle\Http\Client as HttpClient;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

class TransactionIdFactoryTest extends TestCase{
    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testTransactionIdGenerationMissingParameters(){

        $transactionIdFactory = new TransactionIdFactory();

        $response = $transactionIdFactory->generateTransactionId([

        ]);
    }
    /**
     * @expectedException Clapp\OtpHu\BadResponseException
     */
    public function testBadResponse(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, "invalidsoapresponse"));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $transactionIdFactory = new TransactionIdFactory($client);

        $response = $transactionIdFactory->generateTransactionId([
            'shop_id' => $this->faker->randomNumber,
            'private_key' => $this->getDummyRsaPrivateKey(),
        ]);
    }

    public function testSuccessfulTransactionIdGeneration(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$successfulTransactionIdGenerationResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $transactionIdFactory = new TransactionIdFactory($client);

        $response = $transactionIdFactory->generateTransactionId([
            'shop_id' => $this->faker->randomNumber,
            'private_key' => $this->getDummyRsaPrivateKey(),
        ]);

        $this->assertTrue($response->isSuccessful());
        $this->assertNotEmpty($response->getTransactionId());
    }
}
