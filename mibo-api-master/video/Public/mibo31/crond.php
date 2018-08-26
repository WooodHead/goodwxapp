<?php
/**
 * Qvod 统一入口
 */


require_once dirname(__FILE__) . '/../init.php';
require_once 'constant.php';
//装载你的接口
DI()->loader->addDirs('Mibo31');

set_time_limit(0);

$a = $_REQUEST['service'] = $_POST['service'] = $_GET['service'] = "Crond.Act";

/** ---------------- 响应接口请求 ---------------- **/

DI()->logger->info("定时器任务",DI()->request->getAll());

//缓存 - redis
DI()->redis = new Redis_Lite(DI()->config->get('app.aliRedis.servers'));


//缓存 - Memcache/Memcached
DI()->cache = function () {
    return new PhalApi_Cache_Memcache(DI()->config->get('sys.mc'));
};


//签名
//DI()->filter = 'Common_SignFilter';

//加密类
//DI()->mycrypt = new Mycrypt_Lite();

//即使用户把浏览器关掉（断开连接），php也会在服务器上继续执行
//ignore_user_abort(true);

$api = new PhalApi();
$rs = $api->response();
$rs->output();