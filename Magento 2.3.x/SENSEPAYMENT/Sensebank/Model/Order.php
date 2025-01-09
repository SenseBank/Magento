<?php
namespace SENSEPAYMENT\Sensebank\Model;
use Magento\Framework\Model\AbstractModel;
use SENSEPAYMENT\Sensebank\Api\Data\OrderInterface;
class Order extends AbstractModel implements OrderInterface
{
    /**
     * Order constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Order|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \SENSEPAYMENT\Sensebank\Model\ResourceModel\Order $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
    protected function _construct()
    {
        $this->_init('SENSEPAYMENT\Sensebank\Model\ResourceModel\Order');
    }
    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }
    /**
     * Get Gateway Order Reference
     *
     * @return string
     */
    public function getGatewayOrderReference()
    {
        return $this->getData(self::GATEWAY_ORDER_REFERENCE);
    }
    /**
     * Get Increment ID
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }
    /**
     * Get Order Amount
     *
     * @return string
     */
    public function getOrderAmount()
    {
        return $this->getData(self::ORDER_AMOUNT);
    }
    /**
     * Get Order Amount Deposited
     *
     * @return string
     */
    public function getOrderAmountDeposited()
    {
        return $this->getData(self::ORDER_AMOUNT_DEPOSITED);
    }
    /**
     * Get Order Amount Refunded
     *
     * @return string
     */
    public function getOrderAmountRefunded()
    {
        return $this->getData(self::ORDER_AMOUNT_REFUNDED);
    }
    /**
     * Get Status Deposited
     *
     * @return int
     */
    public function getStatusDeposited()
    {
        return $this->getData(self::STATUS_DEPOSITED);
    }
    /**
     * Get Status Reversed
     *
     * @return int
     */
    public function getStatusReversed()
    {
        return $this->getData(self::STATUS_REVERSED);
    }
    /**
     * Get Status Refunded
     *
     * @return int
     */
    public function getStatusRefunded()
    {
        return $this->getData(self::STATUS_REFUNDED);
    }
    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    /**
     * Get Created At
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
    /**
     * Get Updated At
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }
    /**
     * Set ID
     *
     * @param int $id
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set Order Reference
     *
     * @param int $gatewayOrderReference
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setGatewayOrderReference($gatewayOrderReference)
    {
        return $this->setData(self::GATEWAY_ORDER_REFERENCE, $gatewayOrderReference);
    }
    /**
     * Set Increment ID
     *
     * @param int $incrementId
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }
    /**
     * Set Order Amount
     *
     * @param string $orderAmount
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setOrderAmount($orderAmount)
    {
        return $this->setData(self::ORDER_AMOUNT, $orderAmount);
    }
    /**
     * Set Order Amount Deposited
     *
     * @param string $orderAmountDeposited
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setOrderAmountDeposited($orderAmountDeposited)
    {
        return $this->setData(self::ORDER_AMOUNT_DEPOSITED, $orderAmountDeposited);
    }
    /**
     * Set Order Amount Refunded
     *
     * @param string $orderAmountRefunded
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setOrderAmountRefunded($orderAmountRefunded)
    {
        return $this->setData(self::ORDER_AMOUNT_REFUNDED, $orderAmountRefunded);
    }
    /**
     * Set Status Deposited
     *
     * @param int $statusDeposited
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setStatusDeposited($statusDeposited)
    {
        return $this->setData(self::STATUS_DEPOSITED, $statusDeposited);
    }
    /**
     * Set Status Reversed
     *
     * @param int $statusReversed
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setStatusReversed($statusReversed)
    {
        return $this->setData(self::STATUS_REVERSED, $statusReversed);
    }
    /**
     * Set Status Refunded
     *
     * @param int $statusRefunded
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setStatusRefunded($statusRefunded)
    {
        return $this->setData(self::STATUS_REFUNDED, $statusRefunded);
    }
    /**
     * Set Status
     *
     * @param int $status
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    /**
     * Set Created At
     *
     * @param string $date
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::CREATED_AT, $date);
    }
    /**
     * Set Updated At
     *
     * @param string $date
     * @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
     */
    public function setUpdatedAt($date)
    {
        return $this->setData(self::UPDATED_AT, $date);
    }
    /**
     * Load gateway Order By Increment id
     *
     * @param   int $incrementId
     * @return  $this
     */
    public function loadByIncrementId($incrementId)
    {
        $this->_getResource()->loadByIncrementId($this, $incrementId);
        return $this;
    }
}
