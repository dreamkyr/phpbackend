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

    $gcm_regId = isset($_POST['gcm_regId']) ? $_POST['gcm_regId'] : '';
    $ios_fcm_regId = isset($_POST['ios_fcm_regId']) ? $_POST['ios_fcm_regId'] : '';
	
	$android_msg_fcm_regid = isset($_POST['android_msg_fcm_regid']) ? $_POST['android_msg_fcm_regid'] : '';
    $ios_msg_fcm_regid = isset($_POST['ios_msg_fcm_regid']) ? $_POST['ios_msg_fcm_regid'] : '';

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $clientId = helper::clearInt($clientId);
	
	$android_msg_fcm_regid = helper::clearText($android_msg_fcm_regid);
    $android_msg_fcm_regid = helper::escapeText($android_msg_fcm_regid);

    $ios_msg_fcm_regid = helper::clearText($ios_msg_fcm_regid);
    $ios_msg_fcm_regid = helper::escapeText($ios_msg_fcm_regid);

    $gcm_regId = helper::clearText($gcm_regId);
	$ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $username = helper::clearText($username);
    $password = helper::clearText($password);

    $gcm_regId = helper::escapeText($gcm_regId);
    $username = helper::escapeText($username);
    $password = helper::escapeText($password);

    $ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $ios_fcm_regId = helper::escapeText($ios_fcm_regId);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_UNKNOWN, "Error client Id.");
    }

    $access_data = array();

    $account = new account($dbo);
    $access_data = $account->signin($username, $password);

    unset($account);

    if ($access_data["error"] === false) {

        $account = new account($dbo, $access_data['accountId']);

        switch ($account->getState()) {

            case ACCOUNT_STATE_BLOCKED: {

                break;
            }

            default: {

                $auth = new auth($dbo);
                $access_data = $auth->create($access_data['accountId'], $clientId);

                if ($access_data['error'] === false) {

                    $account = new account($dbo, $access_data['accountId']);
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
					  if (strlen($ios_msg_fcm_regid) != 0) {

                        $account->set_ios_msg_fcm_regId($ios_msg_fcm_regid);
                    }

                    if (strlen($android_msg_fcm_regid) != 0) {

                        $account->set_android_msg_fcm_regId($android_msg_fcm_regid);
                    }
                }

                break;
            }
        }
    }

    echo json_encode($access_data);
    exit;
}
