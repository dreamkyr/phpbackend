<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class mgcm extends db_connect
{
    private $accountId = 0;
    // private $url = "https://android.googleapis.com/gcm/send";
   private $url = "https://fcm.googleapis.com/fcm/send";
    private $ids = array();
    private $data = array();
    private $deviceId = "";

    public function __construct($dbo = NULL, $accountId = 0, $deviceId = "")
    {
        parent::__construct($dbo);

        $this->accountId = $accountId;

        $this->deviceId = $deviceId;
        $this->addDeviceId($this->deviceId);
    }

    public function setIds($ids)
    {
        $this->ids = $ids;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function clearIds()
    {
        $this->ids = array();
    }

    public function send()
    {
        $result = array("error" => true,
                        "description" => "regId not found");

        if (empty($this->ids)) {

            return $result;
        }

        $post = array(
            'registration_ids'  => $this->ids,
            'data'              => $this->data,
        );

        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        @curl_exec($ch);

        $result = array("error" => false,
                        "success" => 1,
                        "description");

//        if (curl_errno($ch)) {
//
//            $result = array("error" => true,
//                            "failure" => 1);
//        }

        curl_close($ch);

        $obj = json_encode($result, true);
//
//        $status = 0;
//
//        if ($obj['success'] != 0) {
//
//            $status = 1;
//        }
//
//        $this->addToHistory($this->data['msg'], $this->data['type'], $status, $obj['success']);

        return $obj;
    }

    public function forAll()
    {
        $stmt = $this->db->prepare("SELECT gcm_regid FROM users WHERE gcm_regid <> ''");
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $this->addDeviceId($row['gcm_regid']);
        }
    }

    public function addDeviceId($id)
    {
        $this->ids[] = $id;
    }

    public function setData($msgType, $msg, $id = 0, $message = array())
    {
        $this->data = array("type" => $msgType,
                            "msg" => $msg,
                            "id" => $id,
                            "accountId" => $this->accountId,
                            "msgId" => $message['id'],
                            "msgFromUserId" => $message['fromUserId'],
                            "msgFromUserState" => $message['fromUserState'],
                            "msgFromUserVerify" => $message['fromUserVerify'],
                            "msgFromUserUsername" => $message['fromUserUsername'],
                            "msgFromUserFullname" => $message['fromUserFullname'],
                            "msgFromUserPhotoUrl" => $message['fromUserPhotoUrl'],
                            "msgMessage" => $message['message'],
                            "msgImgUrl" => $message['imgUrl'],
                            "msgCreateAt" => $message['createAt'],
                            "msgDate" => $message['date'],
                            "msgTimeAgo" => $message['timeAgo'],
                            "msgRemoveAt" => $message['removeAt']);
    }

    public function getData()
    {
        return $this->data;
    }

    public function clearData()
    {
        $this->data = array();
    }
}