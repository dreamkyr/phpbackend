<?php

/*!
 * ifsoft.co.uk v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class events_like extends db_connect
{
    private $requestFrom = 0;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM events_likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getCount($event_id, $user_id="") {
        if($user_id == "") {
            $stmt = $this->db->prepare("SELECT count(*) FROM events_likes WHERE event_id = (:event_id) AND like_status = 1");
            $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);

        } else {
            $stmt = $this->db->prepare("SELECT count(*) FROM events_likes WHERE event_id = (:event_id) AND user_id = (:user_id) AND like_status = 1");
            $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM events_likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function update($user_id, $event_id) {
        if($this->getCount($event_id, $user_id)>0) {
            $result = $this->delete($user_id, $event_id);
            if(!$result["error"]) {
                $result = array(
                    "error" => false,
                    "error_code" => ERROR_SUCCESS,
                    "like_status" => false
                );
            }
            return $result;
        } else {
            $result = $this->create($user_id, $event_id);
            if(!$result["error"]) {
                $result = array(
                    "error" => false,
                    "error_code" => ERROR_SUCCESS,
                    "like_status" => true
                );
            }
            return $result;
        }
    }

    public function create($user_id, $event_id) {
        if($this->getCount($event_id, $user_id)>0) {
            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
            return $result;
        }

        $stmt = $this->db->prepare("INSERT INTO events_likes (event_id, user_id, created_at) value (:event_id, :user_id, :created_at)");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":created_at", date("Y-m-d H:i:s"), PDO::PARAM_STR);

        if ($stmt->execute()) {
            $event = new event($this->db);
            $event->setRequestFrom($this->requestFrom);
            $event->increaseEventLikesCount($event_id);
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

    public function delete($user_id, $event_id) {
        $stmt = $this->db->prepare("UPDATE events_likes SET like_status = 0 WHERE user_id = (:user_id) AND event_id = (:event_id) AND like_status = 1");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $event = new event($this->db);
            $event->setRequestFrom($this->requestFrom);
            $event->decreaseEventLikesCount($event_id);
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

    public function insert($data) {

        $stmt = $this->db->prepare("INSERT INTO event_contents (event_id, content, content_type, content_index, created_at, changed_at) ".
            "value (:event_id, :content, :content_type, :content_index, :created_at, :changed_at)");
        $stmt->bindParam(":event_id", $data["event_id"], PDO::PARAM_INT);
        $stmt->bindParam(":content", $data["content"], PDO::PARAM_STR);
        $stmt->bindParam(":content_type", $data["content_type"], PDO::PARAM_INT);
        $stmt->bindParam(":content_index", $data["content_index"], PDO::PARAM_INT);
        $stmt->bindParam(":created_at", date("Y-m-d H:i:s"), PDO::PARAM_STR);
        $stmt->bindParam(":changed_at", date("Y-m-d H:i:s"), PDO::PARAM_STR);

        if ($stmt->execute()) {
            $result = array("error" => false,
                "error_code" => ERROR_SUCCESS);

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

