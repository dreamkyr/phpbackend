<?php



class postslike extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';
    private $profileId = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getMaxIdLikes()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM topics_posts_likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getLikesCount($itemId, $itemType,  $itemType)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM topics_posts_likeslikes WHERE topicPostId = (:itemId) AND itemType = (:itemType) AND removeAt = 0");
		$stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($itemId, $itemFromUserId, $fromUserId,  $itemType)
    {
        $account = new account($this->db, $fromUserId);
        $account->setLastActive();
        unset($account);

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $myLike = false;

        if ($this->is_like_exists($itemId, $fromUserId,  $itemType)) {

            $removeAt = time();

            $stmt = $this->db->prepare("UPDATE topics_posts_likes SET removeAt = (:removeAt) WHERE topicPostId = (:itemId) AND fromUserId = (:fromUserId) AND itemType = (:itemType) AND  AND removeAt = 0");
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
			$stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
            $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
            $stmt->execute();

            $notify = new notify($this->db);
            $notify->removeNotify($itemFromUserId, $fromUserId, NOTIFY_TYPE_DISCUSSION_POST_LIKE, $itemType, $itemId);
            unset($notify);

            $myLike = false;

        } else {

            $createAt = time();
            $ip_addr = helper::ip_addr();

            $stmt = $this->db->prepare("INSERT INTO topics_posts_likes (toUserId, fromUserId, itemType, topicPostId, createAt, ip_addr) value (:toUserId, :fromUserId, :itemType, :itemId, :createAt, :ip_addr)");
            $stmt->bindParam(":toUserId", $itemFromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
			$stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
            $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
            $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
            $stmt->execute();

            $myLike = true;

            $likeId = $this->db->lastInsertId();

            if ($itemFromUserId != $fromUserId && $itemType != ITEM_TYPE_DISCUSSION_COMMENT) {

                $account = new account($this->db, $itemFromUserId);

                if ($account->getAllowLikesGCM() == ENABLE) {

                    $gcm = new gcm($this->db, $itemFromUserId);
                    $gcm->setData(GCM_NOTIFY_DISCUSSION_LIKE, "You have new Disucssion Post like", $itemId);
                    $gcm->send();
                }

                unset($account);

                $notify = new notify($this->db);
                $notify->createNotify($itemFromUserId, $itemId, $fromUserId, NOTIFY_TYPE_DISCUSSION_POST_LIKE, $itemType, $likeId);
                unset($notify);
            }
        }

        switch ($itemType) {

            case ITEM_TYPE_DISCUSSION_POST: {

                $topicposts = new topicposts($this->db);
                $topicposts->setRequestFrom($this->getRequestFrom());

                $result = $topicposts->recalculate($itemId);

                break;
            }

            case ITEM_TYPE_DISCUSSION_COMMENT: {

                $topic_posts_comments = new topic_posts_comments($this->db);
                $topic_posts_comments->setRequestFrom($this->getRequestFrom());

                $result = $topic_posts_comments->recalculate($itemId);

                break;
            }

            default: {

                break;
            }
        }

        if ($itemFromUserId != $this->getRequestFrom()) {

            $account = new account($this->db, $itemFromUserId);
            $account->updateCounters();
            unset($account);
        }

        $result['myLike'] = $myLike;

        return $result;
    }

    public function is_like_exists($itemId, $fromUserId, $itemType)
    {
        $stmt = $this->db->prepare("SELECT id FROM topics_posts_likes WHERE fromUserId = (:fromUserId) AND topicPostId = (:itemId) AND itemType = (:itemType) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function getLikers($itemId, $itemType, $likeId = 0)
    {

        if ($likeId == 0) {

            $likeId = $this->getMaxIdLikes();
            $likeId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likeId" => $likeId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM topics_posts_likes WHERE topicPostId = (:itemId) AND itemType = (:itemType) AND id < (:likeId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':topicPostId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);
        $stmt->bindParam(':likeId', $likeId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->get();
                    unset($profile);

                    array_push($result['items'], $profileInfo);

                    $result['likeId'] = $row['id'];
                }
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

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    public function getProfileId()
    {
        return $this->profileId;
    }
}
