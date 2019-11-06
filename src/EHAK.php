<?php
/**
 * Koren Software
 *
 * @author     Koren Software
 * @copyright  Copyright (c) 2019 Koren Software. (https://koren.ee)
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
    protected $version = '2019v7';

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
     * @return string
     */
    public function getCode(string $arrayName, string $parentCode, string $locationName) : string
    {
        if (!isset($this->data[$arrayName]) ||
            !isset($this->data[$arrayName][$parentCode])
        ) {
            return 'unknown';
        }

        $data = $this->data[$arrayName][$parentCode];

        for ($i = 0; $i < count($data); ++$i) {
            if ($data[$i][1] == $locationName) {
                $k = $data[$i][0];
                return $k;
            }
        }

        return 'unknown';
    }

    /**
     * Get location from  array name and code
     *
     * @return string
     */
    public function getLocation(string $arrayName, string $parentCode, string $locationCode) : string
    {
        if (!isset($this->data[$arrayName]) ||
            !isset($this->data[$arrayName][$parentCode])
        ) {
            return 'unknown';
        }

        $data = $this->data[$arrayName][$parentCode];

        for ($i = 0; $i < count($data); ++$i) {
            if ($data[$i][0] == $locationCode) {
                $k = $data[$i][1];
                return $k;
            }
        }

        return 'unknown';
    }

    /**
     * Get full location from EHAK code
     *
     * @param string $ehakCode
     * 
     * @return array
     */
    public function getFullLocation(string $ehakCode) : array
    {
        $searchKeys = [
            self::COUNTIES,
            self::CITIES,
            self::CITY_DISTRICTS,
            self::PARISHES,
            self::VILLAGES,
        ];

        foreach ($searchKeys as $searchKey) {
            foreach ($this->data[$searchKey] as $item) {

            }
        }

        return [
            self::COUNTIES => '',
            self::CITIES => '',
            self::CITY_DISTRICTS => '',
            self::PARISHES => '',
            self::VILLAGES => '',
        ];
    }
}
