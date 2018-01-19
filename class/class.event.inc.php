<?php

/*!
 * ifsoft.co.uk v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class event extends db_connect
{
    private $requestFrom = 0;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM events");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM events");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function createEvent($userId, $title, $topic, $image = "") {

        $currentTime = time();
        $stmt = $this->db->prepare("INSERT INTO events (topic, user_id, title, image, created_at) value (:topic, :user_id, :title, :image, :created_at)");
        $stmt->bindParam(":topic", $topic, PDO::PARAM_STR);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":title", $title, PDO::PARAM_STR);
        $stmt->bindParam(":image", $image, PDO::PARAM_STR);
        $stmt->bindParam(":created_at", $currentTime, PDO::PARAM_STR);
		
		
        if ($stmt->execute()) {
			
			
			 
			            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "event_id" => $this->getMaxId()
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

    public function getHotEvents() {

        $stmt = $this->db->prepare("SELECT * FROM events WHERE event_status = 1 ORDER BY likes_count DESC LIMIT 50");

        if ($stmt->execute()) {
            $data = [];
            while ($row = $stmt->fetch()) {
                $profileId = $row['user_id'];
                $profile = new profile($this->db, $profileId);
                $profile->setRequestFrom($this->requestFrom);
                $profileInfo = $profile->getVeryShort();
                unset($profile);
                $row['user'] = $profileInfo;

                $eventId = $row['id'];
                $events_like = new events_like($this->db);
                $i_likes_count = $events_like->getCount($eventId, $this->getRequestFrom());
                if($i_likes_count>0) {
                    $row['i_like_status'] = true;
                } else {
                    $row['i_like_status'] = false;
                }
                unset($events_like);

                $data[] = $row;
            }

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "data" => $data
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
    public function removeEvent($event_id) {

        $stmt = $this->db->prepare("DELETE FROM events WHERE id = (:event_id)");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            
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

    public function resetAndGetCommentsCount($event_id) {

        $event_comment = new event_comment($this->db);
        $event_comment->setRequestFrom($this->requestFrom);
        $comments_count = $event_comment->getCount($event_id);
        unset($event_comment);

        $stmt = $this->db->prepare("UPDATE events SET comments_count = (:comments_count) WHERE id = (:event_id) AND event_status = 1");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
        $stmt->bindParam(":comments_count", $comments_count, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $comments_count;

        } else {
            return false;
        }
    }

    public function increaseEventCommentsCount($event_id) {

        $stmt = $this->db->prepare("UPDATE events SET comments_count = comments_count + 1 WHERE id = (:event_id) AND event_status = 1");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;

        } else {
            return false;
        }

        /*$result = array(
            "error" => true,
            "error_code" => ERROR_UNKNOWN,
            "error_message" => $stmt->errorInfo()
        );
        return $result;*/
    }

    public function decreaseEventCommentsCount($event_id) {

        $stmt = $this->db->prepare("UPDATE events SET comments_count = comments_count - 1 WHERE id = (:event_id) AND event_status = 1");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;

        } else {
            return false;
        }
    }

    public function resetAndGetLikesCount($event_id) {

        $events_like = new events_like($this->db);
        $events_like->setRequestFrom($this->requestFrom);
        $likes_count = $events_like->getCount($event_id);
        unset($events_like);

        $stmt = $this->db->prepare("UPDATE events SET likes_count = (:likes_count) WHERE id = (:event_id) AND event_status = 1");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
        $stmt->bindParam(":likes_count", $likes_count, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $likes_count;

        } else {
            return false;
        }
    }

    public function increaseEventLikesCount($event_id)
    {
        $stmt = $this->db->prepare("UPDATE events SET likes_count = likes_count + 1 WHERE id = (:event_id) AND event_status = 1");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;

        } else {
            return false;
        }
    }

    public function decreaseEventLikesCount($event_id)
    {
        $stmt = $this->db->prepare("UPDATE events SET likes_count = likes_count - 1 WHERE id = (:event_id) AND event_status = 1");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;

        } else {
            return false;
        }
    }

    public function getEvents($topic, $itemId) {
        if ($itemId == 0) {
            $itemId = $this->getMaxId();
            $itemId++;
        }

        $stmt = $this->db->prepare("SELECT * FROM events WHERE topic = (:topic)  AND id < (:itemId) AND event_status = 1 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':topic', $topic, PDO::PARAM_STR);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = [];
            while ($row = $stmt->fetch()) {
                $time = new language($this->db, $this->language);
                $profileId = $row['user_id'];
                $createAt = $row['created_at'];
                $profile = new profile($this->db, $profileId);
                $profile->setRequestFrom($this->requestFrom);
                $profileInfo = $profile->getVeryShort();
                unset($profile);

                $row['user'] = $profileInfo;
                $row['timeAgo'] = $time->timeAgo($createAt);

                $eventId = $row['id'];
                $events_like = new events_like($this->db);
                $i_likes_count = $events_like->getCount($eventId, $this->getRequestFrom());
                if($i_likes_count>0) {
                    $row['i_like_status'] = true;
                } else {
                    $row['i_like_status'] = false;
                }
                unset($events_like);

                $data[] = $row;
            }

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "data" => $data
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

    public function get($itemId = 0, $language = 'en')
    {
        if ($itemId == 0) {
            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                         "error_code" => ERROR_SUCCESS,
                         "itemId" => $itemId,
                         "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM photos WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $photo = new photos($this->db);
                    $photo->setRequestFrom($this->requestFrom);
                    $photoInfo = $photo->info($row['id']);
                    unset($post);

                    array_push($result['items'], $photoInfo);
                    $result['itemId'] = $photoInfo['id'];
                    unset($photoInfo);
                }
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

