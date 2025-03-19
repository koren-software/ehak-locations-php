<?php
/**
 * Koren Software
 *
 * @author     Koren Software
 * @copyright  Copyright (c) 2020 Koren Software. (https://koren.ee)
 * @license    MIT
 */

namespace Koren\EHAK;

class EHAK
{
    /**
     * Location array keys
     */
    const COUNTIES = 'counties';
    const CITIES = 'cities';
    const PARISHES = 'parishes';
    const VILLAGES = 'villages';
    const CITY_DISTRICTS = 'city_districts';

    /**
     * Default version to use if version is not set
     */
    protected $version = '2025v1';

    /**
     * EHAK data
     */
    protected $data = [];

    /**
     * Constructor
     */
    public function __construct(?string $version = null, ?string $file = null)
    {
        if (!is_null($version)) {
            $this->version = $version;
        }

        // Get data
        $file = $file ?? dirname(__FILE__).'/data/'.$this->version.'.php';
        $this->data = include $file;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * Get code from array name and location name
     *
     * @return string|null
     */
    public function getCode(string $arrayName, string $parentCode, string $locationName) : ?string
    {
        if (!isset($this->data[$arrayName]) ||
            !isset($this->data[$arrayName][$parentCode])
        ) {
            return null;
        }

        $data = $this->data[$arrayName][$parentCode];

        for ($i = 0; $i < count($data); ++$i) {
            if ($data[$i][1] == $locationName) {
                $k = $data[$i][0];
                return $k;
            }
        }

        return null;
    }

    /**
     * Get location from  array name and code
     *
     * @return string|null
     */
    public function getLocation(string $arrayName, string $parentCode, string $locationCode) : ?string
    {
        if (!isset($this->data[$arrayName]) ||
            !isset($this->data[$arrayName][$parentCode])
        ) {
            return null;
        }

        $data = $this->data[$arrayName][$parentCode];

        for ($i = 0; $i < count($data); ++$i) {
            if ($data[$i][0] == $locationCode) {
                $k = $data[$i][1];
                return $k;
            }
        }

        return null;
    }

    /**
     * Get full location from EHAK code
     *
     * @param string $ehakCode
     *
     * @return array|null
     */
    public function getFullLocation(string $ehakCode) : ?array
    {
        $location = [
            self::COUNTIES => '',
            self::CITIES => '',
            self::CITY_DISTRICTS => '',
            self::PARISHES => '',
            self::VILLAGES => '',
        ];

        $parentKeys = [];
        $found = false;
        $parentSearchKey = null;
        $foundCounty = false;

        foreach (array_keys($location) as $searchKey) {
            foreach ($this->data[$searchKey] as $parentKey => $item) {
                foreach ($item as $parentCode => $child) {
                    if ($child[0] === $ehakCode) {
                        $location[$searchKey] = $child[1];
                        $found = true;
                        break;
                    }
                }

                if ($found && $searchKey !== self::COUNTIES) {
                    switch ($searchKey) {
                        case self::VILLAGES:
                            $parentSearchKey = self::PARISHES;
                            $parentKeys[$parentSearchKey] = $parentKey;
                            break;
                        case self::CITY_DISTRICTS:
                            $parentSearchKey = self::CITIES;
                            $parentKeys[$parentSearchKey] = $parentKey;
                            break;
                        default:
                            // Nothing
                            break;
                    }

                    break;
                }
            }

            // Add city or parish
            if ($found && $parentSearchKey) {
                $fountCityParish = false;
                foreach ($this->data[$parentSearchKey] as $parentCode => $childs) {
                    foreach ($childs as $child) {
                        if ((string)$child[0] === (string)$parentKey) {
                            $location[$parentSearchKey] = $child[1];
                            $parentKey = $parentCode;
                            $fountCityParish = true;
                            break;
                        }
                    }

                    if ($fountCityParish) {
                        break;
                    }
                }
            }

            if ($found) {
                // Add county
                foreach ($this->data[self::COUNTIES] as $parentCode => $childs) {
                    foreach ($childs as $child) {
                        if ((string)$child[0] === (string)$parentKey) {
                            $location[self::COUNTIES] = $child[1];
                            $foundCounty = true;
                            break;
                        }
                    }

                    if ($foundCounty) {
                        break;
                    }
                }
            }

            if ($found || $foundCounty) {
                break;
            }
        }

        if (!$found) {
            return null;
        }

        return $location;
    }

    /**
     * Undocumented function
     *
     * @param array $location
     * @return string|null
     */
    public function getCodeFromFullLocation(array $location) : ?string
    {
        if (empty($location)) {
            return null;
        }

        // Make sure it's in correct order
        // Remove empty values
        $orderedLocation = array_filter([
            self::COUNTIES => $location[self::COUNTIES] ?? null,
            self::CITIES => $location[self::CITIES] ?? null,
            self::CITY_DISTRICTS => $location[self::CITY_DISTRICTS] ?? null,
            self::PARISHES => $location[self::PARISHES] ?? null,
            self::VILLAGES => $location[self::VILLAGES] ?? null,
        ]);

        $parentCode = '1';
        foreach ($orderedLocation as $locationKey => $locationName) {
            $parentCode = $this->getCode($locationKey, (string)$parentCode, (string)$locationName);
        }

        return $parentCode;
    }
}
