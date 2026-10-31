<?php

namespace Devlat\Settings\Plugin\Setting;

use Devlat\Settings\Logger\Logger;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResource;
use Devlat\Settings\Model\Tracker;
use Devlat\Settings\Model\TrackerFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class Save
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var TrackerFactory
     */
    private TrackerFactory $trackerFactory;
    /**
     * @var TrackerResource
     */
    private TrackerResource $trackerResource;
    /**
     * @var Session
     */
    private Session $adminSession;
    /**
     * @var Logger
     */
    private Logger $logger;
    /**
     * @var Config\Structure
     */
    private Config\Structure $configStructure;
    /**
     * @var Initial
     */
    private Initial $initialConfig;
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * Constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param TrackerFactory $trackerFactory
     * @param TrackerResource $trackerResource
     * @param Config\Structure $configStructure
     * @param Session $adminSession
     * @param Initial $initialConfig
     * @param ResourceConnection $resourceConnection
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrackerFactory $trackerFactory,
        TrackerResource $trackerResource,
        Config\Structure $configStructure,
        Session $adminSession,
        Initial $initialConfig,
        ResourceConnection $resourceConnection,
        Logger $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->trackerFactory = $trackerFactory;
        $this->trackerResource = $trackerResource;
        $this->adminSession = $adminSession;
        $this->logger = $logger;
        $this->configStructure = $configStructure;
        $this->initialConfig = $initialConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Config $subject
     * @return void
     * @throws \Zend_Log_Exception
     */
    public function beforeSave(
        Config $subject
    ): void
    {

        $section = $subject->getSection();
        $groups = $subject->getGroups();

        $userId = (int)$this->adminSession->getUser()->getId();

        $this->logger->info("Tracking config updates...");
        foreach ($groups as $groupId => $groupData) {
            $this->processGroup("{$section}/{$groupId}", $groupData, $section, $userId);
        }
    }

    /**
     * Saves configuration tracked in custom table devlat_settings_tracker.
     * @param string $section
     * @param string $configPath
     * @param int $userId
     * @param string $oldValue
     * @param string $newValue
     * @return void
     */
    private function saveTracker(string $section, string $configPath, int $userId, string $oldValue, string $newValue): void
    {
        try {
            /** @var Tracker $tracker */
            $tracker = $this->trackerFactory->create();
            $tracker->setSection($section);
            $tracker->setPath($configPath);
            $tracker->setConfiguratedBy($userId);
            $tracker->setOldValue($oldValue);
            $tracker->setNewValue($newValue);
            $tracker->setVerified(0);
            $this->trackerResource->save($tracker);

            $this->logger->info("Path value: {$configPath} tracked successfully.");

        } catch (\Exception $e) {
            $this->logger->info('Error: '. $e->getMessage() );
        }
    }

    /**
     * Query in which checks if value is stored in core_config_data, otherwise returns null.
     * @param string $configPath
     * @return string|null
     */
    private function getStoredValue(string $configPath): ?string
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('core_config_data'), 'value')
            ->where('path = ?', $configPath)
            ->where('scope = ?', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->where('scope_id', 0);
        $value = $connection->fetchOne($select);

        return $value === false ? null : $value['value'];
    }

    private function getSystemDefault(string $configPath): string
    {
        $value = $this->initialConfig->getData(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        foreach (explode('/', $configPath) as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return '';
            }
            $value = $value[$key];
        }
        return is_array($value) ? '' : (string)$value;
    }

    /**
     * Method in which validates, registers and updates the last config made.
     * @param string $groupPath
     * @param array $groupData
     * @param string $section
     * @param int $userId
     * @return void
     */
    private function processGroup(string $groupPath, array $groupData, string $section, int $userId): void
    {
        foreach ($groupData['fields'] ?? [] as $field => $data) {
            $configPath = "{$groupPath}/{$field}";
            $isSensitive = $this->isSensitiveField($configPath);

            if (!empty($data['inherit'])) {
                $oldValue = $this->getStoredValue($configPath);
                if ($oldValue === null) {
                    continue;
                }
                $newValue = $this->getSystemDefault($configPath);
                if ($oldValue === $newValue) {
                    continue;
                }
                if ($isSensitive) {
                    $oldValue = $oldValue === '' ? '' : '******';
                    $newValue = $newValue === '' ? '' : '******';
                }
                $this->saveTracker($section, $configPath, $userId, $oldValue, $newValue);
                continue;
            }
            if (!isset($data['value'])) {
                continue;
            }
            $isMultiselect = is_array($data['value']);
            $newValue = $this->normalizeConfigValue($data['value']);

            if ($isSensitive && preg_match('/^\*+$/', $newValue)) {
                continue;
            }

            $oldValue = (string)$this->scopeConfig->getValue($configPath);
            if ($isSensitive) {
                if ($oldValue === '' && $newValue === '') {
                    continue;
                }
                $oldValue = $oldValue === '' ? '' : '******';
                $newValue = $newValue === '' ? '' : '******';
            }

            if($isSensitive || !$this->isSameValue($oldValue, $newValue, $isMultiselect)) {
                $this->saveTracker($section, $configPath, $userId, $oldValue, $newValue);
            }
        }
        foreach ($groupData['groups'] ?? [] as $subGroupId => $subGroupData) {
            $this->processGroup("{$groupPath}/{$subGroupId}", $subGroupData, $section, $userId);
        }
    }

    /**
     * Captures the input config value for text and multiselect config fields.
     * @param string|array $value
     * @return string
     */
    private function normalizeConfigValue(string|array $value): string
    {
        if (!is_array($value)) {
            return $value;
        }
        $value = array_filter($value, function($v) {
            return $v !== '' && !is_null($v);
        });

        return implode(',', $value);
    }

    /**
     * Checks if the new config value is different compared with the old one.
     * @param string $oldValue
     * @param string $newValue
     * @param bool $isMultiselect
     * @return bool
     */
    private function isSameValue(string $oldValue, string $newValue, bool $isMultiselect): bool
    {
        if(!$isMultiselect) {
            return $oldValue === $newValue;
        }
        $old = $oldValue === '' ? [] : explode(',', $oldValue);
        $new = $newValue === '' ? [] : explode(',', $newValue);

        return $old === $new;
    }

    /**
     * Checks if the config field is a password/encrypted field.
     * @param string $configPath
     * @return bool
     */
    private function isSensitiveField(string $configPath): bool
    {
        $element = $this->configStructure->getElement($configPath);
        if (!$element instanceof Config\Structure\Element\Field) {
            return false;
        }
        $backend = $element->getData()['backend_model'] ?? '';
        return in_array($element->getType(), ['obscure', 'password'], true)
            || ($backend && is_a($backend, Config\Backend\Encrypted::class, true));
    }
}
