<?php
/**
 * Contains Clapp\OtpHu\Gateway
 */
namespace Clapp\OtpHu;

use Omnipay\Common\AbstractGateway;
use Clapp\OtpHu\Request\GenerateTransactionIdRequest;
use Clapp\OtpHu\Request\PaymentRequest;
use Clapp\OtpHu\Request\TransactionDetailsRequest;
use Clapp\OtpHu\Response\GenerateTransactionIdResponse;
use SimpleXMLElement;
use InvalidArgumentException;
use Guzzle\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Clapp\OtpHu\Contract\TransactionIdFactoryContract;


/**
 * Allows your users to use otpbank.hu's online payment gateway.
 */
class Gateway extends AbstractGateway{
    /**
     * the API endpoint used for communicating with the gateway
     * @var string
     */
    protected $endpoint = "https://www.otpbankdirekt.hu/mwaccesspublic/mwaccess";
    /**
     * the factory used to auto generate transactions IDs
     * @var TransactionIdFactory|null
     */
    protected $transactionIdFactory = null;
    /**
     * Create a new gateway instance
     *
     * @param ClientInterface $httpClient  A Guzzle client to make API calls with
     * @param HttpRequest     $httpRequest A Symfony HTTP request object
     */
    public function __construct(ClientInterface $httpClient = null, HttpRequest $httpRequest = null){
        parent::__construct($httpClient, $httpRequest);

        $this->setParameter('endpoint', $this->endpoint);
    }
    /**
     * @internal
     * @return string
     */
    public function getName(){
        return "otphu";
    }
    /**
     * Start a new transaction on the gateway
     *
     * Possible fields for $options are:
     *
     * - `currency` string 3 letter currency code, e.g. `HUF`
     * - `amount` int|float|string amount of currency to charge (in any format accepted by `number_format()`)
     * - `language` string 2 letter language code, default: `hu`
     *
     * Example:
     *
     * ```php
     * $gateway->purchase([
     *     'amount' => 100,
     *     'currency' => 'HUF'
     * ]);
     * ```
     *
     * @param  array $options payment options
     * @return PaymentRequest the payment request that is ready to be sent to the gateway
     */
    public function purchase($options){
        $transactionId = $this->getTransactionId($options);
         if (!empty($transactionId)){
            $this->setTransactionId($transactionId);
        }

        $request = $this->createRequest("\\".PaymentRequest::class, array_merge($options, $this->getParameters()));

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint',
            'returnUrl'
        );
        /**
         * if we did not provide a transactionID then generate one using the gateway
         */
        if (empty($transactionId)){
            if (empty($this->transactionIdFactory)){
                $this->transactionIdFactory = $this->getDefaultTransactionIdFactory();
            }
            $transactionId = $this->transactionIdFactory->generateTransactionId(array_merge($options, $this->getParameters()));
        }
        $this->setTransactionId($transactionId);
        $request->setTransactionId($transactionId);

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint',
            'transactionId',
            'returnUrl'
        );

        return $request;
    }
    /**
     * create the default TransactionIdFactory
     *
     * @return TransactionIdFactoryContract default TransactionIdFactory
     */
    protected function getDefaultTransactionIdFactory(){
        return new TransactionIdFactoryUsingPaymentGateway($this->httpClient);
    }
    /**
     * Get the details of a transaction from the gateway, including whether or not it's already completed.
     *
     * Possible fields for $options are:
     *
     * - `transaction_id` string the transaction ID of the transaction
     *
     * Example:
     *
     * ```php
     * $request = $gateway->completePurchase([
     *     'transaction_id' => 'ATransactionIdFromOurDatabase',
     * ]);
     * $response = $request->send();
     * ```
     * @param  array $options payment options
     * @return TransactionDetailsRequest the request that is ready to be sent to the gateway
     */
    public function completePurchase($options){
        $transactionId = $this->getTransactionId($options);
        if (!empty($transactionId)){
            $this->setTransactionId($transactionId);
        }

        $request = $this->createRequest("\\".TransactionDetailsRequest::class, $this->getParameters());

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint',
            'transactionId'
        );
        return $request;
    }
    /**
     * Alias for completePurchase()
     *
     * completePurchase() is used for compatibility with Omnipay, but this method
     * has a better name to describe what is actually happening
     *
     * @param  array $options payment options (see completePurchase())
     */
    public function transactionDetails($options){
        return $this->completePurchase($options);
    }
    /**
     * override to allow the `shop_id` parameter to always use `getShopId()`
     *
     * this is required because our gateway differentiates test mode from production mode by putting a "#" character in front of the `shop_id`
     *
     * @internal
     */
    public function getParameters(){
        $params = parent::getParameters();
        if (isset($params['shop_id'])) $params['shop_id'] = $this->getShopId();
        return $params;
    }
    /**
     * set the transcationFactory that will be used to generate transaction IDs if none is provided for purchase()
     * @param TransactionIdFactory $factory the new TransactionIdFactory instance
     */
    public function setTransactionIdFactory(TransactionIdFactoryContract $factory){
        $this->transactionIdFactory = $factory;
        return $this;
    }
    /**
     * @internal
     */
    public function getTransactionIdFactory(){
        return $this->transactionIdFactory;
    }
    /**
     * Get the request return URL.
     *
     * @internal
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    /**
     * Sets the URL where the user will be redirected by the gateway after completing or cancelling the payment on the gateway's website.
     *
     * @param string $value absolute url where the user can be redirected to
     * @return AbstractRequest Provides a fluent interface
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('returnUrl', $value);
    }
    /**
     * Sets the shop id
     * @param string $value shop id - the "#" prefix will be trimmed as it is controlled by setTestMode()
     */
    public function setShopId($value){
        return $this->setParameter("shop_id", $value);
    }
    /**
     * @internal
     */
    public function getShopId(){
        $value = $this->getParameter("shop_id");
        /**
         * testmode-ban van előtte "#" karakter, production módban nincsen
         */
        if (!empty($value)){
            $value = ltrim($value, "#");
        }
        if ($this->getTestMode()){
            $value = "#".$value;
        }
        return $value;
    }
    /**
     * Sets the private key (PEM formatted) used to sign the requests
     *
     * Example:
     *
     * ```php
     * $gateway->setPrivateKey(file_get_contents('path/to/#02299991.privKey.pem'));
     * ```
     * @param string $value private key's value in any format accepted by openssl_get_privatekey()
     */
    public function setPrivateKey($value){
        return $this->setParameter("private_key", $value);
    }
    /**
     * @internal
     */
    public function getPrivateKey(){
        return $this->getParameter("private_key");
    }
    /**
     * Sets the transaction ID to use for the next purchase() request
     *
     * The transaction ID will be used to refer to the transaction later with transactionDetails() or completePurchase()
     * It should be unique for each purchase.
     * If omitted, it will be auto generated by the gateway
     *
     * @param string $value transaction id
     */
    public function setTransactionId($value){
        return $this->setParameter("transactionId", $value);
    }
    /**
     * Get the transaction ID we are using
     *
     * @param array $options gateway options that we also check for a transactionId field
     * @return string transactionId that we either provided or was auto generated by the gateway
     */
    public function getTransactionId($options = []){
        $transactionId = $this->getParameter("transactionId");
        if (!empty($options)){
            if (!empty($options['transactionId'])){
                $transactionId = $options['transactionId'];
            }
            if (!empty($options['transaction_id'])){
                $transactionId = $options['transaction_id'];
            }
        }
        return $transactionId;
    }
}
