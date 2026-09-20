<?php

namespace Devlat\Settings\Controller\Adminhtml\Ajax;

use Devlat\Settings\Logger\Logger;
use Devlat\Settings\Model\Service\Verifier;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Verification extends Action implements HttpPostActionInterface
{

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var Verifier
     */
    private Verifier $verifier;
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
     * @param Verifier $verifier
     * @param Session $adminSession
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verifier $verifier,
        Session $adminSession,
        Logger $logger
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verifier = $verifier;
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

        $id = (int)$this->getRequest()->getParam('id');

        if (!$id) {
            $this->logger->error(__("No ID found in request."));
            return $result->setData([
                'success' => false,
                'message' => __('ID not found'),
            ]);
        }

        try {
            $this->verifier->verify($id, (int)$this->adminSession->getUser()->getId());

            $result->setData([
                'success' => true,
                'message' => __('Verification successful.'),
            ]);
        } catch (NoSuchEntityException $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error(__($e->getMessage()));
            $result->setData([
                'success' => false,
                'message' => __('Something went wrong while verifying this config.'),
            ]);
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
