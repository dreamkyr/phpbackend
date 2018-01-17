<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class update extends db_connect
{
    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);

    }

    function setChatEmojiSupport()
    {
        $stmt = $this->db->prepare("ALTER TABLE messages charset = utf8mb4, MODIFY COLUMN message VARCHAR(800) CHARACTER SET utf8mb4");
        $stmt->execute();
    }

    function setGiftsEmojiSupport()
    {
        $stmt = $this->db->prepare("ALTER TABLE gifts charset = utf8mb4, MODIFY COLUMN message VARCHAR(400) CHARACTER SET utf8mb4");
        $stmt->execute();
    }

    function setPhotosEmojiSupport()
    {
        $stmt = $this->db->prepare("ALTER TABLE photos charset = utf8mb4, MODIFY COLUMN comment VARCHAR(400) CHARACTER SET utf8mb4");
        $stmt->execute();
    }

    function addColumnToUsersTable()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD allowShowMyBirthday INT(6) UNSIGNED DEFAULT 0 after allowCommentReplyGCM");
        $stmt->execute();
    }

    function addColumnToChatsTable()
    {
        $stmt = $this->db->prepare("ALTER TABLE chats ADD message varchar(800) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' after toUserId_lastView");
        $stmt->execute();
    }

    function addColumnToChatsTable2()
    {
        $stmt = $this->db->prepare("ALTER TABLE chats ADD messageCreateAt INT(11) UNSIGNED DEFAULT 0 after message");
        $stmt->execute();
    }

    function setDialogsEmojiSupport()
    {
        $stmt = $this->db->prepare("ALTER TABLE chats charset = utf8mb4, MODIFY COLUMN message VARCHAR(800) CHARACTER SET utf8mb4");
        $stmt->execute();
    }

    function addColumnToAdminsTable()
    {
        $stmt = $this->db->prepare("ALTER TABLE admins ADD access_level INT(11) UNSIGNED DEFAULT 0 after id");
        $stmt->execute();
    }

    // For version 2.0

    function addColumnToUsersTable15()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD allowPhotosComments SMALLINT(6) UNSIGNED DEFAULT 1 after allowComments");
        $stmt->execute();
    }

    function setImagesCommentsEmojiSupport()
    {
        $stmt = $this->db->prepare("ALTER TABLE images_comments charset = utf8mb4, MODIFY COLUMN comment VARCHAR(800) CHARACTER SET utf8mb4");
        $stmt->execute();
    }

    // For version 2.3

    function addColumnToGalleryTable1()
    {
        $stmt = $this->db->prepare("ALTER TABLE photos ADD itemType int(11) UNSIGNED DEFAULT 0 after accessMode");
        $stmt->execute();
    }

    function addColumnToGalleryTable2()
    {
        $stmt = $this->db->prepare("ALTER TABLE photos ADD previewVideoImgUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' after imgUrl");
        $stmt->execute();
    }

    function addColumnToGalleryTable3()
    {
        $stmt = $this->db->prepare("ALTER TABLE photos ADD videoUrl VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' after previewVideoImgUrl");
        $stmt->execute();
    }

    // For version 2.6

    function addColumnToUsersTable1()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD allowShowMyInfo SMALLINT(6) UNSIGNED DEFAULT 1 after allowShowMyBirthday");
        $stmt->execute();
    }

    function addColumnToUsersTable2()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD allowShowMyGallery SMALLINT(6) UNSIGNED DEFAULT 1 after allowShowMyInfo");
        $stmt->execute();
    }

    function addColumnToUsersTable3()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD allowShowMyFriends SMALLINT(6) UNSIGNED DEFAULT 1 after allowShowMyGallery");
        $stmt->execute();
    }

    function addColumnToUsersTable4()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD allowShowMyLikes SMALLINT(6) UNSIGNED DEFAULT 1 after allowShowMyFriends");
        $stmt->execute();
    }

    function addColumnToUsersTable5()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD allowShowMyGifts SMALLINT(6) UNSIGNED DEFAULT 1 after allowShowMyLikes");
        $stmt->execute();
    }

    // For version 2.7

    function addColumnToUsersTable6()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD ios_fcm_regid TEXT after gcm_regid");
        $stmt->execute();
    }

    // For version 2.8

    public function updateUsersTable()
    {
        $stmt = $this->db->prepare("UPDATE users SET allowShowMyLikes = 0, allowShowMyGifts = 0, allowShowMyFriends = 0, allowShowMyGallery = 0, allowShowMyInfo = 0");
        $stmt->execute();
    }
}
