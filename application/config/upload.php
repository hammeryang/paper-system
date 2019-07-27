<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//上传的参数配置
$config['upload_path'] = './uploads/';
$config['allowed_types'] = 'gif|png|jpg|jpeg|xls|xlsx';
$config['max_size'] = 200000;
$config['max_width'] = '10240';
$config['max_height'] = '7680';
$config['encrypt_name'] = True;
