<?php

class Upload
{
    function __construct()
    {
        echo "构造函数是调用";
        die;
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
}