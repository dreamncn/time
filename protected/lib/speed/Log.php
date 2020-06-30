<?php

namespace app;
class Log
{
    private $handler = null;
    private $level = 15;


    public function __construct($file = '', $level = 15)
    {
        $dir_name=dirname($file);
        //目录不存在就创建
        if(!file_exists($dir_name))
        {

            $this->mkdirs($dir_name);
        }
        $this->handler = fopen($file, 'a');
        $this->level = $level;
    }

    public function __destruct()
    {
        fclose($this->handler);
    }

    public function DEBUG($msg)
    {
        $this->write(1, $msg);
    }

    /**
     * @param $level
     * @param $msg
     */
    protected function write($level, $msg)
    {
        $msg = '[' . date('Y-m-d H:i:s') . '][' . $this->getLevelStr($level) . '] ' . $msg . "\n";
        flock($this->handler, LOCK_EX);
        fwrite($this->handler, $msg, strlen($msg));
        flock($this->handler, LOCK_UN);
    }

    private function getLevelStr($level)
    {
        switch ($level) {
            case 1:
                return 'debug';
                break;
            case 2:
                return 'info';
                break;
            case 4:
                return 'warn';
                break;
            case 8:
                return 'error';
                break;
            default:
                return 'debug';
        }
    }

    public function WARN($msg)
    {
        $this->write(4, $msg);
    }

    public function ERROR($msg)
    {
        $debugInfo = debug_backtrace();
        $stack = "[";
        foreach ($debugInfo as $key => $val) {
            if (array_key_exists("file", $val)) {
                $stack .= ",file:" . $val["file"];
            }
            if (array_key_exists("line", $val)) {
                $stack .= ",line:" . $val["line"];
            }
            if (array_key_exists("function", $val)) {
                $stack .= ",function:" . $val["function"];
            }
        }
        $stack .= "]";
        $this->write(8, $stack . $msg);
    }

    public function INFO($msg)
    {
        $this->write(2, $msg);
    }
    public function mkdirs($dir)
    {
        if(is_dir(dirname($dir))){
            mkdir($dir);
        }else  $this->mkdirs(dirname($dir));

    }
}
