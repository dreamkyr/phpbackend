<?php

    /*!
     * ifsoft.co.uk v1.0
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * qascript@ifsoft.co.uk
     *
     * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
     */

	class language extends db_connect
    {

        private $language;

		public function __construct($dbo = NULL, $language = "en")
        {

			parent::__construct($dbo);

            $this->set($language);

		}

        public function timeAgo($time)
        {

            switch($this->get()) {

                case "id" :  {

                    $titles = array("menit","menit","menit");
                    $titles2 = array("jam","jam","jam");
                    $titles3 = array("hari","hari","hari");
                    $titles4 = array("bulan","bulan","bulan");
                    $about = " lalu";
                    $now = "kurang dari 1 menit lalu";
                    break;
                }

                case "ua" :  {

                    $titles = array("хвилину","хвилини","хвилин");
                    $titles2 = array("година","години","годин");
                    $titles3 = array("день","дні","днів");
                    $titles4 = array("місяць","місяці","місяців");
                    $about = " тому";
                    $now = "Тільки що";
                    break;
                }

                case "ru" :  {

                    $titles = array("минуту","минуты","минут");
                    $titles2 = array("час","часа","часов");
                    $titles3 = array("день","дня","дней");
                    $titles4 = array("месяц","месяца","місяців");
                    $about = " назад";
                    $now = "Только что";
                    break;
                }

                default :  {

                    $titles = array("m","m","m");
                    $titles2 = array("h","h","h");
                    $titles3 = array("d","d","d");
                    $titles4 = array("month","months","months");
                    $about = " ago";
                    $now = "Just now";

                    break;
                }
            }

            $new_time = time();
            $time = $new_time - $time;

            if($time < 60) return $now; else
            if($time < 3600) return $this->declOfNum(($time-($time%60))/60, $titles).$about; else
            if($time < 86400) return$this->declOfNum(($time-($time%3600))/3600, $titles2).$about; else
            if($time < 2073600) return $this->declOfNum(($time - ($time % 86400)) / 86400, $titles3).$about; else
            if($time < 62208000) return $this->declOfNum(($time - ($time % 2073600)) / 2073600, $titles4).$about; else return gmdate("d-m-Y", $time);
        }

        static function declOfNum($number, $titles)
        {
            $cases = array(2, 0, 1, 1, 1, 2);
            return $number.''.$titles[ ($number%100>4 && $number%100<20) ? 2 : $cases[($number%10<5) ? $number%10:5] ];
        }

        public function set($language)
        {
            $this->language = $language;
        }

        public function get()
        {
            return $this->language;
        }
	}
