<?php

use Clapp\OtpHu\TransactionIdFactoryUsingPaymentGateway;

use Guzzle\Http\Client as HttpClient;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

class TransactionIdFactoryUsingPaymentGatewayTest extends TestCase{
    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testTransactionIdGenerationMissingParameters(){

        $transactionIdFactory = new TransactionIdFactoryUsingPaymentGateway();

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

        $transactionIdFactory = new TransactionIdFactoryUsingPaymentGateway($client);

        $response = $transactionIdFactory->generateTransactionId([
            'shop_id' => $this->faker->randomNumber,
            'private_key' => $this->getDummyRsaPrivateKey(),
        ]);
    }
    /**
     * @expectedException Clapp\OtpHu\BadResponseException
     */
    public function testBadResponseBecauseMissingTransactionId(){
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, $this->generateResponseBody([
            'resultset' => [
                'record' => [
                    'posid' => '#02299991',
                    'transactionid' => '',
                    'timestamp' => '2016.11.16 23.19.52 695',
                ],
            ],
            'messagelist' => [
                'message' => 'SIKER'
            ],
            "infolist" => []
        ])));
        $client = new HttpClient();
        $client->addSubscriber($plugin);

        $transactionIdFactory = new TransactionIdFactoryUsingPaymentGateway($client);

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

        $transactionIdFactory = new TransactionIdFactoryUsingPaymentGateway($client);

        $response = $transactionIdFactory->generateTransactionId([
            'shop_id' => $this->faker->randomNumber,
            'private_key' => $this->getDummyRsaPrivateKey(),
        ]);

        $this->assertTrue($transactionIdFactory->lastResponse->isSuccessful());

        $this->assertNotEmpty($response);
        $this->assertTrue(is_string($response));
    }
}
