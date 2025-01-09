<?php
namespace SENSEPAYMENT\Sensebank\Model;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order;
require_once(__DIR__ . '/Config/include.php');
/**
 * Class Sensebank
 * @package SENSEPAYMENT\Sensebank\Model
 */
class Sensebank extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var \SENSEPAYMENT\Sensebank\Api\OrderRepositoryInterface
     */
    protected $gatewayOrderRepository;
    /**
     * @var \SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory
     */
    protected $gatewayOrderFactory;
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'sensebank';
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;
    /**
     * Sidebar payment info block
     *
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';
    protected $_encryptor;
    protected $orderFactory;
    protected $urlBuilder;
    protected $_transactionBuilder;
    protected $_logger;
    protected $_customLogger;
    private $allowCallbacks;
    public $gateway_orderId;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $builderInterface,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \SENSEPAYMENT\Sensebank\Api\OrderRepositoryInterface $gatewayOrderRepository,
        \SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory $gatewayOrderFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array())
    {
        $this->orderFactory = $orderFactory;
        $this->_transactionBuilder = $builderInterface;
        $this->_encryptor = $encryptor;
        $this->gatewayOrderRepository = $gatewayOrderRepository;
        $this->gatewayOrderFactory    = $gatewayOrderFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data);
    }
    /**
     *
     * @param $orderId
     * @return Order
     */
    protected function getOrder($orderId)
    {
        return $this->orderFactory->create()->loadByIncrementId($orderId);
    }
    /**
     *
     * @param $orderId
     * @return float
     */
    public function getAmount($orderId)
    {
        return $this->getOrder($orderId)->getGrandTotal();
    }
    /**
     *
     * @param $orderId
     * @return int|null
     */
    public function getCustomerId($orderId)
    {
        return $this->getOrder($orderId)->getCustomerId();
    }
    /**
     * Get currency code by orderId
     *
     * @param $orderId
     * @return null|string
     */
    public function getCurrencyCode($orderId)
    {
        return $this->getOrder($orderId)->getBaseCurrencyCode();
    }
    /**
     * Set order state and status
     * (this method calls by pressing button "Place Order")
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);
    }
    /**
     * Check whether payment method can be used with selected shipping method
     *
     * @param string $shippingMethod
     * @return bool
     */
    protected function isCarrierAllowed($shippingMethod)
    {
        if (isset($shippingMethod) ) {
            $allowedArray = explode(',', $this->getConfigData('allowed_carrier'));
            foreach ($allowedArray as $element) {
                $trimmedElement = trim($element);
                if (strpos($shippingMethod, $trimmedElement) === 0) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote === null) {
            return false;
        }
        return parent::isAvailable($quote) && ($this->isCarrierAllowed(
                    $quote->getShippingAddress()->getShippingMethod()
                ) || $quote->getIsVirtual());
    }
    /**
     *
     * @return string
     */
    public function getGateUrl($with_action = true)
    {
        $action_adr = SENSEPAYMENT_PROD_URL;
        if (defined('SENSEPAYMENT_PROD_URL_ALT') && defined('SENSEPAYMENT_PROD_URL_ALT_PREFIX')) {
            if (substr($this->getConfigData("MERCHANT_LOGIN"), 0, strlen(SENSEPAYMENT_PROD_URL_ALT_PREFIX)) == SENSEPAYMENT_PROD_URL_ALT_PREFIX) {
                $action_adr = SENSEPAYMENT_PROD_URL_ALT;
            } else {
            }
        }
        if ($this->getConfigData('test_mode')) {
            $action_adr = SENSEPAYMENT_TEST_URL;
        }
        if ($with_action == false) {
            return $action_adr;
        }
        if ($this->getConfigData('two_stage')) {
            $action_adr .= "registerPreAuth.do";
        } else {
            $action_adr .= "register.do";
        }
        return $action_adr;
    }
    public function getStatusUrl()
    {
        $action_adr = SENSEPAYMENT_PROD_URL;
        if (defined('SENSEPAYMENT_PROD_URL_ALT') && defined('SENSEPAYMENT_PROD_URL_ALT_PREFIX')) {
            if (substr($this->getConfigData("MERCHANT_LOGIN"), 0, strlen(SENSEPAYMENT_PROD_URL_ALT_PREFIX)) == SENSEPAYMENT_PROD_URL_ALT_PREFIX) {
                $action_adr = SENSEPAYMENT_PROD_URL_ALT;
            } else {
            }
        }
        if ($this->getConfigData('test_mode')) {
            $action_adr = SENSEPAYMENT_TEST_URL;
        }
        $action_adr .= "getOrderStatusExtended.do";
        return $action_adr;
    }
    /**
     * get data array for payment form
     *
     * @param $orderId
     * @return array
     */
    public function getPostData($orderId)
    {
        $postData = array(
            'order_id' => $orderId,
            'amount' => round(number_format($this->getAmount($orderId), 2, '.', '') * 100),
        );
        return $postData;
    }
    /**
     *
     * @param $response
     * @return bool
     */
    private function checkGatewayResponse($response)
    {
        $settings = array(
            'login' => $this->getConfigData("MERCHANT_LOGIN"),
            'password' => $this->_encryptor->decrypt($this->getConfigData("MERCHANT_PASSWORD"))
        );
        $validated = $this->isPaymentValid($settings, $response);
        if ($validated === true) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * @param $responseData
     * @param bool $callback
     * @return Order
     */
    public function processResponse($responseData, $is_callback = false)
    {
        if ($this->checkGatewayResponse($responseData)) {
            $action_adr = $this->getStatusUrl();
            $orderId = $is_callback == true ? $responseData['mdOrder'] : $responseData['orderId']; //bzz
            $this->gateway_orderId = $orderId;
            $args = array(
                'userName' => $this->getConfigData("MERCHANT_LOGIN"),
                'password' => $this->_encryptor->decrypt($this->getConfigData("MERCHANT_PASSWORD")),
                'orderId' => $orderId
            );
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $action_adr,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($args, '', '&')
            ));
            $response = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($response, true);
            list($orderId,) = explode('#', $response['orderNumber']);
            $order = $this->getOrder($orderId);
            /*if (defined('SENSEPAYMENT_ENABLE_CALLBACK') && SENSEPAYMENT_ENABLE_CALLBACK == true) {
                if ($is_callback == true) {
                    $updateState = true;
                } else {
                    $updateState = false;
                }
            } else {
                $updateState = true;
            }*/
            $updateState = true;
            if ($order && ($this->_processOrder($order, $response, $updateState) === true)) {
                return true;
            } else {
                return false;
            }
        }
    }
    /**
     *
     * @param Order $order
     * @param mixed $response
     * @param bool $updateState
     * @return bool
     */
    protected function _processOrder(Order $order, $response, $updateState = false)
    {
        try {
            if (round($order->getGrandTotal() * 100) != $response["amount"]) {
                return false;
            }
            if ($response["orderStatus"] == '2' || $response["orderStatus"] == '1') {
                if ($updateState) {
                    if (isset($response['paymentAmountInfo'])) {
                        $order->setTotalRefunded($response['paymentAmountInfo']['refundedAmount']);
                    }

                    $invoice = $order->prepareInvoice();
                    $invoice->getOrder()->setIsInProcess(true);
                    $invoice->register()->pay();
                    $invoice->save();

                    $order
                        ->setState($this->getConfigData("order_status"))
                        ->setStatus($order->getConfig()->getStateDefaultStatus($this->getConfigData("order_status")))
                        ->save();
                    // ->addStatusHistoryComment(__('Order paid successfully'), false)
                }
                $this->_saveGatewayData($order, $response);
                return true;
            } else {
                if ($updateState) {
                    $order
                        ->setState(Order::STATE_CANCELED)
                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED))
                        ->save();
                }
                $this->_saveGatewayData($order, $response);
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    public function _saveGatewayData ($order, $response) {
        /* @var \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface $gatewayOrder */
        $gatewayOrder = $this->gatewayOrderFactory->create();
        $orderAmount = $order->getGrandTotal();
        $statusDeposited = (int)$response['orderStatus'];
        $orderAmountDeposited = $orderAmount;
        $gatewayOrder
            ->setIncrementId($order->getIncrementId())
            ->setGatewayOrderReference($this->gateway_orderId)
            ->setOrderAmount($orderAmount)
            ->setOrderAmountDeposited($orderAmountDeposited)
            ->setStatusDeposited($statusDeposited)
            ->setCreatedAt(date('Y-m-d H:i:s'))
            ->setUpdatedAt(date('Y-m-d H:i:s'));
        $this->gatewayOrderRepository->save($gatewayOrder);
    }
    public function isPaymentValid($methodSettings, $response)
    {
        return true;
    }
    public function createTransaction($order = null, $paymentData = array())
    {
        try {
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData['payment_id']);
            $payment->setTransactionId($paymentData['payment_id']);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$paymentData]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );
            $message = __('The authorized amount is %1.', $formatedPrice);
            $trans = $this->_transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['payment_id'])
                ->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$paymentData]
                )
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER);
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
            return $transaction->save()->getTransactionId();
        } catch (\Exception $e) {
        }
    }
}
