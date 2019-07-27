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
        $a = $this->CI->uri->segment(1);
        $a = $this->CI->uri->segment(2);
        $a = $this->CI->uri->segment(3);
        echo "<script>alert('调用钩子函数成功!;')</script>";
        var_dump($a);

    }
}
