<?php

namespace Devlat\ConfigTracker\Api\Data;

interface TrackerInterface
{
    const ENTITY_ID = 'id';
    const NAME = 'name';
    const VERIFIED = 'verified';
    const CONFIGURATED_AT = 'configured_at';
    const CHECKED_AT = 'checked_at';

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @param $entityId
     * @return $this
     */
    public function setEntityId($entityId): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param $name
     * @return $this
     */
    public function setName($name): self;

    /**
     * @return bool
     */
    public function getVerified(): bool;

    /**
     * @param $verified
     * @return $this
     */
    public function setVerified($verified): self;

    /**
     * @return \DateTime
     */
    public function getConfiguredAt(): \DateTime;

    /**
     * @param $configuredAt
     * @return $this
     */
    public function setConfiguratedAt($configuredAt): self;

    /**
     * @return \DateTime
     */
    public function getCheckedAt(): \DateTime;

    /**
     * @param $checkedAt
     * @return $this
     */
    public function setCheckedAt($checkedAt): self;

}
