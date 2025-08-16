<?php

namespace Devlat\Settings\Api\Data;

interface TrackerInterface
{
    const ENTITY_ID = 'id';
    const SECTION = 'section';
    const PATH = 'path';
    const CONFIGURATED_BY = 'configurated_by';
    const OLD_VALUE = 'old_value';
    const NEW_VALUE = 'new_value';
    const VERIFIED = 'verified';
    const VERIFIED_BY = 'verified_by';
    const CONFIGURATED_AT = 'configured_at';
    const CHECKED = 'checked';

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
    public function getSection(): string;

    /**
     * @param $section
     * @return $this
     */
    public function setSection($section): self;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path): self;

    /**
     * @return string
     */
    public function getConfiguratedBy(): string;

    /**
     * @param $configuratedBy
     * @return $this
     */
    public function setConfiguratedBy($configuratedBy): self;
    /**
     * @return string
     */
    public function getOldValue(): string;

    /**
     * @param $oldValue
     * @return $this
     */
    public function setOldValue($oldValue): self;

    /**
     * @return string
     */
    public function getNewValue(): string;

    /**
     * @param $newValue
     * @return $this
     */
    public function setNewValue($newValue): self;

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
     * @return string | null
     */
    public function getVerifiedBy(): string | null;

    /**
     * @param $verifiedBy
     * @return $this
     */
    public function setVerifiedBy($verifiedBy): self;

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
    public function getChecked(): \DateTime;

    /**
     * @param $checked
     * @return $this
     */
    public function setChecked($checked): self;

}
