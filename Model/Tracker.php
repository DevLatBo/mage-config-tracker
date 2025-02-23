<?php

namespace Devlat\ConfigTracker\Model;

use Devlat\ConfigTracker\Api\Data\TrackerInterface;
use Magento\Framework\Model\AbstractModel;
use Devlat\ConfigTracker\Model\ResourceModel\Tracker as TrackerResourceModel;

class Tracker extends AbstractModel implements TrackerInterface
{

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(TrackerResourceModel::class);
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param $entityId
     * @return TrackerInterface
     */
    public function setEntityId($entityId): TrackerInterface
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param $name
     * @return TrackerInterface
     */
    public function setName($name): TrackerInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return bool
     */
    public function getVerified(): bool
    {
        return $this->getData(self::VERIFIED);
    }

    /**
     * @param $verified
     * @return TrackerInterface
     */
    public function setVerified($verified): TrackerInterface
    {
        return $this->setData(self::VERIFIED, $verified);
    }

    /**
     * @return \DateTime
     */
    public function getConfiguredAt(): \DateTime
    {
        return $this->getData(self::CONFIGURATED_AT);
    }

    /**
     * @param $configuredAt
     * @return TrackerInterface
     */
    public function setConfiguratedAt($configuredAt): TrackerInterface
    {
        return $this->setData(self::CONFIGURATED_AT, $configuredAt);
    }

    /**
     * @return \DateTime
     */
    public function getCheckedAt(): \DateTime
    {
        return $this->getData(self::CHECKED_AT);
    }

    /**
     * @param $checkedAt
     * @return TrackerInterface
     */
    public function setCheckedAt($checkedAt): TrackerInterface
    {
        return $this->setData(self::CHECKED_AT, $checkedAt);
    }
}
