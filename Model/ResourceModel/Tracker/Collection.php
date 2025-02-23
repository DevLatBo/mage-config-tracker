<?php

namespace Devlat\ConfigTracker\Model\ResourceModel\Tracker;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Devlat\ConfigTracker\Model\Tracker as TrackerModel;
use Devlat\ConfigTracker\Model\ResourceModel\Tracker as TrackerResourceModel;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'id';
    protected function _construct(): void
    {
        $this->_init(TrackerModel::class , TrackerResourceModel::class);
    }
}
