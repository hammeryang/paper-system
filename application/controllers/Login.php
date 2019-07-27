<?php
class Login extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache');
    }
    public function login_password(){

        $data = $this->json_input();
        $name = $data['name'];
        $this->cache->redis->save('user', $name, 60);

        if(!$this->cache->redis->get("$name")){
            $this->json_output(array("登陆成功！"));
        }


    }
}