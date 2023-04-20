#!/usr/local/bin/php
<?php
use Koren\EHAK\EHAK;
use Shuchkin\SimpleXLSX;

include_once dirname(__FILE__).'/../vendor/autoload.php';

$options = getopt("p:o::d::", [
    'path:',
    'output::',
    'debug::'
]);

$ehakPath = $options['path'] ?? $options['p'] ?? false;
$outputFile = $options['output'] ?? $options['o'] ?? dirname(dirname(__FILE__)).'/src/data/{EHAK_VERSION}.php';
$debug = isset($options['debug']) || isset($options['d']) ? true :  false;

if (!$ehakPath) {
    die('Please set EHAK XLSX path with --path or -p option.');
}

if (!defined('DEBUG')) {
    define('DEBUG', $debug);
}

/**
 * Detect item type
 *
 * @param object $item
 *
 * @return string
 */
function type($item)
{
    $types = [
        0 => EHAK::COUNTIES,
        1 => EHAK::PARISHES,
        3 => EHAK::VILLAGES,
        4 => EHAK::CITIES,
        5 => EHAK::VILLAGES,
        6 => EHAK::CITY_DISTRICTS,
        7 => EHAK::VILLAGES,
        8 => EHAK::VILLAGES,
    ];

    return $types[$item['Tüüp']];
}

/**
 * Log based on debug flag
 *
 * @param string $str String to log
 *
 * @return void
 */
function debug($str)
{
    if (DEBUG) {
        echo $str.PHP_EOL;
    }
}

if ($xlsx = SimpleXLSX::parseFile($ehakPath, true)) {
    $headerValues = $rows = [];
    $ehakVersion = null;
    foreach ($xlsx->rows() as $k => $r) {
        if ($k === 0) {
            $firstLine = explode(' ', $r[0]);
            $firstLineLastWord = end($firstLine);
            $ehakVersion = str_replace('EHAK', '', $firstLineLastWord);
        } elseif ($k === 3) {
            $headerValues = $r;
            continue;
        } elseif ($k > 3) {
            $rows[] = array_combine($headerValues, $r);
        }
    }
} else {
    echo SimpleXLSX::parseError();
}

// Location arrays
$counties = $cities = $city_districts = $parishes = $villages = [];

if (count($rows) > 0) {
    foreach ($rows as $row) {
        $type = type($row);

        if ($type === EHAK::COUNTIES) {
            ${$type}['EST'][] = [(string)$row['Kood'], $row['Nimi']];
            ${$type}[1][] = [(string)$row['Kood'], $row['Nimi']];
        } else {
            $countyId = $row['Vald'] !== '0000' ? $row['Vald'] : $row['Maakond'];
            ${$type}[$countyId][] = [(string)$row['Kood'], $row['Nimi']];
        }
    }

    $counties = var_export($counties, true);
    $ehakContents[] = '"'.EHAK::COUNTIES.'" => '.$counties;

    $cities = var_export($cities, true);
    $ehakContents[] .= '"'.EHAK::CITIES.'" => '.$cities;

    $parishes = var_export($parishes, true);
    $ehakContents[] .= '"'.EHAK::PARISHES.'" => '.$parishes;

    $villages = var_export($villages, true);
    $ehakContents[] .= '"'.EHAK::VILLAGES.'" => '.$villages;

    $city_districts = var_export($city_districts, true);
    $ehakContents[] .= '"'.EHAK::CITY_DISTRICTS.'" => '.$city_districts;

    // Replace EHAK version variable
    $outputFile = str_replace(
        '{EHAK_VERSION}',
        $ehakVersion,
        $outputFile
    );

    $ehakContents = implode(",\n", $ehakContents);

    $result = file_put_contents($outputFile, "<?php\nreturn [".$ehakContents."];");

    if ($result) {
        echo 'EHAK PHP data saved to '.basename($outputFile).PHP_EOL;
    } else {
        echo 'Failed to save EHAK data.'.PHP_EOL;
    }
}
