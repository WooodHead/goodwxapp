<?php
class Domain_InternalTest extends Domain_Common {
    public function __construct() {
        parent::__construct();
    }

    public function isInternalStaff($mb_id) {
        $model_staff = new Model_InternalStaff();
        return $model_staff->isInternalStaff($mb_id);
    }

    public function anchorEntry($live_info, $anchor_info) {
        $domain_live = new Domain_Live();

        $options = array(
            'name'        => "mibo" . $live_info['id'],
            'description' => $live_info['live_name'],
            'maxusers'    => 5000,
            'owner'       => $anchor_info['im_id'],
            'members'     => array("mibokf1000"),
        );
        $chatroom_key = "chatroom_" . $live_info['id'];
        $chatroom_id = DI()->redis->get_forever($chatroom_key);

        if (empty($chatroom_id)) {
            //创建聊天室
            $emchat = new Emchat_Lite();
            $chatroom_info = $emchat->createChatRoom($options);
            if (empty($chatroom_info) || !isset($chatroom_info['data']['id'])) {
                throw new PhalApi_Exception("创建聊天室失败", 444);
            }

            $chatroom_id = $chatroom_info['data']['id'];
            DI()->redis->set_forever($chatroom_key, $chatroom_id);
        }

        $stream_name = "mb-" . $live_info['id'] . '-' . $live_info['anchor_id'];
        $filename = '/' . APP_NAME . "/" . $stream_name;

        $auth_key = $domain_live->getAuthkey($filename);
        $flv_authkey = $domain_live->getAuthkey($filename . '.flv');
        $m3u8_authkey = $domain_live->getAuthkey($filename . '.m3u8');

        $add_arr = GetIpLookup(g_getIP());
        $city = isset($add_arr['city']) ? $add_arr['city'] : '火星';

        $update_data = array(
            'chatroom_id' => $chatroom_id,
            'push_url'    => "rtmp://video-center.alivecdn.com/" . APP_NAME . "/" . $stream_name
                            . "?vhost=" . DI()->config->get('app.aliyun.live_domain_host') . "&auth_key=" . $auth_key,
            'look_url'    => 'rtmp://' . DI()->config->get('app.aliyun.live_domain_host') . "/" . APP_NAME . "/" . $stream_name
                            . "?auth_key=" . $auth_key,
            'flv_url'     => 'http://' . DI()->config->get('app.aliyun.live_domain_host') . "/" . APP_NAME . "/" . $stream_name
                            . ".flv?auth_key=" . $flv_authkey,
            'm3u8_url'    => 'http://' . DI()->config->get('app.aliyun.live_domain_host') . "/" . APP_NAME . "/" . $stream_name
                            . ".m3u8?auth_key=" . $m3u8_authkey,
            'replay_url'  => 'http://miboinput.oss-cn-hangzhou.aliyuncs.com/record' . "/" . APP_NAME . "/" . $stream_name
                            . ".m3u8?auth_key=" . $m3u8_authkey,
            'start_time'  => $live_info['status'] == 4 ? $live_info['start_time'] : date('Y-m-d H:i', time()),
            'location'    => isset($this->req['location']) ? $this->req['location'] : $city,
            'type'        => 3, //内部人员直播
        );

        $model_live = new Model_LiveList();
        $rs = $model_live->updateLiveInfo($live_info['id'], $update_data);

        if ($rs != 1 && $rs != 0) {
            throw new PhalApi_Exception("更新直播信息失败", 445);
        }

        DI()->redis->del('get_live_list');

        $update_data['live_id'] = $live_info['id'];
        $update_data = $domain_live->setLiveBean($update_data, true);

        $update_data['receive_num'] = $anchor_info['receive_num'];

        //告诉粉丝开直播了
        $live_info = array_merge($live_info, $update_data);

        $domain_game = new Domain_Game();
        $dealer_info = $domain_game->getDealerByLiveId($live_info['id']);
        if(empty($dealer_info)) $dealer_info = NULL;

        $final_data['dealer_info'] = $dealer_info;
        $final_data['live_info'] = $update_data;

        return $final_data;
    }

    public function instantLive($user_info) {
        $now = date('Y-m-d H:i', time());

        $add_arr = GetIpLookup(g_getIP());
        $city = isset($add_arr['city']) ? $add_arr['city'] : '火星';
        $live_info = array(
            'live_name'    => $now,
            'cover_url'    => $user_info['avatar'],
            'anchor_id'    => $this->req['user_id'],
            'preview_time'   => $now,
            'location'    => isset($this->req['location']) ? $this->req['location'] : $city,
        );


        $last_live = DI()->notorm->live_list->select('id')->order('id desc')->fetchOne();
        $this_live_id = $last_live['id'] + 1;

        $options = array(
            'name'        => "mibo" . $this_live_id,
            'description' => $live_info['live_name'],
            'maxusers'    => 5000,
            'owner'       => $user_info['im_id'],
            'members'     => array("mibokf1000"),
        );
        $chatroom_key = "chatroom_" . $this_live_id;
        $chatroom_id = DI()->redis->get_forever($chatroom_key);
        if (empty($chatroom_id)) {

            //创建聊天室
            $emchat = new Emchat_Lite();
            $chatroom_info = $emchat->createChatRoom($options);
            if (empty($chatroom_info) || !isset($chatroom_info['data']['id'])) {
                throw new PhalApi_Exception("创建聊天室失败", 444);
            }

            $chatroom_id = $chatroom_info['data']['id'];
            DI()->redis->set_forever($chatroom_key, $chatroom_id);
        }

        $stream_name = "mb-" . $this_live_id . '-' . $live_info['anchor_id'];
        $filename = '/' . APP_NAME . "/" . $stream_name;

        $domain_live = new Domain_Live();
        $auth_key = $domain_live->getAuthkey($filename);
        $flv_authkey = $domain_live->getAuthkey($filename . '.flv');
        $m3u8_authkey = $domain_live->getAuthkey($filename . '.m3u8');

        $update_data = array(
            'id'          => $this_live_id,
            'chatroom_id' => $chatroom_id,
            'push_url'    => "rtmp://video-center.alivecdn.com/" . APP_NAME . "/" . $stream_name . "?vhost=" . DI()->config->get('app.aliyun.live_domain_host') . "&auth_key=" . $auth_key,
            'look_url'    => 'rtmp://' . DI()->config->get('app.aliyun.live_domain_host') . "/" . APP_NAME . "/" . $stream_name . "?auth_key=" . $auth_key,
            'flv_url'     => 'http://' . DI()->config->get('app.aliyun.live_domain_host') . "/" . APP_NAME . "/" . $stream_name . ".flv?auth_key=" . $flv_authkey,
            'm3u8_url'    => 'http://' . DI()->config->get('app.aliyun.live_domain_host') . "/" . APP_NAME . "/" . $stream_name . ".m3u8?auth_key=" . $m3u8_authkey,
            'replay_url'  => 'http://miboinput.oss-cn-hangzhou.aliyuncs.com/record' . "/" . APP_NAME . "/" . $stream_name . ".m3u8?auth_key=" . $m3u8_authkey,
            'start_time'  => $now,
            'location'    => $city,
            'type'        => 3,
        );


        $live_info = array_merge($live_info, $update_data);

        $model_live = new Model_LiveList();
        $rs = $model_live->insertPreview($live_info);

        if (!$rs) {
            throw new PhalApi_Exception("服务器出错", 445);
        }

        $del_redis_res = DI()->redis->del('get_live_list');

        $live_info['receive_num'] = $user_info['receive_num'];
        //告诉粉丝开直播了

        return $live_info;
    }


}