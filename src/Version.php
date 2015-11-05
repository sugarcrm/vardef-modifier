<?php

namespace DRI\SugarCRM\VardefModifier;

/**
 * @author Emil Kilhage
 */
class Version
{
    /**
     * @var int
     */
    private $majorVersion;

    /**
     * @var int
     */
    private $minorVersion;

    /**
     * @var int
     */
    private $maintenanceVersion;

    /**
     * @return int
     */
    public function getMajorVersion()
    {
        $this->set();

        return $this->majorVersion;
    }

    /**
     * @return int
     */
    public function getMinorVersion()
    {
        $this->set();

        return $this->minorVersion;
    }

    /**
     * @return int
     */
    public function getMaintenanceVersion()
    {
        $this->set();

        return $this->maintenanceVersion;
    }

    /**
     *
     */
    private function set()
    {
        global $sugar_version;

        if (null !== $this->majorVersion) {
            return;
        }

        $numbers = explode('.', $sugar_version);

        $this->majorVersion = !empty($numbers[0]) ? (int) $numbers[0] : 0;
        $this->minorVersion = !empty($numbers[1]) ? (int) $numbers[1] : 0;
        $this->maintenanceVersion = !empty($numbers[2]) ? (int) $numbers[2] : 0;
    }
}
