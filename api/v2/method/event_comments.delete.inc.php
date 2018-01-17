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

$result = array("error" => true);

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';
    $comment_id = isset($_POST['comment_id']) ? $_POST['comment_id'] : ''; 
    $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : '';

    $auth = new auth($dbo);
    if (!$auth->authorize($accountId, $accessToken)) {
        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $event_comment = new event_comment($dbo);
    $event_comment->setRequestFrom($accountId);

    
    $result = $event_comment->deleteComment($comment_id, $event_id);

    echo json_encode($result);
    exit;
}
