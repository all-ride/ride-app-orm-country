<?php

namespace ride\application\orm\model;

use \ride\library\orm\model\data\Data;

/**
 * Continent data container
 */
class Continent extends Data {

    /**
     * Code of the continent
     * @var string
     */
    public $code;

    /**
     * Name of the continent
     * @var string
     */
    public $name;

    /**
     * Array with Country objects representing the countries in the continent
     * @var Array
     */
    public $countries;

    /**
     * Gets a string representation of this continent
     * @return string
     */
    public function __toString() {
        return $this->name;
    }

}
