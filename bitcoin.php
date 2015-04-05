<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelo/TickerMP.php';
require_once 'modelo/SendGrid_loader.php';
function getJson($url){
    $res = exec("curl " . $url);
	$res = json_decode($res);
	return $res;
}

// VX4RBacMEcMk

function enviarEmail($dest, $msg) {
	$sendgrid = new SendGrid('viagps', 'fGYnL2HOw46m');
    $email = "bitcoin@viagps.cl";
    $mail = new SendGrid\Mail();
    $mail->setFrom($email)->
            setSubject('Bitcoins')->
            setHtml($msg);
    $mail->addTo($dest);
    $sendgrid->smtp->send($mail);
}

$target_url = "https://www.itbit.com/api/feeds/ticker/XBTUSD";
$json = getJson($target_url);
$data = new stdClass();
$data->timestamp = substr($json->currentTime, 0, 19);
$data->ask = $json->ask;
$data->bid = $json->bid;
$data->close = $json->close;
$data->open = $json->open;
$data->low = $json->low;
$data->high = $json->high;
$data->volume = $json->volume;
$data->market = $json->tickerSymbol;

$tkMP = new TickerMP();
$last = $tkMP->fetchLast();
if(strcmp($last->timestamp, $data->timestamp)!=0) {
	$tkMP->insert($data);
	if($data->bid > 630 && $last->bid <= 630) enviarEmail("super.neeph@gmail.com", "Bitcoin bid: " . $data->bid);
    if($data->bid > 680 && $last->bid <= 680) enviarEmail("super.neeph@gmail.com", "Bitcoin bid: " . $data->bid);
    if($data->bid > 700 && $last->bid <= 700) enviarEmail("super.neeph@gmail.com", "Bitcoin bid: " . $data->bid);
}

?>