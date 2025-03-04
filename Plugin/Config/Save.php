<?php

namespace Devlat\ConfigTracker\Plugin\Config;

use \Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class Save
{

    private ScopeConfigInterface $scopeConfig;
    private ResourceConnection $resourceConnection;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
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
        $configsData = $subject->getData();
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
                    // Si el campo no está en la base de datos, lo ignoramos
                    $logger->info("⏩ Ignorado: $configPath (No existe en core_config_data)");
                    continue;
                }

                $logger->info("path: ".$configPath);
                $oldValue = $this->scopeConfig->getValue($configPath);
                $newValue = $data['value'];
                $logger->info("Old Value: ".$oldValue);
                $logger->info("New Value: ".$newValue);
                $logger->info("----------------------");
            }
        }
        /*
        foreach ($groups as $group => $fields) {
            foreach ($fields['fields'] as $field => $data) {
                if (!isset($data['value'])) {
                    continue;
                }

                $configPath = "{$section}/{$group}/{$field}";
                $logger->info($configPath);
                // Obtener el valor anterior desde la base de datos
                $oldValue = $this->scopeConfig->getValue($configPath);

                // Obtener el valor nuevo
                $newValue = $data['value'];

                // Comparar valores
                if ($oldValue != $newValue) {
                    $logger->info("📝 Campo cambiado: $configPath | Antes: $oldValue | Ahora: $newValue");
                }
            }
        }*/
    }
}
