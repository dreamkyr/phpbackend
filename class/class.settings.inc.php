<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class settings extends db_connect
{

	private $requestFrom = 0;
    private $id = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM admins");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function createValue($name, $intValue, $textValue = "")
    {

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("INSERT INTO settings (name, intValue, textValue) value (:name, :intValue, :textValue)");
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":intValue", $intValue, PDO::PARAM_INT);
        $stmt->bindParam(":textValue", $textValue, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);

            return $result;
        }

        return $result;
    }

    public function setValue($name, $intValue, $textValue = "")
    {
        $stmt = $this->db->prepare("UPDATE settings SET intValue = (:intValue), textValue = (:textValue) WHERE name = (:valueName)");
        $stmt->bindParam(":valueName", $name, PDO::PARAM_STR);
        $stmt->bindParam(":intValue", $intValue, PDO::PARAM_INT);
        $stmt->bindParam(":textValue", $textValue, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function getIntValue($name)
    {
        $stmt = $this->db->prepare("SELECT intValue FROM settings WHERE name = (:valueName) LIMIT 1");
        $stmt->bindParam(":valueName", $name, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['intValue'];
        }

        return 0;
    }

    public function getTextValue($name)
    {
        $stmt = $this->db->prepare("SELECT textValue FROM settings WHERE name = (:valueName) LIMIT 1");
        $stmt->bindParam(":valueName", $name, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['textValue'];
        }

        return 0;
    }

    public function setId($accountId)
    {
        $this->id = $accountId;
    }

    public function getId()
    {
        return $this->id;
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

