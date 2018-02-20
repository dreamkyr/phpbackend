<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class profile extends db_connect
{

    private $id = 0;
    private $requestFrom = 0;

    public function __construct($dbo = NULL, $profileId = 0)
    {

        parent::__construct($dbo);

        $this->setId($profileId);
    }

    private function getMaxIdLikes()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM profile_likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }
	
	  private function getMaxIdFavorites()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM profile_favorites");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getILikedCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM profile_likes WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

	 public function getIFavoriteCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM profile_favorites WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

	
    public function get()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                // test to my like

                $myLike = false;

                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {

                    if ($this->is_like_exists($this->requestFrom)) {

                        $myLike = true;
                    }
                }
				
				//test to my favorite
				
				$myFavorite = false;
				
				   if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {

                    if ($this->is_fav_exists($this->requestFrom)) {

                        $myFavorite = true;
                    }
                }

                // test to blocked
                $blocked = false;

                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->requestFrom);

                    if ($blacklist->isExists($this->id)) {

                        $blocked = true;
                    }

                    unset($blacklist);
                }

                // test to friend
                $friend = false;

                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {

                    if ($this->is_friend_exists($this->requestFrom)) {

                        $friend = true;
                    }
                }

                // test to follow
                $follow = false;

                // test to my follower
                $follower = false;

                if (!$friend && $this->getRequestFrom() != $this->getId()) {

                    // test to follow
                    // $follow = false;

                    if ($this->getRequestFrom() != 0) {

                        if ($this->is_follower_exists($this->requestFrom)) {

                            $follow = true;
                        }
                    }

                    // test to my follower
                    // $follower = false;

                    if ($this->getRequestFrom() != 0) {

                        $myProfile = new profile($this->db, $this->requestFrom);

                        if ($myProfile->is_follower_exists($this->getId())) {

                            $follower = true;
                        }

                        unset($myProfile);
                    }
                }

                // is my profile exists in blacklist
                $inBlackList = false;

                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->getId());

                    if ($blacklist->isExists($this->getRequestFrom())) {

                        $inBlackList = true;
                    }

                    unset($blacklist);
                }

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "gcm_regid" => $row['gcm_regid'],
                                "ios_fcm_regid" => $row['ios_fcm_regid'],
                                "android_msg_fcm_regid" => $row['android_msg_fcm_regid'],
                                "ios_msg_fcm_regid" => $row['ios_msg_fcm_regid'],
                                "ghost" => $row['ghost'],
                                "vip" => $row['vip'],
                                "rating" => $row['rating'],
                                "state" => $row['state'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "username" => $row['login'],
                                "fullname" => htmlspecialchars_decode(stripslashes($row['fullname'])),
                                "location" => stripcslashes($row['country']),
                                "iIntro" => stripcslashes($row['iIntro']),
                                "verify" => $row['verify'],
                                "lowPhotoUrl" => $row['lowPhotoUrl'],
                                "bigPhotoUrl" => $row['bigPhotoUrl'],
                                "normalPhotoUrl" => $row['normalPhotoUrl'],
                                "normalCoverUrl" => $row['normalCoverUrl'],
                                "originCoverUrl" => $row['originCoverUrl'],
                                "coverPosition" => $row['coverPosition'],
                                "iAge" => $row['iAge'],
                                "iHeight" => $row['iHeight'],
                                "iBodyType" => $row['iBodyType'],
                                "iEthnicity" => $row['iEthnicity'],
                                "iZodiac" => $row['iZodiac'],
                                "iAM" => stripcslashes($row['iAM']),
                                "iInterestedIN" => stripcslashes($row['iInterestedIN']),
                                "iOccupation" => stripcslashes($row['iOccupation']),
                                "iRelationshipStatus"=>$row['iRelationshipStatus'],
								"iLiving"=>$row['iLiving'],
                                "iEducation" => $row['iEducation'],
								"iPronouns" => stripcslashes($row['iPronouns']),
								"iSmoke" => $row['iSmoke'],
								"iDrink" => $row['iDrink'],
								"iSexPosition" => $row['iSexPosition'],
								"iLookingFor" => stripcslashes($row['iLookingFor']),
								"iInto" => stripcslashes($row['iInto']),
								"iSexualHealth" => $row['iSexualHealth'],
								"iMusic" => stripcslashes($row['iMusic']),
								"iMovies" => stripcslashes($row['iMovies']),
								"iSports" => stripcslashes($row['iSports']),
								"iGoingOut" => stripcslashes($row['iGoingOut']),
								"iPetPeeves" => stripcslashes($row['iPetPeeves']),
								"iFetishes" => stripcslashes($row['iFetishes']),
								"iDealBreaker" => stripcslashes($row['iDealBreaker']),
                                "allowPhotosComments" => $row['allowPhotosComments'],
                                "allowMessages" => $row['allowMessages'],
                                "allowShowMyBirthday" => $row['allowShowMyBirthday'],
                                "allowShowMyInfo" => $row['allowShowMyInfo'],
                                "allowShowMyGallery" => $row['allowShowMyGallery'],
                                "allowShowMyFriends" => $row['allowShowMyFriends'],
                                "allowShowMyLikes" => $row['allowShowMyLikes'],
                                "allowShowMyGifts" => $row['allowShowMyGifts'],
                                "allowShowMyAge" => $row['allowShowMyAge'],
                                "allowShowMyDistance" => $row['allowShowMyDistance'],
                                "allowShowMap" => $row['allowShowMap'],
								"favoritesCount"=>$row['favorites_count'],
                                "friendsCount" => $row['friends_count'],
                                "photosCount" => $row['photos_count'],
                                "likesCount" => $row['likes_count'],
                                "giftsCount" => $row['gifts_count'],
                                "follower" => $follower,
                                "friend" => $friend,
                                "inBlackList" => $inBlackList,
                                "follow" => $follow,
                                "blocked" => $blocked,
                                "myLike" => $myLike,
								"myFavorite"=>$myFavorite,
                                "createAt" => $row['regtime'],
                                "createDate" => date("Y-m-d", $row['regtime']),
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online);
            }
        }

        return $result;
    }

    public function getShort()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                // is my profile exists in blacklist
                $inBlackList = false;

                if ($this->requestFrom != 0) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->getId());

                    if ($blacklist->isExists($this->getRequestFrom())) {

                        $inBlackList = true;
                    }

                    unset($blacklist);
                }

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "gcm_regid" => $row['gcm_regid'],
                                "ios_fcm_regid" => $row['ios_fcm_regid'],
								"android_msg_fcm_regid" => $row['android_msg_fcm_regid'],
                                "ios_msg_fcm_regid" => $row['ios_msg_fcm_regid'],
                                "vip" => $row['vip'],
                                "rating" => $row['rating'],
                                "state" => $row['state'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "username" => $row['login'],
                                "fullname" => htmlspecialchars_decode(stripslashes($row['fullname'])),
                                "location" => stripcslashes($row['country']),
                                "iIntro" => stripcslashes($row['iIntro']),
                                "verify" => $row['verify'],
                                "friendsCount" => $row['friends_count'],
                                "photosCount" => $row['photos_count'],
                                "likesCount" => $row['likes_count'],
                                "giftsCount" => $row['gifts_count'],
                                "lowPhotoUrl" => $row['lowPhotoUrl'],
                                "bigPhotoUrl" => $row['bigPhotoUrl'],
                                "normalPhotoUrl" => $row['normalPhotoUrl'],
                                "normalCoverUrl" => $row['normalCoverUrl'],
                                "originCoverUrl" => $row['originCoverUrl'],
                                "coverPosition" => $row['coverPosition'],
                                "allowPhotosComments" => $row['allowPhotosComments'],
                                "allowMessages" => $row['allowMessages'],
                                "allowShowMyBirthday" => $row['allowShowMyBirthday'],
                                "allowShowMyInfo" => $row['allowShowMyInfo'],
                                "allowShowMyGallery" => $row['allowShowMyGallery'],
                                "allowShowMyFriends" => $row['allowShowMyFriends'],
                                "allowShowMyLikes" => $row['allowShowMyLikes'],
                                "allowShowMyGifts" => $row['allowShowMyGifts'],
                                "allowShowMyAge" => $row['allowShowMyAge'],
                                "allowShowMyDistance" => $row['allowShowMyDistance'],
                                "allowShowMap" => $row['allowShowMap'],
                                "inBlackList" => $inBlackList,
                                "createAt" => $row['regtime'],
                                "createDate" => date("Y-m-d", $row['regtime']),
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online);
            }
        }

        return $result;
    }

    public function getVeryShort()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $online = false;
                $myLike = false;
                $inBlackList = false;
                $follower = false;
                $friend = false;
                $follow = false;
                $blocked = false;
				$myFavorite = false;
				
				 if ($this->requestFrom != 0) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->getId());

                    if ($blacklist->isExists($this->getRequestFrom())) {

                        $inBlackList = true;
                    }

                    unset($blacklist);
                }

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "gcm_regid" => $row['gcm_regid'],
                                "ios_fcm_regid" => $row['ios_fcm_regid'],
								"android_msg_fcm_regid" => $row['android_msg_fcm_regid'],
                                "ios_msg_fcm_regid" => $row['ios_msg_fcm_regid'],
                                "vip" => $row['vip'],
                                "rating" => $row['rating'],
                                "state" => $row['state'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "username" => $row['login'],
                                "fullname" => htmlspecialchars_decode(stripslashes($row['fullname'])),
                                "location" => stripcslashes($row['country']),
                                "iIntro" => stripcslashes($row['iIntro']),
                                "verify" => $row['verify'],
                                "lowPhotoUrl" => $row['lowPhotoUrl'],
                                "bigPhotoUrl" => $row['bigPhotoUrl'],
                                "normalPhotoUrl" => $row['normalPhotoUrl'],
                                "normalCoverUrl" => $row['normalCoverUrl'],
                                "originCoverUrl" => $row['originCoverUrl'],
                                "iAge" => $row['iAge'],
                                "iHeight" => $row['iHeight'],
                                "iBodyType" => $row['iBodyType'],
                                "iEthnicity" => $row['iEthnicity'],
                                "iZodiac" => $row['iZodiac'],
								"iAM" => stripcslashes($row['iAM']),
                                "iInterestedIN" => stripcslashes($row['iInterestedIN']),
                                "iOccupation" => stripcslashes($row['iOccupation']),
                                "iRelationshipStatus"=>$row['iRelationshipStatus'],
								"iLiving"=>$row['iLiving'],
                                "iEducation" => $row['iEducation'],
								"iPronouns" => stripcslashes($row['iPronouns']),
								"iSmoke" => $row['iSmoke'],
								"iDrink" => $row['iDrink'],
								"iSexPosition" => $row['iSexPosition'],
								"iLookingFor" => stripcslashes($row['iLookingFor']),
								"iInto" => stripcslashes($row['iInto']),
								"iSexualHealth" => $row['iSexualHealth'],
								"iMusic" => stripcslashes($row['iMusic']),
								"iMovies" => stripcslashes($row['iMovies']),
								"iSports" => stripcslashes($row['iSports']),
								"iGoingOut" => stripcslashes($row['iGoingOut']),
								"iPetPeeves" => stripcslashes($row['iPetPeeves']),
								"iFetishes" => stripcslashes($row['iFetishes']),
								"iDealBreaker" => stripcslashes($row['iDealBreaker']),
								"favoritesCount"=>$row['favorites_count'],
                                "friendsCount" => $row['friends_count'],
                                "photosCount" => $row['photos_count'],
                                "likesCount" => $row['likes_count'],
                                "giftsCount" => $row['gifts_count'],
                                "createAt" => $row['regtime'],
                                "createDate" => date("Y-m-d", $row['regtime']),
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "allowPhotosComments" => $row['allowPhotosComments'],
                                "allowMessages" => $row['allowMessages'],
                                "allowShowMyBirthday" => $row['allowShowMyBirthday'],
                                "allowShowMyInfo" => $row['allowShowMyInfo'],
                                "allowShowMyGallery" => $row['allowShowMyGallery'],
                                "allowShowMyFriends" => $row['allowShowMyFriends'],
                                "allowShowMyLikes" => $row['allowShowMyLikes'],
                                "allowShowMyGifts" => $row['allowShowMyGifts'],
                                "allowShowMyAge" => $row['allowShowMyAge'],
                                "allowShowMyDistance" => $row['allowShowMyDistance'],
                                "allowShowMap" => $row['allowShowMap'],
                                "online" => $online,
                                "follower" => $follower,
                                "friend" => $friend,
                                "inBlackList" => $inBlackList,
                                "follow" => $follow,
                                "blocked" => $blocked,
                                "myLike" => $myLike,
								"myFavorite" => $myFavorite);
            }
        }

        return $result;
    }

    

	public function removeFavorite($friendId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $my_profile = new profile($this->db, $this->profileId);

        if ($my_profile->is_fav_exists($friendId)) {

            $currentTime = time();

            $stmt = $this->db->prepare("UPDATE profile_favorites SET removeAt = (:removeAt) WHERE toUserId = (:toUserId) AND fromUserId = (:fromUserId) AND removeAt = 0");
            $stmt->bindParam(":toUserId", $this->profileId, PDO::PARAM_INT);
            $stmt->bindParam(":fromUserId", $friendId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

            if ($stmt->execute()) {

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);

                $stmt2 = $this->db->prepare("UPDATE profile_favorites SET removeAt = (:removeAt) WHERE toUserId = (:toUserId) AND fromUserId = (:fromUserId) AND removeAt = 0");
                $stmt2->bindParam(":toUserId", $this->profileId, PDO::PARAM_INT);
                $stmt2->bindParam(":fromUserId", $friendId, PDO::PARAM_INT);
                $stmt2->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
                $stmt2->execute();

                $account = new account($this->db, $this->profileId);
                $account->updateCounters();
                unset($account);

                $account = new account($this->db, $friendId);
                $account->updateCounters();
            }
        }

        return $result;
    }

	public function favorite($fromUserId)
	{
		 $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $account = new account($this->db, $fromUserId);
        $account->setLastActive();
        unset($account);

        if ($this->is_fav_exists($fromUserId)) {

            $removeAt = time();

            $stmt = $this->db->prepare("UPDATE profile_favorites SET removeAt = (:removeAt) WHERE toUserId = (:toUserId) AND fromUserId = (:fromUserId) AND removeAt = 0");
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":toUserId", $toUserId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
            $stmt->execute();

            $myfavorite = false;

        } else {

            $createAt = time();
            $ip_addr = helper::ip_addr();

            $stmt = $this->db->prepare("INSERT INTO profile_favorites (toUserId, fromUserId, createAt, ip_addr) value (:toUserId, :fromUserId, :createAt, :ip_addr)");
            $stmt->bindParam(":toUserId", $this->id, PDO::PARAM_INT);
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
            $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
            $stmt->execute();

            $myfavorite = true;

        }

        $account = new account($this->db, $this->id);
        $account->updateCounters();
        $FavoritesCount = $account->getFavoritesCount();
        unset($account);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "FavoritesCount" => $FavoritesCount,
                        "myfavorite" => $myfavorite);

        return $result;
	}
	
	public function getIFavorite($itemId = 0)
    {
		
		
        if ($itemId == 0) {
            $itemId = $this->getMaxIdFavorites();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM profile_favorites WHERE fromUserId = (:fromUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':fromUserId', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['toUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->get();
                    unset($profile);

                    array_push($result['items'], $profileInfo);

                    $result['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $result;
    }
	
	public function getFavoriteMe($itemId = 0){
		
		if ($itemId == 0) {

            $itemId = $this->getMaxIdFavorites();
            $itemId++;
        }

        $fans = array("error" => false,
                      "error_code" => ERROR_SUCCESS,
                      "itemId" => $itemId,
                      "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM profile_favorites WHERE toUserId = (:toUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':toUserId', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['fromUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->get();
                    unset($profile);

                    array_push($fans['items'], $profileInfo);

                    $fans['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $fans;
	}
	
    public function like($fromUserId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $account = new account($this->db, $fromUserId);
        $account->setLastActive();
        unset($account);

        if ($this->is_like_exists($fromUserId)) {

            $removeAt = time();

            $stmt = $this->db->prepare("UPDATE profile_likes SET removeAt = (:removeAt) WHERE toUserId = (:toUserId) AND fromUserId = (:fromUserId) AND removeAt = 0");
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":toUserId", $toUserId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
            $stmt->execute();

            $notify = new notify($this->db);
            $notify->removeNotify($this->id, $fromUserId, NOTIFY_TYPE_LIKE, 0);
            unset($notify);

            $myLike = false;

        } else {

            $createAt = time();
            $ip_addr = helper::ip_addr();

            $stmt = $this->db->prepare("INSERT INTO profile_likes (toUserId, fromUserId, createAt, ip_addr) value (:toUserId, :fromUserId, :createAt, :ip_addr)");
            $stmt->bindParam(":toUserId", $this->id, PDO::PARAM_INT);
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
            $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
            $stmt->execute();

            $myLike = true;

            if ($this->id != $fromUserId) {

                $blacklist = new blacklist($this->db);
                $blacklist->setRequestFrom($this->id);

                if (!$blacklist->isExists($fromUserId)) {

                    $account = new account($this->db, $this->id);

                    if ($account->getAllowLikesGCM() == ENABLE_LIKES_GCM) {

                        $gcm = new gcm($this->db, $this->id);
                        $gcm->setData(GCM_NOTIFY_LIKE, "You have new like", 0);
                        $gcm->send();
                    }

                    unset($account);

                    $notify = new notify($this->db);
                    $notify->createNotify($this->id, $fromUserId, NOTIFY_TYPE_LIKE, 0);
                    unset($notify);
                }

                unset($blacklist);
            }
        }

        $account = new account($this->db, $this->id);
        $account->updateCounters();
        $likesCount = $account->getLikesCount();
        unset($account);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likesCount" => $likesCount,
                        "myLike" => $myLike);

        return $result;
    }

    public function getFans($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdLikes();
            $itemId++;
        }

        $fans = array("error" => false,
                      "error_code" => ERROR_SUCCESS,
                      "itemId" => $itemId,
                      "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM profile_likes WHERE toUserId = (:toUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':toUserId', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['fromUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    array_push($fans['items'], $profileInfo);

                    $fans['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $fans;
    }

    public function getILiked($itemId = 0)
    {
        if ($itemId == 0) {
            $itemId = $this->getMaxIdLikes();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM profile_likes WHERE fromUserId = (:fromUserId) AND id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':fromUserId', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['toUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    array_push($result['items'], $profileInfo);

                    $result['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $result;
    }

    /**
     * @param $userId
     * @return string
     */
    public function getTodayUserBeLikedCount($userId) {
        $currentTime = date("Y-m-d", time());
        $today = strtotime($currentTime);
        $datetime = new DateTime('tomorrow');
        $tomorrow = strtotime($datetime->format('Y-m-d'));
        $todayStartSeconds = $today;
        $todayEndSeconds = $tomorrow;

        $stmt = $this->db->prepare("SELECT likes_count FROM users WHERE id = (:userId) AND removeAt = 0");
        $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
       

        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getTopUsersBeLiked() {
        $currentTime = date("Y-m-d", time());
        $today = strtotime($currentTime);
        $datetime = new DateTime('tomorrow');
        $tomorrow = strtotime($datetime->format('Y-m-d'));
        $todayStartSeconds = $today;
        $todayEndSeconds = $tomorrow;

        $stmt = $this->db->prepare(" SELECT DISTINCT id FROM users  ORDER BY likes_count DESC");
        
       

        $likedCounts = [];
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $item = array(
                        'user_id' => $row['id']
                    );
                    $likedCounts[] = $item;
                }
            }
        }
        
        if(count($likedCounts) > 100) {
            $likedCounts = array_slice($likedCounts, 0, 99);
        }

        $result = array("error" => false,
            "error_code" => ERROR_SUCCESS,
            "items" => array());

        foreach ($likedCounts as $likedCount) {
            $profile = new profile($this->db, $likedCount['user_id']);
            $profile->setRequestFrom($this->requestFrom);
            $profileInfo = $profile->getVeryShort();
            array_push($result['items'], $profileInfo);
            $result['itemId'] = $row['id'];
            unset($profile);
        }

        return $result;
    }


    private function is_like_exists($fromUserId)
    {
        $stmt = $this->db->prepare("SELECT id FROM profile_likes WHERE fromUserId = (:fromUserId) AND toUserId = (:toUserId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":toUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

	  private function is_fav_exists($fromUserId)
    {
        $stmt = $this->db->prepare("SELECT id FROM profile_favorites WHERE fromUserId = (:fromUserId) AND toUserId = (:toUserId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":toUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }
    public function addFollower($follower_id)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $account = new account($this->db, $this->id);
        $account->setLastActive();
        unset($account);

        if ($this->is_friend_exists($follower_id)) {

            return $result;
        }

        if ($this->is_follower_exists($follower_id)) {

            $stmt = $this->db->prepare("DELETE FROM profile_followers WHERE follower = (:follower) AND follow_to = (:follow_to)");
            $stmt->bindParam(":follower", $follower_id, PDO::PARAM_INT);
            $stmt->bindParam(":follow_to", $this->id, PDO::PARAM_INT);

            $stmt->execute();

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "follow" => false,
                            "followersCount" => 0);

            $notify = new notify($this->db);
            $notify->removeNotify($this->id, $follower_id, NOTIFY_TYPE_FOLLOWER, 0);
            unset($notify);

        } else {

            $create_at = time();

            $stmt = $this->db->prepare("INSERT INTO profile_followers (follower, follow_to, create_at) value (:follower, :follow_to, :create_at)");
            $stmt->bindParam(":follower", $follower_id, PDO::PARAM_INT);
            $stmt->bindParam(":follow_to", $this->id, PDO::PARAM_INT);
            $stmt->bindParam(":create_at", $create_at, PDO::PARAM_INT);

            $stmt->execute();

            $blacklist = new blacklist($this->db);
            $blacklist->setRequestFrom($this->id);

            if (!$blacklist->isExists($follower_id)) {

                $account = new account($this->db, $this->id);

                if ($account->getAllowFollowersGCM() == ENABLE_FOLLOWERS_GCM) {

                    $gcm = new gcm($this->db, $this->id);
                    $gcm->setData(GCM_NOTIFY_FOLLOWER, "You have new follower", 0);
                    $gcm->send();
                }

                unset($account);

                $notify = new notify($this->db);
                $notify->createNotify($this->id, $follower_id, NOTIFY_TYPE_FOLLOWER, 0);
                unset($notify);
            }

            unset($blacklist);

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "follow" => true,
                            "followersCount" => 0);
        }

        return $result;
    }

    public function is_follower_exists($follower_id)
    {

        $stmt = $this->db->prepare("SELECT id FROM profile_followers WHERE follower = (:follower) AND follow_to = (:follow_to) LIMIT 1");
        $stmt->bindParam(":follower", $follower_id, PDO::PARAM_INT);
        $stmt->bindParam(":follow_to", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function is_friend_exists($friend_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM friends WHERE friend = (:friend) AND friendTo = (:friendTo) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":friend", $friend_id, PDO::PARAM_INT);
        $stmt->bindParam(":friendTo", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function reportAbuse($abuseId)
    {
        $result = array("error" => true);

        $create_at = time();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO profile_abuse_reports (abuseFromUserId, abuseToUserId, abuseId, createAt, ip_addr) value (:abuseFromUserId, :abuseToUserId, :abuseId, :createAt, :ip_addr)");
        $stmt->bindParam(":abuseFromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":abuseToUserId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":abuseId", $abuseId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $create_at, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    public function getState()
    {
        $stmt = $this->db->prepare("SELECT state FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row['state'];
    }

    public function getFullname()
    {
        $stmt = $this->db->prepare("SELECT login, fullname FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        $fullname = stripslashes($row['fullname']);

        if (strlen($fullname) < 1) {

            $fullname = $row['login'];
        }

        return $fullname;
    }

    public function getUsername()
    {
        $stmt = $this->db->prepare("SELECT login FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id , PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row['login'];
    }

    public function setId($profileId)
    {
        $this->id = $profileId;
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

