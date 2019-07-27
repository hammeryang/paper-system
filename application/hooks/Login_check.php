<?php
/**
 * 用户登录验证，所有不是以login开头的路由都要先登录
 */
class Login_check
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function user_check()
    {

        echo "dddd";die;
        $this->CI->load->helper('url');
        if (!preg_match("/login.*/i", uri_string())) {
            if (!$this->CI->session->userdata('username')) {
                redirect('login/index');
            }
        }
    }
}
