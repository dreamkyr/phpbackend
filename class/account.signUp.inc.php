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

    $android_msg_fcm_regid = isset($_POST['android_msg_fcm_regid']) ? $_POST['android_msg_fcm_regid'] : '';
    $ios_msg_fcm_regid = isset($_POST['ios_msg_fcm_regid']) ? $_POST['ios_msg_fcm_regid'] : '';
    

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $photoUrl = isset($_POST['photo']) ? $_POST['photo'] : '';

    $user_year = isset($_POST['year']) ? $_POST['year'] : 0;
    $user_month = isset($_POST['month']) ? $_POST['month'] : 0;
    $user_day = isset($_POST['day']) ? $_POST['day'] : 0;
	
	$user_age = isset($_POST['age'])? $_POST['age'] : 0;

    $language = isset($_POST['language']) ? $_POST['language'] : '';

    $clientId = helper::clearInt($clientId);

   
    $user_year = helper::clearInt($user_year);
    $user_month = helper::clearInt($user_month);
    $user_day = helper::clearInt($user_day);
	$user_age = helper::clearInt($user_age);

	$android_msg_fcm_regid = helper::clearText($android_msg_fcm_regid);
    $android_msg_fcm_regid = helper::escapeText($android_msg_fcm_regid);

    $ios_msg_fcm_regid = helper::clearText($ios_msg_fcm_regid);
    $ios_msg_fcm_regid = helper::escapeText($ios_msg_fcm_regid);
   

    $gcm_regId = helper::clearText($gcm_regId);
	$ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $username = helper::clearText($username);
    $fullname = helper::clearText($fullname);
    $password = helper::clearText($password);
    $email = helper::clearText($email);
    $photoUrl = helper::clearText($photoUrl);
    $language = helper::clearText($language);

   
    $gcm_regId = helper::escapeText($gcm_regId);
    $username = helper::escapeText($username);
    $fullname = helper::escapeText($fullname);
    $password = helper::escapeText($password);
    $email = helper::escapeText($email);
    $photoUrl = helper::escapeText($photoUrl);
    $language = helper::escapeText($language);

    $ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $ios_fcm_regId = helper::escapeText($ios_fcm_regId);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_UNKNOWN, "Error client Id.");
    }

    $result = array("error" => true);

    $account = new account($dbo);
    $result = $account->signup($username, $fullname, $password, $email, $user_year, $user_month, $user_day, $user_age);
    unset($account);

    if ($result['error'] === false) {

        $account = new account($dbo);
        $account->setState(ACCOUNT_STATE_ENABLED);
        $account->setLastActive();
        $result = $account->signin($username, $password);
        unset($account);

        if ($result['error'] === false) {

            $auth = new auth($dbo);
            $result = $auth->create($result['accountId'], $clientId);

            if ($result['error'] === false) {

                $account = new account($dbo, $result['accountId']);

                if (strlen($photoUrl) != 0) {

                    $photos = array("error" => false,
                                    "originPhotoUrl" => $photoUrl,
                                    "normalPhotoUrl" => $photoUrl,
                                    "bigPhotoUrl" => $photoUrl,
                                    "lowPhotoUrl" => $photoUrl);

                    $account->setPhoto($photos);

                    unset($photos);
                }


                if (strlen($gcm_regId) != 0) {

                    $account->setGCM_regId($gcm_regId);
                }

                  if (strlen($ios_fcm_regId) != 0) {

                    $account->set_ios_fcm_regId($ios_fcm_regId);
                }

                if (strlen($ios_msg_fcm_regid) != 0) {

                    $account->set_ios_msg_fcm_regId($ios_msg_fcm_regid);
                }

                if (strlen($android_msg_fcm_regid) != 0) {

                    $account->set_android_msg_fcm_regId($android_msg_fcm_regid);
                }

                $result['account'] = array();

                array_push($result['account'], $account->get());
            }
        }
    }

    echo json_encode($result);
    exit;
}
