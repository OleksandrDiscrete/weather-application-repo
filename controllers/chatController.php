<?php
namespace WeatherMaster\Controllers;

class ChatController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getIndex(): void
    {
        $this->render('chat/index', [
            'pageTitle' => 'Real-Time Чат'
        ]);
    }
}