<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WeatherManager
{

    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function weatherApi($cityJson)
    {
        $curl = curl_init();
        $WeatherApiKey = $this->parameterBag->get('weatherapikey');
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.weatherapi.com/v1/current.json?key='.$WeatherApiKey.'&q=bulk',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $cityJson,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response,true);

        return $data;
    }

    public function locationJson($cities)
    {
        $jsonArray = ['locations' => []];

        foreach ($cities as $city) {
            $jsonArray['locations'][] = [
                'q' => $city->getCityname(),
                'custom_id' => $city->getId(),
            ];
        }
        return json_encode($jsonArray);
    }

    public function getCities()
    {
        $jsonFilePath = $this->parameterBag->get('kernel.project_dir') . '/cities.json';
        $jsonContent = file_get_contents($jsonFilePath);
        $cities = json_encode($jsonContent, true);
        $cities = json_decode($cities, true);

        dd($cities);

    }

    public function temperatureAlert($cities,$weathers)
    {
        $temp = [];
        foreach ($cities as $city){
            foreach ($weathers as $weather){
                if ($weather['query']['custom_id'] == $city->getId()){
                    if ($weather['query']['current']['temp_c'] != $city->getAlerttemperature()){
                        $temp[] = ['city' => $city->getCityname(), 'alerttemperature' => $city->getAlerttemperature(), 'temperature' => $weather['query']['current']['temp_c']];
                    }
                }
            }
        }
        return $temp;
    }

}