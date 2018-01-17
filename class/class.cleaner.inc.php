<?php

/*!
     * ifsoft.co.uk v1.0
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk
     *
     * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

class cleaner extends db_connect
{

	private $requestFrom = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function cleanPhotos()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET lowPhotoUrl = '', originPhotoUrl = '', normalPhotoUrl = '', bigPhotoUrl = ''");

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function cleanGallery()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET photos_count = 0 WHERE photos_count > 0");

        if ($stmt->execute()) {

            $stmt2 = $this->db->prepare("UPDATE photos SET removeAt = 1 WHERE removeAt = 0");

            if ($stmt2->execute()) {

                $stmt3 = $this->db->prepare("UPDATE images_comments SET removeAt = 1 WHERE removeAt = 0");
                $stmt3->execute();

                $stmt4 = $this->db->prepare("UPDATE images_likes SET removeAt = 1 WHERE removeAt = 0");
                $stmt4->execute();

                $stmt5 = $this->db->prepare("UPDATE notifications SET removeAt = 1 WHERE notifyType > 6 AND notifyType < 10");
                $stmt5->execute();

                $stmt6 = $this->db->prepare("UPDATE photo_abuse_reports SET removeAt = 1 WHERE removeAt = 0");
                $stmt6->execute();

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);
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

