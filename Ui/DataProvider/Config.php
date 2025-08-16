<?php

namespace Devlat\Settings\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Devlat\Settings\Model\ResourceModel\Tracker\Collection as TrackerCollection;
use Devlat\Settings\Model\ResourceModel\Tracker\CollectionFactory as TrackerCollectionFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;

class Config extends AbstractDataProvider
{
    /**
     * @var array
     */
    private array $loadedData;

    /** @var TrackerCollection  */
    protected $collection;
    /**
     * @var UserCollectionFactory
     */
    private UserCollectionFactory $userCollectionFactory;


    /**
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param TrackerCollectionFactory $trackerCollectionFactory
     * @param UserCollectionFactory $userCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        TrackerCollectionFactory $trackerCollectionFactory,
        UserCollectionFactory $userCollectionFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $trackerCollectionFactory->create();
        $this->userCollectionFactory = $userCollectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (!isset($this->loadedData)) {
            $this->loadedData = [];
            foreach ($this->collection->getItems() as $item) {
                $data = $item->getData();

                // In this part the verified_by is populated with username data instead of ids.
                $userIds = !is_null($data['verified_by']) ? json_decode($data['verified_by'], true) : [];
                $usernames = [];
                if (!empty($userIds)) {
                   $adminUserCollection = $this->userCollectionFactory->create()
                    ->addFieldToFilter('user_id', ['in' => $userIds]);

                   $usernames = $adminUserCollection->getColumnValues('username');
                }
                $data['verified_by'] = $usernames;

                $this->loadedData[$item->getData('id')] = $data;
            }
        }

        return $this->loadedData;
    }
}
