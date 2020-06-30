<?php
namespace app\lib\speed;
use ReflectionClass;

class Dump{
    
    private function dumpString($param) {

        $str = sprintf("<small style='color: #333;font-weight: bold'>string</small> <font color='#cc0000'>'%s'</font> <i>(length=%d)</i>",htmlspecialchars(chkCode($param)),strlen($param));
        echo $str;
    }

    private function dumpArr($param,$i=0) {

        $len = count($param);
        $space='';
        for($m=0;$m<$i;$m++)
            $space.= "    ";
        $i++;
        echo "<b style='color: #333;'>array</b> <i style='color: #333;'>(size=$len)</i> \r\n";
        if($len===0)
            echo $space."  <i><font color=\"#888a85\">empty</font></i> \r\n";
        foreach($param as $key=>$val) {
            echo $space.sprintf("<i style='color: #333;'> %s </i><font color='#888a85'>=&gt;",$key);
            $this->dumpType($val,$i);
            echo "</font> \r\n";
        }
        //echo "\r\n";
    }

    private function dumpObj($param,$i=0) {
        $className = get_class($param);
        if($className=='stdClass'&&$result=json_encode($param)){
            $this->dumpArr(json_decode($result,true),$i);
            return;
        }
        static $objId = 1;
        echo "<b style='color: #333;'>Object</b> <i style='color: #333;'>$className</i>";
        $objId++;
        $this->dumpProp($param,$className,$objId);

    }
    public  function dumpProp($obj,$className,$num)
    {
        if($className==get_class($obj)&&$num>2)return;
        static $pads = [];
        $reflect = new ReflectionClass($obj);
        $prop = $reflect->getProperties();

        $len = count($prop);
        echo "<i style='color: #333;'> (size=$len)</i>";
        array_push($pads, "    ");
        for ($i = 0; $i < $len; $i++) {
            $index = $i;

            $prop[$index]->setAccessible(true);
            $prop_name = $prop[$index]->getName();
            echo "\n", implode('', $pads),sprintf("<i style='color: #333;'> %s </i><font color='#888a85'>=&gt;",$prop_name);
            $this->dumpType($prop[$index]->getValue($obj),$num);
        }
        array_pop($pads);
    }
    public function dumpType($param,$i=0){
        switch(gettype($param)) {
            case 'NULL' :
                echo '<font color=\'#3465a4\'>null</font>';
                break;
            case 'boolean' :
                echo "<small style='color: #333;font-weight: bold'>boolean</small> <font color='#75507b'>".($param?'true':'false')."</font>";
                break;
            case 'integer' :
                echo "<small style='color: #333;font-weight: bold'>int</small> <font color='#4e9a06'>$param</font>";
                break;
            case 'double' :
                echo "<small style='color: #333;font-weight: bold'>float</small> <font color='#f57900'>$param</font>";
                break;
            case 'string' :
                $this->dumpString($param);
                break;
            case 'array' :
                $this->dumpArr($param,$i);
                break;
            case 'object' :
                $this->dumpObj($param,$i);
                break;
            case 'resource' :
                echo '<font color=\'#3465a4\'>resource</font>';
                break;
            default :
                echo '<font color=\'#3465a4\'>unknow type</font>';
                break;
        }
    }

}