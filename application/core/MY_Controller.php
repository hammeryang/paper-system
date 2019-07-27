<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct($need_login = FALSE)
    {
        parent::__construct();
//        $this->output->enable_profiler(TRUE);
//        登录验证 获取访问url 判断 是否是 Login/ login_password 、login_mobile 、login_code
//        if (!$this->login_verify()) {
//            //用户未登录则将请求转发到登录信息提示请求
//            redirect('/Redirect_out/login_return/');
//        }

//        $request_headers = $this->getallheaders();
//        $allow_origin = array(
//            'http://localhost:8080',
//            'http://192.168.0.115:8081',
//            'http://192.168.0.101:8080'
//        );
//        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
//
//        if (in_array($origin, $allow_origin)) {
//            header('Access-Control-Allow-Origin:' . $origin);
//        }
//        header('Access-Control-Max-Age:86400');
//        header('Access-Control-Allow-Credentials:true');
//        header("Access-Control-Allow-Headers: Authenticate,Origin, X-Requested-With, Content-Type, Access-Control-Request-Method");
//        header("Access-Control-Allow-Methods: GET,POST,OPTIONS,PUT,DELETE");
//        header('content-type: application/json;charset=utf-8');
//        header("Access-Control-Allow-Origin:*");
    }

    /**
     * @Notes: 登录验证
     * @Function login_verify
     * @return bool
     */
    public function login_verify()
    {
        $status = TRUE;
        $get_url = $this->uri->segment_array();
        $sign = in_array($get_url[2], array("login_password", "login_mobile", "login_code"));
        if ($get_url[1] === "Login" && $sign) {
            //对登录路径放行
        } else {//判断用户是否登录
            if ($this->session->userdata('user') == null) {
                $status = FALSE;
            }
        }
        return $status;
    }


    public function getallheaders()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    //是否登录
    public function is_login()
    {
        $check = FALSE;
        if ($this->session->userdata('user_id') !== NULL) {
            $check = TRUE;
        }
        return $check;
    }

    public function is_session()
    {
        $check = FALSE;
        if ($this->session->userdata('user_id') !== NULL) {
            $check = TRUE;
        } else if ($this->input->get('token')) {

        }
        return $check;
    }

    //默认方法获取方法
    public function get_post($items = NULL)
    {
        $post = json_decode(file_get_contents('php://input'), TRUE);
        $value = '';
        if (isset($post[$items])) {
            $value = $this->security->xss_clean($post[$items]);
        }

        return $value;
    }

    public function get_user_data()
    {
        $this->load->library(array('encryption'));
        $headers = $this->common->get_all_headers();//获得头信息
        //  print_r($headers);
        $open_value = NULL;
        if (isset($headers['authenticate'])) {

            $client_sign = $headers['authenticate'];//客服端识别码

            if (!empty($client_sign)) {
                $session_id = $this->encryption->decrypt($client_sign);//潜在的判断错误

                if ($session_id) {

                    if ($this->common->set_session($session_id) != -1) {//设置缓存
//                      $this->config->set_item('sess_expiration', 432000);//秒

                        $open_value = $this->session->userdata('server_sign');//获得openvalue

                    }
                }
            }
        }

        return $open_value;
    }

    public function set_header()
    {

    }

    //$base=array(class name, function name, others...),for search, can use blank space as reserved word
    //$multi_order=array(array(uri segment order, default order, array(check order), default order: type asc 0 desc 1, array(default order type)),...)
    //$page=array(uri segment page num, default per page, total rows)
    protected function order_pagination($base, $multi_order, $page, $fix = array())
    {
        $order_url = array();
        $order_model = array();
        $order_url_next = array();
        foreach ($multi_order as $order) {
            $order_key = $this->uri->segment($order[0], $order[1]);
            if (!in_array($order_key, $order[2], TRUE)) {
                $order_key = $order[1];
            }

            $order_type = $this->uri->segment($order[0] + 1, $order[3]);
            if (!($order_type == 0 || $order_type == 1)) {
                $order_type = 0;
            }

            $order_url[] = $order_key;
            $order_url[] = $order_type;
            $order_model[$order_key] = $order_type;

            $order_type_next = 0;
            if ($order_type == 0) {
                $order_type_next = 1;
            }

            if (count($order[2]) != count($order[4])) {
                $order[4] = array_fill(0, count($order[2]), 0);
            }

            $new_order_url_next = array();
            for ($i = 0; $i < count($order[2]); $i++) {
                $next_type = $order[4][$i];
                if (strcasecmp($order[2][$i], $order_key) == 0) {
                    $next_type = $order_type_next;
                }
                if (count($order_url_next) == 0) {
                    $new_order_url_next[] = array($order[2][$i], $next_type);
                } else {
                    foreach ($order_url_next as $url) {
                        $new_url = array_merge($url, array($order[2][$i], $next_type));
                        $new_order_url_next[] = $new_url;
                    }
                }
            }
            $order_url_next = $new_order_url_next;
        }

        $page_num = $this->uri->segment($page[0], 1);
        $this->load->helper('checkstr');
        if (!is_natural_number($page_num)) {
            $page_num = 1;
        }
        $offset = ($page_num - 1) * $page[1];
        $total_page = (int)($page[2] / $page[1]) + 1;

        $base_url = site_url(array_merge($base, $order_url));
        $this->load->helper('config_pagination');
        $this->load->library('pagination');
        $this->pagination->initialize(get_config_pagination($page, $base_url, $fix));
        $link_pagination = $this->pagination->create_links();

        $data["link_pagination"] = $link_pagination;
        $data["total_page"] = $total_page;
        $data["total_rows"] = $page[2];
        $data["per_page"] = $page[1];
        $data["page_num"] = $page_num;
        $data["offset"] = $offset;
        $data['order_model'] = $order_model;
        $data["order_url"] = $order_url_next;
        $data['order_base'] = $base;
        return $data;
    }

    /**
     *  获取当前时间
     */
    protected function get_current_timeStamp()
    {
        return date('Y-m-d H:i:s', time());
    }

    /**
     * 默认密码
     */
    protected function get_default_password()
    {
        return "123456";
    }

    /**
     * 工具函数格式化输出一个数组
     */
    function util($data)
    {
        echo "<pre>";
        print_r($data);
        echo "<pre>";
        echo die;
    }

    /**
     * @return array
     * 多文件上传
     */
    public function save_file()
    {
        $data = array();
        $i = 0;
        if (!empty($_FILES['file']['tmp_name'])) {
            foreach ($_FILES['file']['name'] as $key => $image) {
                //set $_FILES value
                $fileKey = "file";
                $fileKeyNew = "file_{$key}";
                $_FILES[$fileKeyNew] = array(
                    'name' => $_FILES[$fileKey]['name'][$key],
                    'type' => $_FILES[$fileKey]['type'][$key],
                    'tmp_name' => $_FILES[$fileKey]['tmp_name'][$key],
                    'error' => $_FILES[$fileKey]['error'][$key],
                    'size' => $_FILES[$fileKey]['size'][$key],
                );

                if ($this->upload->do_upload($fileKeyNew)) {
                    $uploadData = $this->upload->data();
                    $data['file_name'][$i++] = $uploadData['file_name'];
                } else {
                    $data['success'] = false;
                    $data['msg'] = $this->upload->display_errors();
                    return $data;
                }
            }
            $data['success'] = true;
        } else {
            $data['success'] = false;
        }
        return $data;
    }

    /*
    * 指定年，周，算出具体开始日期与结束日期;
    * $year 指定年;
    * $weeks 指定周;
    * 返回为一个关联数组 key ['start_date']开始日期, key['end_date'] 结束日期
     * 用法如计算2012年第7个周的开始与结否日期：$arr = getDays(2012,7);echo '<pre>';print_r($arr);
     */
    public function weekday($year, $weeks)
    {
        $arr_date = array();
        //$weeks > date('W',mktime(0,0,0,1,1,$year))  || ($year < 1970  || $year > 2038) ? exit('请输入正确的年或周') : true;
        $week_num = date('w', mktime(0, 0, 0, 1, 1, $year));

        switch ($week_num) {
            case 0 :
                $plus_day = 1;
                break;
            case 1 :
                $plus_day = 7;
                break;
            case 2 :
                $plus_day = 6;
                break;
            case 3 :
                $plus_day = 5;
                break;
            case 4 :
                $plus_day = 4;
                break;
            case 5 :
                $plus_day = 3;
                break;
            case 6 :
                $plus_day = 2;
                break;
        }

        $arr_date['end_date'] = date('Y-m-d', mktime(0, 0, 0, 1, (1 * $weeks * 7) + $plus_day, $year));
        $arr_date['start_date'] = date('Y-m-d', mktime(0, 0, 0, 1, (1 * $weeks * 7) + $plus_day - 6, $year));
        return $arr_date;
    }

    /**
     * 将某年某月划分为周返回每周的开始日期以及结束日期
     * 参数 year month
     */
    public function get_week($year, $month)
    {
        $date = array();
        if ($month == "01" || $month == "03" || $month == "05" || $month == "07" || $month == "08" || $month == "10" || $month == "12" || $month == "04" || $month == "06" || $month == "09" || $month == "11") {
            $date[1]["start"] = $year . "-" . $month . "-01";
            $date[1]["end"] = $year . "-" . $month . "-07";
            for ($i = 2; $i < 5; $i++) {
                $date[$i]["start"] = date("Y-m-d", strtotime("+1 day", strtotime($date[$i - 1]["end"])));
                $date[$i]["end"] = date("Y-m-d", strtotime("+6 day", strtotime($date[$i]["start"])));
            }
            if ($month == "04" || $month == "06" || $month == "09" || $month == "11") {
                $date[5]["start"] = date("Y-m-d", strtotime("+1 day", strtotime($date[4]["end"])));
                $date[5]["end"] = $year . "-" . $month . "-30";
            } else {
                $date[5]["start"] = date("Y-m-d", strtotime("+1 day", strtotime($date[4]["end"])));
                $date[5]["end"] = $year . "-" . $month . "-31";
            }
        }
        if ($month == "02") {
            if (date("t", mktime(20, 20, 20, 2, 1, $year)) == 29) {//闰年
                $date[1]["start"] = $year . "-" . $month . "-01";
                $date[1]["end"] = $year . "-" . $month . "-07";
                for ($i = 2; $i < 5; $i++) {
                    $date[$i]["start"] = date("Y-m-d", strtotime("+1 day", strtotime($date[$i - 1]["end"])));
                    $date[$i]["end"] = date("Y-m-d", strtotime("+6 day", strtotime($date[$i]["start"])));
                }
            } else {
                $date[1]["start"] = $year . "-" . $month . "-01";
                $date[1]["end"] = $year . "-" . $month . "-07";
                for ($i = 2; $i < 5; $i++) {
                    $date[$i]["start"] = date("Y-m-d", strtotime("+1 day", strtotime($date[$i - 1]["end"])));
                    $date[$i]["end"] = date("Y-m-d", strtotime("+6 day", strtotime($date[$i]["start"])));
                }
                $date[5]["start"] = date("Y-m-d", strtotime("+1 day", strtotime($date[4]["end"])));
                $date[5]["end"] = $year . "-" . $month . "-29";
            }
        }
        return $date;
    }

    /**
     * 将excel表中的数据转换为数组
     * @param $filePath
     * @param $data
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function excel_fileput($filePath, $data)
    {
        $this->load->library("PHPExcel/Classes/PHPExcel");//ci框架中引入excel类
        $PHPExcel = new PHPExcel();
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        // 加载excel文件
        $PHPExcel = $PHPReader->load($filePath);

        // 读取excel文件中的第一个工作表
        $currentSheet = $PHPExcel->getSheet(0);
        // 取得最大的列号
        $allColumn = $currentSheet->getHighestColumn();
        // 取得一共有多少行
        $allRow = $currentSheet->getHighestRow();
        // 从第二行开始输出，因为excel表中第一行为提示信息
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            /**从第A列开始输出*/
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65, $currentRow)->getValue();
                $data1[$currentColumn] = $val;
            }
            foreach ($data as $key => $val) {
                $data2[$currentRow - 2]["$val"] = $data1[$key];
            }
        }
        return $data2;
    }


    /**
     * 计算某日是这一年的第几周
     * @param $date [description]日期 例如:'2017-01-01'
     * @return int
     */
    public function get_weeks_num($date)
    {
        $time = strtotime($date);
        $month = intval(date('m', $time));//当前时间的月份
        $fyear = strtotime(date('Y-01-01', $time));//今年第一天时间戳
        $fdate = intval(date('N', $fyear));//今年第一天 周几
        $sysweek = intval(date('W', $time));//系统时间的第几周
        //大于等于52 且 当前月为1时， 返回1
        if (($sysweek >= 52 && $month == 1)) {
            return 1;
        }/* else if ($fdate == 1) {
            //如果今年的第一天是周一,返回系统时间第几周
            return $sysweek;
        }*/ else {
            //返回系统周+1
            return $sysweek;
        }
    }

    /**
     * 将二维数组根据某个字段排序
     * @param $array [description]要排序的数组
     * @param $keys [description]要排序的键字段
     * @param int $sort [description]排序类型  SORT_ASC 按照上升顺序排序， SORT_DESC 按照下降顺序排序
     * @return mixed    [description]排序后的数组
     */
    function sort_array($array, $keys, $sort = 0)
    {
        $keysValue = array();
        foreach ($array as $k => $v) {//遍历二维数组，选取二维数组中的某一列
            $keysValue[$k] = $v[$keys];
        }
        if ($sort == 0) {
            $sort = SORT_DESC;
        } else if ($sort == 1) {
            $sort = SORT_ASC;
        } else {
            $sort = SORT_DESC;
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    /**
     * 将数组分页显示(array_slice 函数)
     * @param $array [description]分页的数组
     * @param $page_size [description]每页多少条数据
     * @param $current_page [description]当前第几页
     * @return array
     */
    function page_array($array, $page_size, $current_page)
    {
        global $countPage; //定全局变量
        $current_page = (empty($current_page)) ? '1' : $current_page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($current_page - 1) * $page_size; //计算每次分页的开始位置
        $totals = count($array);//计算要分页数组的总元素个数
        $page_size = (empty($page_size)) ? $totals : $page_size;//判断当前页面显示个数是否为空，如果为空，则返回所有数据
        $countPage = ceil($totals / $page_size); //计算总页面数
        $pageData = array_slice($array, $start, $page_size);//返回根据 $start 和 $page_size 参数所指定的 array 数组中的一段序列。
        return $pageData;  //返回当前页面数据
    }

    /**
     * 毫秒时间戳转日期
     */
    public function get_msec_to_mescdate($msectime)
    {
        $msectime = $msectime * 0.001;
        if (strstr($msectime, '.')) {
            sprintf("%01.3f", $msectime);
            list($usec, $sec) = explode(".", $msectime);
            $sec = str_pad($sec, 3, "0", STR_PAD_RIGHT);
        } else {
            $usec = $msectime;
            $sec = "000";
        }
        $date = date("Y-m-d H:i:s.x", $usec);
        $mescdate = str_replace('x', $sec, $date);
        return $mescdate;
    }

    /**
     * 验证身份证是否合法
     * @param $id_card
     * @return bool
     */
    function check_id_card($id_card)
    {

        // 只能是18位
        if (strlen($id_card) != 18) {
            return false;
        }

        // 取出本体码
        $id_card_base = substr($id_card, 0, 17);

        // 取出校验码
        $verify_code = substr($id_card, 17, 1);

        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        // 校验码对应值
        $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        // 根据前17位计算校验码
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += substr($id_card_base, $i, 1) * $factor[$i];
        }

        // 取模
        $mod = $total % 11;

        // 比较校验码
        if ($verify_code == $verify_code_list[$mod]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 将接受的json数据转换成数组
     */
    public function json_input()
    {
        $json_string = file_get_contents("php://input");//获取发送过来的数据流
        $data_json = json_decode($json_string, true);//将json转为数组Array
        return $data_json;
    }

    /**
     * 将数据转换成json并输出
     * @param $data
     */
    public function json_output($data)
    {
        $res = json_encode($data, JSON_UNESCAPED_UNICODE);//将结果转换成json字符串
        $this->output->append_output($res);//数据传出
    }

    /**
     * 将二维数组分组
     * @param $arr
     * @param $key
     * @return array
     */
    function array_group_by($arr, $key)
    {
        $grouped = [];
        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }
        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $parms);
            }
        }
        return $grouped;
    }

    /****************************************执行结果状态返回*****开始*********************************************************
     * /**
     * @Notes: 添加返回状态信息
     * @Function return_msg_add
     * @param $sign
     * @return mixed
     * @Author: yangshuhua
     * @Time: 2019/7/2 0002   上午 9:17
     */
    function return_msg_add($sign)
    {
        if ($sign !== FALSE) {
            $status_message = $this->config->item('success_add');
        } else {
            $status_message = $this->config->item('false_add');
        }
        return $status_message;
    }

    /**
     * @Notes:  删除操作返回信息
     * @Function return_msg_delete
     * @param $sign
     * @return mixed
     * @Author: yangshuhua
     * @Time: 2019/7/2 0002   上午 9:47
     */
    function return_msg_delete($sign)
    {
        if ($sign !== FALSE) {
            $status_message = $this->config->item('success_delete');
        } else {
            $status_message = $this->config->item('false_delete');
        }
        return $status_message;
    }

    /**
     * @Notes: 修改返回信息
     * @Function return_msg_update
     * @param $sign
     * @return mixed
     * @Author: yangshuhua
     * @Time: 2019/7/2 0002   上午 10:07
     */
    function return_msg_update($sign)
    {
        if ($sign !== FALSE) {
            $status_message = $this->config->item('success_update');
        } else {
            $status_message = $this->config->item('false_update');
        }
        return $status_message;
    }

    /**
     * @Notes:  发布结果返回
     * @Function send
     * @param $sign
     * @return mixed
     */
    function return_msg_send($sign)
    {
        if ($sign !== FALSE) {
            $status_message = $this->config->item('success_send');
        } else {
            $status_message = $this->config->item('false_send');
        }
        return $status_message;
    }

    /**
     * @Notes:   撤销状态返回
     * @Function send
     * @param $sign
     * @return mixed
     */
    function return_msg_revocation($sign)
    {
        if ($sign !== FALSE) {
            $status_message = $this->config->item('success_revocation');
        } else {
            $status_message = $this->config->item('false_revocation');
        }
        return $status_message;
    }

    /**
     * @Notes:  审核结果返回
     * @Function return_msg_audit
     * @param $sign
     * @return mixed
     */
    function return_msg_audit($sign)
    {
        if ($sign !== FALSE) {
            $status_message = $this->config->item('success_audit');
        } else {
            $status_message = $this->config->item('false_audit');
        }
        return $status_message;
    }

    /**
     * @Notes: 禁用结果状态返回
     * @Function return_msg_forbidden
     * @param $sign
     * @return mixed
     */
    function return_msg_forbidden($sign){
        if ($sign !== FALSE) {
            $status_message = $this->config->item('TRUE_FORBIDDEN');
        } else {
            $status_message = $this->config->item('FALSE_FORBIDDEN');
        }
        return $status_message;
    }

    function return_msg_start($sign){
        if ($sign !== FALSE) {
            $status_message = $this->config->item('TRUE_START_USING');
        } else {
            $status_message = $this->config->item('FALSE_START_USING');
        }
        return $status_message;
    }
    /****************************************执行结果状态返回*******结束*******************************************************/


    /*********************************查询类别替换辅助函数开始***************************************/
    /**
     * @Notes: 替换不同类型的参数
     * @Function replace
     * @param $data
     * @return mixed
     */
    public function replace($data, $type)
    {
        $this->load->model(array("MCategory"));
        for ($i = 0; $i < count($data); $i++) {
            $where = $this->fit_out_condition($data[$i], $type);//组装涉及到的类别查询条件
            $category = $this->MCategory->query_notLimit($where, array('id', 'category_name'));//查询类别信息
            $data[$i] = $this->fit_out_replace($data[$i], $category, $type);
        }
        return $data;
    }

    /**
     * @Notes: 组装查询涉及到了类别类型
     * @Function fit_out_condition
     * @param $data
     * @return array
     */
    public function fit_out_condition($data, $type)
    {
        $where = array();
        foreach ($type as $var) {
            if ($data[$var] != NULL) {
                $where = array_merge($where, array(array("id" => $data[$var])));
            }
        }
        return $where;
    }

    /**
     * @Notes:  替换类别名称
     * @Function fit_out_replace
     * @param $data
     * @param $category
     * @return mixed
     */
    public function fit_out_replace($data, $category, $type)
    {
        for ($i = 0; $i < count($category); $i++) {
            foreach ($type as $var) {
                if ($data[$var] != NULL && $data[$var] === $category[$i]['id']) {
                    $data[$var] = $category[$i]['category_name'];
                }
            }
        }
        return $data;
    }
    /*********************************类别替换辅助函数结束***************************************/

}
