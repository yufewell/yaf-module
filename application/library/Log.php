<?php


class Log {

    protected static $baseLogDir   =   APPLICATION_PATH . '/log';

    /**
     * [write description]
     * @param  string $msg  [description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public static function write($msg = 'System Log', $data = [], $level = '') {
        $now = date('Y-m-d H:i:s');
        $destination = self::getLogFile();

        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }  

        $dataStr = '';
        if (is_array($data) && !empty($data)) {
            foreach ($data as $key => $value) {
                if (!is_string($value)) {
                    $value = json_encode($value);
                }
                $dataStr .= $key . ' : "' . $value . '", ';
            }
        }
        
        $dataStr = rtrim($dataStr, ', ');
        $text = $msg. ', Data: [' . $dataStr .']';  
        if (!empty($level)) {
            $text = $level . ': '. $text;
        }

        self::writeFileLog($destination, "[{$now}] ".$_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_URI']."\r\n{$text}\r\n");
    }

    /**
     * [writeFileLog description]
     * @param  string $destination [description]
     * @param  string $content     [description]
     * @return [type]              [description]
     */
    public static function writeFileLog($destination = '', $content = 'default message') {
        error_log($content, 3, $destination);
    }

    /**
     * 日志文件路径
     */
    private static function getLogFile() {
        return self::$baseLogDir . date('/y_m/d') . '.log';
    }

}