<?php

class Model_UserDealLog extends PhalApi_Model_NotORM {

    protected function getTableName($id) {
        return 'user_deal_log';
    }

    public function getUserDealList($uid, $mid, $value) {

        return $this->getORM()
            ->select("*")
            ->where("user_id = ?", $uid)
            ->where('mid = ?', $mid)
            ->where('value in (' . $value . ') ')
            ->fetchAll();
    }

    public function getUserTodayDealRanking() {

        $sql = " SELECT user_id, SUM(spend_num) as total FROM mb_user_deal_log WHERE deal_num>0 AND create_time >= '" . date("Y-m-d H:i:s", strtotime('-1 days')) . "' GROUP BY user_id ORDER BY SUM(deal_num) DESC LIMIT 30";

        return $this->getORM()
            ->queryAll($sql);

    }

    public function insertDealLog($data) {
        $this->getORM()
            ->insert($data);
        return $this->getORM()
            ->insert_id();
    }

    public function getGuardListByAnchorId($anchor_id) {
        return $this->getORM()
            ->select("user_id, sum(deal_num) as deal_num")
            ->where('mid = ? or mid = ?' ,MOUDEL_LIVE_GIFT, MOUDEL_LIVE_TICKET)
            ->where('deal_type=1')
            //->where('status = 1')
            ->where('to_user_id = ?', $anchor_id)
            ->group('user_id')
            ->order(' sum(deal_num) DESC')
            ->limit(30)
            ->fetchPairs('user_id');
    }

    public function getGuardListByTime($anchor_id, $time) {
        return $this->getORM()
            ->select("sum(deal_num) as deal_num")
            ->where('mid = ?', MOUDEL_LIVE_GIFT)
            ->where('deal_type = 1')
            //->where('status = 1')
            ->where('to_user_id = ?', $anchor_id)
            ->where('create_time >= ?', $time)
            ->group('user_id')
            ->order('sum(deal_num) DESC')
            ->limit(50)
            ->fetchPairs('user_id');
    }


    public function getTop3GuardUserlist($user_id) {
        $week = date('Y-m-d H:i:s', strtotime('7 day ago'));
        return $this->getORM()
            ->select("user_id, sum(deal_num) as deal_num")
            ->where('mid=' . IM_GIFT)
            ->where('deal_type=1')
            //->where('status = 1')
            ->where('to_user_id = ?', $user_id)
            ->where('create_time >= ?', $week)
            ->group('user_id')
            ->order(' sum(deal_num) DESC')
            ->limit(3)
            ->fetchPairs('user_id');
    }


}