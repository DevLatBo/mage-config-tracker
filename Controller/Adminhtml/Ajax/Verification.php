<?php

namespace Devlat\Settings\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Verification extends Action implements HttpPostActionInterface
{

    private JsonFactory $resultJsonFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
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
