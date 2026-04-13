<?php

class WeatherApiClient
{
    private $apiKey;
    private $baseUrl = "http://api.weatherapi.com/v1";

    public function __construct($apiKey)
    {
        $this->apiKey = trim($apiKey); 
    }

    public function getCurrentWeather($query)
    {
        $url = "{$this->baseUrl}/current.json?key={$this->apiKey}&q={$query}&aqi=no&lang=uk";

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

        if ($response === FALSE || $httpCode !== 200) {
            return null; 
        }
        
        return json_decode($response, true);
    }
    public function getForecast($query, $days = 7)
    {
        $url = "{$this->baseUrl}/forecast.json?key={$this->apiKey}&q={$query}&days={$days}&aqi=no&alerts=no&lang=uk";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === FALSE || $httpCode !== 200) {
            return null; 
        }
        
        return json_decode($response, true);
    }
}