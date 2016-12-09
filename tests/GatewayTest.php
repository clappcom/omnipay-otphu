<?php

use Clapp\OtpHu\Gateway as OtpHuGateway;
use Omnipay\Common\AbstractGateway;
use Clapp\OtpHu\TransactionIdFactoryUsingPaymentGateway;
use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;
use Illuminate\Validaton\ValidationException;
use Omnipay\Common\Exception\InvalidRequestException;
use Clapp\OtpHu\Request\GenerateTransactionIdRequest;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Guzzle\Http\Client as HttpClient;
use Clapp\OtpHu\Contract\TransactionIdFactoryContract;

class GatewayTest extends TestCase{
    public function testGatewayCreation(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);

        $this->assertInstanceOf(OtpHuGateway::class, $gateway);
        $this->assertInstanceOf(AbstractGateway::class, $gateway);

        $this->assertEquals("otphu", $gateway->getName());
    }

    public function testTestMode(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);

        $shopId = $this->faker->randomNumber;

        $gateway->setShopId($shopId);
        $this->assertEquals($gateway->getShopId($shopId), $shopId);

        $gateway->setTestMode(true);
        $this->assertEquals($gateway->getShopId($shopId), "#".$shopId);

        $gateway->setTestMode(false);
        $this->assertEquals($gateway->getShopId($shopId), $shopId);
    }

    public function testPrivateKeyGetter(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);
        $privateKey = $this->getDummyRsaPrivateKey();
        $gateway->setPrivateKey($privateKey);

        $this->assertEquals($privateKey, $gateway->getPrivateKey());
    }
    public function testReturnUrlGetter(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);
        $returnUrl = "https://www.example.com/processing-your-payment";
        $gateway->setReturnUrl($returnUrl);

        $this->assertEquals($returnUrl, $gateway->getReturnUrl());
    }

    public function testMissingTransactionIdFactory(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);
        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setReturnUrl("https://www.example.com/processing-your-payment");

        try {
            $gateway->purchase([]);
        }catch(InvalidArgumentException $e){
            $this->setLastException($e);
        }
        $this->assertLastException(InvalidArgumentException::class);
    }

    public function testTransactionIdWithoutFactory(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);

        try{
            $gateway->purchase([
                'transactionId' => $this->faker->creditCardNumber,
            ]);
        }catch(InvalidRequestException $e){
            $this->setLastException($e);
        }
        $this->assertLastException(InvalidRequestException::class);
    }
    public function testTransactionIdWithoutFactoryOtherSyntax(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);

        try{
            $gateway->purchase([
                'transaction_id' => $this->faker->creditCardNumber,
            ]);
        }catch(InvalidRequestException $e){
            $this->setLastException($e);
        }
        $this->assertLastException(InvalidRequestException::class);
    }

    public function testTransactionIdWithoutFactoryOnGateway(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);

        $gateway->setTransactionId($this->faker->creditCardNumber);

        try{
            $gateway->purchase([]);
        }catch(InvalidRequestException $e){
            $this->setLastException($e);
        }
        $this->assertLastException(InvalidRequestException::class);
    }

    public function testTransactionIdWithFactory(){
        $gateway = Omnipay::create("\\".OtpHuGateway::class);
        $gateway->setShopId($this->faker->randomNumber);
        $gateway->setPrivateKey($this->getDummyRsaPrivateKey());
        $gateway->setReturnUrl("https://www.example.com/processing-your-payment");

        $mock = $this->getMockBuilder(TransactionIdFactoryContract::class)
            ->setMethods([
                'generateTransactionId',
            ])
            ->getMock();

        $mockResponse = $this->getMockBuilder(GenerateTransactionIdRequest::class)
            ->setConstructorArgs([new HttpClient(), new HttpRequest()])
            ->getMock();

        $generatedTransactionId = $this->faker->creditCardNumber;

        $mock->expects($this->once())
            ->method('generateTransactionId')
            ->will($this->returnValue($mockResponse));

        $gateway->setTransactionIdFactory($mock);
        try{
            $gateway->purchase([]);
        }catch(InvalidRequestException $e){
            $this->setLastException($e);
        }
        $this->assertLastException(InvalidRequestException::class);

        $this->assertTrue($gateway->getTransactionIdFactory() instanceof TransactionIdFactoryContract);
    }
}
