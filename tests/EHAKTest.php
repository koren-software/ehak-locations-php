<?php
/**
 * Koren Software
 *
 * @author     Koren Software
 * @copyright  Copyright (c) 2020 Koren Software. (https://koren.ee)
 * @license    MIT
 */

use Koren\EHAK\EHAK;
use PHPUnit\Framework\TestCase;

class EHAKTest extends TestCase
{
    /**
     * EHAK object
     * 
     * @var \Koren\EHAK\EHAK
     */
    protected $ehak;

    /**
     * Set test data.
     */
    public function setUp() : void
    {
        $this->ehak = new EHAK();
    }

    public function testInitsDefaultVersion()
    {
        $this->assertEquals('2019v8', $this->ehak->getVersion());
    }

    public function testCanSetCustomVersion()
    {
        $customVersionEhak = new EHAK('2019v6');
        $this->assertEquals('2019v6', $customVersionEhak->getVersion());
    }

    public function testCanGetCountyByName()
    {
        $this->assertEquals('0037', $this->ehak->getCode(EHAK::COUNTIES, 'EST', 'Harju maakond'));
    }

    public function testCanGetCountyByCode()
    {
        $this->assertEquals('Harju maakond', $this->ehak->getLocation(EHAK::COUNTIES, '1', '0037'));
        $this->assertEquals('Harju maakond', $this->ehak->getLocation(EHAK::COUNTIES, 'EST', '0037'));
    }

    public function testCanGetCityByName()
    {
        $this->assertEquals('0784', $this->ehak->getCode(EHAK::CITIES, '0037', 'Tallinn'));
    }

    public function testCanGetCityByCode()
    {
        $this->assertEquals('Tallinn', $this->ehak->getLocation(EHAK::CITIES, '0037', '0784'));
    }

    public function testCanGetParishByName()
    {
        $this->assertEquals('0141', $this->ehak->getCode(EHAK::PARISHES, '0037', 'Anija vald'));
    }

    public function testCanGetParishByCode()
    {
        $this->assertEquals('Anija vald', $this->ehak->getLocation(EHAK::PARISHES, '0037', '0141'));
    }

    public function testCanGetVillageByName()
    {
        $this->assertEquals('1088', $this->ehak->getCode(EHAK::VILLAGES, '0141', 'Aegviidu alev'));
    }

    public function testCanGetVillageByCode()
    {
        $this->assertEquals('Aegviidu alev', $this->ehak->getLocation(EHAK::VILLAGES, '0141', '1088'));
    }

    public function testCanGetCityDistrictByName()
    {
        $this->assertEquals('0176', $this->ehak->getCode(EHAK::CITY_DISTRICTS, '0784', 'Haabersti linnaosa'));
    }

    public function testCanGetCityDistrictByCode()
    {
        $this->assertEquals('Haabersti linnaosa', $this->ehak->getLocation(EHAK::CITY_DISTRICTS, '0784', '0176'));
    }

    public function testCanGetFullLocationByVillageCode()
    {
        $this->assertEquals([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => '',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => 'Anija vald',
            EHAK::VILLAGES => 'Aegviidu alev',
        ], $this->ehak->getFullLocation('1088'));
    }

    public function testCanGetFullLocationByCityDistrictCode()
    {
        $this->assertEquals([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => 'Tallinn',
            EHAK::CITY_DISTRICTS => 'Haabersti linnaosa',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ], $this->ehak->getFullLocation('0176'));
    }

    public function testCanGetCodeFromFullLocation()
    {
        $this->assertEquals('0176', $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => 'Tallinn',
            EHAK::CITY_DISTRICTS => 'Haabersti linnaosa',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ]));

        $this->assertEquals('0784', $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => 'Tallinn',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ]));

        $this->assertEquals('0141', $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => '',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => 'Anija vald',
            EHAK::VILLAGES => '',
        ]));

        $this->assertEquals('0037', $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => '',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ]));

        $this->assertEquals(null, $this->ehak->getCodeFromFullLocation([]));
        $this->assertEquals(null, $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Random county',
            EHAK::CITIES => 'Random city',
            EHAK::CITY_DISTRICTS => 'Random city district',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ]));
    }

    public function testCanGetFullLocationByCityCode()
    {
        $this->assertEquals([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => 'Tallinn',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ], $this->ehak->getFullLocation('0784'));
    }

    public function testCanGetFullLocationByParishCode()
    {
        $this->assertEquals([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => '',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => 'Anija vald',
            EHAK::VILLAGES => '',
        ], $this->ehak->getFullLocation('0141'));
    }

    public function testCanGetFullLocationByCountyCode()
    {
        $this->assertEquals([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => '',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ], $this->ehak->getFullLocation('0037'));
    }

    public function testUnknownResults()
    {
        $this->assertEquals(null, $this->ehak->getLocation(EHAK::COUNTIES, '1', '1'));
        $this->assertEquals(null, $this->ehak->getLocation(EHAK::CITIES, '1', '1'));
        $this->assertEquals(null, $this->ehak->getLocation(EHAK::PARISHES, '1', '1'));
        $this->assertEquals(null, $this->ehak->getLocation(EHAK::VILLAGES, '1', '1'));
        $this->assertEquals(null, $this->ehak->getLocation(EHAK::CITY_DISTRICTS, '1', '1'));

        $this->assertEquals(null, $this->ehak->getCode(EHAK::COUNTIES, '1', 'Unknwon place'));
        $this->assertEquals(null, $this->ehak->getCode(EHAK::CITIES, '1', 'Unknwon place'));
        $this->assertEquals(null, $this->ehak->getCode(EHAK::PARISHES, '1', 'Unknwon place'));
        $this->assertEquals(null, $this->ehak->getCode(EHAK::VILLAGES, '1', 'Unknwon place'));
        $this->assertEquals(null, $this->ehak->getCode(EHAK::CITY_DISTRICTS, '1', 'Unknwon place'));

        $this->assertEquals(null, $this->ehak->getFullLocation('nonExistingCode'));
    }
}