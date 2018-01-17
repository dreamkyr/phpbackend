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
<h2>Welcome to the <?php echo APP_TITLE; ?> a LGBTQ Social Network.</h2>
<p><!-- Termly Tracking Code -->

<iframe width="100%" style="height: 95vh" src="https://app.termly.io/document/terms-of-use-for-saas/0abcdc6f-3be1-46c2-8ed3-a3422071b0fd" frameborder="0" allowfullscreen>
  <p>Your browser does not support iframes.</p>
</iframe></p>
</body>
</html>