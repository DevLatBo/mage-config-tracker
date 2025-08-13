<?php

namespace Devlat\Settings\Model;

use Devlat\Settings\Api\Data\TrackerInterface;
use Magento\Framework\Model\AbstractModel;
use Devlat\Settings\Model\ResourceModel\Tracker as TrackerResourceModel;

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
    public function getSection(): string
    {
        return $this->getData(self::SECTION);
    }

    /**
     * @param $section
     * @return TrackerInterface
     */
    public function setSection($section): TrackerInterface
    {
        return $this->setData(self::SECTION, $section);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->getData(self::PATH);
    }

    /**
     * @param string $path
     * @return TrackerInterface
     */
    public function setPath($path): TrackerInterface
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * @return string
     */
    public function getConfiguratedBy(): string
    {
        return $this->getData(self::CONFIGURATED_BY);
    }

    /**
     * @param $configuratedBy
     * @return TrackerInterface
     */
    public function setConfiguratedBy($configuratedBy): TrackerInterface
    {
        return $this->setData(self::CONFIGURATED_BY, $configuratedBy);
    }

    public function getOldValue(): string
    {
        return $this->getData(self::OLD_VALUE);
    }

    public function setOldValue($oldValue): TrackerInterface
    {
        return $this->setData(self::OLD_VALUE, $oldValue);
    }

    public function getNewValue(): string
    {
        return $this->getData(self::NEW_VALUE);
    }

    public function setNewValue($newValue): TrackerInterface
    {
        return $this->setData(self::NEW_VALUE, $newValue);
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
     * @return string
     */
    public function getVerifiedBy(): string
    {
        return $this->getData(self::VERIFIED_BY);
    }

    /**
     * @param $verifiedBy
     * @return TrackerInterface
     */
    public function setVerifiedBy($verifiedBy): TrackerInterface
    {
        return $this->setData(self::VERIFIED_BY, $verifiedBy);
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
