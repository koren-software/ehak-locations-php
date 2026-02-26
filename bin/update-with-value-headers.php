#!/usr/local/bin/php
<?php
use Koren\EHAK\EHAK;
use Shuchkin\SimpleXLSX;

include_once dirname(__FILE__).'/../vendor/autoload.php';

$options = getopt("p:o::v::d::", [
    'path:',
    'output::',
    'version::',
    'debug::'
]);

$ehakPath = $options['path'] ?? $options['p'] ?? false;
$outputFile = $options['output'] ?? $options['o'] ?? dirname(dirname(__FILE__)).'/src/data/{EHAK_VERSION}.php';
$ehakVersion = $options['version'] ?? $options['v'] ?? null;
$debug = isset($options['debug']) || isset($options['d']) ? true : false;

if (!$ehakPath) {
    die('Please set EHAK XLSX path with --path or -p option.');
}

if (!defined('DEBUG')) {
    define('DEBUG', $debug);
}

// Try to extract version from filename if not provided
if (!$ehakVersion) {
    $filename = pathinfo($ehakPath, PATHINFO_FILENAME);
    if (preg_match('/^(\d{4}v\d+)$/', $filename, $matches)) {
        $ehakVersion = $matches[1];
    } else {
        die('Could not determine EHAK version from filename. Please set it with --version or -v option.');
    }
}

/**
 * Detect item type from name and level
 *
 * New XLSX columns: Value, Label-et-EE, Label-en-GB, Label-ru-RU,
 * Parent, Level, Description-*, IsGenerated, IsValid, ValidFrom, ValidTo
 *
 * Level 1 = County (maakond)
 * Level 2 = Municipality: Parish (vald) or City (linn)
 * Level 3 = Settlement: Village (küla/alevik/alev), City (linn), City district (linnaosa)
 *
 * @param string $name Estonian name (Label-et-EE)
 * @param int    $level Level value (1, 2, or 3)
 *
 * @return string
 */
function type($name, $level)
{
    if ($level == 1) {
        return EHAK::COUNTIES;
    }

    if ($level == 2) {
        // Level 2 municipalities: "vald" = parish, everything else = city
        if (substr($name, -5) === ' vald') {
            return EHAK::PARISHES;
        }
        return EHAK::CITIES;
    }

    // Level 3 settlements
    if (substr($name, -9) === ' linnaosa') {
        return EHAK::CITY_DISTRICTS;
    }
    if (substr($name, -5) === ' linn') {
        return EHAK::CITIES;
    }

    return EHAK::VILLAGES;
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
    $rows = $xlsx->rows();
    $headerValues = $rows[0] ?? [];

    debug('Headers: '.implode(', ', $headerValues));
    debug('Total rows: '.count($rows));
    debug('EHAK version: '.$ehakVersion);
} else {
    echo SimpleXLSX::parseError();
    exit(1);
}

// Column indices
$colValue = array_search('Value', $headerValues);
$colName = array_search('Label-et-EE', $headerValues);
$colParent = array_search('Parent', $headerValues);
$colLevel = array_search('Level', $headerValues);

if ($colValue === false || $colName === false || $colParent === false || $colLevel === false) {
    die('Could not find required columns (Value, Label-et-EE, Parent, Level) in XLSX header.');
}

// Location arrays
$counties = $cities = $city_districts = $parishes = $villages = [];

for ($i = 1; $i < count($rows); $i++) {
    $row = $rows[$i];
    $code = (string)$row[$colValue];
    $name = $row[$colName];
    $parent = $row[$colParent];
    $level = (int)$row[$colLevel];

    $itemType = type($name, $level);

    debug("$code $name (level=$level, parent=$parent) => $itemType");

    if ($itemType === EHAK::COUNTIES) {
        ${$itemType}['EST'][] = [$code, $name];
        ${$itemType}[1][] = [$code, $name];
    } else {
        ${$itemType}[$parent][] = [$code, $name];
    }
}

if (count($rows) > 1) {
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
