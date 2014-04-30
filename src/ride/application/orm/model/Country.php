<?php

namespace ride\application\orm\model;

use ride\library\orm\model\data\Data;

/**
 * Country data container
 */
class Country extends Data {

    /**
     * Code of the country
     * @var string
     */
    public $code;

    /**
     * Name of the country
     * @var string
     */
    public $name;

    /**
     * Continent of the country
     * @var Continent|integer
     */
    public $continent;

    /**
     * Gets a string representation of this data
     * @return string
     */
    public function __toString() {
        return $this->name . ' (' . $this->code . ')';
    }

}
