<?php
namespace SENSEPAYMENT\Sensebank\Model;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use SENSEPAYMENT\Sensebank\Api\Data\OrderSearchResultsInterfaceFactory;
use SENSEPAYMENT\Sensebank\Api\Data\OrderInterface;
use SENSEPAYMENT\Sensebank\Api\Data\OrderInterfaceFactory;
use SENSEPAYMENT\Sensebank\Api\OrderRepositoryInterface;
use SENSEPAYMENT\Sensebank\Model\ResourceModel\Order as OrderResource;
use SENSEPAYMENT\Sensebank\Model\ResourceModel\Order\CollectionFactory;
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var OrderResource
     */
    private $orderResource;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var OrderSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var OrderInterface
     */
    private $orderInterface;
    /**
     * @var OrderInterfaceFactory
     */
    private $orderInterfaceFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * OrderRepository constructor.
     * @param OrderResource $orderResource
     * @param OrderFactory $orderFactory
     * @param CollectionFactory $collectionFactory
     * @param OrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param OrderInterfaceFactory $orderInterfaceFactory
     */
    public function __construct(
        OrderResource $orderResource,
        OrderFactory $orderFactory,
        CollectionFactory $collectionFactory,
        OrderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        OrderInterface $orderInterface,
        OrderInterfaceFactory $orderInterfaceFactory
    ) {
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->orderInterface = $orderInterface;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
    }
    /**
     * Save order data
     *
     * @param OrderInterface $order
     * @return int
     * @throws CouldNotSaveException
     */
    public function save(OrderInterface $order)
    {
        try {
            $this->orderResource->save($order);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Sensebank order: %1',
                $exception->getMessage()
            ));
        }
        return $order->getId();
    }
    /**
     * Get Order by ID
     *
     * @param int $id
     * @return Order
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $id, 'entity_id');
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Sensebank order with id "%1" does not exist.', $incrementId));
        }
        return $order;
    }
    /**
     * Load Sensebank order data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \SENSEPAYMENT\Sensebank\Api\Data\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $orders = array();
        /** @var Order $orderModel */
        foreach ($collection as $orderModel) {
            $orderData = $this->orderInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $orderData,
                $orderModel->getData(),
                'SENSEPAYMENT\Sensebank\Api\Data\OrderInterface'
            );
            $orders[] = $orderData;
        }
        $searchResults->setItems($orders);
        return $searchResults;
    }
    /**
     * Delete order
     *
     * @param OrderInterface $order
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(OrderInterface $order)
    {
        try {
            $this->orderResource->delete($order);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Sensebank order: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
    /**
     * Delete Sensebank Order by given Id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
