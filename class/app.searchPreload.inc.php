<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';
	


    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    $gender = isset($_POST['gender']) ? $_POST['gender'] : -1;
    $online = isset($_POST['online']) ? $_POST['online'] : -1;
    $ageFrom = isset($_POST['ageFrom']) ? $_POST['ageFrom'] : 18;
    $ageTo = isset($_POST['ageTo']) ? $_POST['ageTo'] : 110;
	
    $lat = isset($_POST['lat']) ? $_POST['lat'] : '0.000000';
    $lng = isset($_POST['lng']) ? $_POST['lng'] : '0.000000';

  
    $itemId = helper::clearInt($itemId);

   

    $lat = helper::clearText($lat);
    $lat = helper::escapeText($lat);

    $lng = helper::clearText($lng);
    $lng = helper::escapeText($lng);

    if ($gender != -1) $gender = helper::clearInt($gender);
    if ($online != -1) $online = helper::clearInt($online);

    $ageFrom = helper::clearInt($ageFrom);
    $ageTo = helper::clearInt($ageTo);

    $itemId = helper::clearInt($itemId);

    if ($gender != -1) $gender = helper::clearInt($gender);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }
	
	  $account = new account($dbo, $accountId);

    if (strlen($lat) > 0 && strlen($lng) > 0) {

        $result = $account->setGeoLocation($lat, $lng);
    }

    $search = new find($dbo);
    $search->setRequestFrom($accountId);

    $result = $search->preload($itemId, $gender, $online, $ageFrom, $ageTo);

    echo json_encode($result);
    exit;
}
