<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * raccoonsquare@gmail.com
 *
 * Copyright 2012-2018 Demyanchuk Dmitry (raccoonsquare@gmail.com)
 */

class fcm extends db_connect
{
    private $accountId = 0;
    private $url = "https://fcm.googleapis.com/fcm/send";
    private $ids = array();
    private $data = array();

    public function __construct($dbo = NULL, $accountId = 0)
    {
        parent::__construct($dbo);

        $this->accountId = $accountId;

        if ($this->accountId != 0) {

            $account = new account($this->db, $this->accountId);

            $deviceId = $account->getGCM_regId();

            if (strlen($deviceId) != 0) {

                $this->addDeviceId($deviceId);
            }

            $ios_deviceId = $account->get_iOS_regId();

            if (strlen($ios_deviceId) != 0) {

                $this->addDeviceId($ios_deviceId);
            }
        }
    }

    public function setIds($ids)
    {
        $this->ids = $ids;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function setAccountId($id)
    {
        $this->accountId = $id;
    }

    public function getAccountId()
    {
        return $this->accountId;
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

        $notify = array("priority" => "high");

        $post = array(
            'registration_ids'   => $this->ids,
            'notification'       => $notify,
            'priority'           => "high",
            'data'               => $this->data,
            'content_available'  => true,
        );

        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $this->url);
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {

            $result = array("error" => true,
                            "failure" => 1,
                            "description" => curl_error($ch));
        }

        curl_close($ch);

        $obj = json_decode($result, true);

        return $result;
    }

    public function addDeviceId($id)
    {
        $this->ids[] = $id;
    }

    public function setData($fcmId, $msgType, $msg, $addon, $id = 0)
    {
        $this->data = array("type" => $fcmId,
                            "msgType" => $msgType,
                            "msg" => $msg,
                            "addon" => $addon,
                            "id" => $id,
                            "accountId" => $this->accountId);
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