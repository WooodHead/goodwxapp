<?php
/**
 * 工具 - 查看接口参数规则
 */

require_once dirname(__FILE__) . '/../init.php';

//装载你的接口
DI()->loader->addDirs('Mibo');


//缓存 - redis
//DI()->redis = new Redis_Lite(DI()->config->get('app.redis.servers'));

$is_debug = DI()->config->get('sys.debug');
if(!$is_debug) {
    $ip_list = DI()->config->get('sys.ip_white_list');
    $ip = PhalApi_Tool::getClientIp();
    if (!in_array($ip, $ip_list)) {
        //exit("非法入侵，已触发报警系统。如果你是开发人员，请联系管理员自首，您当前IP为：" . $ip);
    }
}
$apiDesc = new PhalApi_Helper_ApiDesc();
$apiDesc->render();
