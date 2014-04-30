<?php

namespace ride\application\orm\model;

use ride\library\orm\model\GenericModel;
use ride\library\system\file\File;

use \Exception;

/**
 * Continent model
 */
class ContinentModel extends GenericModel {

    /**
     * Initializes the save stack
     * @return null
     */
    protected function initialize() {
        $this->dataListDepth = 0;

        parent::initialize();
    }

    /**
     * Gets a list of the data in this model, useful for eg. select fields
     * @param array $options Options for the query
     * @return array Array with the id of the data as key and a string
     * representation as value
     * @see getDataListQuery
     */
    public function getDataList(array $options = null) {
        if (!isset($options['order']['field'])) {
            $options['order']['field'] = 'name';
        }

        return parent::getDataList($options);
    }

    /**
     * Gets all the continents
     * @param string $locale
     * @param integer $recursiveDepth
     * @param boolean $includeUnlocalized
     * @return array
     */
    public function getContinents($locale = null, $recursiveDepth = 0, $includeUnlocalized = true) {
        $query = $this->createQuery($locale);
        $query->setRecursiveDepth($recursiveDepth);
        $query->setFetchUnlocalizedData($includeUnlocalized);
        $query->addOrderBy('{name} ASC');

        return $query->query();
    }

    /**
     * Installs the continents from a data directory
     * @param \ride\library\system\file\File $path Path for the data files
     * @param array $locales Array with locale codes
     * @return array Array with the continent code as key and the continent data
     * as value
     */
    public function installContinents(File $path, array $locales) {
        $query = $this->createQuery();
        $query->setRecursiveDepth(0);

        $continents = $query->query('code');

        $transactionStarted = $this->beginTransaction();
        try {
            foreach ($locales as $locale) {
                $file = $path->getChild($locale . '.ini');
                if (!$file->exists()) {
                    continue;
                }

                $continentNames = parse_ini_file($file->getPath(), false, INI_SCANNER_RAW);

                foreach ($continentNames as $continentCode => $continentName) {
                    if (isset($continents[$continentCode])) {
                        $continent = $continents[$continentCode];
                    } else {
                        $continent = $this->createData();
                        $continent->code = $continentCode;

                        $continents[$continentCode] = $continent;
                    }

                    $continent->name = $continentName;
                    $continent->dataLocale = $locale;

                    $this->save($continent);
                }
            }

            $this->commitTransaction($transactionStarted);
        } catch (Exception $exception) {
            $this->rollbackTransaction($transactionStarted);

            throw $exception;
        }

        return $continents;
    }

}
