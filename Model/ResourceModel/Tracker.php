<?php

namespace Devlat\ConfigTracker\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Tracker extends AbstractDb
{

    /** @var string Main Table Name */
    const MAIN_TABLE = 'config_tracked';
    /** @var string Main table primary key field name */
    const ID_FIELD_NAME = 'id';

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
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
