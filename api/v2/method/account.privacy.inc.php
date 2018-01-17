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

    $allowShowMyLikes = isset($_POST['allowShowMyLikes']) ? $_POST['allowShowMyLikes'] : 0;
    $allowShowMyGifts = isset($_POST['allowShowMyGifts']) ? $_POST['allowShowMyGifts'] : 0;
    $allowShowMyFriends = isset($_POST['allowShowMyFriends']) ? $_POST['allowShowMyFriends'] : 0;
    $allowShowMyGallery = isset($_POST['allowShowMyGallery']) ? $_POST['allowShowMyGallery'] : 0;
    $allowShowMyInfo = isset($_POST['allowShowMyInfo']) ? $_POST['allowShowMyInfo'] : 0;
    $allowShowMyDistance = isset($_POST['allowShowMyDistance']) ? $_POST['allowShowMyDistance'] : 0;

    $allowShowMyLikes = helper::clearInt($allowShowMyLikes);
    $allowShowMyGifts = helper::clearInt($allowShowMyGifts);
    $allowShowMyFriends = helper::clearInt($allowShowMyFriends);
    $allowShowMyGallery = helper::clearInt($allowShowMyGallery);
    $allowShowMyInfo = helper::clearInt($allowShowMyInfo);
    $allowShowMyDistance = helper::clearInt($allowShowMyDistance);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS);

    $account = new account($dbo, $accountId);

    $account->setPrivacySettings($allowShowMyLikes, $allowShowMyGifts, $allowShowMyFriends, $allowShowMyGallery, $allowShowMyInfo, $allowShowMyDistance);

    $result = $account->getPrivacySettings();

    echo json_encode($result);
    exit;
}
