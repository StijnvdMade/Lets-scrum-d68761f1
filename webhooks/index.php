<?php


$bot_token = 'xoxb-870241712243-887497096337-SwnfT7ZRCblhfVysnwa1CnYU';
$message = 'HEYICANRESPOND!!!:)))';


$json = file_get_contents('php://input');
$json_object = json_decode($json);

$channel = $json_object->event->channel;

$api;

if(!isMessageFromUser())
{
    return;
}

include 'api.php';
$api = new Api();
$api->pushStations();

$stations = array();
$station1;
$station2;

$uiccode;

$afkorting1;
$afkorting2;

$valid_options_array = array('vertraging', 'route');


update();

function update()
{
    if(isChatInDM())
    {
        if(!isMessageFromUser()){return;}
        if(!isUserOptionValid()){botSendMessage('je gekozen optie bestaat niet');return;}
        if(!canStationsBeSplit()){botSendMessage('je hebt geen station ingetyped');return;}
        if(!doesStationExist()){botSendMessage('je station bestaat niet in Nederland of is niet goed gespeld :(');return;}
        doStuffBasedOnOption();
    }
    else
    {
        
    }
}

function doesStationExist()
{
    global $uiccode;
    global $api;
    global $station1;
    $station1 = strtolower($station1);
    error_log($station1);
    $station_array = $api->getStationsArray();
    foreach ($station_array as $station) 
    {
        if($station1 == $station['naam'])
        {
            $uiccode = $station['uiccode'];
            return true;
        }
    }

    error_log($uiccode);

    return false;
}

function canStationsBeSplit()
{
    global $json_object;
    global $station1;
    global $station2;

    $message_includes_van = explode(' van ', $json_object->event->text);
    if(count($message_includes_van) != 2){return false;}
    $station1 = $message_includes_van[1];
    $message_includes_naar = explode(' naar ', $message_includes_van[1]);
    if(count($message_includes_naar) != 2){return true;}
    $station1 = $message_includes_naar[0];
    $station2 = $message_includes_naar[1];

    error_log("station 1 : $station1");
    error_log("station 2 : $station2");

    return true;
}

function doStuffBasedOnOption()
{
    if(getUserOption() == 'vertraging'){doVertraging();}
    if(getUserOption() == 'route'){doRoute();}
}

function getAmmountOfWords()
{
    return count(getUserWords());
}

function doRoute()
{
    global $station1;
    $encoded = str_replace(' ', '%20', $station1);
    $encoded = $encoded . '%20';
    $link = "https://www.google.com/maps/dir/amsterdam+science+park,+amsterdam/{$encoded}/.";
    botSendRoute((string)$link);
}

function botSendRoute($message)
{
    global $bot_token;
    global $channel;

    //$htmllink = "<a href='$message'>route</a>";
    error_log($message);
    $response = file_get_contents("https://slack.com/api/chat.postMessage?token={$bot_token}&channel={$channel}&text=<{$message}>");
}


function doVertraging()
{
    global $uiccode;
    global $api;
    global $station1;
    $response = $api->getDisruptionResponse($uiccode);
    if(count($response->payload) == 0)
    {
        botSendMessage('geen storingen');
    }
    else
    {
        botSendMessage($response->payload[0]->type . " tussen " . $station1 . " richting " . $response->payload[0]->titel);
    }
}


function botSendMessage($message)
{
    global $bot_token;
    global $channel;
    $messsgeEncoded = str_replace(' ', '+', $message);
    $g = file_get_contents("https://slack.com/api/chat.postMessage?token={$bot_token}&channel={$channel}&text={$messsgeEncoded}&pretty=1");
}


function getUserWords()
{
    global $json_object;
    return explode(" ", $json_object->event->text);
}


function getUserOption()
{
    global $json_object;
    $user_words = explode(" ", $json_object->event->text);
    $user_option = $user_words[0];
    return $user_option;
}

function isUserOptionValid()
{
    global $valid_options_array;

    foreach ($valid_options_array as $option) 
    {
        if($option == getUserOption())
        {
            return true;
        }
    }

    return false;
}

function filterTextMessageFromDM()
{

}

function checkWhatUserAsked()
{
    global $json_object;
    $message = $json_object->event->text;
}

function isChatInDM()
{

    if(getEventType() == 'message')
    {
        return 'Message in dm';
    }

    return false;
}

function getEventType()
{
    global $json_object;
    return $json_object->event->type;
}



function isMessageFromUser()
{
    global $json_object;
    if(count((array)$json_object->event) == 10)
    {
       return true;
    }

    return false;
}


function checkMesssageType($object)
{
    $messageType = $object->event->type;
    return $messageType;
}

?>