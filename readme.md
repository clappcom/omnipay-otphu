clapp/omnipay-otphu [![Build Status](https://travis-ci.org/dsge/omnipay-otphu.svg?branch=master)](https://travis-ci.org/dsge/omnipay-otphu) [![Coverage Status](https://coveralls.io/repos/github/dsge/omnipay-otphu/badge.svg?branch=master)](https://coveralls.io/github/dsge/omnipay-otphu?branch=master)
===

Experimental package, not recommended for production.

Install
---
`composer.json`:
```javascript
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/dsge/omnipay-otphu.git"
    }
],
```

```
composer require clapp/omnipay-otphu:dev-master
```

Usage
---

```php
<?php
include 'vendor/autoload.php';

$gateway = Omnipay\Omnipay::create("\\".Clapp\OtpHu\Gateway::class);

$gateway->setShopId("0199123456");
$gateway->setPrivateKey(file_get_contents('myShopKey.privKey.pem'));
$gateway->setTransactionId('myGeneratedTransactionId');
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
}
```

```php
// after the user is redirected back to our site by OTP
<?php
include 'vendor/autoload.php';

$gateway = Omnipay\Omnipay::create("\\".Clapp\OtpHu\Gateway::class);

$gateway->setShopId("0199123456");
$gateway->setPrivateKey(file_get_contents('myShopKey.privKey.pem'));
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
}
```
