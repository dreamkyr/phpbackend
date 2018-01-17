<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class feed extends db_connect
{
	private $requestFrom = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function count()
    {
        $count = 0;

        $stmt = $this->db->prepare("SELECT id, friend FROM friends WHERE friendTo = (:friendTo) AND removeAt = 0 ORDER BY createAt DESC");
        $stmt->bindParam(':friendTo', $this->requestFrom, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $stmt2 = $this->db->prepare("SELECT count(*) FROM photos WHERE fromUserId = (:fromUserId) AND removeAt = 0 ORDER BY createAt DESC");
                $stmt2->bindParam(':fromUserId', $row['friend'], PDO::PARAM_INT);
                $stmt2->execute();

                $count = $count + $stmt2->fetchColumn();
            }
        }

        return $count;
    }

    public function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM photos");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function get($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $feed = array("error" => false,
                      "error_code" => ERROR_SUCCESS,
                      "itemId" => $itemId,
                      "items" => array());

        $stmt = $this->db->prepare("SELECT id, friend FROM friends WHERE friendTo = (:friendTo) AND removeAt = 0 ORDER BY createAt DESC");
        $stmt->bindParam(':friendTo', $this->requestFrom, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $items = array();

            while ($row = $stmt->fetch()) {

                $stmt2 = $this->db->prepare("SELECT id FROM photos WHERE fromUserId = (:fromUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC");
                $stmt2->bindParam(':fromUserId', $row['friend'], PDO::PARAM_INT);
                $stmt2->bindParam(':itemId', $itemId, PDO::PARAM_INT);
                $stmt2->execute();

                while ($row2 = $stmt2->fetch())  {

                    $items[] = array("id" => $row2['id'], "itemId" => $row2['id']);
                }
            }

            $stmt3 = $this->db->prepare("SELECT id FROM photos WHERE fromUserId = (:fromUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC");
            $stmt3->bindParam(':fromUserId', $this->requestFrom, PDO::PARAM_INT);
            $stmt3->bindParam(':itemId', $itemId, PDO::PARAM_INT);
            $stmt3->execute();

            while ($row3 = $stmt3->fetch())  {

                $items[] = array("id" => $row3['id'], "itemId" => $row3['id']);
            }

            $currentItem = 0;
            $maxItem = 20;

            if (count($items) != 0) {

                arsort($items);

                foreach ($items as $key => $value) {

                    if ($currentItem < $maxItem) {

                        $currentItem++;

                        $item = new photos($this->db);
                        $item->setRequestFrom($this->requestFrom);

                        $itemInfo = $item->info($value['itemId']);

                        array_push($feed['items'], $itemInfo);

                        $feed['itemId'] = $itemInfo['id'];

                        unset($itemInfo);
                        unset($item);
                    }
                }
            }
        }

        return $feed;
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
