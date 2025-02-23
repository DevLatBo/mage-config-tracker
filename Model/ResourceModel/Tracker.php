<?php

namespace Devlat\ConfigTracker\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Tracker extends AbstractDb
{

    protected $_idFieldName = 'id';

    /**
     * @param Context $context
     * @param $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('config_tracked', 'id');
    }
}
