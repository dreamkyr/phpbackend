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

        $stmt = $this->db->prepare("INSERT INTO events (topic, user_id, title, image, created_at) value (:topic, :user_id, :title, :image, :created_at)");
        $stmt->bindParam(":topic", $topic, PDO::PARAM_STR);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":title", $title, PDO::PARAM_STR);
        $stmt->bindParam(":image", $image, PDO::PARAM_STR);
        $stmt->bindParam(":created_at", date("Y-m-d H:i:s"), PDO::PARAM_STR);

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

