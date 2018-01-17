<?php



class topic_posts_comments extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getCount()
    {
        $stmt = $this->db->prepare("SELECT max(id) FROM topics_posts_comments");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM topics_posts_comments WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count($itemId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM topics_posts_comments WHERE topicPostId = (:itemId) AND removeAt = 0");
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($topicPostId, $fromUserId, $comment, $imgUrl)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $account = new account($this->db, $this->getRequestFrom());
        $account->setLastActive();
        unset($account);

        if (strlen($comment) == 0 && strlen($imgUrl) == 0) {

            $result['asd'] = "asd";

            return $result;
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO topics_posts_comments (topicPostId, itemFromUserId, fromUserId, conmment, imgUrl, createAt, ip_addr, u_agent) value (:topicPostId, :itemFromUserId, :fromUserId, :comment, :imgUrl, :createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":topicPostId", $topicPostId, PDO::PARAM_INT);
        $stmt->bindParam(":itemFromUserId", $itemFromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":fromUserId", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $insertId = $this->db->lastInsertId();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "topicPostId" => $insertId,
                            "comment" => $this->info($this->db->lastInsertId()));

            if (($itemFromUserId != 0) && ($this->requestFrom != $itemFromUserId)) {

                $account = new account($this->db, $itemFromUserId);

                if ($account->getAllowtopics_posts_commentsGCM() == ENABLE) {

                    $gcm = new gcm($this->db, $itemFromUserId);
                    $gcm->setData(GCM_NOTIFY_DISCUSSIOIN_COMMENT, "You have a new comment.", $topicPostId);
                    $gcm->send();
                }

                $notify = new notify($this->db);
                $notify->createNotify($itemFromUserId, $topicPostId, $this->getRequestFrom(), NOTIFY_TYPE_DISCUSSION_COMMENT, ITEM_TYPE_DISCUSSION_POST , $insertId);
                unset($notify);

                unset($account);
            }
        }

        $topicposts = new topicposts($this->db);
        $topicposts->setRequestFrom($this->getRequestFrom());

        $topicposts->recalculate($topicPostId, ITEM_TYPE_DISCUSSION_COMMENT);

        unset($item);

        $account = new account($this->db, $this->getRequestFrom());
        $account->updateCounters();
        unset($account);

        return $result;
    }

    public function recalculate($itemId) {

        $likes_count = 0;

        $postslike = new postslike($this->db);
        $postslike->setRequestFrom($this->getRequestFrom());

        $likes_count = $postslike->getLikesCount($itemId, ITEM_TYPE_DISCUSSION_COMMENT);

        unset($postslike);

        $stmt = $this->db->prepare("UPDATE topics_posts_comments SET likesCount = (:likesCount) WHERE id = (:itemId)");
        $stmt->bindParam(":likesCount", $likes_count, PDO::PARAM_INT);
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likesCount" => $likes_count,
                        "myLike" => false);

        return $result;
    }

    public function remove($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE topics_posts_comments SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $notifyType = NOTIFY_TYPE_DISCUSSION_COMMENT;
            $toItemType = ITEM_TYPE_DISSCUSSION_COMMENT;

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE itemId = (:itemId) AND notifyType = (:notifyType)");
            $stmt2->bindParam(":itemId", $itemId, PDO::PARAM_INT);
            $stmt2->bindParam(":notifyType", $notifyType, PDO::PARAM_INT);
            $stmt2->execute();

            //remove all reports to item

            $stmt5 = $this->db->prepare("UPDATE abuse_reports SET removeAt = (:removeAt) WHERE abuseToItemId = (:abuseToItemId) AND removeAt = 0 AND abuseToItemType = (:abuseToItemType)");
            $stmt5->bindParam(":abuseToItemId", $itemId, PDO::PARAM_INT);
            $stmt5->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt5->bindParam(":abuseToItemType", $toItemType, PDO::PARAM_INT);
            $stmt5->execute();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function removeAll($itemId) {

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE topics_posts_comments SET removeAt = (:removeAt) WHERE topicPostId = (:itemId)");
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
    }

    public function is_answer_exists($itemId, $fromUserId)
    {
        $stmt = $this->db->prepare("SELECT id FROM topics_posts_comments WHERE fromUserId = (:fromUserId) AND topicPostId = (:itemId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function info($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM topics_posts_comments WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $myLike = false;

                if ($this->requestFrom != 0) {

                    $postslike = new postslike($this->db);
                    $postslike->setRequestFrom($this->getRequestFrom());

                    if ($postslike->is_like_exists($row['id'], $this->getRequestFrom(), ITEM_TYPE_DISCUSSION_COMMENT)) {

                        $myLike = true;
                    }

                    unset($postslike);
                }

                $profile = new profile($this->db, $row['id']);
                $fromUserId = $profile->get();
                unset($profile);

                $lowPhotoUrl = "";

                if (strlen($fromUserId['normalPhotoUrl']) != 0) {

                    $lowPhotoUrl = $fromUserId['normalPhotoUrl'];
                }

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "comment" => htmlspecialchars_decode(stripslashes($row['comment'])),
                                "imgUrl" => $row['imgUrl'],
                                "fromUserId" => $row['fromUserId'],
                                "fromUserState" => $fromUserId['state'],
                                "fromUserVerified" => $fromUserId['verified'],
                                "fromUserOnline" => $fromUserId['online'],
                                "fromUserUsername" => $fromUserId['username'],
                                "fromUserFullname" => $fromUserId['fullname'],
                                "fromUserPhotoUrl" => $lowPhotoUrl,
                                "likesCount" => $row['likesCount'],
                                "myLike" => $myLike,
                                "topicPostId" => $row['topicPostId'],
                                "itemFromUserId" => $row['itemFromUserId'],
                                "createAt" => $row['createAt'],
                                "removeAt" => $row['removeAt'],
                                "timeAgo" => $time->timeAgo($row['createAt']));
            }
        }

        return $result;
    }

    public function get($itemId, $index = 0)
    {
        if ($index == 0) {

            $index = $this->getCount() + 1;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "index" => $index,
                        "topicPostId" => $itemId,
                        "comments" => array());

        $stmt = $this->db->prepare("SELECT id FROM topics_posts_comments WHERE topicPostId = (:itemId) AND id < (:index) AND removeAt = 0 ORDER BY id DESC LIMIT 70");
        $stmt->bindParam(':topicPostId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':index', $index, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['comments'], $itemInfo);

                $result['index'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }



    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }
}
