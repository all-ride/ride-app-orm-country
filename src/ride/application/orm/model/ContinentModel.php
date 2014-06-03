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
                        $continent = $this->createEntry();
                        $continent->setCode($continentCode);

                        $continents[$continentCode] = $continent;
                    }

                    $continent->setName($continentName);
                    $continent->setLocale($locale);

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
