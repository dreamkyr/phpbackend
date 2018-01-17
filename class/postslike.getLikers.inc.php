<?php


include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['id']) ? $_POST['id'] : 0;
    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;
    $likeId = isset($_POST['likeId']) ? $_POST['likeId'] : 0;

    $accountId = helper::clearInt($accountId);

    $accessToken = helper::clearText($accessToken);
    $accessToken = helper::escapeText($accessToken);

    $itemId = helper::clearInt($itemId);
    $itemType = helper::clearInt($itemType);
    $likeId = helper::clearInt($likeId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $postslike = new postslike($dbo);
    $postslike->setRequestFrom($accountId);

    $result = $postslike->getLikers($itemId, $itemType, $likeId);

    echo json_encode($result);
    exit;
}
