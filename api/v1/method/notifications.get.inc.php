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

    $notifyId = isset($_POST['notifyId']) ? $_POST['notifyId'] : 0;

    $notifyId = helper::clearInt($notifyId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    if ($notifyId == 0) {

        $account = new account($dbo, $accountId);
        $account->setLastNotifyView();
        unset($account);
    }

    $notify = new notify($dbo);
    $notify->setRequestFrom($accountId);
    $result = $notify->getAll($notifyId);

    echo json_encode($result);
    exit;
}
