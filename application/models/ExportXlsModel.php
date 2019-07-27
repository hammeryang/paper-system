<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportXlsModel extends CI_Model{
    /**
     * @Notes:
     * @Function export_excel
     * @param $data
     * @param string $title
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export_excel($data, $title = '未命名.xlsx') {
        // var_dump($data);
        ini_set ('memory_limit', '1024M');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data as $key1=>$sub_data) { //列
            foreach ($sub_data as $key2=>$item) { //行
                $sheet->setCellValueExplicitByColumnAndRow($key2+1, $key1+1,$item,'s');
            }
        }
        unset($data);
        $writer = new Xlsx($spreadsheet);
        unset($spreadsheet);
//      $writer->save($title);
        header("Pragma: public");
        header("Expires: 0");
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:content-type');
        header('Access-Control-Allow-Credentials:true');
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$title");
        header("Content-Transfer-Encoding:binary");
        $writer->save('php://output');
        exit();
    }
}