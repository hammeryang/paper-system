<?php

defined('BASEPATH') OR exit('No direct script access allowed');
define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));

require 'Test.php';

class Welcome extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array("Office", "Upload"));

    }

    public function index()
    {
        $allow_origin = array(
            'http://localhost:8080',
            'http://192.168.0.115:8081',
            'http://192.168.0.101:8080'
        );
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

//        if (in_array($origin, $allow_origin)) {
//            header('Access-Control-Allow-Origin:' . $origin);
//        }
        header('Access-Control-Max-Age:86400');
        header('Access-Control-Allow-Credentials:true');
        header("Access-Control-Allow-Headers: Authenticate,Origin, X-Requested-With, Content-Type, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET,POST,OPTIONS,PUT,DELETE");
        header('content-type: application/json;charset=utf-8');
        header("Access-Control-Allow-Origin:*");
        $data = array("name"=>"success");
        $res = json_encode($data, JSON_UNESCAPED_UNICODE);//将结果转换成json字符串
        $this->output->append_output($res);//数据传出
    }

}
