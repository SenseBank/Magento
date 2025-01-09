<?php
namespace SENSEPAYMENT\Sensebank\Block\Adminhtml\Sales\Order\View\Tab;
class Action extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $gatewayOrderFactory;
    private $increment_id;
    /**
     * Create constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory $gatewayOrderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory $gatewayOrderFactory,
        array $data = array()
    ) {
        $this->gatewayOrderFactory = $gatewayOrderFactory;
        parent::__construct(
            $context,
            $registry,
            $adminHelper,
            $data
        );
    }
    public function getGatewayOrder() {
        if (empty($this->increment_id)) {
            $this->increment_id = $this->_coreRegistry->registry('current_order')->getIncrementId();
        }
        $gatewayOrder = $this->gatewayOrderFactory->create();
        return $gatewayOrder->loadByIncrementId($this->increment_id);
    }
    /**
     * ######################## TAB settings #################################
     */
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Sensebank Actions');
    }
    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Sensebank Actions');
    }
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if (!defined('SENSEPAYMENT_ENABLE_ACTION_TAB')) {
            return false;
        }
        if (!strcmp($this->_coreRegistry->registry('current_order')->getPayment()->getMethod(), 'sensebank')
            && $this->getGatewayOrder()->getId()) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    public function setIncrementId($incrementId) {
        $this->increment_id = $incrementId;
        return $this;
    }
}
