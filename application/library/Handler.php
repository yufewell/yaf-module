<?php

/**
 * 错误处理类
 */
class Handler
{
    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        $level = '';
        switch ($errno) {     
            case E_ERROR:               $level = "Error";                  break;
            case E_WARNING:             $level = "Warning";                break;
            case E_PARSE:               $level = "Parse Error";            break;
            case E_NOTICE:              $level = "Notice";                 break;
            case E_CORE_ERROR:          $level = "Core Error";             break;
            case E_CORE_WARNING:        $level = "Core Warning";           break;
            case E_COMPILE_ERROR:       $level = "Compile Error";          break;
            case E_COMPILE_WARNING:     $level = "Compile Warning";        break;
            case E_USER_ERROR:          $level = "User Error";             break;
            case E_USER_WARNING:        $level = "User Warning";           break;
            case E_USER_NOTICE:         $level = "User Notice";            break;
            case E_STRICT:              $level = "Strict Notice";          break;
            case E_RECOVERABLE_ERROR:   $level = "Recoverable Error";      break;
            default:                    $level = "Unknown error ($errno)"; break; 
        }

        $errorStr = "$errstr ".$errfile." 第 $errline 行";
        $environ = Yaf_Registry::get('environ');
        if ($environ == 'product') {
            header("HTTP/1.1 404 Not Found"); 
        } elseif ($environ == 'develop') {
            echo $errorStr;
        }
        Log::write($errorStr, $_REQUEST, $level);
    }

    /**
     * php中止时执行的函数
     * @return [type] [description]
     */
    public static function shutdownHandler(){
        $e = error_get_last();
        switch ($e['type']) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                self::errorHandler($e['type'],$e['message'],$e['file'],$e['line']);
                break;         
        }
    }

    /**
     * 异常处理
     * @param  [type] $e [description]
     * @return [type]    
     */
    public static function exceptionHandler($e) {
        self::errorHandler($e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine());
    }
}