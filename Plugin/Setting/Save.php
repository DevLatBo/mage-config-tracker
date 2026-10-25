<?php

namespace Devlat\Settings\Plugin\Setting;

use Devlat\Settings\Logger\Logger;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResource;
use Devlat\Settings\Model\Tracker;
use Devlat\Settings\Model\TrackerFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

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
     * Constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param TrackerFactory $trackerFactory
     * @param TrackerResource $trackerResource
     * @param Config\Structure $configStructure
     * @param Session $adminSession
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrackerFactory $trackerFactory,
        TrackerResource $trackerResource,
        Config\Structure $configStructure,
        Session $adminSession,
        Logger $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->trackerFactory = $trackerFactory;
        $this->trackerResource = $trackerResource;
        $this->adminSession = $adminSession;
        $this->logger = $logger;
        $this->configStructure = $configStructure;
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
            if (!isset($data['value'])) {
                continue;
            }
            // TODO: point 3, track switching to "Use system value".
            if (!empty($data['inherit'])) {
                continue;
            }
            $configPath = "{$groupPath}/{$field}";
            $isMultiselect = is_array($data['value']);
            $newValue = $this->normalizeConfigValue($data['value']);

            $isSensitive = $this->isSensitiveField($configPath);
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
