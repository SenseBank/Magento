<?php
namespace SENSEPAYMENT\Sensebank\Controller\Adminhtml\Order;
use Magento\Sales\Model\Order;
require_once(__DIR__ . '../../../../Model/Config/include.php');
class ActionOrder extends \Magento\Backend\App\Action
{
    protected $_encryptor;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \SENSEPAYMENT\Sensebank\Helper\Data
     */
    protected $dataHelper;
    /**
     * @var \SENSEPAYMENT\Sensebank\Api\OrderRepositoryInterface
     */
    protected $gatewayOrderRepository;
    /**
     * @var \SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory
     */
    protected $gatewayOrderFactory;
    /**
     * ActionOrder constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \SENSEPAYMENT\Sensebank\Helper\Data $dataHelper
     *
     * @param \SENSEPAYMENT\Sensebank\Api\OrderRepositoryInterface $gatewayOrderRepository
     * @param \SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory $gatewayOrderFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context              $context,
        \Magento\Sales\Api\OrderRepositoryInterface      $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder     $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface       $storeManager,
        \SENSEPAYMENT\Sensebank\Helper\Data                      $dataHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \SENSEPAYMENT\Sensebank\Api\OrderRepositoryInterface     $gatewayOrderRepository,
        \SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory   $gatewayOrderFactory
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->_encryptor = $encryptor;
        $this->gatewayOrderRepository = $gatewayOrderRepository;
        $this->gatewayOrderFactory = $gatewayOrderFactory;
        parent::__construct($context);
    }
    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $action_adr = SENSEPAYMENT_PROD_URL;
        if ($this->dataHelper->getStoreConfigFlag('test_mode') === true) {
            $action_adr = SENSEPAYMENT_TEST_URL;
        }
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $args['userName'] = $this->dataHelper->getStoreConfig('MERCHANT_LOGIN');
        $args['password'] = $this->_encryptor->decrypt($this->dataHelper->getStoreConfig('MERCHANT_PASSWORD'));
        $gatewayAction = $this->getRequest()->getParam('gateway_action');
        $gatewayAmount = 0;
        if (!empty($this->getRequest()->getParam('gateway_amount'))) {
            $gatewayAmount = (float)str_replace(',', '.', $this->getRequest()->getParam('gateway_amount'));
        }
        /* @var \SENSEPAYMENT\Sensebank\Model\Order $gatewayOrder */
        $gatewayOrder = $this->gatewayOrderFactory->create();
        $gatewayOrder->load($this->getRequest()->getParam('gateway_order_id'));
        $order = false;
        if ($gatewayOrder && $gatewayOrder->getIncrementId()) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $gatewayOrder->getIncrementId(), 'eq')->create();
            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            if (!empty($orderList)) {
                $order = reset($orderList);
            }
        }
        if ($order && !empty($gatewayAction)) {
            $args['orderId'] = $gatewayOrder->getGatewayOrderReference();
            $gatewayAmount = round($gatewayAmount, 2);
            $args['amount'] = intval($gatewayAmount * 100);
            if ($gatewayAction == 'payment_status') {
                $gose = $this->_getGatewayOrderStatus($args['orderId']);
                $response = json_decode($gose, true);
                list($orderId,) = explode('#', $response['orderNumber']);
                if (!empty($response['orderNumber'])
                    && isset($response['orderStatus'])
                    && $order->getIncrementId() == $orderId) {
                    if ($response['paymentAmountInfo']['paymentState']) {
                        $order->setTotalRefunded($response['paymentAmountInfo']['refundedAmount'] / 100);
                        $order->setTotalPaid($response['paymentAmountInfo']['approvedAmount'] / 100);
                        $order->save();
                        $response = $this->_view->getLayout()
                            ->createBlock('SENSEPAYMENT\Sensebank\Block\Adminhtml\Sales\Order\View\Tab\Action')
                            ->setSuccessMessage(__('Sensebank Status:') . ' ' . $response['paymentAmountInfo']['paymentState'])
                            ->setIncrementId($gatewayOrder->getIncrementId())
                            ->setTemplate('SENSEPAYMENT_Sensebank::sales/order/view/tab/action.phtml')
                            ->toHtml();
                    } else {
                        $response = array(
                            'error' => true,
                            'message' => $response['errorMessage'],
                        );
                    }
                }
            } elseif (strpos($gatewayAction, 'payment_deposit') !== false && $gatewayOrder->getStatusDeposited() == 1) {
                if ($gatewayAction == 'payment_deposit_partial') {
                    $gatewayAmount = (float)str_replace(',', '.', trim($gatewayAmount));
                } else {
                    $gatewayAmount = $gatewayOrder->getOrderAmount();
                }
                if ($gatewayOrder->getOrderAmount() < $gatewayAmount || $gatewayAmount <= 0) {
                    $response = array(
                        'error'     => true,
                        'message'   => __('The amount must be more than 0.00 and less than or equal to %1', number_format($gatewayOrder->getOrderAmount(), 2)),
                    );
                    return $resultPage->setData($response);
                }
                $gatewayAmount = round($gatewayAmount, 2);
                $args['amount'] = intval($gatewayAmount * 100);
                $response = $this->dataHelper->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'deposit.do', array());
                $response = json_decode($response, true);
                if (!empty($response) && isset($response['errorCode'])) {
                    if (empty($response['errorCode'])) {
                        $gatewayOrder
                            ->setOrderAmountDeposited($gatewayAction == 'payment_deposit_partial' ? $gatewayAmount : $gatewayOrder->getOrderAmount())
                            ->setStatusDeposited(2)
                            ->setStatusReversed(0)
                            ->save();
                        $gatewayOrderStatus = $this->dataHelper->getStoreConfig('order_status');
                        $message = __('%1 was deposited successfully!', number_format(($gatewayAction == 'payment_deposit_partial' ? $gatewayAmount : $gatewayOrder->getOrderAmount()), 2));

                        $order->addStatusHistoryComment('Sensebank Payment - ' . $message);
                        $response_status = $this->_getGatewayOrderStatus($args['orderId']);
                        $response_status = json_decode($response_status, true);
                        $order->setTotalRefunded($response_status['paymentAmountInfo']['refundedAmount'] / 100);
                        $order->setTotalPaid($response_status['paymentAmountInfo']['approvedAmount'] / 100);
                        $order->save();
                        $response = $this->_view->getLayout()
                            ->createBlock('SENSEPAYMENT\Sensebank\Block\Adminhtml\Sales\Order\View\Tab\Action')
                            ->setSuccessMessage(__('The amount was deposited successfully!'))
                            ->setIncrementId($gatewayOrder->getIncrementId())
                            ->setTemplate('SENSEPAYMENT_Sensebank::sales/order/view/tab/action.phtml')
                            ->toHtml();
                    } else {
                        $response = array(
                            'error'     => true,
                            'message'   => $response['errorMessage'],
                        );
                    }
                }
            } elseif (strpos($gatewayAction, 'payment_refund') !== false ) {
                if ($gatewayAction == 'payment_refund_partial') {
                    $gatewayAmount = (float)str_replace(',', '.', trim($gatewayAmount));
                } else {
                    $gatewayAmount = $gatewayOrder->getOrderAmountDeposited();
                    if (!empty($gatewayOrder->getStatusRefunded())) {
                        $gatewayAmount -= $gatewayOrder->getOrderAmountRefunded();
                    }
                }
                if ($gatewayOrder->getOrderAmountDeposited() < $gatewayAmount + $gatewayOrder->getOrderAmountRefunded()) {
                    $response = array(
                        'error'     => true,
                        'message'   => __('The amount must be more than 0.00 and less than or equal to %1', number_format(round($gatewayOrder->getOrderAmountDeposited() - $gatewayOrder->getOrderAmountRefunded(), 2), 2)),
                    );
                    return $resultPage->setData($response);
                }
                $gatewayAmount = round($gatewayAmount, 2);
                $args['amount'] = intval($gatewayAmount * 100);
                $response = $this->dataHelper->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'refund.do', array());
                $response = json_decode($response, true);
                if (!empty($response) && isset($response['errorCode'])) {
                    if (empty($response['errorCode'])) {
                        $gatewayStatus = 0;
                        if ($gatewayOrder->getOrderAmountDeposited() == ($gatewayAmount + $gatewayOrder->getOrderAmountRefunded())) {
                            $gatewayStatus = 1;
                        }
                        $gatewayOrder
                            ->setOrderAmountRefunded(round($gatewayAmount + $gatewayOrder->getOrderAmountRefunded(), 2))
                            ->setStatusRefunded(1)
                            ->setStatusReversed(1)
                            ->setStatus($gatewayStatus)
                            ->save();
                        $gatewayOrderStatus = $this->dataHelper->getStoreConfig('order_status_refunded');
                        $message = __('%1 was refunded successfully!', number_format($gatewayAmount, 2));

                        $order->addStatusHistoryComment('Sensebank Payment - ' . $message, false);
                        $response_status = $this->_getGatewayOrderStatus($args['orderId']);
                        $response_status = json_decode($response_status, true);
                        $order->setTotalRefunded($response_status['paymentAmountInfo']['refundedAmount'] / 100);
                        $order->setTotalPaid($response_status['paymentAmountInfo']['approvedAmount'] / 100);
                        $order->save();

                        // Create offline credit memo refund.
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $creditMemoFacory = $objectManager->create('Magento\Sales\Model\Order\CreditmemoFactory');
                        $creditmemoService = $objectManager->create('Magento\Sales\Model\Service\CreditmemoService');
                        $creditmemo = $creditMemoFacory->createByOrder($order);
                        $creditmemoService->refund($creditmemo);

                        $response = $this->_view->getLayout()
                            ->createBlock('SENSEPAYMENT\Sensebank\Block\Adminhtml\Sales\Order\View\Tab\Action')
                            ->setSuccessMessage(__('The amount was refunded successfully!'))
                            ->setIncrementId($gatewayOrder->getIncrementId())
                            ->setTemplate('SENSEPAYMENT_Sensebank::sales/order/view/tab/action.phtml')
                            ->toHtml();
                    } else {
                        $response = array(
                            'error'     => true,
                            'message'   => $response['errorMessage'],
                        );
                    }
                }
            } elseif ($gatewayAction == 'payment_reverse') {
                $response = $this->dataHelper->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'reverse.do', array());
                $response = json_decode($response, true);
                if (!empty($response) && isset($response['errorCode'])) {
                    if (empty($response['errorCode'])) {
                        $gatewayStatus = 0;
                        $gatewayStatusReversed = 0;
                        $response = $this->dataHelper->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'getOrderStatus.do', array());
                        $response = json_decode($response, true);
                        if (!empty($response['OrderNumber']) && isset($response['OrderStatus']) && $order->getIncrementId() == (int)$response['OrderNumber'] && (int)$response['OrderStatus'] === 3) {
                            $gatewayStatus = 1;
                            $gatewayStatusReversed = 1;
                        }
                        $gatewayOrder
                            ->setOrderAmountDeposited(0.00)
                            ->setStatusDeposited(0)
                            ->setStatus($gatewayStatus)
                            ->setStatusReversed($gatewayStatusReversed)
                            ->save();
                        $gatewayOrderStatus = $this->dataHelper->getStoreConfig('order_status_reversed');
                        $message = __('The transaction was reversed successfully!');

                        $order->addStatusHistoryComment('Sensebank Payment - ' . $message);
                        $response_status = $this->_getGatewayOrderStatus($args['orderId']);
                        $response_status = json_decode($response_status, true);
                        $order->setTotalRefunded($response_status['paymentAmountInfo']['refundedAmount'] / 100);
                        $order->setTotalPaid($response_status['paymentAmountInfo']['approvedAmount'] / 100);
                        $order->save();
                        $response = $this->_view->getLayout()
                            ->createBlock('SENSEPAYMENT\Sensebank\Block\Adminhtml\Sales\Order\View\Tab\Action')
                            ->setSuccessMessage(__('The transaction was reversed successfully!'))
                            ->setIncrementId($gatewayOrder->getIncrementId())
                            ->setTemplate('SENSEPAYMENT_Sensebank::sales/order/view/tab/action.phtml')
                            ->toHtml();
                    } else {
                        $response = array(
                            'error'     => true,
                            'message'   => $response['errorMessage'],
                        );
                    }
                }
            }
        } else {
            $response = array(
                'error'     => true,
                'message'   => __('Sensebank order is missing or wrong module configurations!'),
            );
        }
        if (is_array($response)) {
            return $resultPage->setData($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
    public function _getGatewayOrderStatus($gatewayOrderId) {
        $action_adr = SENSEPAYMENT_PROD_URL;
        if ($this->dataHelper->getStoreConfigFlag('test_mode') === true) {
            $action_adr = SENSEPAYMENT_TEST_URL;
        }
        $args['userName'] = $this->dataHelper->getStoreConfig('MERCHANT_LOGIN');
        $args['password'] = $this->_encryptor->decrypt($this->dataHelper->getStoreConfig('MERCHANT_PASSWORD'));
        $args['orderId'] = $gatewayOrderId;
        $gose = $this->dataHelper->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'getOrderStatusExtended.do');
        return $gose;
    }
}
