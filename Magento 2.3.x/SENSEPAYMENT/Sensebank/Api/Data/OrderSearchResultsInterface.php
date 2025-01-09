<?php
namespace SENSEPAYMENT\Sensebank\Api\Data;
use Magento\Framework\Api\SearchResultsInterface;
/**
* @api
*/
interface OrderSearchResultsInterface extends SearchResultsInterface
{
/**
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface[]
*/
public function getItems();
/**
* @param \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface[] $items
* @return $this
*/
public function setItems(array $items);
}
