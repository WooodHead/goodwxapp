<?php

/**
 * 签名校验
 */
class Common_SignFilter implements PhalApi_Filter {

    public function check() {

        $pre = $req = DI()->request->getAll();

        //一、如果是计划任务的请求，不用签名
        $ip = PhalApi_Tool::getClientIp();
        $ip_white_list = DI()->config->get('sys.ip_white_list');
        if(in_array($ip, $ip_white_list) || isset($req['mibo'])) {
            return;
        }

        //如果是ip黑名单，则不通过
        if(DI()->redis->sIsMember('ip_black_list', $ip)) {
            //return;
            throw new PhalApi_Exception('ip异常被封,联系客服', 402);
        }

        if (isset($req['sign'])) {
            $sign = $req['sign'];
            unset($req['sign']);
        } else {
            throw new PhalApi_Exception_BadRequest("没有签名参数", 3);
        }


        if (isset($req['debug'])) {
            unset($req['debug']);
        }

        if (isset($req['__sql__'])) {
            $ip_list = DI()->config->get('sys.ip_white_list');
            unset($req['__sql__']);
            if (in_array($ip, $ip_list)) {
                unset($req['__sql__']);
            } else {
                //throw new PhalApi_Exception_BadRequest("非法入侵，您当前IP为：" . $ip, 30);
            }
        }

        $sign_cfg = DI()->config->get('sys.sign');

        ksort($req);

        $sign_str = http_build_query($req) . '@' . $sign_cfg['key_secret'];
        $signature = md5($sign_str);

        if (isset($pre['__sql__'])) {
            g_debug($sign);
            g_debug("签名参数:\n" . $sign_str . "\nSignature:" . $signature);
        }

        if ($sign != $signature) {
            $error_times = DI()->redis->get_time(md5($ip));
            $error_times = $error_times ? ($error_times + 1) : 1;
            DI()->redis->set_time(md5($ip), $error_times);

            if($error_times > 5) {
                DI()->redis->sadd('ip_black_list', $ip);
            }
            $content = '签名失败,时间：' . date('Y-m-d H:i:s', time()) . ', ip:' . $ip . ',接口：' . $req['service'] . '。';
            $content .= '参数:' . json_encode($req);

            $mailer = new PHPMailer_Lite();
            $add_arr = array('362226577@qq.com', '313256513@qq.com');
            $mailer->send($add_arr, $ip . '签名失败', $content);
            throw new PhalApi_Exception_BadRequest('签名失败', 1);
        }
    }

    public function arrayToUrl($req = array(), $separator = NULL) {

        if (!is_array($req)) {
            return false;
        }

        $params = "";
        $count = count($req);
        $index = 0;
        foreach ($req as $k => $v) {
            $index++;
            if ($index == $count) {
                $params .= $k . '=' . $v;
            } else {
                if ($separator === NULL) {
                    $params .= $k . '=' . $v . '&';
                } else {
                    $params .= $k . '=' . $v . $separator;
                }
            }
        }

        return $params;
    }
}