<?php

namespace ride\application\orm\model;

use \ride\application\orm\entry\ContinentEntry;

/**
 * Continent data container
 */
class Continent extends ContinentEntry {

    /**
     * Gets a string representation of this continent
     * @return string
     */
    public function __toString() {
        return $this->getName();
    }

}
