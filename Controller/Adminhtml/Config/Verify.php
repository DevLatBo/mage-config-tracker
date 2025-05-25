<?php

namespace Devlat\Tracker\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Verify extends Action implements HttpGetActionInterface
{

    const ADMIN_RESOURCE = 'Devlat_Tracker::config_track_log_verify';

    private PageFactory $pageFactory;

    public function __construct(
        PageFactory $pageFactory,
        Context $context
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system');
        $resultPage->getConfig()->getTitle()->prepend(__('Verify & Update'));

        return $resultPage;
    }
}
