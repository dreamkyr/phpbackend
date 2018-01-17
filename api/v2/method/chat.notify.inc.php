<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * raccoonsquare@gmail.com
 *
 * Copyright 2012-2018 Demyanchuk Dmitry (raccoonsquare@gmail.com)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $chatFromUserId = isset($_POST['chatFromUserId']) ? $_POST['chatFromUserId'] : 0;
    $chatToUserId = isset($_POST['chatToUserId']) ? $_POST['chatToUserId'] : 0;

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;

    $notifyId = isset($_POST['notifyId']) ? $_POST['notifyId'] : 0;

    $android_fcm_regId = isset($_POST['android_fcm_regId']) ? $_POST['android_fcm_regId'] : "";
    $ios_fcm_regId = isset($_POST['ios_fcm_regId']) ? $_POST['ios_fcm_regId'] : "";

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $chatFromUserId = helper::clearInt($chatFromUserId);
    $chatToUserId = helper::clearInt($chatToUserId);

    $chatId = helper::clearInt($chatId);

    $notifyId = helper::clearInt($notifyId);

    $result = array("error" => false,
                    "android_fcm_regId" => $android_fcm_regId,
                    "ios_fcm_regId" => $ios_fcm_regId,
                    "error_code" => ERROR_UNKNOWN);

    $profileId = $chatFromUserId;

    if ($profileId == $accountId) {

        if (strlen($android_fcm_regId) > 0 || strlen($ios_fcm_regId) > 0) {

            // GCM_MESSAGE_ONLY_FOR_PERSONAL_USER = 2
            // GCM_NOTIFY_SEEN= 15
            // GCM_NOTIFY_TYPING= 16
            // GCM_NOTIFY_TYPING_START = 27
            // GCM_NOTIFY_TYPING_END = 28

            $fcm = new fcm($dbo, 0);
            $fcm->setAccountId($chatToUserId);
            $fcm->addDeviceId($android_fcm_regId);
            $fcm->addDeviceId($ios_fcm_regId);
            $fcm->setData($notifyId, 2, "Seen", 0, $chatId);
            $fcm->send();

        } else {

            $fcm = new fcm($dbo, $chatToUserId);
            $fcm->setData($notifyId, 2, "Seen", 0, $chatId);
            $fcm->send();
        }

    } else {

        if (strlen($android_fcm_regId) > 0 || strlen($ios_fcm_regId) > 0) {

            // GCM_MESSAGE_ONLY_FOR_PERSONAL_USER = 2
            // GCM_NOTIFY_SEEN= 15
            // GCM_NOTIFY_TYPING= 16
            // GCM_NOTIFY_TYPING_START = 27
            // GCM_NOTIFY_TYPING_END = 28

            $fcm = new fcm($dbo, 0);
            $fcm->setAccountId($chatFromUserId);
            $fcm->addDeviceId($android_fcm_regId);
            $fcm->addDeviceId($ios_fcm_regId);
            $fcm->setData($notifyId, 2, "Seen", 0, $chatId);
            $fcm->send();

        } else {

            $fcm = new fcm($dbo, $chatFromUserId);
            $fcm->setData($notifyId, 2, "Seen", 0, $chatId);
            $fcm->send();
        }
    }

    echo json_encode($result);
    exit;
}
