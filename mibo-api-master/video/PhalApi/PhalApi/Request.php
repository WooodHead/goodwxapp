<?php

/**
 * PhalApi_Request 参数生成类
 * - 负责根据提供的参数规则，进行参数创建工作，并返回错误信息
 * - 需要与参数规则配合使用
 * @package     PhalApi\Request
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2014-10-02
 */
class PhalApi_Request {

    protected $data = array();

    protected $headers = array();

    /**
     * @param array $data 参数来源，可以为：$_GET/$_POST/$_REQUEST/自定义
     */
    public function __construct($data = NULL) {
        $this->data    = $this->genData($data);
        $this->headers = $this->getAllHeaders();
    }

    /**
     * 生成请求参数
     * 此生成过程便于项目根据不同的需要进行定制化参数的限制，如：
     * 如只允许接受POST数据，或者只接受GET方式的service参数，以及对称加密后的数据包等
     *
     * @param array $data 接口参数包
     *
     * @return array
     */
    protected function genData($data) {
        $file_name = PhalApi_Tool::getFileName();
        $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        if (!isset($data) || !is_array($data)) {
            //本地测试或者服务器自己请求自己如无人场发牌,或者是计划直接运行脚本就不用加密请求参数
            //入口文件不是index也是可以不加密的
            $ip = PhalApi_Tool::getClientIp();
            $ip_white_list = DI()->config->get('sys.ip_white_list');
            if(in_array($ip, $ip_white_list) ||
                $file_name != 'index' ||
                isset($_SERVER['SHELL']) ||
                $server_name == 'miboweb.yahalei.com' ||
                (DI()->debug && isset($_REQUEST['mibo']))) {

                return $_REQUEST;
            } else {
                //前端先把所有参数json格式化，再encrypt,再作为params的值传过来
                if(!isset($_REQUEST['params'])) {
                    throw new PhalApi_Exception('params error', 412);
                }
                DI()->mycrypt = new Mycrypt_Lite();
                $data = $_REQUEST['params'];
                $data = DI()->mycrypt->decrypt($data);
                //echo $data;exit;
                $data_arr = [];
                preg_match_all("/(?:\{)(.*)(?:\})/i",$data, $data_arr);
                //print_r($data_arr);exit;
                $data = $data_arr[0][0];
                $data = trim($data);
                $data = json_decode($data, true);
                return $data;
            }
        }

        return $data;
    }

    /**
     * 初始化请求Header头信息
     * @return array|false
     */
    protected function getAllHeaders() {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        //对没有getallheaders函数做处理
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (is_array($value) || substr($name, 0, 5) != 'HTTP_') {
                continue;
            }

            $headerKey = implode('-', array_map('ucwords', explode('_', strtolower(substr($name, 5)))));
            $headers[$headerKey] = $value;
        }

        return $headers;
    }

    /**
     * 获取请求Header参数
     *
     * @param string $key     Header-key值
     * @param mixed  $default 默认值
     *
     * @return string
     */
    public function getHeader($key, $default = NULL) {
        return isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    /**
     * 直接获取接口参数
     *
     * @param string $key     接口参数名字
     * @param mixed  $default 默认值
     *
     * @return Ambigous <unknown, multitype:>
     */
    public function get($key, $default = NULL) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * 根据规则获取参数
     * 根据提供的参数规则，进行参数创建工作，并返回错误信息
     *
     * @param $rule array('name' => '', 'type' => '', 'defalt' => ...) 参数规则
     *
     * @return mixed
     */
    public function getByRule($rule) {
        $rs = NULL;

        if (!isset($rule['name'])) {
            throw new PhalApi_Exception_InternalServerError(T('miss name for rule'));
        }

        $rs = PhalApi_Request_Var::format($rule['name'], $rule, $this->data);

        if ($rs === NULL && (isset($rule['require']) && $rule['require'])) {
            throw new PhalApi_Exception_BadRequest(T('{name} require, but miss', array('name' => $rule['name'])));
        }

        return $rs;
    }

    /**
     * 获取全部接口参数
     * @return array
     */
    public function getAll() {
        return $this->data;
    }
}