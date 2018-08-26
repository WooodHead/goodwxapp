<?php

class Api_Msg extends PhalApi_Api {

    public function getRules() {
        return array(
            'sendMsg' => array(
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '发送用户id'),
                'to_user_id' => array('name' => 'to_user_id', 'type' => 'int', 'require' => false, 'desc' => '接收用户id'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => false, 'desc' => '直播间id'),
                'type' => array('name' => 'type', 'type' => 'int', 'default' => 1,
                                'desc' => '消息类型：1用户消息，2直播间消息，3跑马灯'),
                'content' => array('name' => 'content', 'type' => 'string', 'min' => 10, 'max' => 1000,
                                   'desc' => '发送内容'),
            ),
        );
    }

    /**
     * 发送环信信息
     * @desc 提供给大龙发送环信信息
     * @ignore
     * @request http://mibo.yahalei.com/mibo30/index.php?service=msg.sendMsg&user_id=100&to_user_id=12443&content=sfsssasdfadsfas
     */
    public function sendMsg() {
        $req['user_id'] = $this->user_id;
        $req['to_user_id'] = $this->to_user_id;
        $req['live_id'] = $this->live_id;
        $req['content'] = $this->content;
        $req['type'] = $this->type;

        if($req['user_id'] <= 0) throw new PhalApi_Exception('参数错误', 460);

        $domain = new Domain_Msg();
        $res = $domain->sendMsg($req);
        return $res;
    }


}