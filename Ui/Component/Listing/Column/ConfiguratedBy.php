<?php

namespace Devlat\Settings\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\User\Model\UserFactory;

class ConfiguratedBy extends Column
{
    /**
     * @var UserFactory
     */
    private UserFactory $adminUserFactory;

    /**
     * Constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UserFactory $adminUserFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UserFactory $adminUserFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->adminUserFactory = $adminUserFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Modifies Configurated By column output.
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if(isset($item['configurated_by']) && $item['configurated_by']){
                    $configuratedBy = $item['configurated_by'];
                    $adminUser = $this->adminUserFactory->create();
                    $adminUser->load($configuratedBy);
                    $item['configurated_by'] = $adminUser->getUserName();
                }
            }
        }
        return $dataSource;
    }
}
