<?php

namespace Devlat\Settings\Model\Service;

use Devlat\Settings\Logger\Logger;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResourceModel;
use Devlat\Settings\Model\Tracker;
use Devlat\Settings\Model\TrackerFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Verifier
{
    /**
     * @var TrackerFactory
     */
    private TrackerFactory $trackerFactory;
    /**
     * @var TrackerResourceModel
     */
    private TrackerResourceModel $trackerResourceModel;
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor.
     * @param TrackerFactory $trackerFactory
     * @param TrackerResourceModel $trackerResourceModel
     * @param Logger $logger
     */
    public function __construct(
        TrackerFactory $trackerFactory,
        TrackerResourceModel $trackerResourceModel,
        Logger $logger
    )
    {
        $this->trackerFactory = $trackerFactory;
        $this->trackerResourceModel = $trackerResourceModel;
        $this->logger = $logger;
    }

    /**
     * Marks the tracked config as verified and increases the counter of the given admin user.
     * @param int $trackerId
     * @param int $userId
     * @return Tracker
     * @throws NoSuchEntityException
     */
    public function verify(int $trackerId, int $userId): Tracker
    {
        /** @var Tracker $tracker */
        $tracker = $this->trackerFactory->create();
        $this->trackerResourceModel->load($tracker, $trackerId);

        if (!$tracker->getId()) {
            $this->logger->error(__("Config tracked not found"));
            throw new NoSuchEntityException(__('Config tracked requested not found.'));
        }

        if ($tracker->getVerified()) {
            $this->logger->alert(
                __('Config tracked with ID: %1 has been updated before.', $tracker->getId())
            );
        } else {
            $tracker->setVerified(1);
        }

        $verifiedByData     =   $tracker->getVerifiedBy();
        $arrayVerifiedBy    =   !empty($verifiedByData)
            ? json_decode($verifiedByData, true)
            : [];

        $entry = $arrayVerifiedBy[$userId] ?? array('counter' => 0);
        $entry['counter']++;

        $arrayVerifiedBy = array($userId => $entry) + $arrayVerifiedBy;

        $tracker->setVerifiedBy(json_encode($arrayVerifiedBy, JSON_FORCE_OBJECT));
        $this->trackerResourceModel->save($tracker);

        $this->logger->info(__('Config tracked verified, ID: %1 ', $tracker->getId()));

        return $tracker;
    }
}
