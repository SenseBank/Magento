<?php
namespace SENSEPAYMENT\Sensebank\Model\ResourceModel;
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('sensebank_gateway_order', 'entity_id');
    }
    /**
     * Load RPSPayment Order by Increment Id
     *
     * @param \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface $order
     * @param $incrementId
     * @return $this
     */
    public function loadByIncrementId(\SENSEPAYMENT\Sensebank\Api\Data\OrderInterface $order, $incrementId)
    {
        $connection = $this->getConnection();
        $bind = ['increment_id' => $incrementId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'increment_id = :increment_id'
        );
        $incrementId = $connection->fetchOne($select, $bind);
        if ($incrementId) {
            $this->load($order, $incrementId);
        } else {
            $order->setData([]);
        }

        return $this;
    }
}
