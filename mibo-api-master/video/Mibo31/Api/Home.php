<?php

/**
 * 默认接口服务类
 *
 * @author: dogstar <chanzonghuang@gmail.com> 2014-10-04
 */
class Api_Home extends PhalApi_Api {

    private $homeDomain;

    public function __construct() {
        $this->homeDomain = new Domain_Home();
    }

    public function getRules() {
        return array(
            'getLiveList'   => array(
                'channel'   => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'user_id'   => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户id'),
                'page_no'   => array('name' => 'page_no', 'type' => 'int', 'require' => false, 'desc' => '页码'),
                'page_size' => array('name' => 'page_size', 'type' => 'int', 'require' => false, 'default' => 20, 'desc' => '每页数量'),
            ),
            'getGameList'   => array(
                'channel'   => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'user_id'   => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '用户id'),
                'page_no'   => array('name' => 'page_no', 'type' => 'int', 'require' => false, 'desc' => '页码'),
                'page_size' => array('name' => 'page_size', 'type' => 'int', 'require' => false, 'default' => 20, 'desc' => '每页数量'),
            ),
            'getBannerList' => array(
                'channel' => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
            ),
            'doSearch'      => array(
                'channel'   => array('name' => 'channel', 'type' => 'string', 'require' => true, 'desc' => '渠道ID'),
                'user_id'   => array('name' => 'user_id', 'type' => 'int', 'require' => true, 'desc' => '当前登录的用户id'),
                'key_word'  => array('name' => 'key_word', 'type' => 'string', 'require' => true, 'desc' => '搜索关键字'),
                'page_no'   => array('name' => 'page_no', 'type' => 'int', 'require' => true, 'desc' => '页码'),
                'page_size' => array('name' => 'page_size', 'type' => 'int', 'require' => false, 'default' => 20, 'desc' => '每页数量'),
            ),
            'getCoinRank'   => array(
                'self_user_id'   => array('name' => 'self_user_id', 'type' => 'int', 'require' => true, 'desc' => '当前登录的用户id'),
            ),
            'whoReceiveMost' => array(
                'self_user_id'   => array('name' => 'self_user_id', 'type' => 'int', 'require' => true, 'desc' => '当前登录的用户id,用于判断关注'),
            ),
            'getTodayWinner' => array(
                'self_user_id'   => array('name' => 'self_user_id', 'type' => 'int', 'require' => true, 'desc' => '当前登录的用户id'),
            ),
            'newUserRank' => array(
                'self_user_id'   => array('name' => 'self_user_id', 'type' => 'int', 'require' => true, 'desc' => '当前登录的用户id'),
            ),
        );
    }

    /**
     * 获取首页直播列表
     * @return String id 直播ID号
     * @return String live_name 直播标题
     * @return String cover_url 封面
     * @return String push_url 推流地址
     * @return String look_url 观看地址
     * @return String anchor_id 主播用户
     * @return String start_time 开始发布时间 chou
     * @return String status  视频状态  0:初始直播  1:预告  2:直播中 3:直播结束 4:强制关闭'
     * @return String game_id 游戏ID
     * @return String ticket_price 门票价格
     * @return String ticket_sale 已售门票数
     * @return String invite_code 邀请码
     * @request http://mibo.yahalei.com/mibo/index.php?service=Home.getLiveList&channel=bt&id=13&page_no=1&page_size=10&user_id=34
     */
    public function getLiveList() {
        $live_list = $this->homeDomain->getLiveList();

        if (empty($live_list)) {
            return array();
        }

        return $live_list;
    }

    /**
     * 无主播游戏场列表
     * @desc 无主播游戏场列表
     * @request http://t.com/mibotest.yahalei.com/public/mibo/index.php?service=Home.GetGameList&channel=bt&user_id=14
     */
    public function getGameList() {
        $game_list = $this->homeDomain->getGameList();

        if (empty($game_list)) {
            //throw new PhalApi_Exception("没有更多数据", 455);
            return array();
        }

        return $game_list;
    }

    /**
     * 获取Banner列表
     * @return Stirng title 标题
     * @return Stirng img 图片
     * @return Stirng type 展示类型 0:直播  1:webview   2:link  3:用户主页
     * @return Stirng value 类型值
     * @request http://mibo.yahalei.com/mibo/index.php?service=Home.getBannerList&channel=bt
     */
    public function getBannerList() {
        $banner_list = $this->homeDomain->getBannerList();
        if (empty($banner_list)) {
            throw new PhalApi_Exception("获取Banner失败", 466);
        }
        return $banner_list;
    }

    /**
     * 搜索
     * @desc 搜索用户的昵称或米播ID号
     * @request http://mibo.yahalei.com/mibo/index.php?service=Home.DoSearch&channel=bt&page_no=1&key_word=1&user_id=14
     */
    public function doSearch() {
        $user_domain = new Domain_User();
        $rs = $user_domain->getSearchResult();
        if (empty($rs)) {
            return array();
        }

        return $rs;
    }

    /**
     * 今日大赢家
     * @desc 今日大赢家
     * @return array|mixed
     * @request http://mibo.yahalei.com/mibo/index.php?service=Home.GetTodayWinner&self_user_id=34
     */
    public function getTodayWinner() {
        $domain_rank = new Domain_Rank();
        $winners = $domain_rank->getTodayWinner();
        return $winners;
    }

    /**
     * 新人榜
     * @desc 新人榜，最近三天的注册用户
     */
    public function newUserRank() {
        $domain_rank = new Domain_Rank();
        $users = $domain_rank->newUserRank();
        return $users;
    }

    /**
     * 魅力榜
     * @desc 魅力榜，哪个主播收到最多钻石
     */
    public function whoReceiveMost() {
        $domain_rank = new Domain_Rank();
        return $domain_rank->whoReceiveMost();
    }

    /**
     * 财富榜
     * @desc 财富榜
     * @request http://t.com/video/Public/mibo/index.php?service=Home.GetCoinRank&self_user_id=34
     */
    public function getCoinRank() {
        $domain_rank = new Domain_Rank();
        $rank = $domain_rank->getCoinRank();
        return $rank;
    }

}