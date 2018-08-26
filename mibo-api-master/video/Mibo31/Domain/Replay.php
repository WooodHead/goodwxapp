<?php

class Domain_Replay extends Domain_Common{


    public function __construct() {
        parent::__construct();
    }

    public function getFollowVideo($req) {

        $user_id = $req['user_id'];
        $offset = ($req['page_no'] - 1) * $req['page_size'];

        $follow_num = DI()->notorm->follow_list->where('user_id', $user_id)->where('is_cancel = 0')->count('id');

        $model_live = new Model_LiveList();

        if($follow_num > 0) {
            $list = $model_live->getFollowVideo($user_id, $offset, $req['page_size']);
        } else {
            $list = array();
        }

        if(empty($list) && $req['page_no'] <= 1) {
            $recommend_list = $model_live->getRecommendVideo($offset, $req['page_size']);
            $list = $recommend_list;
        }

        if(empty($list)) {
            return array();
        };

        //强制加入海鲜大叔的视频
        //$seefood_live = DI()->notorm->live_list->where('id > ?', 3976)->where('anchor_id', 26883)->order('id desc')->fetchOne();
        //if(!empty($seefood_live)) {
        //    foreach($list as $tmp_key => $tmp_live) {
        //        if($tmp_live['id'] == $seefood_live['id']) {
        //            unset($list[$tmp_key]);
        //            break;
        //        }
        //    }
        //
        //    if($req['page_no'] <= 1) {
        //        array_unshift($list, $seefood_live);
        //        //$list[0] = $seefood_live;
        //    }
        //}

        if(isset($debug)) {
            //print_r($list);exit;
        }
        $list = $this->getAnchorOfVideo($list);

        if($follow_num <= 0) {
            $header_type = 1;   //没有关注任何主播
        } elseif(isset($recommend_list)) {
            $header_type = 2;   //关注的没有直播和回放
        } else {
            $header_type = 3;   //关注的没有直播，只有回放
            foreach($list as $video) {
                if($video['status'] == 2) {
                    $header_type = 4;   //关注的有直播
                    break;
                }
            }
        }

        if($header_type == 1 || $header_type == 2) {
            foreach($list as &$per_video) {
                $per_video['list_type'] = '1';  //推荐
            }
        } else {
            foreach($list as &$per_video) {
                if($per_video['status'] == 2) {
                    $per_video['list_type'] = '2';  //直播
                } elseif($per_video['status'] == 3) {
                    $per_video['list_type'] = '3';  //回放
                } else {
                    $per_video['list_type'] = '0';  //未知
                }
            }
        }

        //四、取得环信的在线人数
        $list = $this->getVideoSeeNum($list, $req['page_no'], $req['page_size']);

        $final['header_type'] = $header_type;
        $final['list'] = $list;
        return $final;

    }

    public function getVideoSeeNum($list, $page_no, $page_size) {

        $easemob = new Emchat_Lite();

        $see_num_list = DI()->redis->get_time('replay_see_num_page_no_'.$page_no.'_page_size_'.$page_size);
        if(!empty($see_num_list)) {
            foreach($list as &$info) {
                $live_id = $info['id'];
                if($info['status'] == 2 || $info['status'] == 3) {
                    if(array_key_exists($live_id, $see_num_list)) {

                        $info['playing_num'] = $see_num_list[$live_id]['affiliations_count'];

                    } else {
                        $im_info = $easemob->getChatRoomDetail($info['chatroom_id']);
                        $playing_num = isset($im_info['data'][0]['affiliations_count']) ?
                            $im_info['data'][0]['affiliations_count'] : 0 ;
                        $info['playing_num'] = $playing_num;
                    }

                } else {
                    $info['playing_num'] = 0 ;
                }
            }

            return $list;
        } else {

            $see_num_list = [];

            $replay_id_arr = array();
            foreach($list as &$info) {
                $live_id = $info['id'];
                if($info['status'] == 2) {

                    $im_info = $easemob->getChatRoomDetail($info['chatroom_id']);
                    $see_num_list[$live_id]['affiliations_count'] = isset($im_info['data'][0]['affiliations_count']) ?
                        $im_info['data'][0]['affiliations_count'] : 0;
                    $info['playing_num'] = isset($im_info['data'][0]['affiliations_count']) ?
                        $im_info['data'][0]['affiliations_count'] : 0;

                } elseif($info['status'] == 3) {
                    array_push($replay_id_arr, $live_id);
                } else {
                    $see_num_list[$live_id]['affiliations_count'] = 0;
                    $info['playing_num'] = 0 ;
                }
            }

            if(isset($replay_id_arr) && !empty($replay_id_arr)) {
                $model_live_extend = new Model_LiveExtend();
                $replay_see_num_list = $model_live_extend->getSeeNum($replay_id_arr);
                foreach($list as &$per) {
                    if($per['status'] == 3) {
                        if(isset($replay_see_num_list[$per['id']])) {
                            $see_num = $replay_see_num_list[$per['id']]['playing_num'];
                            $see_num_list[$per['id']]['affiliations_count'] = $see_num;
                            $per['playing_num'] = $see_num ;
                        } else {
                            $see_num = 0;
                            $see_num_list[$per['id']]['affiliations_count'] = $see_num;
                            $per['playing_num'] = $see_num ;
                        }
                    }
                }
            }

            DI()->redis->set_time('replay_see_num_page_no_'.$page_no.'_page_size_'.$page_size, $see_num_list, 3);

            return $list;

        }

    }

    public function getAnchorOfVideo($list) {
        $user_ids = '';
        foreach($list as $video) {
            $user_ids .= $video['anchor_id'] . ',';
        }

        $user_ids = rtrim($user_ids, ',');

        $model_user = new Model_User();
        $user_list = $model_user->getUsersInfoByIds($user_ids);

        foreach($list as &$video) {
            if(empty($video['location'])) {
                $video['location'] = '火星';
            }
            foreach($user_list as $user) {
                if($video['anchor_id'] == $user['id']) {
                    $video['nick_name'] = $user['nick_name'];
                    $video['avatar'] = $user['avatar'];
                    $video['sex'] = $user['sex'];
                }
            }
        }

        return $list;

    }

    public function userEntry($req) {
        $live_info = $this->checkLive();

        $model_user = new Model_User();
        $anchor_info = $model_user->getUsersInfoById($live_info['anchor_id']);

        $model_follow = new Model_FollowList();
        $is_follow = $model_follow->isMyFollow($req['user_id'], $live_info['anchor_id']);

        $anchor_info['is_followed'] = $is_follow;

        $live_info['playing_num'] = 0;

        $model_live_extend = new Model_LiveExtend();
        $model_live_extend->addSeeNUm($live_info['id'], $req['user_id']);

        $see_num_list = $model_live_extend->getSeeNum(array($live_info['id']));
        $live_info['playing_num'] = $see_num_list[$live_info['id']]['playing_num'];

        $final_data['anchor_info'] = $anchor_info;
        $final_data['live_info'] = $live_info;

        return $final_data;
    }

    public function userExit($req) {
        $live_info = $this->checkLive();

        $model_user = new Model_User();
        $anchor_info = $model_user->getUsersInfoById($live_info['anchor_id']);

        $model_follow = new Model_FollowList();
        $is_follow = $model_follow->isMyFollow($req['user_id'], $live_info['anchor_id']);

        $anchor_info['is_followed'] = $is_follow;

        $final_data['anchor_info'] = $anchor_info;
        return $final_data;
    }




}