<?php


class discussions extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllGeneralCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM general_topics");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }
	
	public function getAllSupportCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM support_topics");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxIdItems()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM topic_posts");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getGeneralMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM general_topics");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }
	
	private function getSupportMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM support_topics");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }


    public function recalculateGeneral($topicId) {

        $posts_count = 0;

        $posts_count = $this->getItemsCountGeneral($topicId);

        $stmt = $this->db->prepare("UPDATE general_topics SET postCount = (:postsCount) WHERE id = (:topicId)");
        $stmt->bindParam(":postsCount", $posts_count, PDO::PARAM_INT);
        $stmt->bindParam(":general_topic_id", $topicId, PDO::PARAM_INT);
        $stmt->execute();
    }
	
    public function recalculateSupport($topicId) {

        $posts_count = 0;

        $posts_count = $this->getItemsCount($topicId);

        $stmt = $this->db->prepare("UPDATE support_topics SET postsCount = (:postsCount) WHERE id = (:topicId)");
        $stmt->bindParam(":postsCount", $posts_count, PDO::PARAM_INT);
        $stmt->bindParam(":support_topic_id", $topicId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getItemsCountGeneral($topicId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE general_topic_id = (:topicId) AND removeAt = 0");
        $stmt->bindParam(":general_topic_id", $topicId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }
	
	public function getItemsCountSupport($topicId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE support_topic_id = (:topicId) AND removeAt = 0");
        $stmt->bindParam(":general_topic_id", $topicId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function infoGeneral($topicId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM general_topics WHERE id = (:topicId) LIMIT 1");
        $stmt->bindParam(":id", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "itemsCount" => $row['itemsCount'],
                                "title" => htmlspecialchars_decode(stripslashes($row['title'])),
                                "description" => htmlspecialchars_decode(stripslashes($row['description'])),
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function getGeneral($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdGeneral();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $itemId,
                        "topics" => array());

        $stmt = $this->db->prepare("SELECT id FROM general_topics WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['topics'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }
	
	    public function getSupport($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdSupport();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $itemId,
                        "topics" => array());

        $stmt = $this->db->prepare("SELECT id FROM support_topics WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['topics'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }

    public function getItemsGeneral($generalId, $itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdItems();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM topic_posts WHERE general_topic_id= (:generalId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':general_topic_id', $generalId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $topicposts = new topicposts($this->db);
                $topicposts->setRequestFrom($this->getRequestFrom());

                $itemInfo = $topicposts->info_short($row);

                array_push($result['topics'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }

	    public function getItemsSupport($supportId, $itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdItems();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "topics" => array());

        $stmt = $this->db->prepare("SELECT * FROM topic_posts WHERE support_topic_id = (:supportId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':support_topic_id', $supportId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $topicposts = new topicposts($this->db);
                $topicposts->setRequestFrom($this->getRequestFrom());

                $itemInfo = $topicposts->info_short($row);

                array_push($result['topics'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }
    public function getListGeneral()
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "topics" => array());

        $stmt = $this->db->prepare("SELECT id FROM general_topics WHERE removeAt = 0 ORDER BY id");

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['topics'], $itemInfo);

                unset($itemInfo);
            }
        }

        return $result;
    }
	
	 public function getListSupport()
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "topics" => array());

        $stmt = $this->db->prepare("SELECT id FROM support_topics WHERE removeAt = 0 ORDER BY id");

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['topics'], $itemInfo);

                unset($itemInfo);
            }
        }

        return $result;
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
