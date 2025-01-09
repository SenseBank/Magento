<?php
namespace SENSEPAYMENT\Sensebank\Model\Config\Source;
use Magento\Framework\Option\ArrayInterface;
/**
 * Class FFDVersion
 */
class FFDVersion implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>'v1_05', 'label'=>'v1.05'),
            array('value'=>'v1_2', 'label'=>'v1.2'),
        );
    }
}
