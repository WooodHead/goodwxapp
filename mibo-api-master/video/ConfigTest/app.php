<?php
/**
 * 请在下面放置任何您需要的应用配置
 */

return array(

    /**
     * 应用接口层的统一参数
     */
    'apiCommonRules' => array(
        //'sign'    => array('name' => 'sign', 'require' => true, 'min' => 30, 'max'=> 38, 'desc' => '签名'),
        //'channel' => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
    ),
    'PHPMailer'      => array(
        'email' => array(
            'host'     => 'ssl://smtp.qq.com:465',
            'username' => 'service@mibolive.com',
            'password' => 'nojvsegwqldxidah',
            'from'     => 'service@mibolive.com',
            'fromName' => '米播客服',
            'sign'     => '<br/><br/>请不要回复此邮件，谢谢！<br/><br/>-- 感谢您选择和我们合作 ',
        ),
    ),
    'sms'            => array(
        'clsms' => array(   /* 创蓝文化 短信接口 */
            "account" => "sz_hjmb",
            'secret'  => 'Hjmb168888',
            'url'     => "http://222.73.117.156/msg/HttpBatchSendSM?",
        ),
    ),
    'easemob'        => array(
        'client_id'       => 'YXA6biuCgFUJEeWM9uEtjac7Cg',
        'client_secret'   => 'YXA6DcTLtYV5ltFD0jrbRU6NMHmzvKQ',
        'org_name'        => 'heju',
        'app_name'        => 'mibo',    //老米播
        'im_password_key' => '@heju@mibo@_20160921',
        'im_password'     => '6eee4d33c03c8b23cb8d6d3e8a3a45a8',
    ),
    'easemob2'       => array(
        'client_id'       => 'YXA6GSW7sIiFEeat7p2zYgsQ0w',
        'client_secret'   => 'YXA6QprY98xvduQfmqIWzzHxkTR3R8k',
        'org_name'        => 'heju',
        'app_name'        => 'mibo2',   //游戏米播
        'im_password_key' => '@heju@mibo@_20160921',
        'im_password'     => '6eee4d33c03c8b23cb8d6d3e8a3a45a8',
    ),
    'thirdLogin'     => array(
        'qq'     => array(
            "APP_ID"     => 1104838490,
            'APP_KEY'    => '69a89438ebebaf4a9220ffa70fdc51f7',
            'APP_SECRET' => '******',
            'callback'   => "http://d.mibolive.com/mibo/callback/qq.php"
        ),
        //微信可以配置在下面
        /*开发者帐号*/
        'wechat' => array(
            "APP_ID"         => "wx5251f26ac8f346ab",
            'APP_SECRET'     => '4afae7c79d8f2717f4c699bbcf41bc97',
            'APP_SECRET_bak' => 'c505ba7c230b3144c206e531efefd733',
            'callback'       => "http://d.mibolive.com/mibo/callback/wechat.php",
            'redirect_uri'   => "http://d.mibolive.com/mibo/login.php?service=Weixin.Getcash",
        ),
        /*公众号*/
        'weixin' => array(
            "APP_ID"       => "wxa880a0ab53ff4c52",
            'APP_SECRET'   => '9d1a86be32b832e368a94c4e757c6c0e',
            'callback'     => "http://d.mibolive.com/mibo/callback/wechat.php",
            'redirect_uri' => "http://api.mibolive.com/apps/weixin/service.php",
        ),
        'sina'   => array(
            'WB_AKEY'         => '4071240345',
            'WB_SKEY'         => '23488a3128e51bcb5ad34f1d3595f79b',
            'WB_CALLBACK_URL' => "http://mibo.yahalei.com/mibo/callback/sina_cb.php",
            //'WB_CALLBACK_URL' => "https://mibo.yahalei.com/mibo/callback/sina_cb.php",
        ),
    ),
    'aliyun'         => array(
        'private_key'     => 'Aliyunsign4578',
        'private_key_bak' => 'Aliyunsignatrue4578',
    ),

    'aliRedis'          => array(
        //Redis缓存配置项
        'servers'  => array(
            'host'   => '127.0.0.1',        //Redis服务器地址
            'port'   => '6379',             //Redis端口号
            'prefix' => 'developers_',      //Redis-key前缀
            'auth'   => '199016',    //Redis链接密码
        ),
        // Redis分库对应关系
        'DB'       => array(
            'developers' => 1,
            'user'       => 2,
            'code'       => 3,
        ),
        //使用阻塞式读取队列时的等待时间单位/秒
        'blocking' => 5,
    ),
    /**
     * 阿里云Redis缓存
     * ApsaraDB for Redis
     */
    'redis' => array(
        //Redis缓存配置项
        'servers'  => array(
            'host'   => 'r-m5e0ec6bef0d2974.redis.rds.aliyuncs.com',        //Redis服务器地址
            'port'   => 6379,         //Redis端口号
            'prefix' => 'mibo_',      //Redis-key前缀
            'auth'   => 'Miboredis20170123',    //Redis链接密码
        ),
        // Redis分库对应关系
        'DB'       => array(
            'mibo' => 1,
            'user'       => 2,
            'code'       => 3,
        ),
        //使用阻塞式读取队列时的等待时间单位/秒
        'blocking' => 5,
    ),

    'wechat_pay' => array(
        'appid'      => 'wx5039d46ebb6580ad',
        'mch_id'     => '1289671201',
        'api_key'    => 'McFk71bCr9H30ybTop4vCWlC9gWOyebo',
        'notify_url' => 'https://mibo.yahalei.com/mibo/callback/wechat_pay_notify.php',
        //'notify_url' => 'http://mibo.yahalei.com/mibo/callback/wechat_pay_notify.php',
    ),

    'alipay' => array(
        'partner_id'  => '2088411495151139',
        'rsa_private' => '1+QkQi7muA8oA9SbtHjF6XmvaIPxLcAk1dEVaRXPUkjfFQ==',
        'seller_id'   => 'cheng@dianhengad.com',
    ),

    'Swoole' => array(
        //服务
        'server' => array(
            'ip'         => '127.0.0.1',
            'port'       => 9501,
            'worker_num' => 1,
        ),
        //计划任务
        'task'   => array(
            'ip'         => '127.0.0.1',
            'port'       => 9502,
            'worker_num' => 1,
        ),
    ),

);