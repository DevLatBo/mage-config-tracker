<?php

namespace Devlat\Settings\Ui\Component\Listing\Column;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;

class VerifiedBy extends Column
{
    /**
     * @var UserFactory
     */
    private UserFactory $adminUserFactory;
    /**
     * @var UserCollectionFactory
     */
    private UserCollectionFactory $userCollectionFactory;

    /**
     * Constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UserFactory $adminUserFactory
     * @param UserCollectionFactory $userCollectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UserFactory $adminUserFactory,
        UserCollectionFactory $userCollectionFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->adminUserFactory = $adminUserFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Modifies Verified By column output.
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['verified_by']) && !empty($item['verified_by'])) {
                    $verifiedBy = json_decode($item['verified_by'], true);

                    $adminUsersCollection = $this->userCollectionFactory->create()
                        ->addFieldToFilter("user_id", ["in" => $verifiedBy])
                        ->addFieldToSelect("username");
                    $adminUsers = $adminUsersCollection->getColumnValues("username");

                    $item['verified_by'] = $adminUsers;
                }
            }
        }
        return $dataSource;
    }
}
