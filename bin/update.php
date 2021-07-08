#!/usr/local/bin/php
<?php
use Koren\EHAK\EHAK;

include_once dirname(__FILE__).'/../vendor/autoload.php';

$options = getopt("u:o::d::", [
    'url:',
    'output::',
    'debug::'
]);

$ehakUrl = $options['url'] ?? $options['u'] ?? false;
$outputFile = $options['output'] ?? $options['o'] ?? dirname(dirname(__FILE__)).'/src/data/{EHAK_VERSION}.php';
$debug = isset($options['debug']) || isset($options['d']) ? true :  false;

if (!$ehakUrl) {
    die('Please set EHAK XML URL with --url or -u option.');
}

if (!defined('DEBUG')) {
    define('DEBUG', $debug);
}

// Cities which contain themself
if (!defined('CITY_IN_CITY')) {
    define('CITY_IN_CITY', [
        'Tartu linn',
        'Pärnu linn',
        'Narva-Jõesuu linn',
        'Paide linn',
        'Haapsalu linn'
    ]);
}

// Type string used in XML
if (!defined('TYPE_STR')) {
    define('TYPE_STR', 'Tüüp=');
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

    // Remove type string
    $typeNo = (int)str_replace(
        TYPE_STR,
        '',
        // Remove spaces
        str_replace(
            ' ',
            '',
            (string)$item->Property->PropertyQualifier[1]->PropertyText
        )
    );

    return $types[$typeNo];
}

/**
 * Get item label
 *
 * @param object $item
 *
 * @return string
 */
function label($item)
{
    return (string)$item->Label->LabelText;
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

$xml = simplexml_load_file($ehakUrl);

if (false === $xml) {
    echo "Failed to parse ".$ehakUrl.PHP_EOL;
    exit;
}

// Location arrays
$counties = $cities = $city_districts = $parishes = $villages = [];

if (isset($xml->Classification->Item)) {
    $ehakVersion = $xml->Classification->attributes()->version;
    foreach ($xml->Classification->Item as $county) {
        $countyId = (string)$county->attributes()->id;
        $counties['EST'][] = [$countyId, label($county)];
        $counties['1'][] = [$countyId, label($county)];

        debug($county->Label->LabelText);

        foreach ($county->Item as $countyParts) {
            debug($countyParts->Label->LabelText.' (id: '.(string)$countyParts->attributes()->id.')');

            foreach ($countyParts->Item as $countyPart) {
                $type = type($countyPart);
                $countyPartId = (string)$countyPart->attributes()->id;
                ${$type}[$countyId][] = [$countyPartId, label($countyPart)];

                debug($countyPart->Label->LabelText.' (id: '.(string)$countyPart->attributes()->id.')');

                foreach ($countyPart->Item as $parishPart) {
                    $parishPartId = (string)$parishPart->attributes()->id;

                    if ($parishPart->Property) {
                        $type = type($parishPart);
                        ${$type}[$countyPartId][] = [$parishPartId, label($parishPart)];
                    }

                    debug($parishPart->Label->LabelText.' (id: '.(string)$parishPart->attributes()->id.')');

                    foreach ($parishPart->Item as $villagePart) {
                        debug($villagePart->Label->LabelText.' (id: '.(string)$villagePart->attributes()->id.')');

                        if ($villagePart->Property) {
                            $name = label($villagePart);
                            $type = type($villagePart);
                            $villagePartId = (string)$villagePart->attributes()->id;
                            ${$type}[$countyPartId][] = [$villagePartId, $name];
                        }
                    }
                }
            }
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
