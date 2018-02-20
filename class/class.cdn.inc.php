<?php

/*!
 * ifsoft.co.uk v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk, qascript@mail.ru
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class cdn extends db_connect
{
    private $ftp_url = "";
    private $ftp_server = "";
    private $ftp_user_name = "";
    private $ftp_user_pass = "";
    private $cdn_server = "";
    private $conn_id = false;

    public function __construct($dbo = NULL)
    {
        $this->conn_id = @ftp_connect($this->ftp_server);

        parent::__construct($dbo);
    }

    public function upload($file, $remote_file)
    {
        $remote_file = $this->cdn_server.$remote_file;

        if ($this->conn_id) {

            if (@ftp_login($this->conn_id, $this->ftp_user_name, $this->ftp_user_pass)) {

                // upload a file
                if (@ftp_put($this->conn_id, $remote_file, $file, FTP_BINARY)) {

                    return true;

                } else {

                    return false;
                }
            }
        }
    }

    public function uploadMyPhoto($imgFilename)
    {
        rename($imgFilename, "../../".MY_PHOTOS_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".MY_PHOTOS_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadPhoto($imgFilename)
    {
        rename($imgFilename, "../../".PHOTO_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".PHOTO_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadCover($imgFilename)
    {
        rename($imgFilename, "../../".COVER_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".COVER_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadChatImg($imgFilename)
    {
        rename($imgFilename, "../../".CHAT_IMAGE_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".CHAT_IMAGE_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadAudio($audioFilename){
        rename("../../".$audioFilename, "../../".AUDIO_PATH.basename($audioFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".AUDIO_PATH.basename($audioFilename));

        @unlink($audioFilename);

        return $result;
    }

    public function uploadVideo($imgFilename)
    {
        rename("../../".$imgFilename, "../../".VIDEO_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".VIDEO_PATH.basename($imgFilename));

        @unlink($imgFilename);

        return $result;
    }
}
