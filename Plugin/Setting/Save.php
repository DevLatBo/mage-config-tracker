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
     * Constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param TrackerFactory $trackerFactory
     * @param TrackerResource $trackerResource
     * @param Session $adminSession
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrackerFactory $trackerFactory,
        TrackerResource $trackerResource,
        Session $adminSession,
        Logger $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->trackerFactory = $trackerFactory;
        $this->trackerResource = $trackerResource;
        $this->adminSession = $adminSession;
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

        $section = $subject->getSection();
        $groups = $subject->getGroups();

        $userId = $this->adminSession->getUser()->getId();

        $this->logger->info("Tracking config updates...");

        foreach ($groups as $group => $fields) {
            foreach ($fields['fields'] as $field => $data) {
                if (!isset($data['value'])) {
                    continue;
                }
                $configPath = "{$section}/{$group}/{$field}";
                $newValue = $data['value'];

                // TODO: Fix in order to cover errors based on the multiselect fields.
                if (is_array($newValue)) {
                    continue;
                }
                // TODO: Fix in order to cover errors based on password fields.
                if (preg_match('/^\*+$/', (string)$newValue)) {
                    continue;
                }

                $oldValue = (string)$this->scopeConfig->getValue($configPath);
                if($oldValue != $newValue) {
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
        }
    }
}
