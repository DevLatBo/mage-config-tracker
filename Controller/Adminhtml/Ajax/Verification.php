<?php

namespace Devlat\Settings\Controller\Adminhtml\Ajax;

use Devlat\Settings\Logger\Logger;
use Devlat\Settings\Model\Tracker;
use Devlat\Settings\Model\TrackerFactory as TrackerModelFactory;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResourceModel;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;

class Verification extends Action implements HttpPostActionInterface
{

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var TrackerModelFactory
     */
    private TrackerModelFactory $trackerModelFactory;
    /**
     * @var TrackerResourceModel
     */
    private TrackerResourceModel $trackerResourceModel;
    /**
     * @var Session
     */
    private Session $adminSession;
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param TrackerModelFactory $trackerModelFactory
     * @param TrackerResourceModel $trackerResourceModel
     * @param Session $adminSession
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TrackerModelFactory $trackerModelFactory,
        TrackerResourceModel $trackerResourceModel,
        Session $adminSession,
        Logger $logger
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->trackerModelFactory = $trackerModelFactory;
        $this->trackerResourceModel = $trackerResourceModel;
        $this->adminSession = $adminSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Returns the response after updating the verified data in devlat_settings_tracker table.
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        $id = $this->getRequest()->getParam('id');
        $userId = $this->adminSession->getUser()->getId();

        if(!$id) {
            $this->logger->error(__("No ID found in request."));
            return $result->setData([
                'success' => false,
                'message' => __('ID not found'),
            ]);
        }

        try {
            /** @var Tracker $tracker */
            $tracker = $this->trackerModelFactory->create();
            $this->trackerResourceModel->load($tracker, $id);

            if (!$tracker->getId()) {
                $this->logger->error(__("Config tracked not found"));
                return $result->setData([
                    'success' => false,
                    'message' => __('Config tracked requested not found.'),
                ]);
            }

            if ($tracker->getVerified()) {
                $this->logger->alert(
                    __('Config tracked with ID: %1 has been updated before.', $tracker->getId())
                );
            } else {
                $tracker->setVerified(1);
            }

            $trackerUsers = $tracker->getVerifiedBy();
            $arrayUsers = !empty($trackerUsers)
                ? json_decode($trackerUsers, true)
                : [];

            if (!in_array($userId, $arrayUsers)) {
                $arrayUsers[] = $userId;
            }

            $tracker->setVerifiedBy(json_encode($arrayUsers));

            $this->trackerResourceModel->save($tracker);
            $result->setData([
                'success' => true,
                'message' => __('Verification successful.'),
            ]);
            $this->logger->info(__('Config tracked verified, ID: %1 ',$tracker->getId()));
        } catch (\Exception $e) {
            $this->logger->error(__($e->getMessage()));
        }


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
