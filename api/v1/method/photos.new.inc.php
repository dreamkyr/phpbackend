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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $accessMode = isset($_POST['accessMode']) ? $_POST['accessMode'] : 0;

    $comment = isset($_POST['comment']) ? $_POST['comment'] : "";
    $originImgUrl = isset($_POST['originImgUrl']) ? $_POST['originImgUrl'] : "";
    $previewImgUrl = isset($_POST['previewImgUrl']) ? $_POST['previewImgUrl'] : "";
    $imgUrl = isset($_POST['imgUrl']) ? $_POST['imgUrl'] : "";

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $accessMode = helper::clearInt($accessMode);

    $comment = helper::clearText($comment);

    $comment = preg_replace( "/[\r\n]+/", "<br>", $comment); //replace all new lines to one new line
    $comment  = preg_replace('/\s+/', ' ', $comment);        //replace all white spaces to one space

    $comment = helper::escapeText($comment);

    $originImgUrl = helper::clearText($originImgUrl);
    $originImgUrl = helper::escapeText($originImgUrl);

    $previewImgUrl = helper::clearText($previewImgUrl);
    $previewImgUrl = helper::escapeText($previewImgUrl);

    $imgUrl = helper::clearText($imgUrl);
    $imgUrl = helper::escapeText($imgUrl);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $photos = new photos($dbo);
    $photos->setRequestFrom($accountId);

    $result = $photos->add($accessMode, $comment, $originImgUrl, $previewImgUrl, $imgUrl);

    echo json_encode($result);
    exit;
}
