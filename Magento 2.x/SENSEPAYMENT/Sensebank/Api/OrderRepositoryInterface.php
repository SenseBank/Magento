<?php
namespace SENSEPAYMENT\Sensebank\Api;
/**
* Order CRUD interface.
* @api
*/
interface OrderRepositoryInterface
{
/**
* Save order.
*
* @param \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface $order
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function save(\SENSEPAYMENT\Sensebank\Api\Data\OrderInterface $order);
/**
* Retrieve order.
*
* @param int $id
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
* @throws \Magento\Framework\Exception\NoSuchEntityException
*/
public function getById($id);
/**
* Retrieve orders matching the specified criteria.
*
* @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderSearchResultsInterface
*/
public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
/**
* Delete order.
*
* @param \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface $order
* @return bool true on success
*/
public function delete(\SENSEPAYMENT\Sensebank\Api\Data\OrderInterface $order);
/**
* Delete order by ID.
*
* @param int $id
* @return bool true on success
* @throws \Magento\Framework\Exception\NoSuchEntityException
*/
public function deleteById($id);
}
