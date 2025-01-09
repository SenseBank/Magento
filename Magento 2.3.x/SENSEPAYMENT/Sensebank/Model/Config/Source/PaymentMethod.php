<?php
namespace SENSEPAYMENT\Sensebank\Model\Config\Source;
use Magento\Framework\Option\ArrayInterface;
/**
 * Class PaymentMethod
 */
class PaymentMethod implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'1', 'label'=>__('Full prepayment')),
            array('value'=>'2', 'label'=>__('Partial prepayment')),
            array('value'=>'3', 'label'=>__('Advance payment')),
            array('value'=>'4', 'label'=>__('Full payment')),
            array('value'=>'5', 'label'=>__('Partial payment with further credit')),
            array('value'=>'6', 'label'=>__('No payment with further credit')),
            array('value'=>'7', 'label'=>__('Payment on credit')),
        );
    }
}
