<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/16
 * Time: 18:35
 */
class Domain_Pay {

    private $req;

    public function __construct() {
        $this->req = DI()->request->getAll();
    }

    public function getRechargeItemList() {
        $model = new Model_RechargeItem();
        $item_list = $model->getRechargeItemList();

        if (empty($item_list)) {
            throw new PhalApi_Exception_BadRequest("数据为空", 60);
        }
        return $item_list;
    }

    public function getRechargeCoinItemList() {
        $model = new Model_RechargeItem();
        $item_list = $model->getRechargeCoinItemList();

        if (empty($item_list)) {
            throw new PhalApi_Exception_BadRequest("数据为空", 60);
        }
        return $item_list;
    }

    public function getExchange2CoinItemList() {
        $key = "exchange_2_coin_item_list";
        $list_string = DI()->redis->get_time($key);
        if (empty($list_string)) {
            $item_list = DI()->notorm->exchange_item
                ->select("id, diamond_num, coin_num, type")
                ->where("is_show=1")
                ->where("type=0")
                ->order('sort DESC')
                ->fetchAll();
            DI()->redis->set_time($key, json_encode($item_list), 3600);
        } else {
            $item_list = json_decode($list_string, true);
        }

        if (empty($item_list)) {
            throw new PhalApi_Exception_BadRequest("数据为空", 61);
        }
        return $item_list;

    }

    public function getExchange2DiamondItemList() {
        $key = "exchange_2_diamond_item_list";
        $list_string = DI()->redis->get_time($key);
        if (empty($list_string)) {
            $item_list = DI()->notorm->exchange_item
                ->select("id, diamond_num, coin_num, type")
                ->where("is_show=1")
                ->where("type=1")
                ->order('sort DESC')
                ->fetchAll();
            DI()->redis->set_time($key, json_encode($item_list), 3600);
        } else {
            $item_list = json_decode($list_string, true);
        }

        if (empty($item_list)) {
            throw new PhalApi_Exception_BadRequest("数据为空", 62);
        }
        return $item_list;
    }


    public function diamondExchange2Coin() {

        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) {
            throw new PhalApi_Exception_BadRequest("用户ID不存在", 63);
        }

        $item_info = DI()->notorm->exchange_item->select("*")->where('id=?', $this->req['item_id'])->where('type=0')->fetchOne();
        if (empty($item_info)) {
            throw new PhalApi_Exception_BadRequest("兑换列表ID不存在", 64);
        }

        $diamond_num = $item_info['diamond_num'];
        $coin_num = $item_info['coin_num'];
        if ($user_info['diamond_num'] < $diamond_num) {
            throw new PhalApi_Exception_BadRequest("您的钻石不够，请先充值吧", 65);
        }

        $rs = $user_domain->updateUserDiamondNumReduce($user_info['id'], $diamond_num, false, $coin_num);
        if ($rs) {
            $deal_log_data = array(
                'user_id'     => $user_info['id'],
                'to_user_id'  => 0,
                'mid'         => MOUDEL_USER_DIAMOND_EXCHANGE_COIN,
                'value'       => $coin_num,
                'extra'       => $item_info['id'],
                'deal_type'   => DEAL_TYPE_DIAMOND,
                'deal_num'    => $diamond_num,
                'deal_before' => $user_info['diamond_num'],
                'deal_after'  => $user_info['diamond_num'] - $diamond_num,
            );

            $deal_log_model = new Model_UserDealLog();
            $insert_id = $deal_log_model->insertDealLog($deal_log_data);
            if ($insert_id > 0) {
                return $deal_log_data;
            }
        }

        throw new PhalApi_Exception_BadRequest("兑换失败", 66);
    }

    public function getPayType() {
        $os = $this->req['os'] == 'Android' ? 1 : 2;
        $channel = isset($this->req['channel']) ? $this->req['channel'] : '';
        $version_code = isset($this->req['version_code']) ? $this->req['version_code'] : 0;
        $model_config = new Model_AppConfig();
        return $model_config->getPayType($os, $channel, $version_code);
    }

    public function placeOrder() {
        $type = $this->req['type'];
        if ($type == 1) {    //如果是微信下单
            return $this->wechat_order();
        }

        if ($type == 2) {    //如果是支付宝下单
            return $this->alipay_order();
        }

        if ($type == 3) {
            return $this->ios_order();
        }

    }

    public function wechat_order() {

        if(!isset($this->req['operation_id']) || !isset($this->req['operation_value'])) {
            throw new PhalApi_Exception('参数错误', 403);
        }

        //获取微信支付配置
        $wechat_pay_config = DI()->config->get('app.wechat_pay');
        $config['appid'] = $wechat_pay_config['appid'];
        $config['mch_id'] = $wechat_pay_config['mch_id'];
        $config['api_key'] = $wechat_pay_config['api_key'];
        $config['notify_url'] = $wechat_pay_config['notify_url'];
        $WxPay = new ThirdPay_Wechat($config);

        $money = $this->getRechargeItemPrice($this->req['operation_id'], $this->req['operation_value']);

        //订单相关信息
        $body = '米播充值，微信支付';
        $rand_str = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

        //如果是完成原来的订单，则用原来的订单号
        if (isset($this->req['order_no']) && !empty($this->req['order_no'])) {
            $pre_order = DI()->notorm->recharge_log->where('order_no', $this->req['order_no'])->fetchOne();
            $pre_pay_no = isset($pre_order['order_no']) ? $pre_order['order_no'] : '';
        }
        $pay_no = isset($pre_pay_no) && !empty($pre_pay_no) ? $pre_pay_no : 'mibo' . time() . '_' . substr($rand_str, 0, 5);
        //$money = $this->req['money'];

        //微信统一下单接口，获取预支付订单号
        $pay_res = $WxPay->getPrePayOrder($body, $pay_no, $money);
        if (isset($pay_res['return_code']) && $pay_res['return_code'] == 'SUCCESS') {
            //订单写入数据库
            $order['user_id'] = $this->req['user_id'];
            $order['type'] = $this->req['type'];
            $order['money'] = $money;
            $order['order_no'] = $pay_no;
            $order['operation_id'] = $this->req['operation_id'];
            $order['operation_value'] = $this->req['operation_value'];
            $order['channel'] = isset($this->req['channel']) ? $this->req['channel'] : '';
            if (!isset($pre_pay_no)) {
                DI()->notorm->recharge_log->insert($order);
            }
            $signData = array(
                'appid'     => $wechat_pay_config['appid'],
                'partnerid' => $wechat_pay_config['mch_id'],
                'prepayid'  => $pay_res['prepay_id'],
                'package'   => 'Sign=WXPay',
                'noncestr'  => $pay_res['noncestr'],
                'timestamp' => strval(time()),
            );
            ksort($signData);
            $sign = $WxPay->getSign($signData);

            $signData['sign'] = $sign;
            $signData['type'] = $this->req['type'];
            $signData['order_no'] = $pay_no;
            $signData['subject'] = '米播充值';
            $signData['body'] = $body;
            $signData['total_fee'] = $money;
            $signData['id'] = $this->req['id'];     //米播充值钻石价格列表id
            return $signData;
        }

        throw new PhalApi_Exception_BadRequest('统一下单失败', 01);
    }

    public function getRechargeItemPrice($operation_id, $operation_value) {
        $recharge_item = [];
        if($operation_id == 2) {    //礼包
            $model_gift_package = new Model_GiftPackage();
            $recharge_item = $model_gift_package->getPackageBySortId($operation_value);
        }
        if($operation_id == 1) {
            $model = new Model_RechargeItem();
            $recharge_item =  $model->getItemById($operation_value);
        }

        return isset($recharge_item['price']) ? $recharge_item['price'] : 0;
    }

    public function alipay_order() {

        if(!isset($this->req['operation_id']) || !isset($this->req['operation_value'])) {
            throw new PhalApi_Exception('参数错误', 403);
        }

        if (isset($this->req['order_no']) && !empty($this->req['order_no'])) {
            $pre_order = DI()->notorm->recharge_log->where('order_no', $this->req['order_no'])->fetchOne();
            $pre_pay_no = isset($pre_order['order_no']) ? $pre_order['order_no'] : '';
        }
        $rand_str = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $pay_no = isset($pre_pay_no) && !empty($pre_pay_no) ? $pre_pay_no : 'mibo' . time() . '_' . substr($rand_str, 0, 5);

        $money = $this->getRechargeItemPrice($this->req['operation_id'], $this->req['operation_value']);

        //获取支付宝配置信息
        $res = DI()->config->get('app.alipay');
        $res['type'] = $this->req['type'];
        $res['order_no'] = $pay_no;
        $res['subject'] = '米播充值';
        $res['body'] = '米播充值，支付宝支付';
        $res['total_fee'] = $money;

        //订单写入数据库
        $order['user_id'] = $this->req['user_id'];
        $order['type'] = $this->req['type'];
        $order['money'] = $money;
        $order['order_no'] = $pay_no;
        $order['operation_id'] = $this->req['operation_id'];
        $order['operation_value'] = $this->req['operation_value'];
        $order['channel'] = isset($this->req['channel']) ? $this->req['channel'] : '';
        if (!isset($pre_pay_no)) {
            DI()->notorm->recharge_log->insert($order);
        }

        $res['operation_id'] = $this->req['operation_id'];
        $res['operation_value'] = $this->req['operation_value'];
        return $res;
    }

    public function ios_order() {
        if (!isset($this->req['order_no']) && !empty($this->req['order_no'])) {
            $pre_order = DI()->notorm->recharge_log->where('order_no', $this->req['order_no'])->fetchOne();
            $pre_pay_no = isset($pre_order['order_no']) ? $pre_order['order_no'] : '';
        }
        $rand_str = str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $pay_no = isset($pre_pay_no) && !empty($pre_pay_no) ? $pre_pay_no : 'mibo' . time() . '_' . substr($rand_str, 0, 5);

        ////获取支付宝配置信息
        //$res = DI()->config->get('app.alipay');
        $res['type'] = $this->req['type'];
        $res['order_no'] = $pay_no;
        $res['subject'] = '米播充值';
        $res['body'] = '米播充值，支付宝支付';


        //订单写入数据库
        $order['user_id'] = $this->req['user_id'];
        $order['type'] = $this->req['type'];

        $order['order_no'] = $pay_no;
        $order['operation_id'] = isset($this->req['operation_id']) ? $this->req['operation_id'] : 1;
        $order['operation_value'] = isset($this->req['operation_value']) ? $this->req['operation_value'] : 1;

        $money = $this->getRechargeItemPrice($this->req['operation_id'], $this->req['operation_value']);

        $order['channel'] = isset($this->req['channel']) ? $this->req['channel'] : '';
        $order['money'] = $money;
        $res['total_fee'] = $money;

        if (!isset($pre_pay_no)) {
            DI()->notorm->recharge_log->insert($order);
        }

        $res['operation_id'] = isset($this->req['operation_id']) ? $this->req['operation_id'] : 1;
        $res['operation_value'] = isset($this->req['operation_value']) ? $this->req['operation_value'] : 1;
        return $res;
    }

    public function verifyApplePay() {
        //用户发来的参数
        $apple_order_no = isset($this->req['$apple_order_no']) ? $this->req['$apple_order_no'] : '';
        $receipt_data = isset($this->req['receipt_data']) ? $this->req['receipt_data'] : '';
        $order_no = isset($this->req['order_no']) ? $this->req['order_no'] : '';

        //验证参数
        if (strlen($receipt_data) < 20 || empty($order_no)) {
            throw new PhalApi_Exception('非法参数', 411);
        }

        //$had_update = DI()->redis->get_time($apple_order_no);
        //if($had_update) throw new PhalApi_Exception('此订单已经完成', 412);

        $had_verified = DI()->notorm->recharge_ios->where('receipt_data_md5', md5($receipt_data))->fetchOne();
        if(!empty($had_verified)) throw new PhalApi_Exception('此订单已经支付,有问题联系客服！', 412);

        $insert_data = [
            'order_no' => $order_no,
            'receipt_data_md5'  => md5($receipt_data),
            'receipt_data' => $receipt_data,
        ];

        DI()->notorm->recharge_ios->insert($insert_data);
        $insert_id = DI()->notorm->recharge_ios->insert_id();
        DI()->logger->info('ios_pay_receipt_data', $receipt_data);

        //请求验证
        $html = $this->curlApplePay($receipt_data);
        $data = json_decode($html,1);


        //如果是沙盒数据 则验证沙盒模式
        $is_sand_box = isset($this->req['is_sand_box']) ? (int)$this->req['is_sand_box'] : 0;
        if($data['status']=='21007' && $is_sand_box){
            //请求验证
            $html = $this->curlApplePay($receipt_data, $sandbox=1);
            DI()->logger->debug('test-apple-pay---3', $html);
            $data = json_decode($html,1);
            $data['sandbox'] = '1';
        }

        if($data['status']==0){
            $echo = array('buy'=>'1','message'=>'购买成功');
        }else{
            $echo = array('buy'=>'0','message'=>'购买失败','status'=>$data['status']);
            throw new PhalApi_Exception('购买失败', $data['status']);
        }

        //是否是沙盒模式
        if ($data['sandbox']) $echo['sandbox'] = '1';

        $this->afterApplePay();

        //DI()->redis->set_time($apple_order_no, 1, 86400);

        return true;

    }

    /**
     * 21000 App Store不能读取你提供的JSON对象
     * 21002 receipt-data域的数据有问题
     * 21003 receipt无法通过验证
     * 21004 提供的shared secret不匹配你账号中的shared secret
     * 21005 receipt服务器当前不可用
     * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
     * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
     * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
     */
    public function curlApplePay($receipt_data, $sandbox=0){

        //小票信息
        $POSTFIELDS = array("receipt-data" => $receipt_data);
        $POSTFIELDS = json_encode($POSTFIELDS);

        //正式购买地址 沙盒购买地址
        $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $url = $sandbox ? $url_sandbox : $url_buy;

        //简单的curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function afterApplePay() {
        $order_no = isset($this->req['order_no']) ? $this->req['order_no'] : '';
        if(!$order_no) throw new PhalApi_Exception('没有订单号!');

        $pre_order = DI()->notorm->recharge_log->where('order_no = ?', $order_no)->fetchOne();
        if(empty($pre_order)) throw new PhalApi_Exception('订单不存在', 482);

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
            $domain_im->sendUserMsg(1, $pre_order['user_id'], '充值成功，若帐户米钻数量、米币数量不合，联系客户微信：mibokf', $extra);

            //用户获取到的米币、米钻等数量
            $user_get_coin_num = 0;
            $user_get_coin_extra_num = 0;
            $user_get_diamond_num = 0;
            $extra_string = "";

            if ($pre_order['operation_id'] == 1) {
                $op_info = $price_item;
                $user_get_coin_num = $op_info['diamond_num'];
                $user_get_coin_extra_num = $op_info['extra_num'];
                $user_get_diamond_num = $op_info['coin_num'];
                $extra_string = '普通充值';
            } else if ($pre_order['operation_id'] == 2) {
                $op_info = $price_item;
                $user_get_coin_num = $op_info['coin_num'];
                $user_get_coin_extra_num = $op_info['extra_send_coin'];
                $user_get_diamond_num = $op_info['diamond_num'];
                $extra_string = $op_info['name'];
            }

            $add_arr = array('cheng@dianhengad.com', '362226577@qq.com', '313256513@qq.com');
            //$add_arr = array('362226577@qq.com', '313256513@qq.com');

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
                        <td align="left" class="td_right">苹果支付</td>
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

    //用户通过微信下单后，更新用户的钻石，米币
    public function updateDiamond() {
        if(!isset($this->req['os']) ||$this->req['os'] != 'iOS') return true;

        //一、取得订单信息
        $order = DI()->notorm->recharge_log->where('user_id = ?', $this->req['user_id'])
            ->where('order_no = ?', $this->req['order_no'])->fetchOne();
        if (!isset($order) || empty($order)) {
            throw new PhalApi_Exception('订单不存在', 318);
        }

        if ($order['is_update_diamond'] == 1) {
            throw new PhalApi_Exception('该订单已经发放钻石', 319);
        }

        //二、更新已经发放钻石字段
        $update_data = array('is_update_diamond' => 1);
        DI()->notorm->recharge_log->where('user_id = ?', $this->req['user_id'])
            ->where('order_no = ?', $this->req['order_no'])->update($update_data);

        //三、如果是礼包的话，还要把发放礼包记录写入数据库,并更新用户米币信息
        if ($order['operation_id'] == 2) {
            //三、1、写入获取礼包记录
            $model_gift_package_log = new Model_GiftPackageLog();
            $model_gift_package_log->insertUpdateLog($this->req['user_id'], $order['operation_value']);

            //三、2、根据礼包id取出优惠价格能买到的钻石
            $model_gift_package = new Model_GiftPackage();
            $price_item = $model_gift_package->getPackageBySortId($order['operation_value']);
            $model_user = new Model_User();
            $rs = $model_user->updateUserDiamondCoin($this->req['user_id'], $price_item['diamond_num'], $price_item['coin_num']);

        } else {
            //三、1、更新充值的钻石
            $model = new Model_RechargeItem();
            $price_item = $model->getItemById($order['operation_value']);
            $model_user = new Model_User();
            $rs = $model_user->updateUserDiamondCoin($this->req['user_id'], $price_item['diamond_num'], $price_item['extra_num']);
        }

        if (!$rs) {
            throw new PhalApi_Exception('钻石充值失败，联系管理员', 515);
        }

        $user_info = $model_user->getUsersInfoById($this->req['user_id']);

        DI()->mail = new PHPMailer_Lite();

        //用户获取到的米币、米钻等数量，下面的代码都是大龙写的
        $user_get_coin_num = 0;
        $user_get_coin_extra_num = 0;
        $user_get_diamond_num = 0;
        $extra_string = "";

        if ($order['operation_id'] == 1) {
            $Model_RechargeItem = new Model_RechargeItem();
            $op_info = $Model_RechargeItem->getItemById($order['operation_value']);
            $user_get_coin_num = 0;
            $user_get_coin_extra_num = $op_info['extra_num'];
            $user_get_diamond_num = $op_info['diamond_num'];
        } else if ($order['operation_id'] == 2) {
            $Model_GiftPackage = new Model_GiftPackage();
            $op_info = $Model_GiftPackage->getPackageBySortId($order['operation_value']);
            $user_get_coin_num = $op_info['coin_num'];
            $user_get_coin_extra_num = $op_info['extra_send_coin'];
            $user_get_diamond_num = $op_info['diamond_num'];
            $extra_string = $op_info['name'];
        }

        $add_arr = array('cheng@dianhengad.com', '362226577@qq.com', '313256513@qq.com');

        //$mailBody = '<div><b><span large;\"=""><font color="#808080">&nbsp; &nbsp; 好消息！</font></span></b></div><div><b><font color="#808080">&nbsp; &nbsp; 玩家 </font><font color="#99cc00">'.$user_info['nick_name'].' </font><font color="#808080">&nbsp;在 &nbsp;</font><font color="#99cc00">'.$order['create_time'].' </font><font color="#808080">&nbsp;通过 &nbsp;</font><font color="#99cc00">'.($order['type'] == 1?"微信":"支付宝").' &nbsp;</font><font color="#808080">充值了 </font><font color="#99cc00">&nbsp;'.$order['money']*0.01.'</font><font color="#808080"> 元，</font><font color="#99cc00">'.$extra_string.'</font><font color="#808080">获得了 </font><font color="#99cc00">'.$user_get_diamond_num.'</font><font color="#808080">&nbsp;米钻，获得 </font><font color="#99cc00">'.$user_get_coin_num.'</font><font color="#808080"> 米币，获得赠送&nbsp;</font><font color="#99cc00">'.$user_get_coin_extra_num.'</font><font color="#808080"> 米币</font></b><b style="color: rgb(128, 128, 128);">。</b></div><div><font color="#808080"><b>&nbsp; &nbsp; 加油！</b><b>加油！</b><b>加油！</b></font></div><div style="\&quot;text-align:" left;\"=""><font size="\&quot;4\&quot;" color="#808080"><b>&nbsp; &nbsp; 顺祝：新年快乐！</b></font></div>';
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
                        <td align="left" class="td_right">'.$order['create_time'].'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">支付方式：</td>
                        <td align="left" class="td_right">'.($order['type'] == 1?"微信":"支付宝").'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">充值项目：</td>
                        <td align="left" class="td_right">'.$extra_string.'</td>
                    </tr>
                    <tr>
                        <td align="right" class="td_left">充值金额：</td>
                        <td align="left" class="td_right">'.$order['money']*0.01.'元</td>
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
        DI()->mail->send($add_arr, '【米播】充值成功订单通知-' . $order['money'] * 0.01 . "元", $mailBody);

        return $user_info;
    }

    public function updateCoin() {
        if(!isset($this->req['os']) || $this->req['os'] != 'iOS') return true;

        //一、取得订单信息
        $order = DI()->notorm->recharge_log->where('user_id = ?', $this->req['user_id'])
            ->where('order_no = ?', $this->req['order_no'])->fetchOne();
        if (!isset($order) || empty($order)) {
            throw new PhalApi_Exception('订单不存在', 318);
        }

        if ($order['is_update_diamond'] == 1) {
            throw new PhalApi_Exception('该订单已经发放米币', 319);
        }

        //二、1、更新充值的米币
        $model = new Model_RechargeItem();
        $price_item = $model->getItemById($order['operation_value']);
        $model_user = new Model_User();
        $vip_level_arr = DI()->notorm->user->select('vip_level')->where('id', $this->req['user_id'])->fetchOne();
        $vip_level = $vip_level_arr['vip_level'];
        //如果是vip额外赠送的再乘于1.5倍
        if ($vip_level > 0) $price_item['extra_num'] *= 1.5;

        $rs = $model_user->updateUserDiamondCoin(
            $this->req['user_id'], $price_item['diamond_num'], $price_item['extra_num'] + $price_item['coin_num']);

        //DI()->logger->debug('测试充米币',$price_item['id'].'---'.$price_item['diamond_num'].'----'.$price_item['coin_num'].'-----'.$price_item['extra_num']);
        //三、更新已经发放米币字段
        $update_data = array('is_update_diamond' => 1);
        DI()->notorm->recharge_log->where('user_id = ?', $this->req['user_id'])
            ->where('order_no = ?', $this->req['order_no'])->update($update_data);

        if (!$rs) {
            throw new PhalApi_Exception('钻石充值失败，联系管理员', 515);
        }

        $user_info = $model_user->getUsersInfoById($this->req['user_id']);

        DI()->mail = new PHPMailer_Lite();

        //用户获取到的米币、米钻等数量，下面的代码都是大龙写的
        $extra_string = "";

        $Model_RechargeItem = new Model_RechargeItem();
        $op_info = $Model_RechargeItem->getItemById($order['operation_value']);
        $user_get_coin_num = $op_info['coin_num'];
        $user_get_coin_extra_num = $op_info['extra_num'];
        $user_get_diamond_num = $op_info['diamond_num'];


        $add_arr = array('cheng@dianhengad.com', '362226577@qq.com', '313256513@qq.com');

        $mailBody = '<div><b><span large;\"=""><font color="#808080">&nbsp; &nbsp; 好消息！</font></span></b></div><div><b><font color="#808080">&nbsp; &nbsp; 玩家 </font><font color="#99cc00">' . $user_info['nick_name'] . ' </font><font color="#808080">&nbsp;在 &nbsp;</font><font color="#99cc00">' . $order['create_time'] . ' </font><font color="#808080">&nbsp;通过 &nbsp;</font><font color="#99cc00">' . ($order['type'] == 1 ? "微信" : "支付宝") . ' &nbsp;</font><font color="#808080">充值了 </font><font color="#99cc00">&nbsp;' . $order['money'] * 0.01 . '</font><font color="#808080"> 元，</font><font color="#99cc00">' . $extra_string . '</font><font color="#808080">获得了 </font><font color="#99cc00">' . $user_get_diamond_num . '</font><font color="#808080">&nbsp;米钻，获得 </font><font color="#99cc00">' . $user_get_coin_num . '</font><font color="#808080"> 米币，获得赠送&nbsp;</font><font color="#99cc00">' . $user_get_coin_extra_num . '</font><font color="#808080"> 米币</font></b><b style="color: rgb(128, 128, 128);">。</b></div><div><font color="#808080"><b>&nbsp; &nbsp; 加油！</b><b>加油！</b><b>加油！</b></font></div><div style="\&quot;text-align:" left;\"=""><font size="\&quot;4\&quot;" color="#808080"><b>&nbsp; &nbsp; 顺祝：新年快乐！</b></font></div>';

        // $mailBody = '<div><b><font color=\"#808080\"><font size=\"4\">您好，</font><span style=\"font-size: large;\">现在有好消息通知您！</span></font></b></div><div><font size=\"4\"><b><font color=\"#808080\">&nbsp; &nbsp; 玩家&nbsp;</font><font color=\"#99cc00\">'.$user_info['nick_name'].'</font><font color=\"#808080\"> 在 </font><font color=\"#99cc00\">'.$order['create_time'].'</font><font color=\"#808080\"> 通过 '.($order['type'] == 1?"微信":"支付宝").' 充值了 </font><font color=\"#99cc00\">'.$order['money']*0.01.'&nbsp;</font><font color=\"#808080\">元，加油！</font></b></font></div><div style=\"text-align: left;\"><font size=\"4\" color=\"#808080\"><b>新年快乐！</b></font></div>';
        DI()->mail->send($add_arr, '【米播】充值成功订单通知-' . $order['money'] * 0.01 . "元", $mailBody);

        return $user_info;
    }
}