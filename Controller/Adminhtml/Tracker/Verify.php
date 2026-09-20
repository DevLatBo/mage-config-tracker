<?php

namespace Devlat\Settings\Controller\Adminhtml\Tracker;

use Devlat\Settings\Logger\Logger;
use Devlat\Settings\Model\Service\Verifier;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Verify extends Action implements HttpGetActionInterface
{

    const ADMIN_RESOURCE = 'Devlat_Settings::config_track_log_verify';

    private PageFactory $pageFactory;
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

    public function __construct(
        PageFactory $pageFactory,
        Verifier $verifier,
        Session $adminSession,
        Logger $logger,
        Context $context
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->verifier = $verifier;
        $this->adminSession = $adminSession;
        $this->logger = $logger;
    }

    /**
     * Verifies the tracked config before rendering, so the form shows the updated counter.
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id) {
            $this->logger->error(__("No ID found in request."));
            $this->messageManager->addErrorMessage(__('ID not found'));

            return $this->resultRedirectFactory->create()->setPath('config/tracker/index');
        }

        try {
            $this->verifier->verify($id, (int)$this->adminSession->getUser()->getId());
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath('config/tracker/index');
        } catch (\Exception $e) {
            $this->logger->error(__($e->getMessage()));
            $this->messageManager->addErrorMessage(__('Something went wrong while verifying this config.'));
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system');
        $resultPage->getConfig()->getTitle()->prepend(__('Verify & Update'));

        return $resultPage;
    }
}
