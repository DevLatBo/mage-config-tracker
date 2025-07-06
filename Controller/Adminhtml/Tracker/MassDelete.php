<?php

namespace Devlat\Settings\Controller\Adminhtml\Tracker;

use Devlat\Settings\Model\ResourceModel\Tracker\CollectionFactory as TrackerCollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Action\HttpPostActionInterface;

class MassDelete extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Devlat_Settings::config_track_log_delete';
    /**
     * @var TrackerCollectionFactory
     */
    private TrackerCollectionFactory $trackerCollectionFactory;
    /**
     * @var Filter
     */
    private Filter $filter;

    /**
     * @param TrackerCollectionFactory $trackerCollectionFactory
     * @param Filter $filter
     * @param Action\Context $context
     */
    public function __construct(
        TrackerCollectionFactory $trackerCollectionFactory,
        Filter $filter,
        Action\Context $context
    )
    {
        $this->trackerCollectionFactory = $trackerCollectionFactory;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): Redirect
    {
        $collection = $this->trackerCollectionFactory->create();
        $items = $this->filter->getCollection($collection);
        $itemsSize = $items->getSize();

        foreach ($items as $item) {
            $item->delete();
        }

        $this->messageManager->addSuccessMessage(__("The number of items deleted are: %1", $itemsSize));

        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $redirect->setPath('*/*');
    }
}
