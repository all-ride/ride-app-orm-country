<?php

namespace ride\application\orm\model;

use ride\application\orm\entry\CountryEntry;

/**
 * Country data container
 */
class Country extends CountryEntry {

    /**
     * Gets a string representation of this data
     * @return string
     */
    public function __toString() {
        return $this->getName() . ' (' . $this->getCode() . ')';
    }

}
