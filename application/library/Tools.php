<?php

class Tools
{
    /**
     * [returnJson description]
     * @param  Yaf_Response_Abstract $response [description]
     * @param  array                 $outArr   [description]
     * @return [type]                          [description]
     */
    public static function returnJson(Yaf_Response_Abstract $response, array $outArr) {
        $outputJson = json_encode($outArr);
        $response->setHeader( 'Content-Type', 'Application/json');
        $response->setBody($outputJson);
        $response->response();
    }

    /**
     * [checkSign description]
     * @param  array  $checkArr [description]
     * @return [type]           [description]
     */
    public static function checkSign(array $checkArr) {
        $paramStr = '';
        ksort($checkArr);
        foreach ($checkArr as $key => $param) {
            if (empty($param)) {
                return false;
            }
            if ($key == 'sign') {
                continue;
            }
            $paramStr .= $param;
        }
        $signKey = Yaf_Registry::get('config')->signKey;

        return (md5(substr(md5($paramStr), 0, 20) . $signKey) === $checkArr['sign']);
    }

    /**
     * 检查时间戳格式,默认48小时之外为错误
     * @param  [type] $timestamp [description]
     * @return [type]            [description]
     */
    public static function checkTimestamp($timestamp) {
        $timestamp = (int) $timestamp;
        return $timestamp < strtotime('-1 day') || $timestamp > strtotime('+1 day') ? 
                false : 
                (strtotime(date('Y-m-d H:i:s', $timestamp)) === $timestamp);
    }
}