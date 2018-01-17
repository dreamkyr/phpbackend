<?php



class topicposts extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';
    private $profileId = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM topicposts WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM topicposts");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }


    public function add($itemType, $general_topics_id, $support_topics_id,$description, $imgUrl = "")
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if (strlen($description) == 0 && strlen($imgUrl) == 0 && $general_topics_id == 0 && $support_topics_id == 0) {

            return $result;
        }

      
        $urlDescription = "";
        $urlImage = "";
      

        if (strlen($description) != 0) {

            $description = $description." ";
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO topic_posts (toUserId, general_topics_id, support_topics_id, fromUserId, itemType, description, imgUrl,createAt, ip_addr, u_agent) value (:toUserId, :general_topics_id, :support_topics_id, :fromUserId, :itemType, :description,:imgUrl,:createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":toUserId", $wall_id, PDO::PARAM_INT);
		$stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":general_topics_id", $general_topics_id, PDO::PARAM_INT);
		$stmt->bindParam(":support_topics_id", $support_topics_id, PDO::PARAM_INT);
        $stmt->bindParam(":fromUserId", $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(":description", $description, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "id" => $this->db->lastInsertId(),
                            "topicpost" => $this->info($this->db->lastInsertId()));

            $account = new account($this->db, $this->getRequestFrom());
            $account->updateCounters();
            unset($account);

            $discussions = new discussions($this->db);
            $discussions->setRequestFrom($this->getRequestFrom());

            $discussions->recalculateGeneral($general_topics_id);
			$discussions->recalculateSupport($support_topics_id);

            unset($discussions);
        }

        return $result;
    }

    public function edit($itemId, $general_topics_id,$support_topics_id, $description, $imgUrl = "")
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $urlDescription = "";
        $urlImage = "";
       

        if (strlen($description) == 0 && strlen($imgUrl) == 0 && $general_topics_id == 0 && $support_topics_id == 0) {

            return $result;
        }

        if (strlen($description) != 0) {

            $description = $description." ";
        }

        $stmt = $this->db->prepare("UPDATE topic_posts SET general_topics_id = (:general_topics_id), support_topics_id = (:support_topics_id), description = (:description), imgUrl = (:imgUrl) WHERE id = (:itemId)");
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":general_topics_id", $general_topics_id, PDO::PARAM_INT);
        $stmt->bindParam(":description", $description, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "id" => $itemId);

            $discussions = new discussions($this->db);
            $discussions->setRequestFrom($this->getRequestFrom());

            $discussions->recalculateGeneral($general_topics_id);
            $discussions->recalculateSupport($support_topics_id);
            unset($discussions);
        }

        return $result;
    }

    public function close($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $itemInfo = $this->info($itemId);

        if ($itemInfo['error'] === true) {

            return $result;
        }

        if ($itemInfo['fromUserId'] != $this->getRequestFrom() || $itemInfo['toUserId'] != $this->getRequestFrom()) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE topic_posts SET closeAt = (:closeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":closeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function remove($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $itemInfo = $this->info($itemId);

        if ($itemInfo['error'] === true) {

            return $result;
        }

        if ($itemInfo['fromUserId'] != $this->getRequestFrom() || $itemInfo['toUserId'] != $this->getRequestFrom()) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE topic_posts SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $toItemType = ITEM_TYPE_DISCUSSION_POST;

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE notifyToItemId = (:notifyToItemId) AND notifyToItemType = (:notifyToItemType)");
            $stmt2->bindParam(":notifyToItemId", $itemId, PDO::PARAM_INT);
            $stmt2->bindParam(":notifyToItemType", $toItemType, PDO::PARAM_INT);
            $stmt2->execute();

            //remove all comments to item

            $stmt3 = $this->db->prepare("UPDATE topics_posts_comments SET removeAt = (:removeAt) WHERE topicPostId = (:itemId) AND removeAt = 0");
            $stmt3->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt3->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
            $stmt3->execute();



            //remove all likes to item

            $stmt4 = $this->db->prepare("UPDATE topic_posts_likes SET removeAt = (:removeAt) WHERE topicPostId = (:itemId) AND removeAt = 0 AND itemType = (:itemType)");
            $stmt4->bindParam(":topicPostId", $itemId, PDO::PARAM_INT);
            $stmt4->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt4->bindParam(":itemType", $toItemType, PDO::PARAM_INT);
            $stmt4->execute();

            //remove all reports to item

            $stmt5 = $this->db->prepare("UPDATE abuse_reports SET removeAt = (:removeAt) WHERE abuseToItemId = (:abuseToItemId) AND removeAt = 0 AND abuseToItemType = (:abuseToItemType)");
            $stmt5->bindParam(":abuseToItemId", $itemId, PDO::PARAM_INT);
            $stmt5->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt5->bindParam(":abuseToItemType", $toItemType, PDO::PARAM_INT);
            $stmt5->execute();

            $account = new account($this->db, $itemInfo['toUserId']);
            $account->updateCounters();
            unset($account);

            $discussions = new discussions($this->db);
            $discussions->setRequestFrom($this->getRequestFrom());

            $discussions->recalculateGeneral($itemInfo['general_topics_id']);
			$discussions->recalculateSupport($itemInfo['support_topics_id']);

            unset($categories);

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function remove_all()
    {

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT id FROM topic_posts WHERE toUserId = (:toUserId) AND removeAt = 0");
        $stmt->bindParam(':toUserId', $this->getRequestFrom(), PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);

            while ($row = $stmt->fetch()) {

                $this->remove($row['id']);
            }
        }

        return $result;
    }

    public function restore($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $itemInfo = $this->info($itemId);

        if ($itemInfo['error'] === true) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE topic_posts SET removeAt = 0 WHERE id = (:itemId)");
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function recalculate($itemId) {

        $comment_count = 0;
        $likes_count = 0;
      

        $postslike = new postslike($this->db);
        $postslike->setRequestFrom($this->getRequestFrom());

        $topic_post_count = $postslike->getLikesCount($itemId,ITEM_TYPE_DISCUSSION_POST);

        unset($like);

        $topic_posts_comments = new topic_posts_comments($this->db);
        $topic_posts_coments->setRequestFrom($this->getRequestFrom());

        $topic_comments_count = $topic_comments_count->count($itemId);

        unset($topic_posts_comments);

        

        $stmt = $this->db->prepare("UPDATE topic_posts SET likesCount = (:likesCount), commentsCount = (:commentsCount) WHERE id = (:itemId)");
        $stmt->bindParam(":likesCount", $likes_count, PDO::PARAM_INT);
        $stmt->bindParam(":commentsCount", $answers_count, PDO::PARAM_INT);
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likesCount" => $topic_likes_count,
                        "commentsCount" => $comment_count,
                        "myLike" => false);

        return $result;
    }

    public function info($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM topic_posts WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->getLanguage());

                $myLike = false;

                if ($this->requestFrom != 0) {

				
				     $postslike = new postslike($this->db);
					 $postslikelike->setRequestFrom($this->getRequestFrom());
             
                    if (postslike->is_like_exists($itemId, $this->getRequestFrom(), ITEM_TYPE_DISCUSSION_POST)) {

                        $myLike = true;
                    }

                   unset($postslike);
                }

                $profile = new profile($this->db, $row['fromUserId']);
                $profileInfo = $profile->get();
                unset($profile);


                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "accessMode" => $row['accessMode'],
								"itemType" => $row['itemType'],
                                "toUserId" => $row['toUserId'],
                                "fromUserId" => $row['fromUserId'],
                                "fromUserVerified" => $profileInfo['verified'],
                                "fromUserOnline" => $profileInfo['online'],
                                "fromUserUsername" => $profileInfo['username'],
                                "fromUserFullname" => $profileInfo['fullname'],
                                "fromUserPhoto" => $profileInfo['photoUrl'],
                                "general_topics_id" => $row['general_topics_id'],
								"support_topics_id" => $row['support_topics_id'],
                                "desc" => htmlspecialchars_decode(stripslashes($row['description'])),                            
                                "imgUrl" => $row['imgUrl'],                         
                                "commentsCount" => $row['commentsCount'],
                                "answersCount" => $row['answersCount'],
                                "likesCount" => $row['likesCount'],                           
                                "myLike" => $myLike,
                                "createAt" => $row['createAt'],
                                "closeAt" => $row['closeAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function info_short($row)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $time = new language($this->db, $this->getLanguage());

        $myLike = false;

        if ($this->requestFrom != 0) {

		    $postslike = new postslike($this->db);
            $postslikelike->setRequestFrom($this->getRequestFrom());

            if (postslike->is_like_exists($row['id'], $this->getRequestFrom(), ITEM_TYPE_DISCUSSION_POST) {

                $myLike = true;
            }
			unset($postslike);
         
        }

        $profile = new profile($this->db, $row['fromUserId']);
        $profileInfo = $profile->get();
        unset($profile);


        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $row['id'],
                        "accessMode" => $row['accessMode'],
						"itemType" => $row['itemType'],
                        "toUserId" => $row['toUserId'],
                        "fromUserId" => $row['fromUserId'],
                        "fromUserVerified" => $profileInfo['verified'],
                        "fromUserOnline" => $profileInfo['online'],
                        "fromUserUsername" => $profileInfo['username'],
                        "fromUserFullname" => $profileInfo['fullname'],
                        "fromUserPhoto" => $profileInfo['photoUrl'],
                        "general_topics_id" => $row['general_topics_id'],
                        "desc" => htmlspecialchars_decode(stripslashes($row['description'])),
                        "imgUrl" => $row['imgUrl'],
                        "commentsCount" => $row['commentsCount'],
                        "answersCount" => $row['answersCount'],
                        "likesCount" => $row['likesCount'],
                        "myLike" => $myLike,
                        "createAt" => $row['createAt'],
                        "closeAt" => $row['closeAt'],
                        "date" => date("Y-m-d H:i:s", $row['createAt']),
                        "timeAgo" => $time->timeAgo($row['createAt']),
                        "removeAt" => $row['removeAt']);

        return $result;
    }

    public function get($profileId, $itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $itemId,
                        "topicposts" => array());

        $stmt = $this->db->prepare("SELECT * FROM topic_posts WHERE toUserId = (:toUserId) AND removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':toUserId', $profileId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info_short($row);

                array_push($result['topicposts'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }





    public function favorites($itemId = 0)
    {
        if ($itemId == 0) {

            $postslike = new postslike($this->db);
            $postslikelike->setRequestFrom($this->getRequestFrom());

            $itemId = $postslike->getMaxIdLikes();
            $itemId++;

            unset($postslike);
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "topicPostId" => $itemId,
                        "items" => array());

        $itemType = ITEM_TYPE_POST;

        $stmt = $this->db->prepare("SELECT id, topicPostId FROM topic_posts_likes WHERE removeAt = 0 AND id < (:itemId) AND fromUserId = (:fromUserId)  ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':fromUserId', $this->getRequestFrom(), PDO::PARAM_INT);
        $stmt->bindParam(':topicPostId', $itemId, PDO::PARAM_INT);


        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $itemInfo = $this->info($row['topicPostId']);

                    array_push($result['topicposts'], $itemInfo);

                    $result['topicPostId'] = $row['id'];

                    unset($itemInfo);
                }
            }
        }

        return $result;
    }

    public function hashtag($itemId, $hashtag)
    {
        $originQuery = $hashtag;

        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "query" => $originQuery,
                        "items" => array());

        $hashtag = str_replace('#', '', $hashtag);
        $search_explode = explode(' ', trim($hashtag, ' '));

        $sql = "SELECT * FROM topic_posts WHERE (description LIKE '%#{$search_explode[0]} %' OR description LIKE '#{$search_explode[0]}' OR description LIKE '% #{$search_explode[0]} %' OR description LIKE '% #{$search_explode[0]},%' OR description LIKE '% #{$search_explode[0]}!%' OR description LIKE '% #{$search_explode[0]}.%' OR description LIKE '%,#{$search_explode[0]},%' OR description LIKE '#{$search_explode[0]},%') AND removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $itemInfo = $this->info_short($row);

                    array_push($result['items'], $itemInfo);

                    $result['itemId'] = $itemInfo['id'];

                    unset($itemInfo);
                }
            }
        }

        return $result;
    }

    private function addScheme($url, $scheme = 'http://')
    {
        return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
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
