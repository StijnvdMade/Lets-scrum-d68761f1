<!DOCTYPE html>
<html lang="en">
<head>
    <title>NS Message</title>
    <style>
    body{
        background-color: rgb(252, 198, 63);
    }
    </style>
</head>
<body>
    <?php
    require_once 'HTTP/Request2.php'; // De API gebruikt Pear
    $request = new Http_Request2('https://gateway.apiportal.ns.nl/public-reisinformatie/api/v2/disruptions'); // De NS API
    $url = $request->getUrl();
    $json_object;
    $NSMessage = ""; // Het bericht dat uiteindelijk wordt verstuurd
    $subMessage = ""; // Deel van het bericht
    $headers = array(
        'Ocp-Apim-Subscription-Key' => '775d0797242444f8a39eb00133527001', // Onze eigen key voor de NS API
        'X-APIKEY' => '',
        'Accept-Type' => 'application/json',
        'Content-Type' => 'application/json',
    );
    $request->setHeader($headers);
    $parameters = array(
        'type' => '',
        'actual' => '',
        'lang' => ''
    );
    $url->setQueryVariables($parameters);
    $request->setMethod(HTTP_Request2::METHOD_GET);
    $request->setBody("{body}");
    try 
    {
        $response = $request->send();
        //echo $response->getBody();
        $json_object = json_decode($response->getBody(), true);
        SetNSMessage();
        
    } 
    catch (HttpException $ex) 
    {
        echo $ex;
    }
    
    function SetNSMessage()
    {
        global $NSMessage;
        global $subMessage;
        global $json_object;
        $tijd = function($json, $i, $type) 
        {
            if(count($json['payload'][$i]['verstoring']['geldigheidsLijst']) == 0)
            {
                return "Niet bekend";
            }
            return $json['payload'][$i]['verstoring']['geldigheidsLijst'][0][$type];
            
        };
        $gevolg = function($json, $i)
        {
            if(!isset($json['payload'][$i]['verstoring']['gevolg']))
            {
                return "geen gevolgen";
            }
            return $json['payload'][$i]['verstoring']['gevolg'];
        };
        for ($i = 1; $i < 5; $i++) 
        {
            $subMessage = "";
            
    
        $subMessage = $subMessage . $json_object['payload'][$i]['titel'] . "\n" .
        "Beschrijving: " . 
        $json_object['payload'][$i]['verstoring']['oorzaak'] . "\n" .
        "StartTijd: " . 
        $tijd($json_object, $i, 'startDatum') . "\n" .
        "eindTijd: " . 
        $tijd($json_object, $i, 'eindDatum') . "\n" .
        "Periode: " . 
        $json_object['payload'][$i]['verstoring']['verwachting'] . "\n";
        "Gevolg: " . 
        $gevolg($json_object, $i) . "\n";
        // $json_object['payload'][$i]['titel'] . "<br>" .
        // "Beschrijving: " . 
        // $json_object['payload'][$i]['verstoring']['oorzaak'] . "<br>" .
        // "StartTijd: " . 
        // $tijd($json_object, $i, 'startDatum') . "<br>" .
        // "eindTijd: " . 
        // $tijd($json_object, $i, 'eindDatum') . "<br>" .
        // "Periode: " . 
        // $json_object['payload'][$i]['verstoring']['verwachting'] . "<br>" .
        // "Advies: " . 
        // $gevolg($json_object, $i);
        
        // error_log("Er is wat fout gegaan", 3, "error_log    ");
        $subMessage = $subMessage . "\n";
        $NSMessage = $NSMessage . $subMessage . "\n";
        
        for ($i = 1; $i < 10; $i++) 
        {
            $subMessage = "";
            if($json_object['payload'][$i]['verstoring']['landelijk'])
            {
                $subMessage = $subMessage . $json_object['payload'][$i]['type'] . " tussen " . $json_object['payload'][$i]['titel'] . "\n" .
                " op " . $json_object['payload'][$i]['verstoring']['periode'] . '.' . "\n" . 
                "de extra reistijd is "  . $json_object['payload'][$i]['verstoring']['extraReistijd'] . "." . "\n" . 
                "NS reisadvies : " . join(" ", $json_object['payload'][$i]['verstoring']['reisadviezen']['reisadvies'][0]['advies']) . ".".  "\n";
            }
            $subMessage = $subMessage . "\n";
            $NSMessage = $NSMessage . $subMessage . "\n";
        }
    }
    function Message() 
    {
        global $NSMessage;
        error_reporting(E_ALL);
        $data = array(
        // Your message
            'text' => $NSMessage,
        );
        $json_string = json_encode($data);
        // Your webhook URL
        $webhook_url = 'https://hooks.slack.com/services/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';
        $slack_call = curl_init();
        curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json_string);
        curl_setopt($slack_call, CURLOPT_CRLF, true);
        curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($slack_call, CURLOPT_URL, $webhook_url);
        curl_setopt($slack_call, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($slack_call, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json',
            'Content-Length:'.strlen($json_string)));
        $result = curl_exec($slack_call);
        curl_close($slack_call);
    }
}
    Message();
    ?>
    <h1>Message sent!</h1>
    <a href="https://slack.com/oauth/authorize?client_id=870241712243.883925278630&scope=bot,incoming-webhook"><img alt="Add to Slack" height="40" width="139" src="https://platform.slack-edge.com/img/add_to_slack.png" srcset="https://platform.slack-edge.com/img/add_to_slack.png 1x, https://platform.slack-edge.com/img/add_to_slack@2x.png 2x"></a>
    </html>
</body>