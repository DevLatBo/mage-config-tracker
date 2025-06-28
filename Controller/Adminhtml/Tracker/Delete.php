<?php

namespace Devlat\Settings\Controller\Adminhtml\Tracker;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Devlat\Settings\Model\Tracker;
use Devlat\Settings\Model\TrackerFactory;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResource;
use Magento\Framework\Controller\ResultFactory;

class Delete extends Action implements HttpGetActionInterface
{

    const ADMIN_RESOURCE = "Devlat_Settings::config_track_log_delete";
    /**
     * @var TrackerFactory
     */
    private TrackerFactory $trackerFactory;
    /**
     * @var TrackerResource
     */
    private TrackerResource $trackerResource;

    public function __construct(
        TrackerFactory $trackerFactory,
        TrackerResource $trackerResource,
        Context $context
    )
    {
        $this->trackerFactory = $trackerFactory;
        $this->trackerResource = $trackerResource;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $id = $this->getRequest()->getParam('id');

            /** @var Tracker $tracker */
            $tracker = $this->trackerFactory->create();

            $this->trackerResource->load($tracker, $id);
            if($tracker->getId()){
                $this->trackerResource->delete($tracker);
                $this->messageManager->addSuccessMessage(__('The config log has been deleted.'));
            } else {
                $this->messageManager->addErrorMessage(__('The config log could not be deleted.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirect->setPath('*/*');
    }
}
