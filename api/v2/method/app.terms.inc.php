<?php

    include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
    include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

    header("Content-type: text/html; charset=utf-8");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Licenses</title>

    <style type="text/css">

        html {
            background-color: #fff;
        }

        body {
            margin: 20px;
        }

        h2 {
            display: block;
            font-weight: bold;
            font-size: 16px;
        }

        h3 {
            margin-top: 20px;
            display: block;
            font-weight: normal;
            font-size: 14px;
        }

        p {
            margin-bottom: 20px;
            margin-top: 20px;
            display: block;
        }

        span {
            display: block;
            margin-left: 20px;
            margin-top: 10px;
        }

    </style>

</head>
<body>
<h2>Welcome to the <?php echo APP_TITLE; ?> site and/or mobile app, a web resource that helps you post messages and images.</h2>
<p>The <?php echo APP_TITLE; ?> site (<?php echo APP_URL; ?>) (herein after – the Application) and/or mobile app is a network project uniting the people who love make posts news, messages, images.</p>

<p>The text you have to write your own, because one person has forbidden me to use "words" :) I'm sorry, but I have no right to write the text in this section :) Now I will write only code :)
    <br>
    <br>
    Say "thank you" to this good man :)
    <br>
    <br>
    Please edit the file: app/v2/method/app.terms.inc.php</p>
</body>
</html>