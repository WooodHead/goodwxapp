<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/30
 * Time: 18:35
 */
define('PSZ_CARD_STYLE_NORMAL', 10);    //普通牌
define('PSZ_CARD_STYLE_DUIZI', 11);     //对子
define('PSZ_CARD_STYLE_TONGHUA', 12);   //同花
define('PSZ_CARD_STYLE_SHUNZI', 13);    //顺子
define('PSZ_CARD_STYLE_TONGHUASHUN', 14); //同花顺
define('PSZ_CARD_STYLE_BAOZI', 15);     //豹子

class Domain_PSZGame {
    //黑桃 红桃 梅花 方块
    public $suits = array('1', '2', '3', '4');
    public $color = array('黑桃', '红桃', '梅花', '方块');
    public $figures = array('2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A');
    public $cards = array();

    public function __construct() {
        $cards = array();
//        foreach ($this->suits as $suit) {
//            foreach ($this->figures as $figure) {
//                $cards[] = array($suit, $figure, $color);
//            }
//        }

        for ($i = 0; $i < count($this->suits); $i++) {
            for ($j = 0; $j < count($this->figures); $j++) {
                $cards[] = array($this->suits[$i], $this->figures[$j], $this->color[$i] . '-' . $this->figures[$j]);
            }
        }
        $this->cards = $cards;
    }

    public function reqCard() {
        $rand = rand(0, 1000);
        $dealer_card = array();
//        if($rand < 5) {
//            $dealer_card = $this->getBaoZi();
//        } else if($rand < 30) {
//            $dealer_card = $this->getTongHuaShun();
//        } else if($rand < 60) {
//            $dealer_card = $this->getTonghua();
//        } else if($rand < 90) {
//            $dealer_card = $this->getShunZi();
//        } else if($rand < 150) {
//            $dealer_card = $this->getDuiZi();
//        } else {
//            $dealer_card = $this->getCard();
//        }

        if($rand < 5) {
            $dealer_card = $this->getBaoZi();
        } else if($rand < 20) {
            $dealer_card = $this->getTongHuaShun();
        } else if($rand < 40) {
            $dealer_card = $this->getTonghua();
        } else if($rand < 60) {
            $dealer_card = $this->getShunZi();
        } else if($rand < 100) {
            $dealer_card = $this->getDuiZi();
        } else {
            $dealer_card = $this->getCard();
        }

        $this->cards_pop($dealer_card);

        $pool1_card = $this->getCard();
        $pool2_card = $this->getCard();
        $pool3_card = $this->getCard();

        $result = array(
            'dealer_card' => $dealer_card,
            'pool1_card'  => $pool1_card,
            'pool2_card'  => $pool2_card,
            'pool3_card'  => $pool3_card,
        );

        return $result;

    }

    /**
     * 过滤掉已经产生的牌值
     */
    private function cards_pop($dealer_card) {
        foreach ($this->cards as $key => $card) {
            if($dealer_card[0][0] == $card[0] && $dealer_card[0][1] == $card[1]
                || $dealer_card[1][0] == $card[0] && $dealer_card[1][1] == $card[1]
                || $dealer_card[2][0] == $card[0] && $dealer_card[2][1] == $card[1]) {
                unset($this->cards[$key]);
            }
        }

    }



    /**
     * 生成豹子
     */
    private function getBaoZi() {

        $randVal = rand(0,12);
        $suit = array('1', '2', '3', '4');
        shuffle($suit);

        $cards = array();
        for ($i =0; $i<3; $i ++) {
            $cards[] = array($suit[$i], $this->figures[$randVal],$this->color[$i]."-".$this->figures[$randVal]);
        }


        //生成3张牌
        return $cards;


    }


    /**
     * 生成同花顺
     */
    private function getTongHuaShun() {
        $val1 = rand(0,12);
        $val2 = $val1 == 12? 0: $val1 + 1;
        $val3 = $val2 == 12? 0: $val2 + 1;

        $arr = array($val1,$val2,$val3);
        shuffle($arr);
        $val1 = $arr[0];
        $val2 = $arr[1];
        $val3 = $arr[2];

        $cards = array();

        $suit = array('1', '2', '3', '4');
        $ss = rand(0,3);

        $cards[] = array($suit[$ss], $this->figures[$val1], $this->color[$ss]."-".$this->figures[$val1]);

        $cards[] = array($suit[$ss], $this->figures[$val2], $this->color[$ss]."-".$this->figures[$val2]);

        $cards[] = array($suit[$ss], $this->figures[$val3], $this->color[$ss]."-".$this->figures[$val3]);

        return $cards;
    }


    public function getTonghua() {
        $val1 = rand(0,12);
        $val2 = rand(0,12);
        $val3 = rand(0,12);

        while ($val1 == $val2) {
            $val2 = rand(0,12);
        }

        while ($val2 == $val3 || $val1 == $val3) {
            $val3 = rand(0,12);
        }

        $suit = array('1', '2', '3', '4');
        $ss = rand(0,3);
        $cards = array();
        $cards[] = array($suit[$ss], $this->figures[$val1], $this->color[$ss]."-".$this->figures[$val1]);
        $cards[] = array($suit[$ss], $this->figures[$val2], $this->color[$ss]."-".$this->figures[$val2]);
        $cards[] = array($suit[$ss], $this->figures[$val3], $this->color[$ss]."-".$this->figures[$val3]);

        return $cards;
    }

    /**
     * 生成顺子
     */
    private function getShunZi() {
        $val1 = rand(0,12);
        $val2 = $val1 == 12? 0: $val1 + 1;
        $val3 = $val2 == 12? 0: $val2 + 1;

        $cards = array();

        $suit = array('1', '2', '3', '4');
        $s1 = rand(0,3);
        $cards[] = array($suit[$s1], $this->figures[$val1], $this->color[$s1]."-".$this->figures[$val1]);

        $s2 = rand(0,3);
        $cards[] = array($suit[$s2], $this->figures[$val2], $this->color[$s2]."-".$this->figures[$val2]);

        $s3 = rand(0,3);
        $cards[] = array($suit[$s3], $this->figures[$val3], $this->color[$s3]."-".$this->figures[$val3]);

        return $cards;
    }
    /**
     * 生成对子
     */
    private function getDuiZi() {

        $val1 = rand(0,12);
        $val2 = $val1;
        $val3 = rand(0,12);

        while ($val1 == $val3) {
            $val3 = rand(0,12);
        }

        $suit = array('1', '2', '3', '4');
        shuffle($suit);

        $cards[] = array($suit[0], $this->figures[$val1], $this->color[0]."-".$this->figures[$val1]);

        $cards[] = array($suit[1], $this->figures[$val2], $this->color[1]."-".$this->figures[$val2]);

        $cards[] = array($suit[2], $this->figures[$val3], $this->color[2]."-".$this->figures[$val3]);

        //生成3张牌
        return $cards;
    }

    public function getCard() {
        //打乱牌序
        shuffle($this->cards);

        //生成3张牌
        return array(array_pop($this->cards), array_pop($this->cards), array_pop($this->cards));
    }

    public function getPoolRate($uid, $loop_info) {
        if (empty($loop_info)) {
            throw new PhalApi_Exception("牌值异常数据", 556);
        }

        $dealer_card = json_decode($loop_info['dealer_card'], true);
        $pool1_card = json_decode($loop_info['pool1_card'], true);
        $pool2_card = json_decode($loop_info['pool2_card'], true);
        $pool3_card = json_decode($loop_info['pool3_card'], true);

        $rate_1 = $rate_2 = $rate_3 = 0;
        $dealer_score = $this->ownScore($dealer_card);
        $pool1_score = $this->ownScore($pool1_card);
        $pool2_score = $this->ownScore($pool2_card);
        $pool3_score = $this->ownScore($pool3_card);

        if ($uid == $loop_info['dealer_id']) { //庄家
            if ($this->compareCards($dealer_card, $pool1_card) > 0) {
                $rate_1 = $this->formatRate($dealer_score);
            } else {
                $rate_1 = $this->formatRate($pool1_score) * (-1);
            }
            if ($this->compareCards($dealer_card, $pool2_card) > 0) {
                $rate_2 = $this->formatRate($dealer_score);
            } else {
                $rate_2 = $this->formatRate($pool2_score) * (-1);
            }
            if ($this->compareCards($dealer_card, $pool3_card) > 0) {
                $rate_3 = $this->formatRate($dealer_score);
            } else {
                $rate_3 = $this->formatRate($pool3_score) * (-1);
            }
        } else { //普通用户
            if ($this->compareCards($pool1_card, $dealer_card) > 0) {
                $rate_1 = $this->formatRate($pool1_score);
            } else {
                $rate_1 = $this->formatRate($dealer_score) * (-1);
            }
            if ($this->compareCards($pool2_card, $dealer_card) > 0) {
                $rate_2 = $this->formatRate($pool2_score);
            } else {
                $rate_2 = $this->formatRate($dealer_score) * (-1);
            }
            if ($this->compareCards($pool3_card, $dealer_card) > 0) {
                $rate_3 = $this->formatRate($pool3_score);
            } else {
                $rate_3 = $this->formatRate($dealer_score) * (-1);
            }
        }

        return array($rate_1, $rate_2, $rate_3);
    }

    private function formatRate($score) {
        $rate = 0.0;
        if ($score > 60 * 100000) { //豹子
            $rate = 5.0;
        } else if ($score > (30 * 100000 + 20 * 100000)) { //同花顺
            $rate = 3.0;
        } else if ($score > 30 * 100000) { //同花
            $rate = 2.0;
        } else if ($score > 20 * 100000) { //顺子
            $rate = 1.5;
        } else {
            $rate = 1.0;
        }

        return $rate;
    }


    public function getCardStyle($card_info) {
        $score = $this->ownScore($card_info);
        $style = PSZ_CARD_STYLE_NORMAL;
        if ($score > 60 * 100000) { //豹子
            $style = PSZ_CARD_STYLE_BAOZI;
        } else if ($score > (30 * 100000 + 20 * 100000)) { //同花顺
            $style = PSZ_CARD_STYLE_TONGHUASHUN;
        } else if ($score > 30 * 100000) { //同花
            $style = PSZ_CARD_STYLE_TONGHUA;
        } else if ($score > 20 * 100000) { //顺子
            $style = PSZ_CARD_STYLE_SHUNZI;
        } else if ($score > 10 * 100000) { //对子
            $style = PSZ_CARD_STYLE_DUIZI;
        }

        return $style;
    }


    public function compareCards($card1, $card2) {
        $score1 = $this->ownScore($card1);
        $score2 = $this->ownScore($card2);
        if ($score1 > $score2) {
            return 1;
        } elseif ($score1 < $score2) {
            return -1;
        }
        return 0;
    }


    private function ownScore($card) {
        $suit = $figure = array();
        foreach ($card as $v) {
            $suit[] = $v[0];
            $figure[] = array_search($v[1], $this->figures) + 2;
        }
        //牌值前补齐0
        for ($i = 0; $i < 3; $i++) {
            $figure[$i] = str_pad($figure[$i], 2, '0', STR_PAD_LEFT);
        }

        //根据牌值进行降序排列
        rsort($figure);

        //对于第一张和第二张是对子做特殊处理，保证是对子排在后面
        if ($figure[1] == $figure[2]) {
            $temp = $figure[0];
            $figure[0] = $figure[2];
            $figure[2] = $temp;
        }
        //单牌算分
        $score = intval($figure[0] . $figure[1] . $figure[2]);

        //豹子算分 60*100000  x5.0倍
        if ($figure[0] == $figure[1] && $figure[0] == $figure[2]) {
            $score += 60 * 100000;
        }

        //同花算分 30*100000 x2.0倍
        if ($suit[0] == $suit[1] && $suit[0] == $suit[2]) {
            $score += 30 * 100000;
        }

        //顺子算分 20*100000 x1.5倍
        if ($figure[0] == $figure[1] + 1 && $figure[1] == $figure[2] + 1 || implode($figure) == '140302') {
            $score += 20 * 100000;
        }
        //对子算分 10*100000
        if ($figure[0] == $figure[1] && $figure[1] != $figure[2]) {
            $score += 10 * 100000;
        }
        return $score;
    }


}