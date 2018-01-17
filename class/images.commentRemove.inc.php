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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $commentId = isset($_POST['commentId']) ? $_POST['commentId'] : 0;

    $accountId = helper::clearInt($accountId);

    $commentId = helper::clearInt($commentId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $images = new images($dbo);
    $images->setRequestFrom($accountId);

    $commentInfo = $images->commentsInfo($commentId);

    if ($commentInfo['fromUserId'] == $accountId) {

        $images->commentsRemove($commentId);

    } else {

        $photos = new photos($dbo);
        $photos->setRequestFrom($accountId);

        $imageInfo = $photos->info($commentInfo['imageId']);

        if ($imageInfo['fromUserId'] == $accountId) {

            $images->commentsRemove($commentId);
        }
    }

    unset($comments);
    unset($post);

    echo json_encode($result);
    exit;
}
