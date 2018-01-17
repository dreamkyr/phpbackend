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
        $stmt = $this->db->prepare("SELECT count(*) FROM event_comments");
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

            $profileId = $row['user_id'];
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

    public function create($userId, $comment, $event_id) {

        $stmt = $this->db->prepare("INSERT INTO events_comments (event_id, user_id, comment, created_at) value (:event_id, :user_id, :comment, :created_at)");
        $stmt->bindParam(":event_id", $event_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindParam(":created_at", date("Y-m-d H:i:s"), PDO::PARAM_STR);

        if ($stmt->execute()) {
            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "new_comment" => $this->getLastItem()
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

        $stmt = $this->db->prepare("SELECT * FROM events_comments WHERE event_id = (:event_id)  AND id < (:itemId) AND comment_status = 1 ORDER BY id DESC");// LIMIT 20
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_STR);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = [];
            while ($row = $stmt->fetch()) {
                $profileId = $row['user_id'];
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

        } else {
            $result = array(
                "error" => true,
                "error_code" => ERROR_UNKNOWN,
                "error_message" => $stmt->errorInfo()
            );
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

