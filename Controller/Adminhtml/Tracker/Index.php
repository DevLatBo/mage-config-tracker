<?php

namespace Devlat\Settings\Controller\Adminhtml\Tracker;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Redirect;

class Index extends Action implements HttpGetActionInterface
{

    /** @var string  */
    const ADMIN_RESOURCE = 'Devlat_Settings::config_track_logs';

    /** @var string  */
    const TABLE = "devlat_settings_tracker";

    /**
     * @var PageFactory
     */
    private PageFactory $pageFactory;
    private ResourceConnection $resourceConnection;

    /**
     * Constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ResourceConnection $resourceConnection
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Set title as "Tracker" and
     * validates if the main table for the settings tracker exists.
     * @return Page|Redirect
     */
    public function execute(): Page|Redirect
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::TABLE);

        if(!$connection->isTableExists($tableName)){
            $this->messageManager->addErrorMessage(__('Tracker log table does not exist. Please report to personal support.'));

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('admin/dashboard/index');
        }
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system');
        $resultPage->getConfig()->getTitle()->prepend(__('Config Tracker Logs'));
        return $resultPage;
    }
}
