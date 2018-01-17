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

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $gcm_regId = isset($_POST['gcm_regId']) ? $_POST['gcm_regId'] : '';
    $ios_fcm_regId = isset($_POST['ios_fcm_regId']) ? $_POST['ios_fcm_regId'] : '';

    $facebookId = isset($_POST['facebookId']) ? $_POST['facebookId'] : '';

    $clientId = helper::clearInt($clientId);

    $gcm_regId = helper::clearText($gcm_regId);
    $gcm_regId = helper::escapeText($gcm_regId);

    $facebookId = helper::clearText($facebookId);
    $facebookId = helper::escapeText($facebookId);

    $ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $ios_fcm_regId = helper::escapeText($ios_fcm_regId);

    $access_data = array("error" => true,
                         "error_code" => ERROR_UNKNOWN);

    $helper = new helper($dbo);

    $accountId = $helper->getUserIdByFacebook($facebookId);

    if ($accountId != 0) {

        $auth = new auth($dbo);
        $access_data = $auth->create($accountId, $clientId);

        if ($access_data['error'] === false) {

            $account = new account($dbo, $accountId);
            $account->setState(ACCOUNT_STATE_ENABLED);
            $account->setLastActive();
            $access_data['account'] = array();

            array_push($access_data['account'], $account->get());

            if (strlen($gcm_regId) != 0) {

                $account->setGCM_regId($gcm_regId);
            }

            if (strlen($ios_fcm_regId) != 0) {

                $account->set_iOS_regId($ios_fcm_regId);
            }
        }
    }

    echo json_encode($access_data);
    exit;
}
