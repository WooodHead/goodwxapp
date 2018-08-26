<?php


define('DN_CARD_STYLE_WUNIU', 20);//无牛
define('DN_CARD_STYLE_NIU1', 21); //牛1
define('DN_CARD_STYLE_NIU2', 22);//牛2
define('DN_CARD_STYLE_NIU3', 23);//牛3
define('DN_CARD_STYLE_NIU4', 24);//牛4
define('DN_CARD_STYLE_NIU5', 25);//牛5
define('DN_CARD_STYLE_NIU6', 26);//牛6
define('DN_CARD_STYLE_NIU7', 27);//牛7
define('DN_CARD_STYLE_NIU8', 28);//牛8
define('DN_CARD_STYLE_NIU9', 29);//牛9
define('DN_CARD_STYLE_NIUNIU', 30);//牛牛
define('DN_CARD_STYLE_YINGNIU', 31);//银牛
define('DN_CARD_STYLE_JINGNIU', 32); //金牛
define('DN_CARD_STYLE_ZHADAN', 33);//炸弹
define('DN_CARD_STYLE_WUXIAONIU', 34); //五小牛


class Domain_DNGame {

    public $suits = array('1', '2', '3', '4');
    public $color = array('黑桃', '红桃', '梅花', '方块');
    public $figures = array('A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K');
    public $cards = array();

    public function __construct() {
        $cards = array();

        for ($i = 0; $i < count($this->suits); $i++) {
            for ($j = 0; $j < count($this->figures); $j++) {
                $cards[] = array($this->suits[$i], $this->figures[$j], $this->color[$i] . '-' . $this->figures[$j]);
            }
        }
        $this->cards = $cards;
    }

    public function reqCard() {

//        $rand = rand(0, 1000);
//        $dealer_card = array();

//        if($rand < 1) {
//            $dealer_card = $this->get5niu();
//        } else if($rand < 2) {
//            $dealer_card = $this->getBomb();
//        } else if($rand < 3) {
//            $dealer_card = $this->getGold();
//        } else if($rand < 5) {
//            $dealer_card = $this->getSilver();
//        } else if($rand < 6) {
//            $dealer_card = $this->getNiuNiu();
//        } else {
//            $dealer_card = $this->getCard();
//        }
        $dealer_card = $this->getCard();

        $this->cards_pop($dealer_card);

        $pool1_card = $this->getCard();
        $pool2_card = $this->getCard();

        $result = array(
            'dealer_card' => $dealer_card,
            'pool1_card'  => $pool1_card,
            'pool2_card'  => $pool2_card,
        );

        return $result;

    }

    public function getCard() {
        //打乱牌序
        shuffle($this->cards);

        //生成3张牌
        return array(array_pop($this->cards), array_pop($this->cards), array_pop($this->cards), array_pop($this->cards), array_pop($this->cards));
    }

    /**
     * @param $uid
     * @param $loop_info
     * @return array
     * @throws PhalApi_Exception
     */
    public function getPoolRate($uid, $loop_info) {
        if (empty($loop_info)) {
            throw new PhalApi_Exception("牌值异常数据", 556);
        }

        $dealer_card = json_decode($loop_info['dealer_card'], true);
        $pool1_card = json_decode($loop_info['pool1_card'], true);
        $pool2_card = json_decode($loop_info['pool2_card'], true);


        $rate_1 = $rate_2 = $rate_3 = 0;

        if ($uid == $loop_info['dealer_id']) { //庄家
            $rate_1 = $this->compareCards($dealer_card, $pool1_card);
            $rate_2 = $this->compareCards($dealer_card, $pool2_card);
        } else { //普通用户
            $rate_1 = $this->compareCards($pool1_card, $dealer_card);
            $rate_2 = $this->compareCards($pool2_card, $dealer_card);
        }

        return array($rate_1, $rate_2);
    }

    /**
     * 换算倍率
     * @param $cards
     * @param $niu
     */
    public function formatRate($cards, $niu) {

        if (count($cards) != 5) {
            return 0;
        }

        $huaniu_num = 0;//花牛的个数
        $shi_num = 0;//为10的个数
        $card_set = array();
        $card_old = array();//保存旧牌
        foreach ($cards as $card) {
            $card_old[] = $card[1];
            if ($card[1] == "J" || $card[1] == "Q" || $card[1] == "K") {
                $huaniu_num++;
                $card_set[] = 10;
            } else if ($card[1] == '10') {
                $shi_num++;
                $card_set[] = 10;
            } else if ($card[1] == 'A') {
                $card_set[] = 1;
            } else {
                $card_set[] = intval($card[1]);
            }
        }

        //五小牛 10倍
        if (array_sum($card_set) < 10 && max($card_set) < 5) {
            return NIU_WU_XIAO;
        }

        //炸弹 6
        $same_array = array_count_values($card_old);
        if (max($same_array) >= 4) {
            return NIU_ZHA_DAN;
        }

        //金牛 5
        if ($huaniu_num >= 5) {
            return NIU_JING;
        }

        //银牛 4
        if ($huaniu_num == 4 && $shi_num == 1) {
            return NIU_YING;
        }

        //牛牛 3
        if (array_sum($card_set) % 10 == 0 && $niu == 10) {
            return NIU_NIU;
        }

        //牛几和无牛 1-2
        if ($niu == 1) {
            return NIU_1;
        } else if ($niu == 2) {
            return NIU_2;
        } else if ($niu == 3) {
            return NIU_3;
        } else if ($niu == 4) {
            return NIU_4;
        } else if ($niu == 5) {
            return NIU_5;
        } else if ($niu == 6) {
            return NIU_6;
        } else if ($niu == 7) {
            return NIU_7;
        } else if ($niu == 8) {
            return NIU_8;
        } else if ($niu == 9) {
            return NIU_9;
        } else {
            return NIU_WU; //0.5
        }
    }

    /**
     * 获得牌型
     * @param $card_info
     * @return int
     */
    public function getCardStyle($card_info) {
        $style = DN_CARD_STYLE_WUNIU;
        $card_niu = $this->niu($card_info);
        $rate = $this->formatRate($card_info, $card_niu);

        switch ($rate) {
            case NIU_WU:
                $style = DN_CARD_STYLE_WUNIU;
                break;
            case 1:
                $style = 20 + (int)$card_niu;
                break;
            case 2:
                $style = 20 + (int)$card_niu;
                break;
            case NIU_NIU:
                $style = DN_CARD_STYLE_NIUNIU;
                break;
            case NIU_YING:
                $style = DN_CARD_STYLE_YINGNIU;
                break;
            case NIU_JING:
                $style = DN_CARD_STYLE_JINGNIU;
                break;
            case NIU_ZHA_DAN:
                $style = DN_CARD_STYLE_ZHADAN;
                break;
            case NIU_WU_XIAO:
                $style = DN_CARD_STYLE_WUXIAONIU;
                break;
            default:
                break;
        }
        return $style;
    }

    /**
     * 比较2个池的牌大小，返回倍率 注意如果是大于，则返回正数倍率，如果是小于，但返回负数倍率
     * @param $card1
     * @param $card2
     * @return rete
     */
    public function compareCards($card1, $card2) {

        $ret = 0;
        //算出有几牛
        $card1_niu = $this->niu($card1);
        $card2_niu = $this->niu($card2);

        //算出倍率
        $rate_card1 = $this->formatRate($card1, $card1_niu);
        $rate_card2 = $this->formatRate($card2, $card2_niu);

        if ($rate_card1 > $rate_card2) {
            $ret = $rate_card1;
            //如果倍率相等
        } else if ($rate_card1 == $rate_card2) {
            //如果倍率相等，则比较牛大小
            if ($card1_niu > $card2_niu) {
                $ret = $rate_card1;
                //如果牛大小相等，则比较谁最大牌值
            } else if ($card1_niu == $card2_niu) {

                $card1_score_set = array();//保存card1的索引
                $card1_score_suit = array();//保存card1的花色索引
                $card2_score_set = array();//保存card2的索引
                $card2_score_suit = array();//保存card2的花色索引

                foreach ($card1 as $card) {
                    $card1_score_set[] = intval(array_search($card[1], $this->figures));
                    //$card1_score_suit[] = 5 - $this->suits[0];
                    $card1_score_suit[] = $card[0];
                }

                foreach ($card2 as $card) {
                    $card2_score_set[] = intval(array_search($card[1], $this->figures));
                    $card2_score_suit[] = 5 - $this->suits[0];
                    $card2_score_suit[] = $card[0];
                }

                //比较最大索引值的
                if(is_array($card1_score_set) && is_array($card2_score_set)) {
                    while (max($card1_score_set) == max($card2_score_set)) {

                        $pos_1 = array_search(max($card1_score_set), $card1_score_set);//查出最大值的位置
                        $pos_2 = array_search(max($card2_score_set), $card2_score_set);//查出最大值的位置

                        if($pos_1 === false || $pos_2 === false) break;

                        //黑桃》红桃》梅花》方块
                        if ($card1_score_suit[$pos_1] < $card2_score_suit[$pos_2]) {
                            //比较花色，大者为大
                            $ret = $rate_card1;
                            break;
                        } else if ($card1_score_suit[$pos_1] == $card2_score_suit[$pos_2]) {
                            //比较花色，如果一致就将数组中最大值删掉
                            unset($card1_score_set[$pos_1]);
                            unset($card1_score_suit[$pos_1]);
                            unset($card2_score_set[$pos_2]);
                            unset($card2_score_suit[$pos_2]);
                            continue;
                        } else {
                            //比较花色，小都为小
                            $ret = $rate_card2 * (-1);
                            break;
                        }
                    }
                }

                //如果最大索引值不相等，大者为大
                if(!empty($card1_score_set) && !empty($card2_score_set)) {
                    if (max($card1_score_set) != max($card2_score_set)) {
                        $ret = max($card1_score_set) > max($card2_score_set) ? $rate_card1 : $rate_card2 * (-1);
                    }
                }

            } else {
                $ret = $rate_card2 * (-1);
            }

        } else {
            $ret = $rate_card2 * (-1);
        }

        return $ret;
    }


    /**
     * 斗牛计算算法
     * @param array $cards =array(1,2,7,8,0);
     * @return int 0没有牛,10牛牛
     */
    public function niu($pork = array()) {
        $cards = array();
        foreach ($pork as $pk) {
            if ($pk[1] == '10' || $pk[1] == 'J' || $pk[1] == 'K' || $pk[1] == 'Q') {
                $cards[] = 10;
            } else if ($pk[1] == 'A') {
                $cards[] = 1;
            } else {
                $cards[] = intval($pk[1]);
            }
        }

        //所有有牛的组合
        $nius = array(
            array(1, 1, 8),
            array(1, 2, 7),
            array(1, 3, 6),
            array(1, 4, 5),
            array(2, 2, 6),
            array(2, 3, 5),
            array(2, 4, 4),
            array(3, 3, 4),
            array(10, 9, 1),
            array(10, 8, 2),
            array(10, 7, 3),
            array(10, 6, 4),
            array(10, 5, 5),
            array(9, 9, 2),
            array(9, 8, 3),
            array(9, 7, 4),
            array(9, 6, 5),
            array(8, 7, 5),
            array(8, 8, 4),
            array(8, 6, 6),
            array(7, 7, 6),
            array(10, 10, 10),
        );

        $flag = false;//默认没有牛
        $bakcards = $cards;
        foreach ($nius as $key => $niu) {
            $fnum = 0;
            foreach ($niu as $k => $v) {
                if (in_array($v, $cards)) {
                    $fnum++; //
                    array_splice($cards, array_search($v, $cards), 1); //删除已查到的位置值
                }
            }
            if ($fnum > 2) {    //3个都对上了，说明有牛
                $flag = true;
                break;
            } else { //没牛，重新赋值
                $cards = $bakcards;
            }

        }
        if ($flag) {//返回牛几
            $niunum = intval($cards[0] + $cards[1]) % 10;
            return $niunum === 0 ? 10 : $niunum;
        } else {
            return 0;
        }
    }

    //五小牛，5张牌小于5，全部加起来小于10
    private function get5niu() {

        //牌容器
        $cards = [];

        //牌类型
        $suit = array('1', '2', '3', '4');

        //五张牌总和
        $sum = 9;

        //牌数量
        $card_num = count($cards);

        while($card_num < 5) {

            //保底牌
            $minimum = 4 - $card_num;

            //总和减去剩余保底的牌
            $card_num_max = ($sum - $minimum) > $minimum ? $minimum : $sum - $minimum;
            if($card_num >= 4) {
                $card_num_max = $sum > 5 ? 4 : $sum;
            }

            //随机取得牌值
            $card_num_index = rand(1, $card_num_max) - 1;

            //打乱牌型
            shuffle($suit);

            //获得一张牌
            $card = array(
                $suit[0],
                $this->figures[$card_num_index],
                $this->color[$suit[0] - 1] . "-" . $this->figures[$card_num_index],
            );

            //是否已经有这张牌
            $had_card = false;
            foreach($cards  as $per_card) {
                if($card[0] == $per_card[0] && $card[1] == $per_card[1]) {
                    $had_card = true;
                    break;
                }
            }

            //增加一张牌
            if(!$had_card) {
                $cards[] = $card;
                $sum -= $card_num_index + 1;
            }

            $card_num = count($cards);

        }

        shuffle($cards);
        return $cards;
    }

    //炸弹
    private function getBomb() {
        //一、随机牌值
        $rand_val = rand(0,12);
        $suit = array('1', '2', '3', '4');
        shuffle($suit);

        //二、四张一样的牌
        $cards = array();
        for ($i =0; $i<4; $i ++) {
            $cards[] = array($suit[$i], $this->figures[$rand_val],$this->color[$i]."-".$this->figures[$rand_val]);
        }

        //另一张牌值
        $another_rand_val = $rand_val - 1;
        if($another_rand_val < 0) {
            $another_rand_val += 2;
        }

        //另一张牌型
        $another_rand_suit_no = rand(0, 3);
        $cards[] = array(
            $suit[$another_rand_suit_no],
            $this->figures[$another_rand_val],
            $this->color[$another_rand_suit_no] . "-" . $this->figures[$another_rand_val],
        );
        //生成3张牌
        shuffle($cards);
        return $cards;
    }

    //金牛
    private function getGold() {

        //所有牌容器
        $cards = array();

        $cards_num = 0;

        while($cards_num < 5) {
            //金牛五张花
            $rand_val = rand(10,12);
            $suit = array('1', '2', '3', '4');
            shuffle($suit);

            $rand_style = $suit[0];

            $card = array($rand_style, $this->figures[$rand_val],$this->color[$rand_style - 1]."-".$this->figures[$rand_val]);

            //如果已经有一张牌,要判断是否已经存在新产生的牌
            if($cards_num > 0) {
                $had_card = false;
                foreach($cards  as $per_card) {
                    if($card[0] == $per_card[0] && $card[1] == $per_card[1]) {
                        $had_card = true;
                        break;
                    }
                }

                if(!$had_card) {
                    $cards[] = $card;
                }
            } else {
                $cards[] = $card;
            }

            $cards_num = count($cards);
        }

        shuffle($cards);
        return $cards;

    }

    //银牛
    private function getSilver() {
        $cards = array();

        $cards_num = 0;

        //先生成四张花
        while($cards_num < 4) {
            $rand_val = rand(10,12);
            $suit = array('1', '2', '3', '4');
            shuffle($suit);

            $rand_style = $suit[0];

            $card = array($rand_style, $this->figures[$rand_val],$this->color[$rand_style - 1]."-".$this->figures[$rand_val]);

            if($cards_num > 0) {
                $had_card = false;
                foreach($cards  as $per_card) {
                    if($card[0] == $per_card[0] && $card[1] == $per_card[1]) {
                        $had_card = true;
                        break;
                    }
                }

                if(!$had_card) {
                    $cards[] = $card;
                }
            } else {
                $cards[] = $card;
            }

            $cards_num = count($cards);
        }

        //牌值为10的牌
        $card_ten = 9;

        $suit = array('1', '2', '3', '4');
        shuffle($suit);
        $rand_style = $suit[0];

        $card = array($rand_style, $this->figures[$card_ten],$this->color[$rand_style - 1]."-".$this->figures[$card_ten]);

        $cards[] = $card;

        shuffle($cards);
        return $cards;
    }

    //牛牛牌
    private function getNiuNiu() {
        $cards = array();

        $cards_num = 0;

        //获得两张花
        while($cards_num < 2) {
            $rand_val = rand(9,12);
            $suit = array('1', '2', '3', '4');
            shuffle($suit);

            $rand_style = $suit[0];

            $card = array($rand_style, $this->figures[$rand_val],$this->color[$rand_style - 1]."-".$this->figures[$rand_val]);

            $had_card = false;
            foreach($cards  as $per_card) {
                if($card[0] == $per_card[0] && $card[1] == $per_card[1]) {
                    $had_card = true;
                    break;
                }
            }

            if(!$had_card) {
                $cards[] = $card;
            }

            $cards_num = count($cards);

        }


        //获得三张加起来为10的倍数
        $left_three_card_sum = 0;
        while($cards_num < 5) {
            if($left_three_card_sum <= 0) {
                $rand_val = rand(1,9);
            } else {
                if($cards_num < 4) {
                    $rand_val = rand(1,9);
                } else {
                    $rand_val = 10 - ($left_three_card_sum % 10);
                }
            }
            $suit = array('1', '2', '3', '4');
            shuffle($suit);

            $rand_style = $suit[0];

            $card_num_index = $rand_val - 1;
            $card = array($rand_style, $this->figures[$card_num_index],$this->color[$rand_style-1]."-".$this->figures[$card_num_index]);

            $had_card = false;
            foreach($cards  as $per_card) {
                if($card[0] == $per_card[0] && $card[1] == $per_card[1]) {
                    $had_card = true;
                    break;
                }
            }

            if(!$had_card) {
                $left_three_card_sum += $rand_val;
                $cards[] = $card;
            }

            $cards_num = count($cards);
        }

        shuffle($cards);
        return $cards;
    }

    /**
     * 过滤掉已经产生的牌值
     */
    private function cards_pop($dealer_card) {
        foreach ($this->cards as $key => $card) {
            foreach($dealer_card as $per_dealer_card) {
                if($per_dealer_card[0] == $card[0] && $per_dealer_card[1] == $card[1]) {
                    unset($this->cards[$key]);
                    break;
                }
            }
        }

    }


}