<?php



include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['id']) ? $_POST['id'] : 0;

    $hashtag = isset($_POST['hashtag']) ? $_POST['hashtag'] : '';

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);

    $hashtag = helper::clearText($hashtag);
    $hashtag = helper::escapeText($hashtag);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $topicposts = new topicpost($dbo);
    $topicposts->setRequestFrom($accountId);

    $result = $items->hashtag($itemId, $hashtag);

    echo json_encode($result);
    exit;
}
