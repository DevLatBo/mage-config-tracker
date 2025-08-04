<?php

namespace Devlat\Settings\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Verification extends Template
{
    /**
     * @var string
     */
    protected $_template = 'confirm_verification.phtml';
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * Constructor.
     * @param Template\Context $context
     * @param RequestInterface $request
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
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

    /**
     * Gets the config tracked id.
     * @return int
     */
    public function getTrackerId(): int
    {
        return (int)$this->request->getParam('id');
    }
}
