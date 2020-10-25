<?php
/**
 * Koren Software
 *
 * @author     Koren Software
 * @copyright  Copyright (c) 2020 Koren Software. (https://koren.ee)
 * @license    MIT
 */

namespace Koren\EHAK;

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
        $this->assertSame('2020v3', $this->ehak->getVersion());
    }

    public function testCanSetCustomVersion()
    {
        $customVersionEhak = new EHAK('2019v6');
        $this->assertSame('2019v6', $customVersionEhak->getVersion());
    }

    public function testCanGetCountyByName()
    {
        $this->assertSame('0037', $this->ehak->getCode(EHAK::COUNTIES, 'EST', 'Harju maakond'));
    }

    public function testCanGetCountyByCode()
    {
        $this->assertSame('Harju maakond', $this->ehak->getLocation(EHAK::COUNTIES, '1', '0037'));
        $this->assertSame('Harju maakond', $this->ehak->getLocation(EHAK::COUNTIES, 'EST', '0037'));
    }

    public function testCanGetCityByName()
    {
        $this->assertSame('0784', $this->ehak->getCode(EHAK::CITIES, '0037', 'Tallinn'));
    }

    public function testCanGetCityByCode()
    {
        $this->assertSame('Tallinn', $this->ehak->getLocation(EHAK::CITIES, '0037', '0784'));
    }

    public function testCanGetParishByName()
    {
        $this->assertSame('0141', $this->ehak->getCode(EHAK::PARISHES, '0037', 'Anija vald'));
    }

    public function testCanGetParishByCode()
    {
        $this->assertSame('Anija vald', $this->ehak->getLocation(EHAK::PARISHES, '0037', '0141'));
    }

    public function testCanGetVillageByName()
    {
        $this->assertSame('1088', $this->ehak->getCode(EHAK::VILLAGES, '0141', 'Aegviidu alev'));
    }

    public function testCanGetVillageByCode()
    {
        $this->assertSame('Aegviidu alev', $this->ehak->getLocation(EHAK::VILLAGES, '0141', '1088'));
    }

    public function testCanGetCityDistrictByName()
    {
        $this->assertSame('0176', $this->ehak->getCode(EHAK::CITY_DISTRICTS, '0784', 'Haabersti linnaosa'));
    }

    public function testCanGetCityDistrictByCode()
    {
        $this->assertSame('Haabersti linnaosa', $this->ehak->getLocation(EHAK::CITY_DISTRICTS, '0784', '0176'));
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
        $this->assertSame('0176', $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => 'Tallinn',
            EHAK::CITY_DISTRICTS => 'Haabersti linnaosa',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ]));

        $this->assertSame('0784', $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => 'Tallinn',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => '',
            EHAK::VILLAGES => '',
        ]));

        $this->assertSame('0141', $this->ehak->getCodeFromFullLocation([
            EHAK::COUNTIES => 'Harju maakond',
            EHAK::CITIES => '',
            EHAK::CITY_DISTRICTS => '',
            EHAK::PARISHES => 'Anija vald',
            EHAK::VILLAGES => '',
        ]));

        $this->assertSame('0037', $this->ehak->getCodeFromFullLocation([
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
