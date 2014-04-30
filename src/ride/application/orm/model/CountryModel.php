<?php

namespace ride\application\orm\model;

use ride\library\orm\model\GenericModel;
use ride\library\system\file\File;

use \Exception;

/**
 * Country model
 */
class CountryModel extends GenericModel {

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
     * Gets all the countries
     * @param boolean $recursiveDepth
     * @param string $locale
     * @param boolean $includeUnlocalized
     * @return array
     */
    public function getCountries($recursiveDepth = 0, $locale = null, $includeUnlocalized = true) {
        $query = $this->createQuery($locale);
        $query->setRecursiveDepth($recursiveDepth);
        $query->setIncludeUnlocalizedData($includeUnlocalized);
        $query->addOrderBy('{name} ASC');

        return $query->query();
    }

    /**
     * Gets a country by it's code
     * @param string $code
     * @param integer $recursiveDepth
     * @param string $locale
     * @param boolean $includeUnlocalized
     * @return Country|null
     */
    public function getByCode($code, $recursiveDepth = 0, $locale = null, $includeUnlocalized = true) {
        $query = $this->createQuery($locale);
        $query->setRecursiveDepth($recursiveDepth);
        $query->setIncludeUnlocalizedData($includeUnlocalized);
        $query->addCondition('{code} = %1%', $code);

        return $query->queryFirst();
    }

    /**
     * Installs the countries from a data directory.
     * @param \ride\library\system\file\File $path Path to the data files of the
     * countries
     * @param array $locales Array with locale codes
     * @param array $continents Array with the installed continents
     * @return null
     */
    public function installCountries(File $path, array $locales, array $continents) {
        $continentCountryCodes = $this->getContinentCountryCodes();

        $query = $this->createQuery();
        $query->setRecursiveDepth(0);

        $countries = $query->query('code');

        $transactionStarted = $this->beginTransaction();
        try {
            foreach ($locales as $locale) {
                $file = $path->getChild($locale . '.ini');
                if (!$file->exists()) {
                    continue;
                }

                $countryNames = $this->readCountries($file);

                foreach ($countryNames as $countryCode => $countryName) {
                    if (isset($countries[$countryCode])) {
                        $country = $countries[$countryCode];
                    } else {
                        $country = $this->createData();
                        $country->code = $countryCode;
                        $country->continent = $this->getContinentForCountry($continentCountryCodes, $continents, $countryCode);

                        $countries[$countryCode] = $country;
                    }

                    $country->name = $countryName;
                    $country->dataLocale = $locale;

                    $this->save($country);
                }
            }

            $this->commitTransaction($transactionStarted);
        } catch (Exception $e) {
            $this->rollbackTransaction($transactionStarted);

            throw $e;
        }
    }

    /**
     * Gets the id of the continent for the provided country
     * @param array $continentCountryCodes Array with the continent code as key and an array with country codes as value
     * @param array $continents Array with the continent code as key and continent data objects as value
     * @param string $countryCode Code of the country
     * @return integer Primary key of the continent if found, 0 otherwise
     */
    private function getContinentForCountry(array $continentCountryCodes, array $continents, $countryCode) {
        foreach ($continentCountryCodes as $continentCode => $continentCountries) {
            if (in_array($countryCode, $continentCountries) && isset($continents[$continentCode])) {
                return $continents[$continentCode]->id;
            }
        }

        return null;
    }

    /**
     * Reads the countries from the provided file
     * @param \ride\library\system\file\File $file
     * @return array Array with the country code as key and the name as value
     */
    protected function readCountries(File $file) {
        $countries = array();

        $content = $file->read();

        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }

            list($code, $name) = explode('=', $line, 2);

            $code = trim($code);
            $name = trim($name);

            $name = ltrim(rtrim($name, '"'), '"');

            $countries[$code] = $name;
        }

        return $countries;
    }

    /**
     * Gets an array with an overview of the continents and their countries
     * @return array Array with the code of the continent as key and an array
     * with the codes of the countries as value
     */
    protected function getContinentCountryCodes() {
        return array(
            'AFRICA' => array (
                'AO', 'BF', 'BI', 'BJ', 'BW', 'CF', 'CG', 'CI', 'CM', 'CV', 'DJ', 'DZ', 'EG', 'EH',
                'ER', 'ET', 'GA', 'GH', 'GM', 'GN', 'GQ', 'GW', 'KE', 'KM', 'LS', 'LR', 'LY', 'MA',
                'ML', 'MG', 'MR', 'MU', 'MW', 'MZ', 'NA', 'NE', 'NG', 'RE', 'SL', 'ST', 'RW', 'SC',
                'SD', 'SH', 'SN', 'SO', 'SZ', 'TD', 'TG', 'TN', 'TZ', 'UG', 'YT', 'ZA', 'ZM', 'ZR',
                'ZW',
            ),
            'ANTARCTICA' => array (
                'AQ', 'BV', 'GS', 'HM', 'TF',
            ),
            'ASIA' => array (
                'AE', 'AF', 'BD', 'BH', 'BN', 'BT', 'CN', 'HK', 'ID', 'IL', 'IN', 'IO', 'IQ', 'IR',
                'JO', 'JP', 'KG', 'KH', 'KP', 'KR', 'KW', 'KZ', 'LA', 'LB', 'LK', 'MM', 'MN', 'MO',
                'MV', 'MY', 'NP', 'OM', 'PH', 'PK', 'QA', 'SA', 'SG', 'SU', 'SY', 'TH', 'TJ', 'TM',
                'TP', 'TW', 'UZ', 'VN', 'YE',
            ),
            'EUROPE' => array (
                'AD', 'AL', 'AM', 'AT', 'AZ', 'BA', 'BE', 'BG', 'BY', 'CH', 'CS', 'CY', 'CZ', 'DE',
                'DK', 'EE', 'ES', 'FI', 'FO', 'FR', 'FX', 'GB', 'GE', 'GI', 'GR', 'HR', 'HU', 'IE',
                'IS', 'IT', 'LI', 'LT', 'LU', 'LV', 'MC', 'MD', 'MK', 'MT', 'NL', 'NO', 'PL', 'PT',
                'RO', 'RU', 'SE', 'SJ', 'SK', 'SM', 'SI', 'TR', 'UA', 'UK', 'VA', 'YU',
            ),
            'NORTH_AMERICA' => array (
                'AG', 'AI', 'AN', 'AW', 'BB', 'BM', 'BS', 'BZ', 'CA', 'CR', 'CU', 'DM', 'DO', 'GD',
                'GL', 'GP', 'GT', 'HN', 'HT', 'JM', 'KN', 'KY', 'LC', 'MQ', 'MS', 'MX', 'NI', 'PA',
                'PM', 'PR', 'TT', 'TC', 'SV', 'UM', 'US', 'VC', 'VG', 'VI',
            ),
            'OCEANIA' => array (
                'AS', 'AU', 'CC', 'CK', 'CX', 'FJ', 'FM', 'GU', 'KI', 'MH', 'MP', 'NC', 'NF', 'NR',
                'NU', 'NZ', 'PW', 'PF', 'PG', 'PN', 'SB', 'TK', 'TO', 'TV', 'VU', 'WF', 'WS',
            ),
            'SOUTH_AMERICA' => array (
                'AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PE', 'PY', 'SR', 'UY', 'VE',
            ),
        );
    }

}
