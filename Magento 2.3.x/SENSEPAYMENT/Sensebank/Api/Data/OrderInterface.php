<?php
namespace SENSEPAYMENT\Sensebank\Api\Data;
/**
* Sensebank Order interface.
* @api
*/
interface OrderInterface
{
/**
* Constants for keys of data array. Identical to the name of the getter in snake case
*/
const ID                            = 'entity_id';
const GATEWAY_ORDER_REFERENCE       = 'gateway_order_reference';
const INCREMENT_ID                  = 'increment_id';
const ORDER_AMOUNT                  = 'order_amount';
const ORDER_AMOUNT_DEPOSITED        = 'order_amount_deposited';
const ORDER_AMOUNT_REFUNDED         = 'order_amount_refunded';
const STATUS_DEPOSITED              = 'status_deposited';
const STATUS_REVERSED               = 'status_reversed';
const STATUS_REFUNDED               = 'status_refunded';
const STATUS                        = 'status';
const CREATED_AT                    = 'created_at';
const UPDATED_AT                    = 'updated_at';
/**#@-*/
/**
* Get ID
*
* @return int
*/
public function getId();
/**
* Get order ID
*
* @return string
*/
public function getGatewayOrderReference();
/**
* Get Increment ID
*
* @return string
*/
public function getIncrementId();
/**
* Get Order Amount
*
* @return string
*/
public function getOrderAmount();
/**
* Get Order Amount Deposited
*
* @return string
*/
public function getOrderAmountDeposited();
/**
* Get Order Amount Refunded
*
* @return string
*/
public function getOrderAmountRefunded();
/**
* Get Status Deposited
*
* @return int
*/
public function getStatusDeposited();
/**
* Get Status Reversed
*
* @return int
*/
public function getStatusReversed();
/**
* Get Status Refunded
*
* @return int
*/
public function getStatusRefunded();
/**
* Get Status
*
* @return int
*/
public function getStatus();
/**
* Get Created At
*
* @return \Magento\Framework\Stdlib\DateTime\DateTime
*/
public function getCreatedAt();
/**
* Get Updated At
*
* @return \Magento\Framework\Stdlib\DateTime\DateTime
*/
public function getUpdatedAt();
/**
* Set ID
*
* @param int $id
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setId($id);
/**
* Set Gateway Order Reference
*
* @param string $gatewayOrderReference
* @return SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setGatewayOrderReference($gatewayOrderReference);
/**
* Set Order ID
*
* @param string $incrementId
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setIncrementId($incrementId);
/**
* Set Order Amount
*
* @param string $orderAmount
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setOrderAmount($orderAmount);
/**
* Set Order Amount Deposited
*
* @param string $orderAmountDeposited
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setOrderAmountDeposited($orderAmountDeposited);
/**
* Set Order Amount Refunded
*
* @param string $orderAmountRefunded
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setOrderAmountRefunded($orderAmountRefunded);
/**
* Set Status Deposited
*
* @param int $statusDeposited
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setStatusDeposited($statusDeposited);
/**
* Set Status Reversed
*
* @param int $statusReversed
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setStatusReversed($statusReversed);
/**
* Set Status Refunded
*
* @param int $statusRefunded
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setStatusRefunded($statusRefunded);
/**
* Set Status
*
* @param int $status
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setStatus($status);
/**
* Set Date Added
*
* @param string $date
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setCreatedAt($date);
/**
* Set Date Added
*
* @param string $date
* @return \SENSEPAYMENT\Sensebank\Api\Data\OrderInterface
*/
public function setUpdatedAt($date);
}
