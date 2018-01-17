<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2015 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

$imgFileUrl = "";
$videoFileUrl = "";

$result = array("error" => true, "qwerty" => "a");

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    if (isset($_FILES['uploaded_file']['name'])) {

        $currentTime = time();
        $uploaded_file_ext = @pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);

        if (@move_uploaded_file($_FILES['uploaded_file']['tmp_name'], "../../".TEMP_PATH."{$currentTime}.".$uploaded_file_ext)) {

            $imgLib = new imglib($dbo);
            $response = $imgLib->createMyPhoto("../../".TEMP_PATH."{$currentTime}.".$uploaded_file_ext);

            if ($response['error'] === false) {

                $imgFileUrl = $response['normalPhotoUrl'];

                $result = array("error" => false,
                                "imgFileUrl" => $imgFileUrl,
                                "videoFileUrl" => $videoFileUrl);
            }

            unset($imgLib);
        }

//        $result = array("error" => true, "qwerty" => "b");
    }

    if (isset($_FILES['uploaded_video_file']['name'])) {

        $currentTime = time();
        $uploaded_file_ext = @pathinfo($_FILES['uploaded_video_file']['name'], PATHINFO_EXTENSION);

        if (@move_uploaded_file($_FILES['uploaded_video_file']['tmp_name'], "../../".TEMP_PATH."{$currentTime}.".$uploaded_file_ext)) {

            $cdn = new cdn($dbo);

            $response = $cdn->uploadVideo(TEMP_PATH."{$currentTime}.".$uploaded_file_ext);

            if ($response['error'] === false) {

                $videoFileUrl = $response['fileUrl'];

                $result = array("error" => false,
                                "imgFileUrl" => $imgFileUrl,
                                "videoFileUrl" => $videoFileUrl);
            }

            unset($cdn);

//            $result = array("error" => true, "qwerty" => "c");
        }
    }

    echo json_encode($result);
    exit;
}
