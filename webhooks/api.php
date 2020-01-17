<?php


class Api 
{

    public $stations_array = array();

    public $allStations;

    public $response;


    public function __construct()
    {
        $res = file_get_contents('http://4biddenshop.com/stations/stations.php');
        $this->response = json_decode($res);
    }

    public function pushStations()
    {
        for ($i=0; $i < count($this->response->payload); $i++) 
        { 
            if($this->response->payload[$i]->land == 'NL')
            {
                $uiccode = $this->response->payload[$i]->UICCode;
                $afkorting = $this->response->payload[$i]->code;
                $naam = $this->response->payload[$i]->namen->lang;
                $station = array('naam' => strtolower($naam), 'uiccode' => $uiccode, 'afk' => $afkorting);
                array_push($this->stations_array, $station);
            }
        }

        error_log(count($this->stations_array) . ' stations in nederland');

    }

    public function getStationsArray()
    {
        return $this->stations_array;
    }

    public function getDisruptionResponse($uiccode)
    {
        $response = file_get_contents("https://4biddenshop.com/disruptions/arrivals.php?uiccode={$uiccode}");
        $jsondecodedRes = json_decode($response);
        return $jsondecodedRes;
    }

    
}






?>