<?php

namespace app\lib\speed\mvc;

use app\Error;

/**
 * Class View
 * @package lib\speed\mvc
 * 模板渲染输出类
 */
class View
{
    private $left_delimiter, $right_delimiter, $template_dir, $compile_dir;
    private $template_vals = array();

    /**
     * View constructor.
     * @param $template_dir
     * @param $compile_dir
     * @param string $left_delimiter
     * @param string $right_delimiter
     */
    public function __construct($template_dir, $compile_dir, $left_delimiter = '<{', $right_delimiter = '}>')
    {
        $this->left_delimiter = $left_delimiter;
        $this->right_delimiter = $right_delimiter;
        $this->template_dir = $template_dir;
        $this->compile_dir = $compile_dir;
    }

    /**
     * @param $tempalte_name
     * @return false|string
     */
    public function render($tempalte_name)
    {
        $complied_file = $this->compile($tempalte_name);
        if(isDebug()){
            logs('[Speed]Compile time-consuming: ' . strval((microtime(true) - $GLOBALS['display_start']) * 1000) . 'ms', 'info');
        }
        ob_start();
        $_view_obj = &$this;
        extract($this->template_vals, EXTR_SKIP);
        include $complied_file;
        return ob_get_clean();
    }

    /**
     * @param $template_name
     * @return string
     */
    public function compile($template_name)
    {
        GLOBAL $__module;
        $template_name = ($__module == '' ? '' : $__module . DS) . $template_name . '.html';
        $file = $this->template_dir . DS . $template_name;
        if (!file_exists($file))
            Error::err('Err: "' . $file . '" is not exists!');
        if (!is_writable($this->compile_dir) || !is_readable($this->compile_dir))
            Error::err('Err: Directory "' . $this->compile_dir . '" is not writable or readable');
        $complied_file = $this->compile_dir . DS . md5(realpath($file)) . '.' . filemtime($file) . '.' . basename($template_name) . '.php';
        if (file_exists($complied_file)){
            if(isDebug()){
                logs('[View]Find cache file "'.$template_name.'"','info');
                logs('[View]Return cache file "'.$template_name.'"','info');
            }
            return $complied_file;
        }


        $template_data = file_get_contents($file);
        $template_data = $this->_compile_struct($template_data);

        $template_data = $this->_compile_function($template_data);
        $template_data = '<?php use app\lib\speed\mvc; if(!class_exists("app\lib\speed\mvc\View", false)) exit("no direct access allowed");?>' . $template_data;
        $template_data = $this->_complie_script_get($template_data);
        $template_data = $this->_complie_script_put($template_data);
        $this->_clear_compliedfile($template_name);

        $tmp_file = $complied_file . uniqid('_tpl', true);
        if (!file_put_contents($tmp_file, $template_data)) Error::err('Err: File "' . $tmp_file . '" can not be generated.');

        $success = @rename($tmp_file, $complied_file);
        if (!$success) {
            if (is_file($complied_file)) @unlink($complied_file);
            $success = @rename($tmp_file, $complied_file);
        }
        if (!$success) Error::err('Err: File "' . $complied_file . '" can not be generated.');
        if(isDebug()){
            logs('[View]Complied  file "'.$template_name.'" successful!','info');
        }
        return $complied_file;
    }

    private function _compile_struct($template_data)
    {
        $foreach_inner_before = '<?php if(!empty($1)){ $_foreach_$3_counter = 0; $_foreach_$3_total = count($1);?>';
        $foreach_inner_after = '<?php $_foreach_$3_index = $_foreach_$3_counter;$_foreach_$3_iteration = $_foreach_$3_counter + 1;$_foreach_$3_first = ($_foreach_$3_counter == 0);$_foreach_$3_last = ($_foreach_$3_counter == $_foreach_$3_total - 1);$_foreach_$3_counter++;?>';
        $pattern_map = array(
            '<{\*([\s\S]+?)\*}>' => '<?php /* $1*/?>',
            '<{#(.*?)}>' => '<?php echo $1; ?>',
            '(<{((?!}>).)*?)(\$[\w\"\'\[\]]+?)\.(\w+)(.*?}>)' => '$1$3[\'$4\']$5',
            '(<{.*?)(\$(\w+)@(index|iteration|first|last|total))+(.*?}>)' => '$1$_foreach_$3_$4$5',
            '<{(\$[\$\w\.\"\'\[\]]+?)\snofilter\s*}>' => '<?php echo $1; ?>',
            '<{(\$[\$\w\"\'\[\]]+?)\s*=(.*?)\s*}>' => '<?php $1 =$2; ?>',
            '<{(\$[\$\w\.\"\'\[\]]+?)\s*}>' => '<?php echo htmlspecialchars($1, ENT_QUOTES, "UTF-8"); ?>',
            '<{if\s*(.+?)}>' => '<?php if ($1) : ?>',
            '<{else\s*if\s*(.+?)}>' => '<?php elseif ($1) : ?>',
            '<{else}>' => '<?php else : ?>',
            '<{break}>' => '<?php break; ?>',
            '<{continue}>' => '<?php continue; ?>',
            '<{\/if}>' => '<?php endif; ?>',
            '<{foreach\s*(\$[\$\w\.\"\'\[\]]+?)\s*as(\s*)\$([\w\"\'\[\]]+?)}>' => $foreach_inner_before . '<?php foreach( $1 as $$3 ) : ?>' . $foreach_inner_after,
            '<{foreach\s*(\$[\$\w\.\"\'\[\]]+?)\s*as\s*(\$[\w\"\'\[\]]+?)\s*=>\s*\$([\w\"\'\[\]]+?)}>' => $foreach_inner_before . '<?php foreach( $1 as $2 => $$3 ) : ?>' . $foreach_inner_after,
            '<{\/foreach}>' => '<?php endforeach; }?>',
            '<{include\s*file=(.+?)}>' => '<?php include $_view_obj->compile($1); ?>',
        );
        $pattern = $replacement = array();
        foreach ($pattern_map as $p => $r) {
            $pattern = '/' . str_replace(array("<{", "}>"), array($this->left_delimiter . '\s*', '\s*' . $this->right_delimiter), $p) . '/i';
            $count = 1;
            while ($count != 0) {
                $template_data = preg_replace($pattern, $r, $template_data, -1, $count);
            }
        }
        return $template_data;
    }

    private function _compile_function($template_data)
    {
        $pattern = '/' . $this->left_delimiter . '(\w+)\s*(.*?)' . $this->right_delimiter . '/';
        return preg_replace_callback($pattern, array($this, '_compile_function_callback'), $template_data);
    }

    private function _clear_compliedfile($tempalte_name)
    {
        $dir = scandir($this->compile_dir);
        if ($dir) {
            $part = md5(realpath($this->template_dir . DS . $tempalte_name));
            foreach ($dir as $d) {
                if (substr($d, 0, strlen($part)) == $part) {
                    @unlink($this->compile_dir . DS . $d);
                }
            }
        }
    }

    /**
     * @param $mixed
     * @param string $val
     */
    public function assign($mixed, $val = '')
    {
        if (is_array($mixed)) {
            foreach ($mixed as $k => $v) {
                if ($k != '') $this->template_vals[$k] = $v;
            }
        } else {
            if ($mixed != '') $this->template_vals[$mixed] = $val;
        }
    }

    private function _compile_function_callback($matches)
    {

        if (empty($matches[2])) return '<?php echo ' . $matches[1] . '();?>';
        $sysfunc = preg_replace('/\((.*)\)\s*$/', '<?php echo ' . $matches[1] . '($1);?>', $matches[2], -1, $count);
        if ($count) return $sysfunc;

        $pattern_inner = '/\b([-\w]+?)\s*=\s*(\$[\w"\'\]\[\-_>\$]+|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|([->\w]+))\s*?/';
        $params = "";
        if (preg_match_all($pattern_inner, $matches[2], $matches_inner, PREG_SET_ORDER)) {
            $params = "array(";
            foreach ($matches_inner as $m) $params .= '\'' . $m[1] . "'=>" . $m[2] . ", ";
            $params .= ")";
        } else {
            Error::err('Err: Parameters of \'' . $matches[1] . '\' is incorrect!');
        }
        return '<?php echo ' . $matches[1] . '(' . $params . ');?>';
    }

    public function _complie_script_get($template_data)
    {
        $isMatched = preg_match_all('/<!--include_start-->([\s\S]*?)<!--include_end-->/', $template_data, $matches);
        if ($isMatched && $isMatched === 1) {
            $script = $matches[1][0];
            $template_data = str_replace($matches[0][0], '<?php $template_file_script="' . base64_encode($script) . '";?>', $template_data);
        }
        return $template_data;
    }

    public function _complie_script_put($template_data)
    {

        $template_data = str_replace('<!--template_file_script-->', '<?php echo isset($template_file_script)?base64_decode($template_file_script):"";?>', $template_data);
        return $template_data;
    }
}