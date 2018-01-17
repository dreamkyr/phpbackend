<?php

/*!
 * ifsoft.co.uk v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */


class db_connect
{

    protected $db;

    protected function __construct($db = NULL)
    {

        if (is_object($db)) {
            $this->db = $db;
            $this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

        }  else  {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;

            try  {
                $this->db = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
                $this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
//                $this->db = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));

            } catch (Exception $e) {

                die ($e->getMessage());
            }
        }
    }
}
