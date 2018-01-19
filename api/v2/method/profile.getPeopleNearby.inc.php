<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $distance = isset($_POST['distance']) ? $_POST['distance'] : 80;
    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

 

    $lat = isset($_POST['lat']) ? $_POST['lat'] : '0.000000';
    $lng = isset($_POST['lng']) ? $_POST['lng'] : '0.000000';

    $distance = helper::clearInt($distance);
    $itemId = helper::clearInt($itemId);

   

    $lat = helper::clearText($lat);
    $lat = helper::escapeText($lat);

    $lng = helper::clearText($lng);
    $lng = helper::escapeText($lng);

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

	$blacklist = new blacklist($dbo);
    $blacklist->setRequestFrom($itemId);

    if (!$blacklist->isExists($accountId)) 
	{

		$geo = new geo($dbo);
		$geo->setRequestFrom($accountId);

		$result = $geo->getPeopleNearby($itemId, $lat, $lng, $distance);
    }
    

    echo json_encode($result);
    exit;
}
