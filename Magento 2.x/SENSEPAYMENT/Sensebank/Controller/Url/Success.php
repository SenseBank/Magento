<?php
namespace SENSEPAYMENT\Sensebank\Controller\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
class Success extends Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
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
        $data = $this->getRequest()->getQuery();
        $response = $paymentMethod->processResponse($data);
        if ($response === true){
            $msg_success = __('Thank you! Your order has been successfully paid.');
            $this->messageManager->addSuccess($msg_success);
        } else {
            $msg_error = __('An error occurred during the payment! contact the store manager.');
            $this->messageManager->addError($msg_error);
        }
        $this->_redirect('checkout/cart');
    }
}
