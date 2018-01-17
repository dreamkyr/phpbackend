<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

// Path to move uploaded files

$uploaded_file = "";
$uploaded_file_name = "";
$uploaded_file_ext = "";

$file_ext = "";

// array for final json respone
$response = array("error" => true);

if (!empty($_POST)) {

    // reading other post parameters
    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    if (isset($_FILES['uploaded_file']['name'])) {

        $uploaded_file = $_FILES['uploaded_file']['tmp_name'];
        $uploaded_file_name = basename($_FILES['uploaded_file']['name']);
        $uploaded_file_ext = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);

        try {

            $time = time();
            if (!move_uploaded_file($_FILES['uploaded_file']['tmp_name'], "../../".TEMP_PATH."{$time}.".$uploaded_file_ext)) {

                // make error flag true
                $response['error'] = true;
                $response['message'] = 'Could not move the file!';
            }

            $imglib = new imglib($dbo);
            $response = $imglib->createPhoto("../../".TEMP_PATH."{$time}.".$uploaded_file_ext);
            unset($imglib);

            if ($response['error'] === false) {

                $account = new account($dbo, $accountId);
                $account->setPhoto($response);
            }

        } catch (Exception $e) {

            // Exception occurred. Make error flag true
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        }
    }

    // Echo final json response to client
    echo json_encode($response);
}
