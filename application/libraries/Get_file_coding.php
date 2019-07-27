<?php
define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));

/**
 * @Class ： Get_file_coding
 * @Notes:  判断字符串的编码格式
 * @Author: yangshuhua
 * @Time: 2019/7/17 0017   上午 10:35
 * @link
 */
class Get_file_coding
{
    /**
     * @Notes:  判断编码类型
     * @Function detect_utf_encoding
     * @param $text
     * @return string
     */
    function detect_utf_encoding($text)
    {
        $first2 = substr($text, 0, 2);
        $first3 = substr($text, 0, 3);
        $first4 = substr($text, 0, 3);

        if ($first3 == UTF8_BOM) return 'UTF-8';
        elseif ($first4 == UTF32_BIG_ENDIAN_BOM) return 'UTF-32BE';
        elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) return 'UTF-32LE';
        elseif ($first2 == UTF16_BIG_ENDIAN_BOM) return 'UTF-16BE';
        elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) return 'UTF-16LE';
    }

    /**
     * @Notes:  返回编码格式
     * @Function getFileEncoding
     * @param $str
     * @return false|string
     */
    function getFileEncoding($str)
    {
        $encoding = mb_detect_encoding($str);
        if (empty($encoding)) {
            $encoding = detect_utf_encoding($str);
        }
        return $encoding;
    }
}