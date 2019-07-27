<?php


class Test extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache');
    }

    public function test()
    {

        $data = $this->json_input();
        if (!$this->cache->redis->get("user")) {
            $this->json_output(array("未登录请从新登陆！"));
            return;
        }
        echo $data["name"]."已登录";
    }

    public function cookie_test()
    {
        $this->input->set_cookie("username", "sdfsdfdsfdsfdsf", 60);
    }

    public function cookie_get()
    {
        echo $this->input->cookie("username");//适用于控制器
    }

    /**
     *  验证函数
     */
    public function test_form()
    {
        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'Username', 'required');
        if ($this->form_validation->run() == FALSE)
        {
            echo "s";
            echo form_error('username', '<div class="error">', '</div>');

            //            $this->util($this->form_validation->error_array());
        }
        else
        {
            echo "ss";
        }
    }
}