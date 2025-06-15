<?php

namespace Devlat\Settings\Model\ResourceModel\Tracker;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Devlat\Settings\Model\Tracker as TrackerModel;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResourceModel;

class Collection extends AbstractCollection
{

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(TrackerModel::class , TrackerResourceModel::class);
    }
}
