<?php
namespace SENSEPAYMENT\Sensebank\Block\Form;
/**
 * Abstract class for Sensebank payment method form
 */
abstract class Sensebank extends \Magento\Payment\Block\Form
{
    protected $_instructions;
    protected $_template = 'form/form.phtml';
}
