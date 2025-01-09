<?php
namespace SENSEPAYMENT\Sensebank\Model\ResourceModel\Order;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init(
            'SENSEPAYMENT\Sensebank\Model\Order',
            'SENSEPAYMENT\Sensebank\Model\ResourceModel\Order'
        );
    }
}
