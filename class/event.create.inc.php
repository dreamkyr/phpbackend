<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2015 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $topic = isset($_POST['topic']) ? $_POST['topic'] : '';
    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $title = helper::clearText($title);
    $topic = helper::clearText($topic);
    $accountId = helper::clearInt($accountId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);
    if (!$auth->authorize($accountId, $accessToken)) {
        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $event = new event($dbo);
    $event->setRequestFrom($accountId);

    if (isset($_FILES['uploaded_file']['name'])) {
        $currentTime = sprintf("%.0f", round(microtime(true) * 1000));
        $uploaded_file_ext = @pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
        $file_name = $currentTime . "." . $uploaded_file_ext;

        if (@move_uploaded_file($_FILES['uploaded_file']['tmp_name'], "../../../photo/event_content/" . $file_name)) {
            $result = $event->createEvent($accountId, $title, $topic, $file_name);

        } else {
            $result['msg'] = "can not copy file";
        }
    } else {
        $result = $event->createEvent($accountId, $title, $topic);
    }

    echo json_encode($result);
    exit;
}
