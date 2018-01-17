<?php



include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;
    $itemId = isset($_POST['id']) ? $_POST['id'] : 0;

    $profileId = helper::clearInt($profileId);
    $itemId = helper::clearInt($itemId);

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $topicposts = new topicposts($dbo);
    $topicposts->setRequestFrom($accountId);

    $itemInfo = $topicposts->info($itemId);

    $myAnswer = false;

    if ($accountId != 0) {

        $topic_posts_comments = new topic_posts_comments($dbo);
        $topic_posts_comments->setRequestFrom($accountId);

        if ($topic_posts_comments->is_answer_exists($itemId, $accountId)) {

            $myAnswer = true;
        }

        unset($topic_posts_comments);
    }

    $itemInfo['myAnswer'] = $myAnswer;

    if ($itemInfo['error'] === false && $itemInfo['removeAt'] == 0) {

        $topic_posts_comments = new topic_posts_comments($dbo);
        $topic_posts_comments->setRequestFrom($accountId);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $itemId,
                        "comments" => $topic_posts_comments->get($itemId, 0),
                        "items" => array());

        array_push($result['items'], $itemInfo);

        unset($topic_posts_comments);
    }

    unset($itemInfo);
    unset($topicpost);

    echo json_encode($result);
    exit;
}
