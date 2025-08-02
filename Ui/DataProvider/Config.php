<?php

namespace Devlat\Settings\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Devlat\Settings\Model\ResourceModel\Tracker\Collection as TrackerCollection;
use Devlat\Settings\Model\ResourceModel\Tracker\CollectionFactory as TrackerCollectionFactory;

class Config extends AbstractDataProvider
{
    /**
     * @var array
     */
    private array $loadedData;

    /** @var TrackerCollection  */
    protected $collection;


    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param TrackerCollectionFactory $trackerCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        TrackerCollectionFactory $trackerCollectionFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $trackerCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/oscar.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        if (!isset($this->loadedData)) {
            $this->loadedData = [];

            foreach ($this->collection->getItems() as $item) {
                $this->loadedData[$item->getData('id')] = $item->getData();
            }
        }
        $logger->info(print_r($this->loadedData, true));
        return $this->loadedData;
    }
}
