<?php

class Model_PkRoom extends PhalApi_Model_NotORM {

    protected function getTableName($id) {
        return 'pk_room';
    }

    public function getRoomInfoById($id) {

        $room_info = DI()->redis->get_time('room_info_id_'.$id);
        if(!empty($room_info)) return $room_info;

        $room_info = $this->getORM()->where('id', $id)->fetchOne();

        $room_info = !empty($room_info) ? $room_info : NULL;

        if(!empty($room_info)) {
            DI()->redis->set_time('room_info_id_'.$room_info['id'], $room_info);
        }
        return $room_info;
    }

    public function getRoomList($page_no = 1, $page_size = 20) {
        $offset = ($page_no - 1) * $page_size;

        $room_list = $this->getORM()->where('status = 2')->order('id desc')->limit($offset, $page_size)->fetchAll();
        return !empty($room_list) ? $room_list : [];
    }


}