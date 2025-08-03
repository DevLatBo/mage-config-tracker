<?php

namespace Devlat\Settings\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Verification extends Template
{
    protected $_template = 'confirm_verification.phtml';
    private RequestInterface $request;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    )
    {
        $this->request = $request;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    public function getTrackerId() {
        return $this->request->getParam('id');
    }
}
