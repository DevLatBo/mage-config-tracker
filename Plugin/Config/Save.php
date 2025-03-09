<?php

namespace Devlat\ConfigTracker\Plugin\Config;

use Devlat\ConfigTracker\Model\ResourceModel\Tracker as TrackerResource;
use Devlat\ConfigTracker\Model\Tracker;
use Devlat\ConfigTracker\Model\TrackerFactory;
use \Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class Save
{

    private ScopeConfigInterface $scopeConfig;
    private ResourceConnection $resourceConnection;
    private TrackerFactory $trackerFactory;
    private TrackerResource $trackerResource;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrackerFactory $trackerFactory,
        TrackerResource $trackerResource,
        ResourceConnection $resourceConnection
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->trackerFactory = $trackerFactory;
        $this->trackerResource = $trackerResource;
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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/oscar.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $section = $subject->getSection();
        $groups = $subject->getGroups();
        //$configsData = $subject->getData();
        $logger->info("Section: ". $section);

        //$logger->info("Groups: ". print_r($groups, true));
        //$logger->info(print_r($subject->getData(), true));

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
                    $logger->info("⏩ Ignorado: $configPath (No existe en core_config_data)");
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

                        $logger->info("path: ".$configPath);
                        $logger->info("Old Value: ".$oldValue);
                        $logger->info("New Value: ".$newValue);
                    } catch (\Exception $e) {
                        $logger->info($e->getMessage());
                    }
                }
            }
        }
    }
}
