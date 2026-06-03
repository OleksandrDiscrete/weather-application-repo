<?php
namespace WeatherMaster\Controllers;

use WeatherMaster\Services\RegexService;

class ApiController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getAlert()
    {
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
    }
}