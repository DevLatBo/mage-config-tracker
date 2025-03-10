<?php

namespace Devlat\ConfigTracker\Plugin\Config;

use Devlat\ConfigTracker\Logger\Logger;
use Devlat\ConfigTracker\Model\ResourceModel\Tracker as TrackerResource;
use Devlat\ConfigTracker\Model\Tracker;
use Devlat\ConfigTracker\Model\TrackerFactory;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class Save
{

    private ScopeConfigInterface $scopeConfig;
    private ResourceConnection $resourceConnection;
    private TrackerFactory $trackerFactory;
    private TrackerResource $trackerResource;
    private Logger $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrackerFactory $trackerFactory,
        TrackerResource $trackerResource,
        ResourceConnection $resourceConnection,
        Logger $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->trackerFactory = $trackerFactory;
        $this->trackerResource = $trackerResource;
        $this->logger = $logger;
    }

    /**
     * @param Config $subject
     * @return array
     * @throws \Zend_Log_Exception
     */
    public function beforeSave(
        Config $subject
    ): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('core_config_data');

        $section = $subject->getSection();
        $groups = $subject->getGroups();

        $this->logger->info("Tracking config updates...");

        foreach ($groups as $group => $fields) {
            foreach ($fields['fields'] as $field => $data) {
                if (!isset($data['value'])) {
                    continue;
                }
                $configPath = "{$section}/{$group}/{$field}";
                $query = "SELECT COUNT(*) FROM $tableName WHERE path = :path";
                $binds = ['path' => $configPath];
                $existsInDb = (bool) $connection->fetchOne($query, $binds);

                if (!$existsInDb) {
                    $this->logger->info("Path {$configPath} not found in database");
                    continue;
                }

                $oldValue = $this->scopeConfig->getValue($configPath);
                $newValue = $data['value'];
                if($oldValue != $newValue) {
                    try {
                        /** @var Tracker $tracker */
                        $tracker = $this->trackerFactory->create();
                        $tracker->setSection($section);
                        $tracker->setPath($configPath);
                        $tracker->setOldValue($oldValue);
                        $tracker->setNewValue($newValue);
                        $tracker->setVerified(0);
                        $this->trackerResource->save($tracker);

                        $this->logger->info("Path value: {$configPath} tracked successfully.");

                    } catch (\Exception $e) {
                        $this->logger->info('Error: '. $$e->getMessage() );
                    }
                }
            }
        }
    }
}
