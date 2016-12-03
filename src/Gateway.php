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
         * generáltassunk az OTP-vel transactionId-t, ha nem lenne nekünk
         */
        if (empty($transactionId)){
            if (empty($this->transactionIdFactory)){
                throw new InvalidArgumentException('missing factory for auto generating transaction_id');
            }
            $response = $this->transactionIdFactory->generateTransactionId(array_merge($options, $this->getParameters()));
            $transactionId = $response->getTransactionId();
        }
        $this->setTransactionId($transactionId);

        $request->validate(
            'shop_id',
            'private_key',
            'endpoint',
            'transactionId',
            'returnUrl'
        );

        return $request;
    }

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

    public function transactionDetails($options){
        return $this->completePurchase($options);
    }
    /**
     * override, hogy ha van shop_id-nk, akkor az menjen át a shop id getter függvényén
     * ez azért fontos, mert az OTP-nél a testmode abban nyilvánul meg, hogy a shop_id egy "#" karakterrel kezdődik
     */
    public function getParameters(){
        $params = parent::getParameters();
        if (isset($params['shop_id'])) $params['shop_id'] = $this->getShopId();
        return $params;
    }

    public function setTransactionIdFactory(TransactionIdFactory $factory){
        $this->transactionIdFactory = $factory;
        return $this;
    }
    public function getTransactionIdFactory(){
        return $this->transactionIdFactory;
    }
    /**
     * Get the request return URL.
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    /**
     * Sets the request return URL.
     *
     * @param string $value
     * @return AbstractRequest Provides a fluent interface
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('returnUrl', $value);
    }

    public function setShopId($value){
        return $this->setParameter("shop_id", $value);
    }
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
    public function setPrivateKey($value){
        return $this->setParameter("private_key", $value);
    }
    public function getPrivateKey(){
        return $this->getParameter("private_key");
    }
    public function setTransactionId($value){
        return $this->setParameter("transactionId", $value);
    }
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

    public function getCustomerReturnUrl(){
        return $this->getParameter("customer_return_url");
    }
    public function setCustomerReturnUrl($value){
        return $this->setParameter("customer_return_url", $value);
    }
}
