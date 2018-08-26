<?php

class Domain_GiftPackage {
    private $req;

    public function __construct() {
        $this->req = DI()->request->getAll();
    }

    //先判断是否送过礼包，若没则发送礼包，并记录，增加用户礼包;
    public function sendPackage() {

        $model_gift_package = new Model_GiftPackage();
        $package_info = $model_gift_package->getPackageBySortId($this->req['gift_package_id']);

        if(empty($package_info)) {
            throw new PhalApi_Exception('无此礼包', 313);
        }

        $model_package_log = new Model_GiftPackageLog();
        $rs = $model_package_log->insertUpdateLog($this->req['user_id'], $this->req['gift_package_id']);

        if(!$rs) {
            throw new PhalApi_Exception('已经送过此大礼包', 323);
        }

        $coin_num = $package_info['extra_send_coin'];

        $model_user = new Model_User();
        $update_rs = $model_user->updateUserDiamondCoin($this->req['user_id'], 0, $coin_num);
        if($update_rs) {
            $domain_im = new Domain_IM();
            $msg = '系统赠送您' . $package_info['name'] . '，价值' . $coin_num . '米币';
            $domain_im->sendUserMsg(1, $this->req['user_id'], $msg);
            $user_info = $model_user->getUsersInfoById($this->req['user_id'], 'coin_num');
            $final_info['coin_num'] = $user_info['coin_num'];
            return $final_info;
        } else {
            if($coin_num == 0) {
                $user_info = $model_user->getUsersInfoById($this->req['user_id'], 'coin_num');
                $final_info['coin_num'] = $user_info['coin_num'];
                return $final_info;
            }
            throw new PhalApi_Exception('帐户添加米币失败', 523);
        }
    }
}