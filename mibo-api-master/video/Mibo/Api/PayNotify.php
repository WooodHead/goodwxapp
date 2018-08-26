<?php
/*
 * +----------------------------------------------------------------------
 * | 支付异步/同步回调
 * +----------------------------------------------------------------------
 * | Copyright (c) 2015 summer All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: summer <aer_c@qq.com> <qq7579476>
 * +----------------------------------------------------------------------
 * | This is not a free software, unauthorized no use and dissemination.
 * +----------------------------------------------------------------------
 * | Date
 * +----------------------------------------------------------------------
 */

class Api_PayNotify extends PhalApi_Api {

    public function getRules() {
        return array(
            'index' => array(
                'type' 	=> array('name' => 'type', 'type' =>'string', 'require' => true, 'desc' => '支付类型，比如alipay,wechat'),
                'method'    => array('name' => 'method', 'type' =>'string', 'desc' => '回调类型，notify异步/return同步'),
            ),
        );
    }

    /**
     * 支付异步/同步回调
     * @desc
     * @return string 无 根据不同的引擎，返回不同的信息，如果错误信息，则存入日志
     */
    public function index() {
        DI()->pay = new Notify_Lite();

        //一、组装第三方支付配置信息
        if($this->type == 'wechat') {
            $notify_url = 'https://mibo.yahalei.com/mibo/callback/wechat_pay_notify.php';
            $wechat_config = DI()->config->get('app.wechat_pay');
            $config['appid'] = $wechat_config['appid'];
            $config['mchid'] = $wechat_config['mch_id'];
            $config['key'] = $wechat_config['api_key'];
            $pay_type = 1;

        } elseif($this->type == 'alipay') {
            $notify_url = 'https://mibo.yahalei.com/mibo/callback/alipay_notify.php';
            $alipay_config = DI()->config->get('app.alipay');

            $config['partner'] = $alipay_config['partner_id'];
            $config['key'] = $alipay_config['rsa_private'];
            $config['email'] = $alipay_config['seller_id'];
            $pay_type = 2;

        } else {
            throw new PhalApi_Exception('请求参数错误', 500);
        }

        //二、获取对应的支付引擎
        DI()->pay->set($this->type, $notify_url, $config);

        //三、获取第三方回调信息
        $notify = $GLOBALS['PAY_NOTIFY'];
        if(!$notify) {
            DI()->logger->log('pay-error','Not data commit', array('Type' => $this->type));
            exit; //直接结束程序，不抛出错误
        }

        //四验证
        if(DI()->pay->verifyNotify($notify) == true){
            //四、1、获取订单信息
            $info = DI()->pay->getInfo();
            $info['money'] *= 100;

            $pre_order = DI()->notorm->recharge_log->where('order_no = ?', $info['out_trade_no'])->fetchOne();
            if(empty($pre_order)) {
                $insert_order = array('order_no' => $info['out_trade_no'], 'type' => $pay_type, 'money' => $info['money'], 'is_notify' => 1);
                DI()->notorm->recharge_log->insert($insert_order);
            }


            if($pre_order['money'] != $info['money']) {
                DI()->logger->info('pay-place-order-下单金额与第三方通知的不一致', '下单金额：'.$pre_order['money'].'，通知金额：'.$info['money']);
            }

            //四、4、更新用户vip等级
            if(!empty($pre_order) && !empty($pre_order['user_id'] && $pre_order['money'] == $info['money'])) {
            //if(!empty($pre_order) && !empty($pre_order['user_id'])) {
                $recharge_amount = DI()->redis->get_time('recharge_amount_user_id_'.$pre_order['user_id']);
                if(!$recharge_amount) {
                    $recharge_amount = DI()->r_notorm->recharge_log->where('user_id',$pre_order['user_id'])->where('is_notify = 1')
                        ->sum('money');
                    $recharge_amount = $recharge_amount ? $recharge_amount : 0;
                    $recharge_amount += $info['money'];
                    DI()->redis->set_time('recharge_amount_user_id_'.$pre_order['user_id'], $recharge_amount, 3600 * 5);
                } else {
                    $recharge_amount = $recharge_amount + $pre_order['money'];
                    DI()->redis->set_time('recharge_amount_user_id_'.$pre_order['user_id'], $recharge_amount, 3600 * 5);
                }


                $recharge_amount = $recharge_amount / 100;
                $vip_level = 0;
                if($recharge_amount >= 29) {
                    switch($recharge_amount) {
                        case $recharge_amount >= 29 && $recharge_amount < 200:
                            $vip_level = 1;
                            break;
                        case $recharge_amount >= 200 && $recharge_amount < 500:
                            $vip_level = 2;
                            break;
                        case $recharge_amount >= 500 && $recharge_amount < 1000:
                            $vip_level = 3;
                            break;
                        case $recharge_amount >= 1000 && $recharge_amount < 2000:
                            $vip_level = 4;
                            break;
                        case $recharge_amount >= 2000 && $recharge_amount < 5000:
                            $vip_level = 5;
                            break;
                        case $recharge_amount >= 5000 && $recharge_amount < 10000:
                            $vip_level = 6;
                            break;
                        case $recharge_amount >= 10000 && $recharge_amount < 20000:
                            $vip_level = 7;
                            break;
                        case $recharge_amount >= 20000 && $recharge_amount < 50000:
                            $vip_level = 8;
                            break;
                        case $recharge_amount >= 50000 && $recharge_amount < 100000:
                            $vip_level = 9;
                            break;
                        case $recharge_amount >= 100000 && $recharge_amount < 200000:
                            $vip_level = 10;
                            break;
                        case $recharge_amount >= 200000 && $recharge_amount < 500000:
                            $vip_level = 11;
                            break;
                        case $recharge_amount >= 500000:
                            $vip_level = 12;
                            break;
                        default:
                            $vip_level = 0;
                            break;
                    }
                }

                $pre_vip_level = DI()->notorm->user->where('id', $pre_order['user_id'])->fetchOne('vip_level');
                if($pre_vip_level < $vip_level) {
                    DI()->notorm->user->where('id', $pre_order['user_id'])->update(array('vip_level' => $vip_level));
                }

                //更新用户钻石
                if($pre_order['is_update_diamond'] != 1) {
                    $this->updateUserInfo($pre_order);
                }

            }

            //订单更新成功
            if ($this->method == "return") {
                //TODO 同步回调需要跳转的页面
            } else {

                DI()->logger->log('pay-success', 'Pay Success',array('Type' => $this->type, 'Method' => $this->method, 'Data'=> $info));

                //移除超全局变量
                unset($GLOBALS['PAY_NOTIFY']);
                //支付接口需要返回的信息，通知接口我们已经接收到了支付成功的状态
                DI()->pay->notifySuccess();

                exit; //需要结束程序
            }

        }else{
            DI()->pay->notifyError();
            DI()->logger->log('pay-error','Verify error', array('Type' => $this->type, 'Method'=> $this->method, 'Data' => $notify));
            exit;
        }
    }

    public function updateUserInfo($pre_order) {
        $model = new Model_RechargeItem();

        $price_item = [];
        if($pre_order['operation_id'] == 2) {
            $model_gift_package = new Model_GiftPackage();
            $price_item = $model_gift_package->getPackageBySortId($pre_order['operation_value']);
        }
        if($pre_order['operation_id'] == 1) {
            $price_item =  $model->getItemById($pre_order['operation_value']);
        }

        if(!empty($price_item)) {

            $model_user = new Model_User();

            $update_rs = false;
            //三、如果是礼包的话，还要把发放礼包记录写入数据库,并更新用户米币信息
            if ($pre_order['operation_id'] == 2) {

                //三、1、根据礼包id取出优惠价格能买到的钻石
                $model_gift_package = new Model_GiftPackage();
                $price_item = $model_gift_package->getPackageBySortId($pre_order['operation_value']);
                $update_rs = $model_user->updateUserDiamondCoin($pre_order['user_id'], $price_item['diamond_num'], $price_item['coin_num']);

                //三、2、写入获取礼包记录
                $model_gift_package_log = new Model_GiftPackageLog();
                $model_gift_package_log->insertUpdateLog($pre_order['user_id'], $pre_order['operation_value']);

            } else {

                //三、1、更新充值的钻石
                $model = new Model_RechargeItem();
                $price_item = $model->getItemById($pre_order['operation_value']);

                if($price_item['type'] == 0) {
                    $update_rs = $this->rechargeDiamond($pre_order, $price_item);
                }

                if($price_item['type'] == 1) {
                    $update_rs = $this->rechargeCoin($pre_order, $price_item);
                }

            }

            if($update_rs) {
                $update_data = array('is_update_diamond' => 1, 'is_notify' => 1);
                DI()->notorm->recharge_log->where('user_id = ?', $pre_order['user_id'])
                    ->where('order_no = ?', $pre_order['order_no'])->update($update_data);
            } else {
                DI()->logger->info('pay-update-fail', $update_rs);
            }

            $user_info = DI()->notorm->user->where('id', $pre_order['user_id'])->fetchOne();

            $domain_im = new Domain_IM();
            $extra = ['user_info' => $user_info];
            $domain_im->sendUserMsg(1, $pre_order['user_id'], '充值成功，若帐户米钻数量、米币数量不对，联系客服微信：mibokf', $extra);

            //用户获取到的米币、米钻等数量
            $user_get_coin_num = 0;
            $user_get_coin_extra_num = 0;
            $user_get_diamond_num = 0;
            $extra_string = "";

            if ($pre_order['operation_id'] == 1) {
                $op_info = $price_item;
                $user_get_coin_num = $op_info['coin_num'];
                $user_get_coin_extra_num = $op_info['extra_num'];
                $user_get_diamond_num = $op_info['diamond_num'];

                $extra_string = $op_info['diamond_num'] > 0 ? '钻石充值' : '米币充值';
            } else if ($pre_order['operation_id'] == 2) {
                $op_info = $price_item;
                $user_get_coin_num = $op_info['coin_num'];
                $user_get_coin_extra_num = $op_info['extra_send_coin'];
                $user_get_diamond_num = $op_info['diamond_num'];
                $extra_string = $op_info['name'];
            }

            $add_arr = array('cheng@dianhengad.com', '362226577@qq.com', 'info2501@163.com','313256513@qq.com');
            //$add_arr = array('313256513@qq.com');

            $mailBody = '<div><br></div>
<div>
    <includetail>
        <style type="text/css">
            body {
                margin: 0 auto;
                padding: 0;
                font-family: \'Microsoft YaHei\', Tahoma, Arial;
                color: #333333;
                background-color: #fff;
                font-size: 12px;
            }

            a {
                color: #00a2ca;
                line-height: 22px;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
                color: #00a2ca;
            }

            td {
                font-family: \'Microsoft YaHei\';
            }

            .td_left {
                width:200px;
                height:26px;
            }
            .td_right {
                width:560px;
                height:26px;
                padding-left:15px;
                font-weight:bold;
                color:#99cc00;
            }
        </style>

        <table width="800" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ffffff"
               style="font-family:\'Microsoft YaHei\';">
        <tbody>
        <tr>
            <td>
                <table width="800" border="0" align="center" cellpadding="0" cellspacing="0" height="40"></table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="800" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#373d41"
                       height="48">
                    <tbody>
                    <tr>
                        <td width="200" height="48" border="0" align="left" valign="middle" style="padding-left:20px;">
                            <a href="http://api.mibolive.com/apps/mibo/download.php" target="_blank" align="center">
                                <img src="http://image.cdn.mibolive.com/img/webimg/28.png" width="28" height="28"
                                     border="0" align="center">
                                <span width="70" height="48" border="0" style="line-height: 48px; font-weight:bold;color:#fff;"> 米播直播</span>
                                <span width="70" height="48" border="0" style="line-height: 48px; font-size:10px; "> ----赚他一个亿</span>
                            </a>
                        </td>
                        <td width="500" height="48" colspan="2" align="right" valign="middle"
                            style="color:#ffffff; padding-right:20px;">
                            &nbsp;
                            <a href="http://www.mibolive.com" target="_blank"
                               style="color:#ffffff;text-decoration:none;">官网</a>
                            &nbsp;&nbsp; <span style="color:#6c7479;">|</span>&nbsp;&nbsp;
                            <a href="http://api.mibolive.com/apps/mibo/download.php" target="_blank"
                               style="color:#ffffff;text-decoration:none;">下载地址</a>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </td>
        </tr>
        <tr>
            <td>

                <table width="800" border="0" align="left" cellpadding="0" cellspacing="0"
                       style=" border:1px solid #edecec; border-top:none; border-bottom:none; padding:0 20px;font-size:14px;color:#333333; background-color:rgb(240,240,240)">

                    <tbody>
                    <tr>
                        <td width="760" height="56" border="0" align="left" colspan="2"
                            style=" font-size:16px;vertical-align:bottom;">尊敬的
                        米播团队<a></a>：
                        </td>
                    </tr>
                    <tr>
                        <td width="760" height="30" border="0" align="left" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">玩家昵称：</td>
                        <td align="left" class="td_right">'.$user_info['nick_name'].'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">订单时间：</td>
                        <td align="left" class="td_right">'.$pre_order['create_time'].'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">支付方式：</td>
                        <td align="left" class="td_right">'.($pre_order['type'] == 1?"微信":"支付宝").'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">充值项目：</td>
                        <td align="left" class="td_right">'.$extra_string.'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">充值金额：</td>
                        <td align="left" class="td_right">'.$pre_order['money']*0.01.'元</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">获得米钻：</td>
                        <td align="left" class="td_right">'.$user_get_diamond_num.'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">获得米币：</td>
                        <td align="left" class="td_right">'.$user_get_coin_num.'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">赠送米币：</td>
                        <td align="left" class="td_right">'.$user_get_coin_extra_num.'</td>
                    </tr>
                    <tr>
                        <td width="720" height="56" colspan="2" style="padding-left:40px;">加油！加油！加油！</td>
                    </tr>
                    <tr>
                        <td width="720" height="32" colspan="2"
                            style="padding-left:40px;"> 如若系统测试给您带来不便，深表歉意！
                        </td>
                    </tr>
                    <tr>
                        <td width="720" height="32" colspan="2" style="padding-left:40px;">&nbsp;</td>
                    </tr>


                    <tr>
                        <td width="720" height="14" colspan="2"
                            style="padding-bottom:16px; border-bottom:1px dashed #e5e5e5;";">
                        深圳和聚网络科技有限公司
                        </td>
                    </tr>
                    <tr>
                        <td width="720" height="14" colspan="2"
                            style="padding:8px 0 28px;color:#999999; font-size:12px;";">
                        此为系统邮件请勿回复
                        </td>
                    </tr>
                    </tbody>
                </table>

            </td>
        </tr>

        <tr>
            <td width="800" height="100" align="center" valign="middle">
                <img border="0" height="100"
                     src="http://image.cdn.mibolive.com/img/webimg/email_ewm.jpg">
            </td>
        </tr>
        </tbody>
        </table>
    </includetail>
</div>';
            $mailer = new PHPMailer_Lite();
            $mailer->send($add_arr, '【米播】充值成功订单通知-' . $pre_order['money'] * 0.01 . "元", $mailBody);


        }
    }

    public function rechargeDiamond($pre_order, $price_item) {

        $model_user = new Model_User();
        $update_rs = $model_user->updateUserDiamondCoin($pre_order['user_id'], $price_item['diamond_num'], $price_item['extra_num']);
        return $update_rs;
    }

    public function rechargeCoin($pre_order, $price_item) {

        $model_user = new Model_User();
        $vip_level_arr = DI()->notorm->user->select('vip_level')->where('id', $pre_order['user_id'])->fetchOne();
        $vip_level = $vip_level_arr['vip_level'];
        //如果是vip额外赠送的再乘于1.5倍
        if ($vip_level > 0) $price_item['extra_num'] *= 1.5;

        $update_rs = $model_user->updateUserDiamondCoin(
            $pre_order['user_id'], $price_item['diamond_num'], $price_item['extra_num'] + $price_item['coin_num']);
        return $update_rs;

    }

}








