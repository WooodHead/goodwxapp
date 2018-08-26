<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/16
 * Time: 18:35
 */
class Domain_Share extends Domain_Common{

    protected $logo_url = "https://mmbiz.qlogo.cn/mmbiz_png/5A0v82JgqJZdrMYqNe9C1yDBtE8Gt1f4XSpDh1c8ic0tBEZBicEzWgLndLtATZMo0icWYibNdwShicfNJ9zG9Eiak6BQ/0?wx_fmt=png";

    public function __construct() {
        parent::__construct();
    }

    public function shareLive() {
        $live_domain = new Domain_Live();
        $live_info = $live_domain->checkLive();

        $user_domain = new Domain_User();
        $user_info = $user_domain->checkUser();

        $anchor_info = $user_domain->checkUser($live_info['anchor_id']);

        //$share_url = "http://web.mibolive.com/wap/share/live.php?live_id=" . $live_info['id'] .
        //    "&anchor_id=" . $live_info['anchor_id'] . "&user_id=" . $user_info['id'] .
        //    "&channel=" . $this->req['channel'] . '&type=' . $this->req['share_type'];
        $share_url = "http://os.mibolive.com/index.php?g=Web&m=Share&a=live&live_id=" . $live_info['id'] .
            "&anchor_id=" . $live_info['anchor_id'] . "&user_id=" . $user_info['id'] .
            "&channel=" . $this->req['channel'] . '&type=' . $this->req['share_type'];

        if(isset($live_info['location']) && !empty($live_info['location']) && $live_info['location'] != '火星') {
            $title = "【".$anchor_info['nick_name']."】正在" . $live_info['location'] . "直播";
            $desc = "我在看主播【".$anchor_info['nick_name']."】正在" . $live_info['location'] . "直播，快来围观！";
        } else {
            $title = "【".$anchor_info['nick_name']."】正在直播";
            $desc = "我在看主播【".$anchor_info['nick_name']."】正在直播，快来围观！";
        }
        $share_info = array(
            'user_id' => $this->req['user_id'],
            'live_id' => $this->req['live_id'],
            'imgUrl'  => $live_info['cover_url'],
            'logoUrl' => $this->logo_url,
            'link'    => $share_url,
            'title'   => $title,
            'desc'    => $desc,
            'type'    => $this->req['share_type'],
        );

        return $share_info;
    }

    public function shareNoAnchorLive() {
        $live_domain = new Domain_Live();
        $live_info = $live_domain->checkLive();

        $user_domain = new Domain_User();
        $user_info = $user_domain->checkUser();

        $anchor_info = $user_domain->checkUser($live_info['anchor_id']);

//        $share_url = "http://web.mibolive.com/wap/share/ent.php?live_id=" . $live_info['id'] .
//            "&anchor_id=" . $live_info['anchor_id'] . "&user_id=" . $user_info['id'] .
//            "&channel=" . $this->req['channel'] . '&type=' . $this->req['share_type'];

        $share_url = "http://api.mibolive.com/apps/mibo/download.php";

        $title = "上米播，玩转娱乐场";
        $desc = "「 ".$user_info['nick_name']."」邀请你到米播娱乐场，感受直播新玩法";
        $share_info = array(
            'user_id' => $this->req['user_id'],
            'live_id' => $this->req['live_id'],
            'imgUrl'  => $live_info['cover_url'],
            'logoUrl' => $this->logo_url,
            'link'    => $share_url,
            'title'   => $title,
            'desc'    => $desc,
            'type'    => $this->req['share_type'],
        );

        return $share_info;
    }

    public function sharePkRoom() {
    if(!isset($this->req['room_id'])) throw new PhalApi_Exception('参数错误', 432);

    $model_room = new Model_PkRoom();
    $room_info = $model_room->getRoomInfoById($this->req['room_id']);
    if (empty($room_info)) throw new PhalApi_Exception('房间不存在', 455);

    $user_domain = new Domain_User();
    $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
    if (empty($user_info)) throw new PhalApi_Exception_BadRequest('用户名不存在', 56);

    //$owner_info = $user_domain->getUsersInfoById($room_info['owner_id']);

    //$share_url = "http://web.mibolive.com/wap/share/pk.php?id=".$room_info['id'];
    $share_url = "http://api.mibolive.com/apps/mibo/download.php";

    $title = '技术如何，过来练练！';

    if(!empty($room_info['password'])) {
        $desc = "「 ".$user_info['nick_name']."」邀请你到米播PK场 （房间号：".$room_info['id']." 密码：".$room_info['password']."）";
    } else {
        $desc = "「 ".$user_info['nick_name']."」邀请你到米播PK场 （房间号：".$room_info['id']."）";
    }

    $share_info = array(
        'user_id'     => $this->req['user_id'],
        'room_id' => $this->req['room_id'],
        //'imgUrl'  => $room_info['cover_img'],
        'imgUrl'  => 'http://image.cdn.mibolive.com/img/pub/20170210/589d8028c6555.jpg',
        'logoUrl' => $this->logo_url,
        'link'    => $share_url,
        'title'   => $title,
        'desc'    => $desc,
        'type'    => $this->req['share_type'],
    );

    return $share_info;

}

    public function shareRoomResult() {
        if(!isset($this->req['room_id'])) throw new PhalApi_Exception('参数错误', 432);

        $model_room = new Model_PkRoom();
        $room_info = $model_room->getRoomInfoById($this->req['room_id']);
        if (empty($room_info)) throw new PhalApi_Exception('房间不存在', 455);

        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) throw new PhalApi_Exception_BadRequest('用户名不存在', 56);

        //$owner_info = $user_domain->getUsersInfoById($room_info['owner_id']);

//        $share_url = "http://web.mibolive.com/wap/share/pk.php?id=".$room_info['id'];
//        $share_url = "http://api.mibolive.com/apps/mibo/download.php";
        $share_url = "http://os.mibolive.com/index.php?g=Web&m=Share&a=pkRoomResult&room_id=" . $room_info['id'] .
            "&user_id=" . $user_info['id'];

        $title = '技术如何，过来练练！';

        $desc = "小伙伴们快来！我已超神，欢迎来战！";

        $share_info = array(
            'user_id'     => $this->req['user_id'],
            'room_id' => $this->req['room_id'],
            //'imgUrl'  => $room_info['cover_img'],
            'imgUrl'  => 'http://image.cdn.mibolive.com/img/pub/20170210/589d8028c6555.jpg',
            'logoUrl' => $this->logo_url,
            'link'    => $share_url,
            'title'   => $title,
            'desc'    => $desc,
            'type'    => $this->req['share_type'],
        );

        return $share_info;

    }

    public function shareThenSendCard() {

        if(!isset($this->req['user_id'])) throw new PhalApi_Exception('参数错误', 488);

        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) throw new PhalApi_Exception_BadRequest('用户名不存在', 56);

//        $share_url = "http://web.mibolive.com/wap/share/pk.php";
        $share_url = "http://api.mibolive.com/apps/mibo/download.php";

        $title = '【领房卡】米播每日送卡';

        $desc = "我领取了米播上线大礼，快来一起分！";

        $share_info = array(
            'user_id'     => $this->req['user_id'],
            'room_id' => $this->req['room_id'],
            'imgUrl'  => isset($user_info['avatar']) ? $user_info['avatar']
                            : 'http://image.cdn.mibolive.com/img/pub/20170210/589d8028c6555.jpg',
            'logoUrl' => $this->logo_url,
            'link'    => $share_url,
            'title'   => $title,
            'desc'    => $desc,
            'type'    => $this->req['share_type'],
        );

        return $share_info;
    }

    public function shareMyPkResult() {
        //if(!isset($this->req['room_id'])) throw new PhalApi_Exception('参数错误', 432);
        //
        //$model_room = new Model_PkRoom();
        //$room_info = $model_room->getRoomInfoById($this->req['room_id']);
        //if (empty($room_info)) throw new PhalApi_Exception('房间不存在', 455);

        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) throw new PhalApi_Exception_BadRequest('用户名不存在', 56);

        //$owner_info = $user_domain->getUsersInfoById($room_info['owner_id']);

//        $share_url = "http://web.mibolive.com/wap/share/pk.php";
//        $share_url = "http://api.mibolive.com/apps/mibo/download.php";
        $share_url = "http://os.mibolive.com/index.php?g=Web&m=Share&a=myPkResult&user_id=".$user_info['id'];

        $title = '技术如何，过来练练！小伙伴们快来！我已超神，欢迎来战！';

        $desc = "技术如何，过来练练！小伙伴们快来！我已超神，欢迎来战！";

        $share_info = array(
            'user_id'     => $this->req['user_id'],
            //'imgUrl'  => $room_info['cover_img'],
            'imgUrl'  => 'http://image.cdn.mibolive.com/img/pub/20170210/589d8028c6555.jpg',
            'logoUrl' => $this->logo_url,
            'link'    => $share_url,
            'title'   => $title,
            'desc'    => $desc,
            'type'    => $this->req['share_type'],
        );

        return $share_info;

    }

    public function shareLiveRes() {
        $today = date('Y-m-d', time());
        $im = new Domain_IM();

        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) {
            throw new PhalApi_Exception_BadRequest('用户名不存在', 56);
        }

        //一、获取分享次数
        $share_times = DI()->notorm->share_log->where('user_id = ?', $user_info['id'])
            ->where('share_time >= ?', $today)->count();
        if ($share_times <= 2) {
            //分享经验
            $user_domain->setShareExperience($user_info['id'], $this->req['live_id'], $this->req['share_type']);
            //$add_coin_num = 50000;
            //$user_domain->updateUserDiamondNumReduce($user_info['id'], 0, false, $add_coin_num);
            //$im->sendUserMsg(1, $user_info['id'], '直播分享成功，系统赠送50000米币');
        }

        //二、写入分享记录
        $data = array(
            'user_id'    => $user_info['id'],
            'live_id'    => isset($this->req['live_id']) ? $this->req['live_id'] : 0,
            'title'      => $this->req['title'],
            'desc'       => $this->req['desc'],
            'img_url'    => $this->req['img_url'],
            'link'       => $this->req['link'],
            'share_type' => $this->req['share_type'],
        );

        DI()->notorm->share_log->insert($data);

        //三、获取直播间环信聊天室id，发送分享消息
        if (isset($this->req['is_in_room']) && $this->req['is_in_room'] == 1) {

            $chatroom_key = "chatroom_" . $this->req['live_id'];
            $chatroom_id = DI()->redis->get_forever($chatroom_key);
            if (empty($chatroom_id)) {
                $live_model = new Model_LiveList();
                $live_info = $live_model->getLiveInfoByLiveId($this->req['live_id']);
                $chatroom_id = $live_info['chatroom_id'];
                DI()->redis->set_forever($chatroom_key, $chatroom_id);
            }

            $msg = '「' . $user_info['nick_name'] . "」分享了直播。";

            $msg_rs = $im->sendLiveMsg($user_info['id'], $chatroom_id, $msg, IM_SYS_NOTICE);
            if (!$msg_rs) {
                DI()->logger->error('分享直播环信消息错误');
            }
        }
        $data['diamond_num'] = $user_info['diamond_num'];
        $data['coin_num'] = $user_info['coin_num'] + (isset($add_coin_num) ? $add_coin_num : 0);

        return $data;
    }

    public function shareNoAnchorLiveRes() {
        $today = date('Y-m-d', time());
        $im = new Domain_IM();

        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) {
            throw new PhalApi_Exception_BadRequest('用户名不存在', 56);
        }

        //一、获取分享次数
        $share_times = DI()->notorm->share_log->where('user_id = ?', $user_info['id'])
            ->where('share_time >= ?', $today)->count();
        if ($share_times <= 2) {
            //分享经验
            $user_domain->setShareExperience($user_info['id'], $this->req['live_id'], $this->req['share_type']);
            //$add_coin_num = 50000;
            //$user_domain->updateUserDiamondNumReduce($user_info['id'], 0, false, $add_coin_num);
            //$im->sendUserMsg(1, $user_info['id'], '直播分享成功，系统赠送50000米币');
        }

        //二、写入分享记录
        $data = array(
            'user_id'    => $user_info['id'],
            'live_id'    => isset($this->req['live_id']) ? $this->req['live_id'] : 0,
            'title'      => $this->req['title'],
            'desc'       => $this->req['desc'],
            'img_url'    => $this->req['img_url'],
            'link'       => $this->req['link'],
            'share_type' => $this->req['share_type'],
        );

        DI()->notorm->share_log->insert($data);

        //三、获取直播间环信聊天室id，发送分享消息
        if (isset($this->req['is_in_room']) && $this->req['is_in_room'] == 1) {

            $chatroom_key = "chatroom_" . $this->req['live_id'];
            $chatroom_id = DI()->redis->get_forever($chatroom_key);
            if (empty($chatroom_id)) {
                $live_model = new Model_LiveList();
                $live_info = $live_model->getLiveInfoByLiveId($this->req['live_id']);
                $chatroom_id = $live_info['chatroom_id'];
                DI()->redis->set_forever($chatroom_key, $chatroom_id);
            }

            $msg = '「' . $user_info['nick_name'] . "」分享了直播。";

            $msg_rs = $im->sendLiveMsg($user_info['id'], $chatroom_id, $msg, IM_SYS_NOTICE);
            if (!$msg_rs) {
                DI()->logger->error('分享直播环信消息错误');
            }
        }
        $data['diamond_num'] = $user_info['diamond_num'];
        $data['coin_num'] = $user_info['coin_num'] + (isset($add_coin_num) ? $add_coin_num : 0);

        return $data;
    }

    public function sharePkRoomRes() {
        $today = date('Y-m-d', time());
        $im = new Domain_IM();

        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) {
            throw new PhalApi_Exception_BadRequest('用户名不存在', 56);
        }

        //二、写入分享记录
        $data = array(
            'user_id'    => $user_info['id'],
            'room_id'    => isset($this->req['room_id']) ? $this->req['room_id'] : 0,
            'title'      => $this->req['title'],
            'desc'       => $this->req['desc'],
            'img_url'    => $this->req['img_url'],
            'link'       => $this->req['link'],
            'share_type' => $this->req['share_type'],
        );

        DI()->notorm->share_log->insert($data);

        $data['diamond_num'] = $user_info['diamond_num'];
        $data['coin_num'] = $user_info['coin_num'] + (isset($add_coin_num) ? $add_coin_num : 0);

        return $data;
    }

    public function sendRoomCard() {
        $user_id = $this->req['user_id'];

        $user_info = DI()->notorm->user->where('id', $user_id)->select('id, room_card')->fetchOne();

        if(empty($user_info)) throw new PhalApi_Exception('用户不存在', 408);

        $tomorrow_time = strtotime('tomorrow');
        $today_remain_time = $tomorrow_time - time();

        $had_card = DI()->redis->get_time('today_room_card_user_id_'.$user_id);

        if(!$had_card) {
            $user_info['room_card'] += 1;
            $update_card = [
                'room_card' => $user_info['room_card'],
            ];
            DI()->notorm->user->where('id', $user_id)->update($update_card);
            DI()->redis->set_time('today_room_card_user_id_'.$user_id, 1, $today_remain_time);
        }

        //二、写入分享记录
        $data = array(
            'user_id'    => $user_info['id'],
            'room_id'    => isset($this->req['room_id']) ? $this->req['room_id'] : 0,
            'title'      => $this->req['title'],
            'desc'       => $this->req['desc'],
            'img_url'    => $this->req['img_url'],
            'link'       => $this->req['link'],
            'share_type' => $this->req['share_type'],
        );

        DI()->notorm->share_log->insert($data);

        $user_info['room_card'] = (string)$user_info['room_card'];

        return $user_info;
    }

}