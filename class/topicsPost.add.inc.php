<?php



include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;

    $general_topic_id = isset($_POST['general_topic_id']) ? $_POST['general_topic_id'] : 0;
	$support_topic_id = isset($_POST['support_topic_id']) ? $_POST['support_topic_id'] : 0;

    $desc = isset($_POST['desc']) ? $_POST['desc'] : "";
    $imgUrl = isset($_POST['imgUrl']) ? $_POST['imgUrl'] : "";
   
    $clientId = helper::clearInt($clientId);
 
    $itemType = helper::clearInt($itemType);
    $general_topic_id = helper::clearInt($general_topic_id);
	$support_topic_id = helper::clearInt($support_topic_id);

    $desc = helper::clearText($desc);

    $desc = preg_replace( "/[\r\n]+/", "<br>", $desc); //replace all new lines to one new line
    $desc  = preg_replace('/\s+/', ' ', $desc);        //replace all white spaces to one space

    $desc = helper::escapeText($desc);


    $imgUrl = helper::clearText($imgUrl);
    $imgUrl = helper::escapeText($imgUrl);


    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $topicposts = new topicposts($dbo);
    $topicposts->setRequestFrom($accountId);

    $result = $topicposts->add($itemType,$general_topic_id, $support_topic_id, $desc, $imgUrl);

    echo json_encode($result);
    exit;
}