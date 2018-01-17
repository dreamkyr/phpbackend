<?php

/*!
 * ifsoft.co.uk v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class event_comment extends db_connect
{
    private $requestFrom = 0;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM events_comments");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getCount($event_id) {
        $stmt = $this->db->prepare("SELECT count(*) FROM events_comments WHERE event_id = (:event_id) AND comment_status = 1");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM events_comments");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getLastItem() {
        $stmt = $this->db->prepare("SELECT * FROM events_comments WHERE comment_status = 1 ORDER BY id DESC LIMIT 1");

        if ($stmt->execute()) {
            $row = $stmt->fetch();

            $profileId = $row['fromUserId'];
            $profile = new profile($this->db, $profileId);
            $profile->setRequestFrom($this->requestFrom);
            $profileInfo = $profile->getVeryShort();
            unset($profile);

            $row['user'] = $profileInfo;

            return $row;
        }

        $result = array(
            "error" => true,
            "error_code" => ERROR_UNKNOWN,
            "error_message" => $stmt->errorInfo()
        );
        return $result;
    }

    public function create($replyToUserId = 0, $comment, $event_id) {
		
		$result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

      

        $stmt = $this->db->prepare("INSERT INTO events_comments (event_id, fromUserId, replyToUserId, comment, createAt) value (:event_id, :fromUserId, :replyToUserId, :comment, :createAt)");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
	    $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":replyToUserId", $replyToUserId, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", date("Y-m-d H:i:s"), PDO::PARAM_STR);
		
		   if ($stmt->execute()) {
				 $new_comment = $this->getLastItem();

				$event = new event($this->db);
				$event->setRequestFrom($this->requestFrom);
				$event->increaseEventCommentsCount($event_id);
				unset($event);
				
				$account = new account($this->db, $this->requestFrom);
				$account->setLastActive();
				unset($account);

				$result = array(
					"error" => false,
					"error_code" => ERROR_SUCCESS,
					"new_comment" => $new_comment
				);
			} else 
			{
				$result = array(
					"error" => true,
					"error_code" => ERROR_UNKNOWN,
					"error_message" => $stmt->errorInfo()
				);
			} 
        return $result;
    }

    public function delete($user_id, $event_id) {

        $stmt = $this->db->prepare("UPDATE events_comments SET comment_status = 0 WHERE fromUserId = (:fromUserId) AND event_id = (:event_id) AND comment_status = 1");
        $stmt->bindParam(":fromUserId", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $event = new event($this->db);
            $event->setRequestFrom($this->requestFrom);
            $event->decreaseEventCommentsCount($event_id);
            unset($event);

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        } else {
            $result = array(
                "error" => true,
                "error_code" => ERROR_UNKNOWN,
                "error_message" => $stmt->errorInfo()
            );
        }

        return $result;
    }

    public function deleteComment($comment_id, $event_id) {

        $stmt = $this->db->prepare("UPDATE events_comments SET comment_status = 0 WHERE id = (:comment_id)");
        $stmt->bindParam(":comment_id", $comment_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $event = new event($this->db);
            $event->setRequestFrom($this->requestFrom);
            $event->decreaseEventCommentsCount($event_id);
            unset($event);

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        } else {
            $result = array(
                "error" => true,
                "error_code" => ERROR_UNKNOWN,
                "error_message" => $stmt->errorInfo()
            );
        }

        return $result;
    }


    public function getItems($event_id, $itemId) {
		
       if ($itemId == 0) {
            $itemId = $this->getMaxId();
            $itemId++;
        }
		

        $stmt = $this->db->prepare("SELECT * FROM events_comments WHERE event_id = (:event_id)  AND id < (:itemId) AND comment_status = 1 ORDER BY id DESC LIMIT 70");// LIMIT 20
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_STR);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

       if ($stmt->execute()) {
			$data = [];
            while ($row = $stmt->fetch()) {

               $profileId = $row['fromUserId'];
                $profile = new profile($this->db, $profileId);
                $profile->setRequestFrom($this->requestFrom);
                $profileInfo = $profile->getVeryShort();
                unset($profile);
                $row['user'] = $profileInfo;

                $data[] = $row;
            }
			
			 $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "data" => $data
            );
        }else {
            $result = array(
                "error" => true,
                "error_code" => ERROR_UNKNOWN,
                "error_message" => $stmt->errorInfo()
            );
        }

        return $result;

   
    }

	
	    public function commentsInfo($commentId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM event_comments WHERE id = (:commentId) LIMIT 1");
        $stmt->bindParam(":id", $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $profile = new profile($this->db, $row['fromUserId']);
                $fromUserId = $profile->get();
                unset($profile);

                $replyToUserId = $row['replyToUserId'];
                $replyToUserUsername = "";
                $replyToFullname = "";

                if ($replyToUserId != 0) {

                    $profile = new profile($this->db, $row['replyToUserId']);
                    $replyToUser = $profile->get();
                    unset($profile);

                    $replyToUserUsername = $replyToUser['username'];
                    $replyToFullname = $replyToUser['fullname'];
                }

                $lowPhotoUrl = "/img/profile_default_photo.png";

                if (strlen($fromUserId['lowPhotoUrl']) != 0) {

                    $lowPhotoUrl = $fromUserId['lowPhotoUrl'];
                }

             

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "comment" => stripslashes($row['comment']),
                                "fromUserId" => $row['fromUserId'],
                                "fromUserState" => $fromUserId['state'],
                                "fromUserVerify" => $fromUserId['verify'],
                                "fromUserUsername" => $fromUserId['username'],
                                "fromUserFullname" => $fromUserId['fullname'],
                                "fromUserPhotoUrl" => $lowPhotoUrl,
                                "replyToUserId" => $replyToUserId,
                                "replyToUserUsername" => $replyToUserUsername,
                                "replyToFullname" => $replyToFullname,
                                "event_id" => $row['event_id'],
                                "imageFromUserId" => $imageInfo['fromUserId'],
                                "createAt" => $row['createAt'],
                                "timeAgo" => $time->timeAgo($row['createAt']));
            }
        }

        return $result;
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

