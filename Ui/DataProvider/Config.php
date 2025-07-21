<?php

namespace Devlat\Settings\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Devlat\Settings\Model\ResourceModel\Tracker\Collection as TrackerCollection;

class Config extends AbstractDataProvider
{
    private array $loadedData;
    protected TrackerCollection $trackerCollection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param TrackerCollection $trackerCollection
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        TrackerCollection $trackerCollection,
        array $meta = [],
        array $data = []
    )
    {
        $this->trackerCollection = $trackerCollection;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (!isset($this->loadedData)) {
            $this->loadedData = [];

            foreach ($this->trackerCollection as $item) {
                $this->loadedData[$item->getId()] = $item->getData();
            }
        }
        return $this->loadedData;
    }
}
