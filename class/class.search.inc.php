<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class search extends db_connect
{

    private $requestFrom = 0;
    private $language = 'en';

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    private function getCount($queryText, $gender = -1, $online = -1, $ageFrom = 13, $ageTo = 110)
    {
        $queryText = "%".$queryText."%";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $current_year = date("Y");

        $fromYear = $current_year - $ageFrom;
        $toYear = $current_year - $ageTo;

        $dateSql = " AND bYear < {$fromYear} AND bYear > {$toYear}";

        $sql = "SELECT count(*) FROM users WHERE state = 0 AND (login LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}')".$genderSql.$onlineSql.$dateSql;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function lastIndex()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn() + 1;
    }
	
	private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function query($queryText = '', $userId = 0, $gender = -1, $online = -1, $ageFrom = 18, $ageTo = 110, $lat, $lng)
    {
        $originQuery = $queryText;

        if ($userId == 0) {

            $userId = $this->lastIndex();
            $userId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $current_year = date("Y");

        $fromYear = $current_year - $ageFrom;
        $toYear = $current_year - $ageTo;

        $dateSql = " AND bYear < {$fromYear} AND bYear > {$toYear}";

        $users = array("error" => false,
                       "error_code" => ERROR_SUCCESS,
                       "itemCount" => $this->getCount($originQuery, $gender, $online, $ageFrom, $ageTo),
                       "itemId" => $userId,
                       "query" => $originQuery,
                       "items" => array());

        $queryText = "%".$queryText."%";

        $sql = "SELECT id, regtime,lat, lng, 3956 * 2 *
                    ASIN(SQRT( POWER(SIN(($origLat - lat)*pi()/180/2),2)
                    +COS($origLat*pi()/180 )*COS(lat*pi()/180)
                    *POWER(SIN(($origLon-lng)*pi()/180/2),2)))
                    as distance  FROM users WHERE state = 0 AND (login LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}') AND id < {$userId}".$genderSql.$onlineSql.$dateSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);
					$profileInfo = $profile->getVeryShort();
                    $profileInfo['distance'] = round($this->getDistance($lat, $lng, $profileInfo['lat'], $profileInfo['lng']));

                    array_push($users['items'], $profileInfo);

                    $users['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $users;
    }

    public function preload($itemId = 0, $online = -1, $ageFrom = 18, $ageTo = 110,$lat, $lng)
    {
       
		
	if ($itemId == 0) {

            $itemId = $this->lastIndex();
            $itemId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

      

        $current_year = date("Y");

        $fromYear = $current_year - $ageFrom;
        $toYear = $current_year - $ageTo;

        $dateSql = " AND bYear < {$fromYear} AND bYear > {$toYear}";

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemCount" => $this->getCount("", $gender, $online, $photo),
                        "itemId" => $itemId,
                        "items" => array());

        $sql = "SELECT id, regtime FROM users WHERE state = 0 AND id < {$itemId}".$genderSql.$onlineSql.$dateSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);

                    array_push($result['items'], $profile->get());

                    $result['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $result;
    }
	
    public function getDistance($fromLat, $fromLng, $toLat, $toLng) 
	{

        $latFrom = deg2rad($fromLat);
        $lonFrom = deg2rad($fromLng);
        $latTo = deg2rad($toLat);
        $lonTo = deg2rad($toLng);

        $delta = $lonTo - $lonFrom;

        $alpha = pow(cos($latTo) * sin($delta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($delta), 2);
        $beta = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($delta);

        $angle = atan2(sqrt($alpha), $beta);

        return ($angle * 6371000) / 1000;
    }

    public function info($ip_addr)
    {
        $info = helper::getContent('http://www.geoplugin.net/json.gp?ip='.$ip_addr);

        return json_decode($info, true);
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
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

