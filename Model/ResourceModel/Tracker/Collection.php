<?php

namespace Devlat\Tracker\Model\ResourceModel\Tracker;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Devlat\Tracker\Model\Tracker as TrackerModel;
use Devlat\Tracker\Model\ResourceModel\Tracker as TrackerResourceModel;

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
