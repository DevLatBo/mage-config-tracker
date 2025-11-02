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
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
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
                $verifiedByData = !is_null($data['verified_by']) ? json_decode($data['verified_by'], true) : [];
                $adminUsernames = [];
                if (!empty($verifiedByData)) {
                    $userIds = array_keys($verifiedByData);
                    $userIds = array_map('intval', $userIds);
                    $adminUserCollection = $this->userCollectionFactory->create()
                        ->addFieldToSelect(['user_id', 'username'])
                        ->addFieldToFilter('user_id', ['in' => $userIds]);

                    $userMap = [];
                    foreach ($adminUserCollection as $user) {
                        $userMap[$user->getUserId()] = $user->getUsername();
                    }

                    // new array built with username and counter.
                    foreach ($verifiedByData as $userId => $info) {
                        $username = isset($userMap[$userId]) ? $userMap[$userId] : null;
                        $adminUsernames[] = [
                            'username' => $username,
                            'counter' => $info['counter']
                        ];
                    }
                }
                $data['verified_by'] = $adminUsernames;

                $this->loadedData[$item->getData('id')] = $data;
            }
        }

        return $this->loadedData;
    }
}
