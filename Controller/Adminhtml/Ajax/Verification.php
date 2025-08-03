<?php

namespace Devlat\Settings\Controller\Adminhtml\Ajax;

use Devlat\Settings\Model\TrackerFactory as TrackerModelFactory;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResourceModel;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Verification extends Action implements HttpPostActionInterface
{

    private JsonFactory $resultJsonFactory;
    private TrackerModelFactory $trackerModelFactory;
    private TrackerResourceModel $trackerResourceModel;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TrackerModelFactory $trackerModelFactory,
        TrackerResourceModel $trackerResourceModel
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->trackerModelFactory = $trackerModelFactory;
        $this->trackerResourceModel = $trackerResourceModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $id = $this->getRequest()->getParam('id');

        if(!$id) {
            return $result->setData([
                'success' => false,
                'message' => __('ID not found'),
            ]);
        }

        $tracker = $this->trackerModelFactory->create();
        $this->trackerResourceModel->load($tracker, $id);

        if (!$tracker->getId()) {
            return $result->setData([
                'success' => false,
                'message' => __('Tracker requested not found.'),
            ]);
        }

        if ($tracker->getVerified()) {
            return $result->setData([
                'success' => false,
                'message' => __('Thiis item has been verified before.'),
            ]);
        }
        $tracker->setVerified(1);
        $this->trackerResourceModel->save($tracker);


        $result->setData([
            'success' => true,
            'message' => __('Verification successful.'),
        ]);
        return $result;
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Devlat_Settings::config_track_log_verify');
    }
}
