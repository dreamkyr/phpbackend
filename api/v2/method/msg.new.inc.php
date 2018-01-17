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

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $chatFromUserId = isset($_POST['chatFromUserId']) ? $_POST['chatFromUserId'] : 0;
    $chatToUserId = isset($_POST['chatToUserId']) ? $_POST['chatToUserId'] : 0;

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;
    $messageText = isset($_POST['messageText']) ? $_POST['messageText'] : "";
    $messageImg = isset($_POST['messageImg']) ? $_POST['messageImg'] : "";

    $listId = isset($_POST['listId']) ? $_POST['listId'] : 0;

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $profileId = helper::clearInt($profileId);

    $chatFromUserId = helper::clearInt($chatFromUserId);
    $chatToUserId = helper::clearInt($chatToUserId);

    $chatId = helper::clearInt($chatId);

    $listId = helper::clearInt($listId);

    $messageText = helper::clearText($messageText);

    $messageText = preg_replace( "/[\r\n]+/", "<br>", $messageText); //replace all new lines to one new line
    $messageText  = preg_replace('/\s+/', ' ', $messageText);        //replace all white spaces to one space

    $messageText = helper::escapeText($messageText);

    $messageImg = helper::clearText($messageImg);
    $messageImg = helper::escapeText($messageImg);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $profile = new profile($dbo, $profileId);
    $profile->setRequestFrom($accountId);

    $profileInfo = $profile->getShort();

    if ($profileInfo['state'] != ACCOUNT_STATE_ENABLED) {

        echo json_encode($result);
        exit;
    }

    if ($profileInfo['allowMessages'] == 0) {

        if (!$profile->is_friend_exists($accountId)) {

            echo json_encode($result);
            exit;
        }
    }

    if (!$profileInfo['inBlackList']) {

        $messages = new msg($dbo);
        $messages->setRequestFrom($accountId);

        $result = $messages->create($profileId, $chatId, $messageText, $messageImg, $chatFromUserId, $chatToUserId, $profileInfo['gcm_regid'], $listId, $profileInfo['ios_fcm_regid']);
    }

    echo json_encode($result);
    exit;
}
