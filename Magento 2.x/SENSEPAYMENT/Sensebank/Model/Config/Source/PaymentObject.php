<?php
namespace SENSEPAYMENT\Sensebank\Model\Config\Source;
use Magento\Framework\Option\ArrayInterface;
/**
 * Class PaymentObject
 */
class PaymentObject implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'1', 'label'=>__('Goods')),
            array('value'=>'2', 'label'=>__('Excised goods')),
            array('value'=>'3', 'label'=>__('Job')),
            array('value'=>'4', 'label'=>__('Service')),
            array('value'=>'5', 'label'=>__('Stake in gambling')),
            array('value'=>'7', 'label'=>__('Lottery ticket')),
            array('value'=>'9', 'label'=>__('Intellectual property provision')),
            array('value'=>'10', 'label'=>__('Payment')),
            array('value'=>'11', 'label'=>__('Agent\'s commission')),
            array('value'=>'12', 'label'=>__('Combined')),
            array('value'=>'13', 'label'=>__('Other')),
        );
    }
}
