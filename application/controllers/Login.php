<?php
class Login extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache');
    }
    public function login_password(){

        $data = $this->json_input();
        $name = "admin";//$data['name'];
        $this->cache->redis->save('user', $name, 20);

        if(!$this->cache->redis->get("$name")){
            $this->json_output(array("登陆成功！"));
        }


    }
}