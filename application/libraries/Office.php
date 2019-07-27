<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: ZS
 * Date: 2019/7/11
 * Time: 14:49
 */
require 'vendor/autoload.php';


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

define('EXPORT_DEFAULT', 1);
define('EXPORT_SAVE', 0);
define('EXPORT_DOWN', 1);
define('EXPORT_SAVE_DOWN', 2);

class Office
{
    /**
     * 读取excel里面的内容保存为数组
     * @param string $file_path
     * @param array $read_column
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function read($file_path = '/', $read_column = array())
    {
        $reader = IOFactory::createReader('Xlsx');

        $reader->setReadDataOnly(TRUE);

        //载入excel表格
        $spreadsheet = $reader->load($file_path);

        // 读取第一個工作表
        $sheet = $spreadsheet->getSheet(0);

        // 取得总行数
        $highest_row = $sheet->getHighestRow();

        // 取得总列数
        $highest_column = $sheet->getHighestColumn();

        //读取内容
        $data_origin = array();
        $data = array();
        for ($row = 2; $row <= $highest_row; $row++) { //行号从2开始
            for ($column = 'A'; $column <= $highest_column; $column++) { //列数是以A列开始

                $str = $sheet->getCell($column . $row)->getValue();
                //保存该行的所有列
                $data_origin[$column] = $str;
            }
            //取出指定的数据
            foreach ($read_column as $key => $val) {
                $data[$row - 2][$val] = $data_origin[$key];
            }
        }
        return $data;
    }

    /**
     * 导出excel表并保存到服务器
     * @param array $title 标题行名称
     * @param array $data 导出数据
     * @param string $file_name 文件名
     * @param string $save_path 保存路径
     * @param int $options 下载或保存
     * @return string   返回文件全路径
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export($title = array(), $data = array(), $file_name = '', $save_path = './', $options = 0)
    {
        //实例化类
        $spreadsheet = new Spreadsheet();

        //横向单元格标识
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        //设置sheet名称
        $spreadsheet->getActiveSheet(0)->setTitle('sheet1');
        //设置纵向单元格标识
        $_row = 1;
        if ($title) {
            $_cnt = count($title);
            $spreadsheet->getActiveSheet(0)->mergeCells('A' . $_row . ':' . $cellName[$_cnt - 1] . $_row);   //合并单元格
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('A' . $_row, '数据导出：' . date('Y-m-d H:i:s'));  //设置合并后的单元格内容
            $_row++;
            $i = 0;
            foreach ($title AS $v) {   //设置列标题
                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $_row, $v);
                $i++;
            }
            $_row++;
        }
        //填写数据
        if ($data) {
            $i = 0;
            foreach ($data AS $_v) {
                $j = 0;
                foreach ($_v AS $_cell) {
                    $spreadsheet->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + $_row), $_cell);
                    $j++;
                }
                $i++;
            }
        }
        //文件名处理
        if (!$file_name) {
            $file_name = uniqid(time(), TRUE);
        }
        $writer = new Xlsx($spreadsheet);
        if ($options == 1) {   //网页下载
            header('pragma:public');
            header("Content-Disposition:attachment;filename = $file_name.xlsx");
            $writer->save('php://output');
        } else if ($options == 0) {
            $file_name = iconv("utf-8", "gb2312", $file_name);   //转码
            $save_path = $save_path . $file_name . '.xlsx';
            $writer->save($save_path);
            return $file_name . '.xlsx';
        } else if ($options == 2) {
            header('pragma:public');
            header("Content-Disposition:attachment;filename = $file_name.xlsx");
            $writer->save('php://output');
            $file_name = iconv("utf-8", "gb2312", $file_name);   //转码
            $save_path = $save_path . $file_name . '.xlsx';
            $writer->save($save_path);
        }

        //删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    }
}
