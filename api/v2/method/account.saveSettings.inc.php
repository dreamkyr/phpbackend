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

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    $iIntro = isset($_POST['iIntro']) ? $_POST['iIntro'] : '';

   
    $year = isset($_POST['year']) ? $_POST['year'] : 0;
    $month = isset($_POST['month']) ? $_POST['month'] : 0;
    $day = isset($_POST['day']) ? $_POST['day'] : 0;

    $iAge = isset($_POST['iAge']) ? $_POST['iAge'] : 0;
    $iHeight = isset($_POST['iHeight']) ? $_POST['iHeight'] : 0;
    $iBodyType = isset($_POST['iBodyType']) ? $_POST['iBodyType'] : 0;
    $iEthnicity = isset($_POST['iEthnicity']) ? $_POST['iEthnicity'] : 0;
    $iZodiac = isset($_POST['iZodiac']) ? $_POST['iZodiac'] : 0;
    $iAM = isset($_POST['iAM']) ? $_POST['iAM'] : '';
    $iInterestedIN = isset($_POST['iInterestedIN']) ? $_POST['iInterestedIN'] : '';
    $iRelationshipStatus = isset($_POST['iRelationshipStatus']) ? $_POST['iRelationshipStatus'] : '';
    $iLiving = isset($_POST['iLiving']) ? $_POST['iLiving'] : '';
    
    $iOccupation = isset($_POST['iOccupation']) ? $_POST['iOccupation'] : '';
    $iEducation = isset($_POST['iEducation']) ? $_POST['iEducation'] : 0;
	$iPronouns = isset($_POST['iPronouns']) ? $_POST['iPronouns'] : '';
    $iSmoke	= isset($_POST['iSmoke']) ? $_POST['iSmoke'] : 0;
    $iDrink = isset($_POST['iDrink']) ? $_POST['iDrink'] : 0;
	$iSexPosition = isset($_POST['iSexPosition']) ? $_POST['iSexPosition'] : 0;
    $iLookingFor = isset($_POST['iLookingFor']) ? $_POST['iLookingFor'] : '';
    $iInto = isset($_POST['iInto']) ? $_POST['iInto'] : '';
    $iSexualHealth = isset($_POST['iSexualHealth']) ? $_POST['iSexualHealth'] : 0;
    $iMusic = isset($_POST['iMusic']) ? $_POST['iMusic'] : '';
	$iMovies = isset($_POST['iMovies']) ? $_POST['iMovies'] : '';
    $iSports = isset($_POST['iSports']) ? $_POST['iSports'] : '';
    $iGoingOut = isset($_POST['iGoingOut']) ? $_POST['iGoingOut'] : '';
    $iPetPeeves = isset($_POST['iPetPeeves']) ? $_POST['iPetPeeves'] : '';
    $iFetishes = isset($_POST['iFetishes']) ? $_POST['iFetishes'] : '';
	$iDealBreaker = isset($_POST['iDealBreaker']) ? $_POST['iDealBreaker'] : '';

    $allowShowMyBirthday = isset($_POST['allowShowMyBirthday']) ? $_POST['allowShowMyBirthday'] : 0;

    $iAge = helper::clearInt($iAge);
    $iHeight = helper::clearInt($iHeight);
    $iBodyType = helper::clearInt($iBodyType);
    $iEthnicity = helper::clearInt($iEthnicity);
    $iZodiac = helper::clearInt($iZodiac);
    $iAM = helper::clearText($iAM);
	$iAM = helper::escapeText($iAM);
    $iInterestedIN = helper::clearText($iInterestedIN);
    $iInterestedIN = helper::escapeText($iInterestedIN);
    $iRelationshipStatus = helper::clearText($iRelationshipStatus);
	$iRelationshipStatus = helper::escapeText($iRelationshipStatus);
    $iLiving = helper::clearText($iLiving);
	$iLiving = helper::escapeText($iLiving);
    $iEducation = helper::clearInt($iEducation);
	$iPronouns = helper::clearText($iPronouns);
	$iPronouns = helper::escapeText($iPronouns);
    $iSmoke = helper::clearInt($iSmoke);
    $iDrink = helper::clearInt($iDrink);
	$iSexPosition = helper::clearInt($iSexPosition);
    $iLookingFor = helper::clearText($iLookingFor);
	$iLookingFor = helper::escapeText($iLookingFor);
    $iInto = helper::clearText($iInto);
	$iInto = helper::escapeText($iInto);
    $iSexualHealth = helper::clearInt($iSexualHealth);
    $iMusic = helper::clearText($iMusic);
	$iMusic = helper::escapeText($iMusic);
    $iMovies = helper::clearText($iMovies);
	$iMovies = helper::escapeText($iMovies);
    $iSports = helper::clearText($iSports);
	$iSports = helper::escapeText($iSports);
	$iGoingOut = helper::clearText($iGoingOut);
	$iGoingOut = helper::escapeText($iGoingOut);
 
 
   
    $allowShowMyBirthday = helper::clearInt($allowShowMyBirthday);

    $accountId = helper::clearInt($accountId);

    $fullname = helper::clearText($fullname);
    $fullname = helper::escapeText($fullname);

    $location = helper::clearText($location);
    $location = helper::escapeText($location);


    $iIntro = helper::clearText($iIntro);

    $iIntro = preg_replace( "/[\r\n]+/", " ", $iIntro);    //replace all new lines to one new line
    $iIntro = preg_replace('/\s+/', ' ', $iIntro);        //replace all white spaces to one space

    $iIntro = helper::escapeText($iIntro);

    $iOccupation = helper::clearText($iOccupation);
	
	$iOccupation = preg_replace("/[\r\n]+/", " ", $iOccupation);
	$iOccupation = preg_replace('/\s+/', ' ', $iOccupation);
	
	$iOccupation = helper::escapeText($iOccupation);
	
	$iPetPeeves = helper::clearText($iPetPeeves);

    $iPetPeeves = preg_replace( "/[\r\n]+/", " ", $iPetPeeves);    //replace all new lines to one new line
    $iPetPeeves = preg_replace('/\s+/', ' ', $iPetPeeves);        //replace all white spaces to one space

    $iPetPeeves = helper::escapeText($iPetPeeves);
	
	$iFetishes = helper::clearText($iFetishes);

    $iFetishes = preg_replace( "/[\r\n]+/", " ", $iFetishes);    //replace all new lines to one new line
    $iFetishes = preg_replace('/\s+/', ' ', $iFetishes);        //replace all white spaces to one space

    $iFetishes = helper::escapeText($iFetishes);
	
	$iDealBreaker = helper::clearText($iDealBreaker);

    $iDealBreaker = preg_replace( "/[\r\n]+/", " ", $iDealBreaker);    //replace all new lines to one new line
    $iDealBreaker = preg_replace('/\s+/', ' ', $iDealBreaker);        //replace all white spaces to one space

    $iDealBreaker = helper::escapeText($iDealBreaker);

    $year = helper::clearInt($year);
    $month = helper::clearInt($month);
    $day = helper::clearInt($day);


    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $account = new account($dbo, $accountId);
    $account->setLastActive();

    $account->setFullname($fullname);
    $account->setLocation($location);
    $account->setiIntro($iIntro);

    $account->setBirth($year, $month, $day);

    $account->set_iAge($iAge);
    $account->set_iHeight($iHeight);
    $account->set_iBodyType($iBodyType);
    $account->set_iEthnicity($iEthnicity);
    $account->set_iZodiac($iZodiac);
    $account->set_iAM($iAM);
    $account->set_iInterestedIN($iInterestedIN);
    $account->set_iRelationshipStatus($iRelationshipStatus);
    $account->set_iLiving($iLiving);
    $account->set_iOccupation($iOccupation);
    $account->set_iEducation($iEducation);
	$account->set_iPronouns($iPronouns);
    $account->set_iSmoke($iSmoke);
    $account->set_iDrink($iDrink);
	$account->set_iSexPosition($iSexPosition);
    $account->set_iLookingFor($iLookingFor);
    $account->set_iInto($iInto);
    $account->set_iSexualHealth($iSexualHealth);
    $account->set_iMusic($iMusic);
    $account->set_iMovies($iMovies);
    $account->set_iSports($iSports);
	$account->set_iGoingOut($iGoingOut);
    $account->set_iPetPeeves($iPetPeeves);
    $account->set_iFetishes($iFetishes);
    $account->set_iDealBreaker($iDealBreaker);
 
    $account->set_allowShowMyBirthday($allowShowMyBirthday);


    $result = $account->get();

    echo json_encode($result);
    exit;
}
