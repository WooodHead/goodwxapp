<?php

class Api_Live extends PhalApi_Api {

    private $liveDomain;

    public function __construct() {
        $this->liveDomain = new Domain_Live();
    }


    public function getRules() {
        return array(
            'anchorEntry'   => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '主播用户ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
                'location' => array('name' => 'location', 'type' => 'string', 'require' => false, 'desc' => '直播地址，
                                    如：广东-深圳'),
            ),
            'userEntry'     => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => false, 'desc' => '渠道ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
            ),
            'userExit'      => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => false, 'desc' => '渠道ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
            ),
            'anchorExit'    => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => false, 'desc' => '渠道ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
            ),
            'getGiftList'   => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'version_code' => array('name' => 'version_code', 'type' => 'string', 'require' => false, 'desc' => '版本号'),
            ),
            'buyLiveTicket' => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
                'price'   => array('name' => 'price', 'type' => 'int', 'require' => true, 'desc' => '价格'),
            ),
            'sendBarrage'   => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
                'price'   => array('name' => 'price', 'type' => 'int', 'require' => true, 'desc' => '价格'),
            ),
            'sendGift'      => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
                'gift_id' => array('name' => 'gift_id', 'type' => 'int', 'require' => true, 'desc' => '礼物ID'),
                'gift_num' => array('name' => 'gift_num', 'type' => 'int', 'require' => true, 'min' => 1, 'desc' => '礼物数量'),
            ),
            'guardList'      => array(
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播ID'),
            ),
            'liveEnd'   => array(
                'live_id' => array('name' => 'live_id', 'type' => 'int', 'require' => true, 'desc' => '直播间id'),
                'chatroom_id' => array('name' => 'chatroom_id', 'type' => 'string', 'require' => true, 'desc' => '环信聊天室id'),
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '主播id'),
            ),
            'setLivePreview'   => array(
                'user_id'      => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '主播id'),
                'live_name'    => array('name' => 'live_name', 'type' => 'string', 'require' => true, 'desc' => '直播名称'),
                'cover_url'    => array('name' => 'cover_url', 'type' => 'string', 'require' => true, 'desc' => '封面地址'),
                'ticket_price' => array('name' => 'ticket_price', 'type' => 'int', 'require' => true, 'desc' => '门票价格'),
                'start_time' => array('name' => 'start_time', 'type' => 'string', 'require' => true,
                                      'desc' => '预计开播时间,格式：2016-11-18 15:30。最多七天内'),
                'time_length' => array('name' => 'time_length', 'type' => 'int', 'require' => false, 'desc' => '预计时长'),
            ),
            'instantLive' => array(
                'user_id'      => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '主播id'),
            ),
            'pop'   => array(
                'user_id' => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户id'),
            ),
        );
    }

    /**
     * 主播进入
     * @desc 主播发起直播后获取推流相关接口
     */
    public function anchorEntry() {
        return $this->liveDomain->anchorEntry();
    }

    /**
     * 主播退出
     * @desc 主播退出后服务器需要做的相关操作
     */
    public function anchorExit() {
        return $this->liveDomain->anchorExit();
    }

    /**
     * 用户进入
     * @desc 用户进入直播间，获取直播间用户头像列表
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.UserEntry&channel=bt&live_id=1&user_id=14
     */
    public function userEntry() {
        return $this->liveDomain->userEntry();
    }

    /**
     * 用户退出
     */
    public function userExit() {
        return $this->liveDomain->userExit();
    }

    /**
     * 获取礼物列表
     * @desc 获取礼物列表,版本不同，礼物不同，20后的版本加入了米币礼物，用type区分，1为钻石礼物，2为米币礼物
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.getGiftList&channel=bt
     */
    public function getGiftList() {
        return $this->liveDomain->getGiftList();

    }

    /**
     * 购买直播间门票
     * @desc 购买直播间门票,若是邀请码，刚价格为0
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.buyLiveTicket&channel=bt&user_id=14&live_id=1&price=1
     */
    public function buyLiveTicket() {
        return $this->liveDomain->buyLiveTicket();
    }

    /**
     * 发送弹幕
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.sendBarrage&channel=bt&user_id=14&live_id=1&price=1
     */
    public function sendBarrage() {
        return $this->liveDomain->sendBarrage();
    }

    /**
     * 发送礼物
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.sendGift&channel=bt&user_id=14&live_id=1&gift_id=1&__sql__=1
     */
    public function sendGift() {
        return $this->liveDomain->sendGift();
    }

    /**
     * 获取守护榜单
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.guardList&channel=bt&user_id=14&live_id=1
     */
    public function guardList() {
        return $this->liveDomain->guardList();
    }

    /**
     * 直播结束
     * @desc 直播结束
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.liveEnd&live_id=7&user_id=1&chatroom_id=12312
     */
    public function liveEnd() {
        $domain_live = new Domain_Live();
        return $domain_live->liveEnd();
    }

    /**
     * 发起直播预告
     * @desc 发起直播预告
     * @request http://mibo.yahalei.com/mibo/index.php?service=Live.SetLivePreview&user_id=27&live_name=sss&
     */
    public function setLivePreview() {
        $domain_live = new Domain_Live();
        return $domain_live->setLivePreview();
    }

    /**
     * 立即开播
     * @desc 立即开播
     * @return array
     */
    public function instantLive() {
        $domain_live = new Domain_live();
        return $domain_live->instantLive();
    }

    /**
     * 直播间定时弹出
     * @desc 直播间定时弹出 type:1是充值，2是礼物，3vip礼包，4，土豪礼包
     */
    public function pop() {
        $data = $this->liveDomain->pop();

        return $data;
    }

}