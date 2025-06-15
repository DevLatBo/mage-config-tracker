<?php

namespace Devlat\Settings\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tracker extends AbstractDb
{

    /** @var string Main Table Name */
    const MAIN_TABLE = 'devlat_settings_tracker';
    /** @var string Main table primary key field name */
    const ID_FIELD_NAME = 'id';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
