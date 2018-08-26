<?php

class Domain_Pk extends Domain_Common{

    private $model_pk_room;
    private $one_card_loop_num = 8;

    public function __construct() {
        parent::__construct();
        $this->model_pk_room = new Model_PkRoom();
    }

    public function getRoomList() {
        $page_no = isset($this->req['page_no']) ? $this->req['page_no'] : 1;
        $page_size = isset($this->req['page_size']) ? $this->req['page_size'] : 20;

        $redis_room_list = DI()->redis->get_time('pk_room_list');
        //var_dump($room_list);exit;
        if(isset($redis_room_list[$page_no.'_'.$page_size])) {
            return $redis_room_list[$page_no.'_'.$page_size];
        }

        $model_pk = new Model_PkRoom();
        $room_list = $model_pk->getRoomList($page_no, $page_size);

        $owner_ids_arr = array();

        foreach($room_list as $tmp_room) {
            array_push($owner_ids_arr, $tmp_room['owner_id']);
        }

        $owner_ids_arr = array_unique($owner_ids_arr);

        $owner_list = DI()->notorm->user->select('id, nick_name, vip_level, avatar, diamond_num, coin_num')
                    ->where('id', $owner_ids_arr)->fetchAll();

        foreach($room_list as &$room) {
            foreach($owner_list as $tmp_owner) {
                if($tmp_owner['id'] == $room['owner_id']) {
                    $room = $room + $tmp_owner;
                    break;
                }
            }
        }

        $redis_room_list[$page_no.'_'.$page_size] = $room_list;

        DI()->redis->set_time('pk_room_list', $redis_room_list, 300);
        return $room_list;
    }

    //创建房间
    public function createRoom(){

        $user_info = $this->checkUser();

        $had_room = DI()->notorm->pk_room->where('owner_id', $user_info['id'])->where('status', 2)->count('id');
        if($had_room) throw new PhalApi_Exception('您有未关闭的房间，请先关闭', 399);

        //一、判断用户、房间、门卡
        if (empty($user_info)) {
            throw new PhalApi_Exception('不存在用户', 400);
        }

        $loop_num = $this->req['loop_num'];
        if($loop_num == 20) {
            $need_card_num = 2;
        } else {
            $need_card_num = $loop_num / $this->one_card_loop_num;
        }

        if($need_card_num > $user_info['room_card']) {
            throw new PhalApi_Exception('房卡不足', 466);
        }

        $last_room_id = DI()->notorm->pk_room->order('id desc')->fetchOne('id');
        $this_room_id = $last_room_id + 1;

        $room_data = [
            'room_name'  => isset($this->req['room_name']) ? $this->req['room_name'] : '',
            'owner_id'   => $user_info['id'],
            'password'   => isset($this->req['password']) ? $this->req['password'] : '',
            'type'       => $this->req['type'],
            'mini_limit' => $this->req['mini_limit'],
            'loop_num'   => $this->req['loop_num'],
            'status'   => 2,
        ];

        //二、创建聊天室
        $options = array(
            'name'        => "mibo_pk_room_" . $this_room_id,
            'description' => isset($this->req['room_name']) ? $this->req['room_name'] : 'pk场' . $this_room_id,
            'maxusers'    => 5000,
            'owner'       => $user_info['im_id'],
            'members'     => array("mibokf1000"),
        );
        $chatroom_key = "pk_chatroom_" . $this_room_id;
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

        $room_data['chatroom_id'] = $chatroom_id;

        //二、写进记录
        $model_pk = new Model_PkRoom();
        $room_id = $model_pk->insert($room_data);


        if(!$room_id) {
            throw new PhalApi_Exception("内部错误：创建房间失败", 445);
        }

        DI()->redis->del('pk_room_list');

        $room_data['room_id'] = $room_id;
        $final_data['room_data'] = $room_data;
        return $final_data;
    }

    public function continueRoom() {

        $old_room_id = $this->req['old_room_id'];

        $user_info = $this->checkUser();

        //一、判断用户、房间、门卡
        if (empty($user_info)) {
            throw new PhalApi_Exception('不存在用户', 400);
        }

        $old_room_info = $this->model_pk_room->getRoomInfoById($old_room_id);

        if(!empty($old_room_info)) throw new PhalApi_Exception('old room not exists', 400);

        $loop_num = $old_room_info['loop_num'];
        $need_card_num = $loop_num / $this->one_card_loop_num;
        if($need_card_num > $user_info['room_card']) {
            throw new PhalApi_Exception('房卡不足', 466);
        }

        $last_room_id = DI()->notorm->pk_room->order('id desc')->fetchOne('id');
        $this_room_id = $last_room_id + 1;

        $room_data = [
            'room_name'  => $old_room_info['room_name'],
            'owner_id'   => $old_room_info['owner_id'],
            'password'   => $old_room_info['password'],
            'type'       => $old_room_info['type'],
            'mini_limit' => $old_room_info['mini_limit'],
            'loop_num'   => $old_room_info['loop_num'],
            'chatroom_id' => $old_room_info['chatroom_id'],
            'status'   => 2,
        ];

        //二、写进记录
        $model_pk = new Model_PkRoom();
        $room_id = $model_pk->insert($room_data);


        if(!$room_id) {
            throw new PhalApi_Exception("内部错误：创建房间失败", 445);
        }

        DI()->redis->del('pk_room_list');

        $room_data['room_id'] = $room_id;
        $final_data['room_data'] = $room_data;
        return $final_data;
    }

    public function userEntry() {

        //一、检测初始值
        $user_info = $this->checkUser();
        $room_info = $this->model_pk_room->getRoomInfoById($this->req['room_id']);
        if(empty($room_info) || $room_info['status'] == 3) throw new PhalApi_Exception('房间不存在', 401);

        //二、房间密码
        if(!empty($room_info['password'])) {
            $input_password = isset($this->req['password']) ? $this->req['password'] : '';
            if($input_password != $room_info['password']) throw new PhalApi_Exception('密码错误', 403);
        }

        //三、房间用户信息
        $chatroom_users_key = "pk_room_" . $room_info['id'] . "_user_list";
        $chatroom_user_list = DI()->redis->get_forever($chatroom_users_key);

        $user_arr = array(
            'id'        => $user_info['id'],
            'nick_name' => $user_info['nick_name'],
            'avatar'    => $user_info['avatar'],
            'sex'       => $user_info['sex'],
            'level'     => $user_info['level'],
            'signature' => $user_info['signature'],
            'user_type' => $user_info['user_type'],
            'vip_level' => $user_info['vip_level'],
            'send_num'  => 0,
        );

        //三、2、存入这个用户的数据
        if (empty($chatroom_user_list)) {
            DI()->redis->set_forever($chatroom_users_key, json_encode(array($user_info['id'] => $user_arr)));
            $user_list = array($user_arr);
        } else {

            $redis_user_list = json_decode($chatroom_user_list, true);
            if (!array_key_exists($user_info['id'], $redis_user_list)) {
                $redis_user_list = $this->getLiveUserList($redis_user_list, $user_arr);
                DI()->redis->set_forever($chatroom_users_key, json_encode($redis_user_list));
            }
            $user_list = $redis_user_list;
        }

        $seat_users = $this->getSeatUser($room_info['id']);

        //$model_card = new Model_Card();
        //$final_data['room_current_loop'] = $model_card->getRoomCurrentLoop($room_info['id']);

        //四、根据发牌时间和当前时间，把进入直播间的通用数据存储一定时间长的缓存
        $pk_room_info_userentry = DI()->redis->get_time('pk_room_info_userentry_' . $this->req['room_id']);
        if (!empty($pk_room_info_userentry)) {

            $final_data = json_decode($pk_room_info_userentry, true);

            //a,b都是动态数据
            //a,用户是否关注主播
            $is_followed = DI()->notorm->follow_list->where('user_id = ?', $this->req['user_id'])
                ->where('to_user_id = ?', $room_info['owner_id'])->where('is_cancel = 0')->fetchOne('id');
            if (!empty($is_followed)) {
                $final_data['owner_info']['is_followed'] = true;
            } else {
                $final_data['owner_info']['is_followed'] = false;
            }

            //b,最后的牌值
            $last_loop_card_info = $this->getLastCardInfo($this->req['room_id']);
            $final_data['last_card_info'] = $last_loop_card_info;

            $total_people = count($user_list);
            if ($total_people > 50) {
                $user_list = array_slice($user_list, 0, 50);
            }
            $user_list = array_values($user_list);
            $final_data['user_list'] = $user_list;

            $final_data['seat_users'] = $seat_users;
            return $final_data;

        } else {

            //五、进入直播间信息通告
            $msgBody = array(
                'pid'       => strval(111), //协议ID
                'content'   => "【公告】倡导绿色文明直播，米播严禁利用游戏进行赌博，违者封号，谢谢配合！" ,
                'user_id'   => "",
                'nick_name' => "",
                'avatar'    => "",
                'level'     => "",
                'sex'       => "",
                'signature' => "",
                'extra'     => null,
            );

            //五、如果用户个数大于50个则取前50个用户数据，直播上面的头像
            $total_people = count($user_list);
            if ($total_people > 50) {
                $user_list = array_slice($user_list, 0, 50);
            }
            $user_list = array_values($user_list);

            $domain_user = new Domain_User();
            $owner_info = $domain_user->setSimpleUserInfoBean($domain_user->getUsersInfoById($room_info['owner_id']));

            //六、用户是否关注主播
            $is_followed = DI()->notorm->follow_list->where('user_id = ?', $this->req['user_id'])
                ->where('to_user_id = ?', $room_info['owner_id'])->where('is_cancel = 0')->fetchOne('id');
            if (!empty($is_followed)) {
                $owner_info['is_followed'] = true;
            } else {
                $owner_info['is_followed'] = false;
            }

            //七、取得庄家信息
            //$model_card = new Model_Card();
            //$dealer_info = $model_card->getDealerByRoomId($room_info['id']);

            //七（1）要是取不到新的庄家信息，就使用主播信息
            //if (empty($dealer_info)) {
            //    $dealer_info = $owner_info;
            //}

            //八、最后一局牌值,如果
            $last_loop_card_info = $this->getLastCardInfo($this->req['room_id']);

            //九、直播间的小活动
            //$model_activity = new Model_Activity();
            //$activity_info = $model_activity->getList();

            //十、最终返回给客户端的数据
            $final_data['owner_info'] = $owner_info;
            //'dealer_info'    => $dealer_info,
            $final_data['user_list'] = $user_list;
            $final_data['notice_info'] = $msgBody;
            $final_data['room_info'] = $room_info;
            $final_data['last_card_info'] = $last_loop_card_info;
            $final_data['seat_users'] = $seat_users;


            DI()->redis->set_time('pk_room_info_userentry_' . $this->req['room_id'],
                json_encode($final_data), 300);

            return $final_data;
        }
    }

    //获得房间入座的用户信息
    public function getSeatUser($room_id) {
        //取得入座用户id,$user_ids键为座位，值为用户id
        $user_ids = DI()->redis->hGetAll('pk_seat_data_room_id_'.$room_id);
        if(!empty($user_ids)) {
            $user_list = DI()->notorm->user->select('id, nick_name, avatar')
                ->where('id', $user_ids)->fetchPairs('id');

            $seat_users = [];
            foreach($user_ids as $pool_id => $user_id) {
                if(isset($user_list[$user_id])) {
                    $seat_users[$pool_id] = $user_list[$user_id];
                    $seat_users[$pool_id]['pool_id'] = $pool_id;
                }
            }
        }

        $seat_users = isset($seat_users) ? array_values($seat_users) : NULL;
        return $seat_users;
    }

    public function ownerExit() {
        //$owner_info = $this->checkUser();
        $room_id = $this->req['room_id'];
        $room_info = $this->model_pk_room->getRoomInfoById($this->req['room_id']);

        if (empty($room_info) || $room_info['owner_id'] != $this->req['owner_id']) {
            throw new PhalApi_Exception("不是房主", 460);
        }

        $update_room_data = [
            'status' => 3
        ];

        $update_res = DI()->notorm->pk_room->where('id', $room_id)->update($update_room_data);
        if(!$update_res) throw new PhalApi_Exception('退出失败', 461);

        $emchat = new Emchat_Lite();
        $emchat->deleteChatRoom($room_info['chatroom_id']);
        $chatroom_users_key = "pk_room_" . $room_info['id'] . "_user_list";

        //清空用户列表
        DI()->redis->del($chatroom_users_key);
        DI()->redis->del('pk_room_list');

        $chatroom_key = "pk_chatroom_" . $room_info['id'];
        DI()->redis->del($chatroom_key);

        return true;
    }

    public function userExit() {
        $room_info = $this->model_pk_room->getRoomInfoById($this->req['room_id']);
        $user_info = $this->checkUser();

        $redis_seat_key = 'pk_seat_data_room_id_'.$room_info['id'];
        $seat_data = DI()->redis->hGetAll($redis_seat_key);

        if($pool_id = array_search($user_info['id'], $seat_data)){
            DI()->redis->hDel($redis_seat_key, $pool_id);
        }

        //被T的人退出房间，再进入还能入座
        $redis_kick_list = 'pk_kick_list_room_id_'.$room_info['id'];
        //$been_kicked = DI()->redis->sIsMember($redis_kick_list, $user_info['id']);
        DI()->redis->sRem($redis_kick_list, $user_info['id']);

        $chatroom_users_key = "pk_room_" . $room_info['id'] . "_user_list";
        $chatroom_user_list = DI()->redis->get_forever($chatroom_users_key);

        if (empty($chatroom_user_list)) {
            return true;
        } else {
            $redis_user_list = json_decode($chatroom_user_list, true);
            if (array_key_exists($user_info['id'], $redis_user_list)) {
                unset($redis_user_list[$user_info['id']]);
                DI()->redis->set_forever($chatroom_users_key, json_encode($redis_user_list));
            }
            //sort($redis_user_list);
            return true;
        }
    }

    public function downSeat() {
        $room_id = (int)$this->req['room_id'];
        $user_id = (int)$this->req['user_id'];
        $pool_id = (int)$this->req['pool_id'];

        $redis_key = 'pk_seat_data_room_id_'.$room_id;

        $seat_data = DI()->redis->hGetAll($redis_key);


        $redis_kick_list = 'pk_kick_list_room_id_'.$room_id;
        $been_kicked = DI()->redis->sIsMember($redis_kick_list, $user_id);
        if($been_kicked) throw new PhalApi_Exception('被踢过，不能再上座', 405);

        if(in_array($user_id, $seat_data)) throw new PhalApi_Exception('一个人只能坐一个位置', 403);

        if(!empty($seat_data[$pool_id])) throw new PhalApi_Exception('此座位有人', 402);

        $res = DI()->redis->hSet($redis_key, $pool_id, $user_id);

        if(!$res) throw new PhalApi_Exception('入座失败', 401);

        return true;

    }

    public function upSeat() {
        $room_id = (int)$this->req['room_id'];
        $user_id = (int)$this->req['user_id'];
        $pool_id = (int)$this->req['pool_id'];

        $redis_key = 'pk_seat_data_room_id_'.$room_id;
        $res = DI()->redis->hDel($redis_key, $pool_id);

        if(!$res) throw new PhalApi_Exception('退座失败', 402);

        return true;
    }

    public function forceUpSeat() {
        $room_id = (int)$this->req['room_id'];
        $owner_id = (int)$this->req['owner_id'];
        $user_id = (int)$this->req['user_id'];
        $pool_id = (int)$this->req['pool_id'];

        $room_info = $this->model_pk_room->getRoomInfoById($this->req['room_id']);
        if(empty($room_info) || $owner_id != $room_info['owner_id']) {
            throw new PhalApi_Exception('没有踢人权限', 403);
        }

        $redis_kick_list = 'pk_kick_list_room_id_'.$room_id;
        DI()->redis->sAdd($redis_kick_list, $user_id, 10800);

        $redis_key = 'pk_seat_data_room_id_'.$room_id;
        $res = DI()->redis->hDel($redis_key, $pool_id);

        if(!$res) throw new PhalApi_Exception('踢人失败', 402);

        return true;
    }

    public function getLiveUserList($pre_user_list, $user) {

        //一、直播间的头像排序，前面10个用户送得最多排在前面，再根据vip等级
        //二、原理：使用array_slice截取，再拼接
        $ten_user = array_slice($pre_user_list, 0, 10, true);
        $i = 0;

        foreach($ten_user as $per_user) {
            if($per_user['send_num'] <= 0) {
                if($user['vip_level'] >= $per_user['vip_level']) break;
            } else {
                $per_user['send_num'] = isset($per_user['send_num']) ? $per_user['send_num'] : 0;
                if($user['send_num'] >= $per_user['send_num']) break;
            }
            $i++;
        }

        $user_before = array_slice($pre_user_list, 0, $i, true);

        $user_before[$user['id']] = $user;
        $pre_num = count($pre_user_list);
        $user_after = array_slice($pre_user_list, $i, $pre_num - $i, true);

        $new_user_list = $user_before + $user_after;

        return $new_user_list;
    }

    public function getLastCardInfo($room_id) {
        $gid = 1;
        if($gid) {
            $model_card = new Model_Card();
            $last_loop_card_info = $model_card->getPkLastCard($room_id);
            if(empty($last_loop_card_info)) return NULL;


            $last_loop_card_info['gid'] = (string)$gid;

            $dealer_pool = 0;
            if($last_loop_card_info['dealer_id'] == $last_loop_card_info['pool1_user_id']) {
                $dealer_pool = 1;
            }
            if($last_loop_card_info['dealer_id'] == $last_loop_card_info['pool2_user_id']) {
                $dealer_pool = 2;
            }
            if($last_loop_card_info['dealer_id'] == $last_loop_card_info['pool3_user_id']) {
                $dealer_pool = 3;
            }
            if($last_loop_card_info['dealer_id'] == $last_loop_card_info['pool4_user_id']) {
                $dealer_pool = 4;
            }

            $domain_pk_game = new Domain_PkGame();
            $deal_card_info = $domain_pk_game->dealCardInfo($last_loop_card_info, $dealer_pool);

            $last_loop_card_info = array_merge($last_loop_card_info, $deal_card_info);

            //$last_loop_card_info['pool1_card'] = json_decode($last_loop_card_info['pool1_card'], true);
            //$last_loop_card_info['pool2_card'] = json_decode($last_loop_card_info['pool2_card'], true);
            //$last_loop_card_info['pool3_card'] = json_decode($last_loop_card_info['pool3_card'], true);
            //$last_loop_card_info['pool4_card'] = json_decode($last_loop_card_info['pool4_card'], true);

            $last_loop_card_info['current_server_time'] = round(microtime(true) * 1000) . '';

            $model_card = new Model_Card();
            $current_loop = $model_card->getRoomCurrentLoop($room_id);
            $last_loop_card_info['current_loop'] = $current_loop;

            return $last_loop_card_info;
        } else {
            return NULL;
        }

    }

    public function searchRoom() {
        $room_id = $this->req['room_id'];
        $model_pk = new Model_PkRoom();
        $room_info = $model_pk->getRoomInfoById($room_id);
        return $room_info;
    }

    public function shareRoom() {

        $model_room = new Model_PkRoom();
        $room_info = $model_room->getRoomInfoById($this->req['room_id']);
        if (empty($room_info)) throw new PhalApi_Exception('房间不存在', 455);


        $user_domain = new Domain_User();
        $user_info = $user_domain->getUsersInfoById($this->req['user_id']);
        if (empty($user_info)) throw new PhalApi_Exception_BadRequest('用户名不存在', 56);

        $owner_info = $user_domain->getUsersInfoById($room_info['owner_id']);


        $logo_url = "https://mmbiz.qlogo.cn/mmbiz/5A0v82JgqJaP4Y1kbXZiaz8ibbEPNIPQnxYtuAmdgeO1de4EVNkcFWyibwEob2EibsA8kX9gf8sea4WKfRc4ibeTmMA/0?wx_fmt=png";
        $share_url = "http://web.mibolive.com/wap/share/pk.php?id=".$room_info['id'];

        $title = '您的好友邀请您玩游戏';

        $desc = "「 ". $user_info['nick_name'] ."」邀请你到直播PK场 房间号：[".$room_info['id']."]  密码：[".
                $room_info['password']."]【米播】直播真人PK场，大战8局，随机庄，更起劲，速度来啊！";

        $rs = array(
            'user_id'     => $this->req['user_id'],
            'room_id' => $this->req['room_id'],
            //'imgUrl'  => $room_info['cover_img'],
            'imgUrl'  => 'http://image.cdn.mibolive.com/img/pub/20170210/589d8028c6555.jpg',
            'logoUrl' => $logo_url,
            'link'    => $share_url,
            'title'   => $title,
            'desc'    => $desc,
            'type'    => $this->req['share_type'],
        );
        return $rs;
    }



}