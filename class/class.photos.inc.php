<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class photos extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';
    private $profileId = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM photos");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM photos");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxIdLikes()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM images_likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM photos WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($mode, $comment, $originImgUrl = "", $previewImgUrl = "", $imgUrl = "", $itemType = 0, $videoUrl = "", $photoArea = "", $photoCountry = "", $photoCity = "", $photoLat = "0.000000", $photoLng = "0.000000")
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if (strlen($originImgUrl) == 0 && strlen($previewImgUrl) == 0 && strlen($imgUrl) == 0) {

            return $result;
        }

        if (strlen($comment) != 0) {

            $comment = $comment." ";
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO photos (fromUserId, accessMode, itemType, comment, originImgUrl, previewImgUrl, imgUrl, videoUrl, area, country, city, lat, lng, createAt, ip_addr, u_agent) value (:fromUserId, :accessMode, :itemType, :comment, :originImgUrl, :previewImgUrl, :imgUrl, :videoUrl, :area, :country, :city, :lat, :lng, :createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":accessMode", $mode, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindParam(":originImgUrl", $originImgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":previewImgUrl", $previewImgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":videoUrl", $videoUrl, PDO::PARAM_STR);
        $stmt->bindParam(":area", $photoArea, PDO::PARAM_STR);
        $stmt->bindParam(":country", $photoCountry, PDO::PARAM_STR);
        $stmt->bindParam(":city", $photoCity, PDO::PARAM_STR);
        $stmt->bindParam(":lat", $photoLat, PDO::PARAM_STR);
        $stmt->bindParam(":lng", $photoLng, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "photoId" => $this->db->lastInsertId(),
                            "photo" => $this->info($this->db->lastInsertId()));

            $account = new account($this->db, $this->requestFrom);
            $account->updateCounters();
            unset($account);
        }

        return $result;
    }

    public function remove($photoId)
    {
        $result = array("error" => true);

        $photoInfo = $this->info($photoId);

        if ($photoInfo['error'] === true) {

            return $result;
        }

        if ($photoInfo['fromUserId'] != $this->requestFrom) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE photos SET removeAt = (:removeAt) WHERE id = (:photoId)");
        $stmt->bindParam(":photoId", $photoId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE itemId = (:itemId) AND notifyType > 6");
            $stmt2->bindParam(":itemId", $photoId, PDO::PARAM_INT);
            $stmt2->execute();

            //remove all comments to post

            $stmt3 = $this->db->prepare("UPDATE images_comments SET removeAt = (:removeAt) WHERE imageId = (:imageId)");
            $stmt3->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt3->bindParam(":imageId", $photoId, PDO::PARAM_INT);
            $stmt3->execute();

            //remove all likes to post

            $stmt4 = $this->db->prepare("UPDATE images_likes SET removeAt = (:removeAt) WHERE imageId = (:imageId) AND removeAt = 0");
            $stmt4->bindParam(":imageId", $photoId, PDO::PARAM_INT);
            $stmt4->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt4->execute();

            $result = array("error" => false);

            $account = new account($this->db, $photoInfo['fromUserId']);
            $account->updateCounters();
            unset($account);
        }

        return $result;
    }

    public function restore($photoId)
    {
        $result = array("error" => true);

        $photoInfo = $this->info($photoId);

        if ($photoInfo['error'] === true) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE photos SET removeAt = 0 WHERE id = (:photoId)");
        $stmt->bindParam(":photoId", $photoId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    private function getLikesCount($imageId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images_likes WHERE imageId = (:imageId) AND removeAt = 0");
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function recalculate($imageId) {

        $comments_count = 0;
        $likes_count = 0;
        $rating = 0;

        $likes_count = $this->getLikesCount($imageId);

        $images = new images($this->db);
        $comments_count = $images->commentsCount($imageId);
        unset($comments);

        $rating = $likes_count + $comments_count;

        $stmt = $this->db->prepare("UPDATE photos SET likesCount = (:likesCount), commentsCount = (:commentsCount), rating = (:rating) WHERE id = (:imageId)");
        $stmt->bindParam(":likesCount", $likes_count, PDO::PARAM_INT);
        $stmt->bindParam(":commentsCount", $comments_count, PDO::PARAM_INT);
        $stmt->bindParam(":rating", $rating, PDO::PARAM_INT);
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->execute();

        $account = new account($this->db, $this->requestFrom);
        $account->updateCounters();
        unset($account);
    }

    public function like($imageId, $fromUserId)
    {
        $account = new account($this->db, $fromUserId);
        $account->setLastActive();
        unset($account);

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $imageInfo = $this->info($imageId);

        if ($imageInfo['error'] === true) {

            return $result;
        }

        if ($imageInfo['removeAt'] != 0) {

            return $result;
        }

        if ($this->is_like_exists($imageId, $fromUserId)) {

            $removeAt = time();

            $stmt = $this->db->prepare("UPDATE images_likes SET removeAt = (:removeAt) WHERE imageId = (:imageId) AND fromUserId = (:fromUserId) AND removeAt = 0");
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
            $stmt->execute();

            $notify = new notify($this->db);
            $notify->removeNotify($imageInfo['fromUserId'], $fromUserId, NOTIFY_TYPE_IMAGE_LIKE, $imageId);
            unset($notify);

        } else {

            $createAt = time();
            $ip_addr = helper::ip_addr();

            $stmt = $this->db->prepare("INSERT INTO images_likes (toUserId, fromUserId, imageId, createAt, ip_addr) value (:toUserId, :fromUserId, :imageId, :createAt, :ip_addr)");
            $stmt->bindParam(":toUserId", $imageInfo['fromUserId'], PDO::PARAM_INT);
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
            $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
            $stmt->execute();

            if ($imageInfo['fromUserId'] != $fromUserId) {

                $blacklist = new blacklist($this->db);
                $blacklist->setRequestFrom($imageInfo['fromUserId']);

                if (!$blacklist->isExists($fromUserId)) {

                    $account = new account($this->db, $imageInfo['fromUserId']);

                    if ($account->getAllowLikesGCM() == ENABLE_LIKES_GCM) {

                        $gcm = new gcm($this->db, $imageInfo['fromUserId']);
                        $gcm->setData(GCM_NOTIFY_IMAGE_LIKE, "You have new like", $imageId);
                        $gcm->send();
                    }

                    unset($account);

                    $notify = new notify($this->db);
                    $notify->createNotify($imageInfo['fromUserId'], $fromUserId, NOTIFY_TYPE_IMAGE_LIKE, $imageId);
                    unset($notify);
                }

                unset($blacklist);
            }
        }

        $this->recalculate($imageId);

        $img_info = $this->info($imageId);

        if ($img_info['fromUserId'] != $this->requestFrom) {

            $account = new account($this->db, $img_info['fromUserId']);
            $account->updateCounters();
            unset($account);
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likesCount" => $img_info['likesCount'],
                        "myLike" => $img_info['myLike']);

        return $result;
    }

    private function is_like_exists($imageId, $fromUserId)
    {
        $stmt = $this->db->prepare("SELECT id FROM images_likes WHERE fromUserId = (:fromUserId) AND imageId = (:imageId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function getLikers($imageId, $likeId = 0)
    {

        if ($likeId == 0) {

            $likeId = $this->getMaxIdLikes();
            $likeId++;
        }

        $likers = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likeId" => $likeId,
                        "likers" => array());

        $stmt = $this->db->prepare("SELECT * FROM images_likes WHERE imageId = (:imageId) AND id < (:likeId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
        $stmt->bindParam(':likeId', $likeId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['fromUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->get();
                    unset($profile);

                    array_push($likers['likers'], $profileInfo);

                    $likers['likeId'] = $row['id'];
                }
            }
        }

        return $likers;
    }

    public function info($photoId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM photos WHERE id = (:photoId) LIMIT 1");
        $stmt->bindParam(":photoId", $photoId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $myLike = false;

                if ($this->requestFrom != 0) {

                    if ($this->is_like_exists($photoId, $this->requestFrom)) {

                        $myLike = true;
                    }
                }

                $profile = new profile($this->db, $row['fromUserId']);
                $profileInfo = $profile->get();
                unset($profile);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "accessMode" => $row['accessMode'],
                                "itemType" => $row['itemType'],
                                "fromUserId" => $row['fromUserId'],
                                "fromUserVerify" => $profileInfo['verify'],
                                "fromUserUsername" => $profileInfo['username'],
                                "fromUserFullname" => $profileInfo['fullname'],
                                "fromUserPhoto" => $profileInfo['lowPhotoUrl'],
                                "fromUserAllowPhotosComments" => $profileInfo['allowPhotosComments'],
                                "comment" => htmlspecialchars_decode(stripslashes($row['comment'])),
                                "area" => htmlspecialchars_decode(stripslashes($row['area'])),
                                "country" => htmlspecialchars_decode(stripslashes($row['country'])),
                                "city" => htmlspecialchars_decode(stripslashes($row['city'])),
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "imgUrl" => $row['imgUrl'],
                                "previewImgUrl" => $row['previewImgUrl'],
                                "originImgUrl" => $row['originImgUrl'],
                                "previewVideoImgUrl" => $row['previewVideoImgUrl'],
                                "videoUrl" => $row['videoUrl'],
                                "rating" => $row['rating'],
                                "commentsCount" => $row['commentsCount'],
                                "likesCount" => $row['likesCount'],
                                "myLike" => $myLike,
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function get($profileId, $photoId = 0, $accessMode = 0)
    {
        if ($photoId == 0) {

            $photoId = $this->getMaxId();
            $photoId++;
        }

        $photos = array("error" => false,
                       "error_code" => ERROR_SUCCESS,
                       "photoId" => $photoId,
                       "photos" => array());

        if ($accessMode == 0) {

            $stmt = $this->db->prepare("SELECT id FROM photos WHERE accessMode = 0 AND fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:photoId) ORDER BY id DESC LIMIT 20");
            $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
            $stmt->bindParam(':photoId', $photoId, PDO::PARAM_INT);

        } else {

            $stmt = $this->db->prepare("SELECT id FROM photos WHERE fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:photoId) ORDER BY id DESC LIMIT 20");
            $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
            $stmt->bindParam(':photoId', $photoId, PDO::PARAM_INT);
        }

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $photoInfo = $this->info($row['id']);

                array_push($photos['photos'], $photoInfo);

                $photos['photoId'] = $photoInfo['id'];

                unset($photoInfo);
            }
        }

        return $photos;
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

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    public function getProfileId()
    {
        return $this->profileId;
    }
}
