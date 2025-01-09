<?php
namespace SENSEPAYMENT\Sensebank\Model\Config\Source;
use Magento\Framework\Option\ArrayInterface;
/**
 * Class Sno
 */
class Sno implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>__('General')),
            array('value'=>'1', 'label'=>__('Simplified, income')),
            array('value'=>'2', 'label'=>__('Simplified, income minus expences')),
            array('value'=>'3', 'label'=>__('Unified tax on imputed income')),
            array('value'=>'4', 'label'=>__('Unified agricultural tax')),
            array('value'=>'5', 'label'=>__('Patent taxation system')),
        );
    }
}
