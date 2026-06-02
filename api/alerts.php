<?php
namespace WeatherMaster\Api;

include_once "../services/regexService.php";
use WeatherMaster\Services\RegexService;

header('Content-Type: application/json');

$alertFilePath = __DIR__ . "/../data/alert.txt";
$response = [
    'hasAlert' => false,
    'html' => '',
    'hash' => ''
];

if (file_exists($alertFilePath)) {
    $rawText = file_get_contents($alertFilePath);

    if (trim($rawText) !== '') {
        $response['hasAlert'] = true;
        $response['html'] = RegexService::textToHtml($rawText);
        $response['hash'] = md5($rawText);
    }
}

echo json_encode($response);
exit();