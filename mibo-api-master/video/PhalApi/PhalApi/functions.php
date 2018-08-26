<?php
/**
 * 考虑再三，出于人性化关怀，提供要些快速的函数和方法
 *
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2014-12-17
 */

/**
 * 获取DI
 * 相当于PhalApi_DI::one()
 * @return PhalApi_DI
 */
function DI() {
    return PhalApi_DI::one();
}

/**
 * 设定语言，SL为setLanguage的简写
 * @param string $language 翻译包的目录名
 */
function SL($language) {
    PhalApi_Translator::setLanguage($language);
}

/**
 * 快速翻译
 * @param string $msg 待翻译的内容
 * @param array $params 动态参数
 */
function T($msg, $params = array()) {
    return PhalApi_Translator::get($msg, $params);
}

/*****************************************************
 * 获取客户端IP地址
 ******************************************************/
function g_getIP() {
    if (@$_SERVER["HTTP_X_FORWARDED_FOR"])
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    else if (@$_SERVER["HTTP_CLIENT_IP"])
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    else if (@$_SERVER["REMOTE_ADDR"])
        $ip = $_SERVER["REMOTE_ADDR"];
    else if (@getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (@getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (@getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
    else
        $ip = "Unknown";
    return $ip;

}

//获取手机端省区
function GetIpLookup($ip = ''){
    if(empty($ip)){
        return "";
    }
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if(empty($res)){ return false; }
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if(!isset($jsonMatches[0])){ return false; }
    $json = json_decode($jsonMatches[0], true);
    if(isset($json['ret']) && $json['ret'] == 1){
        $json['ip'] = $ip;
        unset($json['ret']);
    }else{
        return false;
    }
    return $json;
}


/**
 * 2位小数的随机数
 * @param int $min
 * @param int $max
 * @return string
 */
function g_randomFloat($min = 0, $max = 10) {
    $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
    return sprintf("%.2f", $num);

}

/**
 * 随机字母+数字
 * @param int $length
 * @return string
 */
function g_getRandChar($length = 4) {
    $str = "";
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
}

/**
 * 打印日志
 * @param int $length
 * @return string
 */
function g_debug($msg = '') {
    $pre = '<!DOCTYPE html><html lang=\"en\"> <head> <meta charset=\"UTF-8\"> </head> <body>' . "<textarea type='text' style='color:#786eff; border-color: #33aaff; width: 52%; min-height: 65px;'>";
    $after = "</textarea></body></html>";
    print_r($pre . $msg . $after);
}

/**
 * 隐藏手机号中间4位
 * @param $phone
 * @return mixed
 */
function g_hidtel($phone) {
    $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
    if ($IsWhat == 1) {
        return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);
    } else {
        return preg_replace('/(1[345678]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
    }
}

/**
 * 验证手机号是否正确
 * @author 范鸿飞
 * @param INT $mobile
 */
function g_isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

function g_tranTime($time) {
    $rtime = date("m-d H:i", $time);
    $htime = date("H:i", $time);

    $time = time() - $time;

    switch($time) {
        case (-60 * 60 * 24 * 3) >= $time :
            $str = '三天后 ';
            break;
        case (-60 * 60 * 24 * 2) >= $time && $time > (-60 * 60 * 24 * 3) :
            $str = '后天 ' . $htime;
            break;
        case (-60 * 60 * 24 * 1) >= $time && $time > (-60 * 60 * 24 * 2) :
            $str = '明天 ' . $htime;
            break;
        case (-60 * 60) >= $time && $time > (-60 * 60 * 24) :
            $hour = floor(-$time / (60 * 60));
            $str = $hour . '小时后 ' . $htime;
            break;
        case 0 >= $time && $time > (-60 * 60) :
            $min = floor(-$time / 60);
            $str = $min . '分钟后';
            break;
        case 60 >= $time && $time > 0 :
            $str = '刚刚';
            break;
        case (60 * 60) >= $time && $time > 60 :
            $min = floor($time / 60);
            $str = $min . '分钟前';
            break;
        case (60 * 60 * 24) >= $time && $time > (60 * 60) :
            $h = floor($time / (60 * 60));
            $str = $h . '小时前 ' ;
            break;
        case (60 * 60 * 24 * 2) >= $time && $time > (60 * 60 * 24) :
            $str = '昨天' ;
            break;
        case (60 * 60 * 24 * 3) >= $time && $time > (60 * 60 * 24 * 2) :
            $str = '前天' ;
            break;
        default:
            $str = $rtime;
            break;
    }
    //if ($time < 60) {
    //    $str = '刚刚';
    //} elseif ($time < 60 * 60) {
    //    $min = floor($time / 60);
    //    $str = $min . '分钟前';
    //} elseif ($time < 60 * 60 * 24) {
    //    $h = floor($time / (60 * 60));
    //    $str = $h . '小时前 ' ;
    //} elseif ($time < 60 * 60 * 24 * 3) {
    //    $d = floor($time / (60 * 60 * 24));
    //    if ($d == 1)
    //        $str = '昨天 ';
    //    else
    //        $str = '前天 ';
    //} else {
    //    $str = $rtime;
    //}
    return $str;
}

//传入一个日期，返回今天 10：23，明天：10:23，周三 10:23这样的格式
function g_dayToWeak($date) {
    $weekarray = array("日","一","二","三","四","五","六");
    $time = strtotime($date);
    $now = time();
    $today_time = strtotime(date('Y-m-d 23:59:59', $now));
    $tomorrow_time = $today_time + 86400;
    $left_time = $time - $now;
    if($left_time > 0) {
        if($time <= $today_time) {
            $week = '今天';
        } elseif($time <=$tomorrow_time) {
            $week = '明天';
        } else {
            $week = "周".$weekarray[date("w",$time)];
        }

    } else {
        $week = "周".$weekarray[date("w",$time)];
    }
    $hour = date(' H:i', $time);
    return $week.$hour;
}

