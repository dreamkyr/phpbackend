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

<h3>--------</h3>

<p>1. The User shall use the Site Software and/or mobile app at his/her sole risk and expense. Software is made available for use on “as is” basis.</p>
<p>2. The Administration may not be held liable for any illegal acts of the User or any third parties.</p>
<p>3. The Administration shall bear no liability for any statements of the User published with use of <?php echo APP_TITLE; ?> Software. Also, the Administration shall not be liable for any (mis)conduct of the User while using the <?php echo APP_TITLE; ?> Software.</p>
<p>4. The Administration shall bear no liability for loss by the User of access to his/her account on the Site (including loss of login, password or any other details required for access to and use of <?php echo APP_TITLE; ?> Software).</p>
<p>5. The Administration shall bear no liability for any missing, inaccurate or incorrect data specified by the User when using the <?php echo APP_TITLE; ?> Software.</p>
<p>6. The Administration shall not exchange any elements of Site Supplemental Functionality acquired by the User for any other elements of the same.</p>
<p>7. The Administration shall not reimburse the User for any expenses connected with acquiring of the rights to use the Site anr/or mobile app Supplemental Functionality including in case where the User’s access to the Site <?php echo APP_TITLE; ?> and/or mobile app is suspended as a result, inter alia, of breaching the <?php echo APP_TITLE; ?> Terms and Policies, and/or should this Agreement is suspended or terminated by any reason.</p>
<p>8. The Administration shall not be obliged to present to the User any documents or other evidences of User’s breach hereof as a result of which the User was fully or partially denied a right to access the Site (and/ or mobile app).</p>
<p>9. The User shall not:
    <span>9.1. reproduce, duplicate, copy, sell, disseminate, trade in or resell the <?php echo APP_TITLE; ?> Software and/ or the right to use the same for any purpose whatsoever other than where such transactions have been expressly authorized in accordance herewith;</span>
    <span>9.2. distribute any audiovisual and/ or graphic images contained in <?php echo APP_TITLE; ?> Software, or any elements of its design or user interface outside the Site and/or mobile app, whether for commercial or non-commercial purpose, unless upon prior written permission from concerned right holders; and</span>
    <span>9.3. use Software by any means not stipulated herein or not admissible in the ordinary operation of <?php echo APP_TITLE; ?> Software.</span>
</p>
</body>
</html>