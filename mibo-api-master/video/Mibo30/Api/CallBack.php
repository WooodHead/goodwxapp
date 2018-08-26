<?php

class Api_CallBack extends PhalApi_Api {


    public function __construct() {

    }

    public function getRules() {
        return array();
    }

    /**
     * 视频直播推游状态回调
     * @desc 此直接用于阿里服务器与米播服务器的通讯
     */
    public function aliyunNotify() {

        DI()->logger->info("直播回调", DI()->request->getAll());
        $action = DI()->request->get('action');
        if($action == 'publish_done') {
            //从阿里云那边取得直播情况，如果主播网络不好则发消息到前端，让前端进行处理，并且通过这个消息处理首页直播列表，
            //把断播的清理掉
            $aliyun_id = DI()->request->get('id');
            $aliyun_id_arr = explode('-', $aliyun_id);
            $live_id = $aliyun_id_arr[1];

            $live_info = DI()->notorm->live_list->where('id = ?', $live_id)->fetchOne();

            if($live_info['status'] == 2) {
                $model_live = new Model_LiveList();
                $end_time = date('Y-m-d H:i:s', time());
                $update_data = array(
                    'status' => 4,
                    'end_time' => $end_time,
                    'break_times' => new NotORM_Literal("break_times + 1")
                );
                //DI()->logger->debug('notify---------', '------'.$live_id.'----'.$live_info['chatroom_id']);
                $model_live->updateLiveInfo($live_id, $update_data);
                $domain_im = new Domain_IM();
                $break_times = DI()->redis->get_incr('break_times_live_id_'.$live_id);
                if($break_times >= 6) {
                    //$domain_im->sendLiveMsg(1, $live_info['chatroom_id'], '亲爱的主播，您这个直播已经断流超过五次了，系统判定无效！请关闭此直播，重新开启房间，且不要再频繁断流了！', 111);
                    $domain_im->sendLiveMsg(1, $live_info['chatroom_id'], '主播正在后台化妆，精彩即将呈现...', 122);
                } else {
                    $domain_im->sendLiveMsg(1, $live_info['chatroom_id'], '主播正在后台化妆，精彩即将呈现...', 122);
                }
                $close_redis = DI()->redis->del('get_live_list');
            }
        }

        if($action == 'publish') {
            //从阿里云那边取得直播情况，如果主播网络不好则发消息到前端，让前端进行处理，并且通过这个消息处理首页直播列表，
            //把断播的重新开启
            $aliyun_id = DI()->request->get('id');
            $aliyun_id_arr = explode('-', $aliyun_id);
            $live_id = $aliyun_id_arr[1];

            $live_info = DI()->notorm->live_list->select('chatroom_id, status, break_times')->where('id = ?', $live_id)->fetchOne();

            //DI()->logger->debug('notify--2------', $live_info['status'].'-----'.$live_info['break_times']);
            if($live_info['status'] == 4 || $live_info['break_times'] > 0) {     //是否是中断
                $model_live = new Model_LiveList();
                $model_live->updateLiveInfo($live_id, array('status' => 2));
                $domain_im = new Domain_IM();
                //DI()->logger->debug('notify--3------', $live_info['chatroom_id']);
                $domain_im->sendLiveMsg(1, $live_info['chatroom_id'], '主播已经化妆好了！', 123);
                //$domain_im->sendLiveMsg(1, $live_info['chatroom_id'], '主播已经化妆好了！', 111);
                //$key = 'aliyun_notify_' . $aliyun_id;
                //DI()->redis->set_time($key, 1);     //用于首页清理断播的直播
            }
            $close_redis = DI()->redis->del('get_live_list');
        }




    }




}