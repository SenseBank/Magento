<?php
namespace SENSEPAYMENT\Sensebank\Controller\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
require_once(__DIR__ . '../../../Model/Config/include.php');
class Execute extends Action implements CsrfAwareActionInterface
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;
    protected $_encryptor;
    protected $_urlBuilder;
    protected $_customLogger;
    protected $_responseFactory;
    protected $orderRepository;
    protected $orderManagement;
    protected $module_version_str = "1.5.7";
    protected $method_form;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \SENSEPAYMENT\Sensebank\Block\Widget\Redirect $method_form,
        \SENSEPAYMENT\Sensebank\Logger\Logger $customLogger
    )
    {
        parent::__construct($context);
        $this->_encryptor = $encryptor;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_responseFactory = $responseFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->_customLogger = $customLogger;
        $this->method_form = $method_form;
    }
    /**
     * Load the page defined
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /* @var $paymentMethod \Magento\Authorizenet\Model\DirectPost */
        $paymentMethod = $this->_objectManager->create('SENSEPAYMENT\Sensebank\Model\Sensebank');
        $post_data = $this->method_form->getPostData();
        if (defined('SENSEPAYMENT_ENABLE_CALLBACK') && SENSEPAYMENT_ENABLE_CALLBACK == true) {
            $loginAPI = $paymentMethod->getConfigData("MERCHANT_LOGIN");
            $userPassword = $this->_encryptor->decrypt($paymentMethod->getConfigData("MERCHANT_PASSWORD"));
            $returnUrl = $this->_urlBuilder->getUrl('sensebank/url/success');
            $gate_url = str_replace("payment/rest", "mportal/mvc/public/merchant/update", $paymentMethod->getGateUrl(false));
            $gate_url .= substr($loginAPI, 0, -4); // we guess username = login w/o "-api"
            $callback_addresses_string = $this->_urlBuilder->getUrl('sensebank/url/callback');
            $res = $this->_updateGatewayCallback($loginAPI, $userPassword, $gate_url, $callback_addresses_string);
            $this->_customLogger->info("[CB REQUEST] " . $loginAPI . "|" . $userPassword . "|" . $gate_url . "|" . $callback_addresses_string);
            $this->_customLogger->info("[CB RESPONSE] " . $res);
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();
        $args['orderNumber'] = $post_data['order_id'] . "#" . time();
        $args['description'] = __("Payment for order #") . $post_data['order_id'];
        $args['userName'] = $paymentMethod->getConfigData("MERCHANT_LOGIN");
        $args['password'] = $this->_encryptor->decrypt($paymentMethod->getConfigData("MERCHANT_PASSWORD"));
        $args['returnUrl'] = $this->_urlBuilder->getUrl('sensebank/url/success');
        $args['amount'] = $post_data['amount']; //already comes from other place =) * 100;
        $jsonParams_array = array(
            'CMS:' => 'Magento ' . $version,
            'Module-Version: ' => 'Sensebank ' . $this->module_version_str,
        );
        if ( !empty($paymentMethod->getConfigData("backToShopURL"))
        ) {
            $jsonParams_array['backToShopUrl'] = $paymentMethod->getConfigData("backToShopURL");
        }
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($post_data['order_id']);
        $customerId = $order->getCustomerId();
        if (!empty($customerId)) {
            $customerEmail = $order->getCustomerEmail();
            $baseUrl = $this->_urlBuilder->getUrl();
            $client_email = !empty($customerEmail) ? $customerEmail : "";
            $args['clientId'] = md5($customerId  .  $client_email  . $baseUrl);
        }
        if ($paymentMethod->getConfigData('send_order')) {
            $args['taxSystem'] = $paymentMethod->getConfigData('tax_system');
            $items_array = array();
            $cn = 1;
            foreach ($order->getAllVisibleItems() as $visible_item) {
                $item_data = $visible_item->getData();
                $item_price = ceil($item_data['price'] * 100);
                $item_rate = (int)$item_data['tax_percent'];
                $item_tax_type = 0;
                switch ($item_rate) {
                    case 20:
                        $item_tax_type = 6;
                        break;
                    case 18:
                        $item_tax_type = 3;
                        break;
                    case 10:
                        $item_tax_type = 2;
                        break;
                    case 0:
                        $item_tax_type = 1;
                        break;
                }
                $item['positionId'] = $cn;
                $item['name'] = $item_data['name'];
                $item['quantity'] = array(
                    'value' => round($item_data['qty_ordered'], 2),
                    'measure' => $paymentMethod->getConfigData('FFDVersion') == 'v1_05' ? SENSEPAYMENT_MEASUREMENT_NAME : SENSEPAYMENT_MEASUREMENT_CODE
                );
                $item['itemAmount'] = $item_price * $item_data['qty_ordered'];
                $item['itemCode'] = $item_data['product_id'];
                $item['tax'] = array(
                    'taxType' => $item_tax_type
                );
                $item['itemPrice'] = $item_price;
                $cn++;
                $attributes = array();
                $attributes[] = array(
                    "name" => "paymentMethod",
                    "value" => $paymentMethod->getConfigData('ffd_paymentMethodType')
                );
                $attributes[] = array(
                    "name" => "paymentObject",
                    "value" => $paymentMethod->getConfigData('ffd_paymentObjectType')
                );
                $item['itemAttributes']['attributes'] = $attributes;
                $items_array[] = $item;
            }
            if ($order->getShippingAmount() > 0) {
                $itemShipment['positionId'] = $cn;
                $itemShipment['name'] = __("Delivery");
                $itemShipment['quantity'] = array(
                    'value' => 1,
                    'measure' => $paymentMethod->getConfigData('FFDVersion') == 'v1_05' ? SENSEPAYMENT_MEASUREMENT_NAME : SENSEPAYMENT_MEASUREMENT_CODE
                );
                $itemShipment['itemAmount'] = $itemShipment['itemPrice'] = ceil($order->getShippingAmount() * 100);
                $itemShipment['itemCode'] = __("Delivery");
                $itemShipment['tax'] = array(
                    'taxType' => 0
                );
                $attributes = array();
                $attributes[] = array(
                    "name" => "paymentMethod",
                    "value" => $paymentMethod->getConfigData('ffd_paymentMethodType')
                );
                $attributes[] = array(
                    "name" => "paymentObject",
                    "value" => 4
                );
                $itemShipment['itemAttributes']['attributes'] = $attributes;
                $items_array[] = $itemShipment;
            }
            $order_bundle = array(
                'orderCreationDate' => time(), //$order->getCreatedAt(), //todo maybe =)
                'customerDetails' => array(
                    'email' => $order->getCustomerEmail(),
                    'phone' => preg_replace('/\D+/', '', $order->getShippingAddress()->getTelephone())
                ),
                'cartItems' => array('items' => $items_array)
            );
            $args['orderBundle'] = json_encode($order_bundle);
        } // send_order IF
        $args['jsonParams'] = json_encode($jsonParams_array);
        $headers = array(
            'CMS: Magento ' . $version,
            'Module-Version: ' .  $this->module_version_str
        );
        $response = $this->_sendGatewayData(http_build_query($args, '', '&'), $paymentMethod->getGateUrl(), $headers);
        $this->_customLogger->info("[REQUEST] " . json_encode($args));
        $this->_customLogger->info("[RESPONSE] " . $response);
        $response = json_decode($response, true);
        if (!empty($response['orderId']) && !empty($response['formUrl'])) {
            $CustomRedirectionUrl = $this->_urlBuilder->getUrl($response['formUrl']);
            $this->_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
            exit();
        } else {
            $order = $this->checkoutSession->getLastRealOrder();
            if ($order && $order->getId()) {
                $order->addStatusHistoryComment('Unable to process your payment with this payment method' . (!empty($response['errorMessage']) ? ': ' . $response['errorMessage'] : ''));
                $order->save();
                $this->orderManagement->cancel($order->getId());
            }
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addError($response['errorMessage']);
            $this->_redirect('checkout/cart');
        }
    }
    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }
    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    public function _updateGatewayCallback($login, $password, $action_address, $callback_addresses_string)
    {
        $headers = array(
            'Content-Type:application/json',
            'Authorization: Basic ' . base64_encode($login . ":" . $password)
        );
        $data['callbacks_enabled'] = true;
        $data['callback_type'] = "STATIC";
        $data['callback_addresses'] = $callback_addresses_string;
        $data['callback_http_method'] = "GET";
        $data['callback_operations'] = "deposited,approved,declinedByTimeout";
        $response = $this->_sendGatewayData(json_encode($data), $action_address, $headers);
        return $response;
    }
    public function _sendGatewayData($data, $action_address, $headers = array())
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $action_address,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
