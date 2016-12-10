<?php

use Clapp\OtpHu\Gateway as OtpHuGateway;
use Omnipay\Omnipay;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

class UsageExampleTest extends TestCase{
    public function testUsageExamplePurchase(){
        /**
         * mock
         */
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$successfulTransactionIdGenerationResponseBody));
        $plugin->addResponse(new Response(200, null, self::$successfulPurchaseResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);
        /**
         * /mock
         */
        /**
         * usage example:
         */
        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId("0199123456");
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setReturnUrl("https://www.example.com/processing-your-payment");
        $gateway->setTestMode(false);

        try {
            $request = $gateway->purchase([
                'amount' => '100.00',
                'currency' => 'HUF'
            ]);
            $response = $request->send();

            if ($response->isRedirect()){
                $redirectionUrl = $response->getRedirectUrl();
                $transactionId = $response->getTransactionId();
                /**
                 * redirect the user to $redurectionUrl and store $transactionId for later use
                 */
            }
        }catch(Exception $e){
            /**
             * something went wrong
             */
            throw $e;
        }
        /**
         * /usage example
         */
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($redirectionUrl);
        $this->assertNotEmpty($transactionId);
    }
    public function testUsageExampleCompletePurchase(){
        /**
         * mock
         */
        $plugin = new MockPlugin();
        $plugin->addResponse(new Response(200, null, self::$transactionDetailsRejectedResponseBody));
        $client = new HttpClient();
        $client->addSubscriber($plugin);
        /**
         * /mock
         */
        /**
         * usage example:
         */
        $gateway = Omnipay::create("\\".OtpHuGateway::class, $client);

        $gateway->setShopId("0199123456");
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setTestMode(false);

        try {
            $response = $gateway->completePurchase([
                'transactionId' => 'myGeneratedTransactionId',
            ])->send();

            if ($response->isSuccessful()){
                /**
                 * the user's payment was successful
                 */
            }
            if ($response->isPending()){
                /**
                 * the user's payment is still pending, we should try $gateway->completePurchase() later
                 */
            }
            if ($response->isCancelled()){
                /**
                 * the user cancelled the payment
                 */
            }
            if ($response->isRejected()){
                /**
                 * the payment gateway rejected the user's payment
                 */
                $reasonCode = $response->getTransaction()->getRejectionReasonCode(); //OTP's error code string
                $reasonMessage = $response->getTransaction()->getRejectionReasonMessage(); //human readable string
            }
        }catch(Exception $e){
            /**
             * something went wrong
             */
            throw $e;
        }
        /**
         * /usage example
         */
        $this->assertTrue($response->isRejected());
    }
}
