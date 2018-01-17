<?php



include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $topicId = isset($_POST['support_topic_id']) ? $_POST['suppport_topic_id'] : 0;
    $itemId = isset($_POST['id']) ? $_POST['id'] : 0;

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $categoryId = helper::clearInt($categoryId);
    $itemId = helper::clearInt($itemId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $discussions = new discussions($dbo);
    $discussions->setRequestFrom($accountId);

    $result = $discussions->getItemSupport($topicId, $itemId);

    echo json_encode($result);
    exit;
}
