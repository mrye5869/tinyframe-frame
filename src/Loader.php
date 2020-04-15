<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe    email：55585190@qq.com          加载类
// +----------------------------------------------------------------------
namespace og;

class Loader
{

    /**
     * 加载
     *
     * @param $classname
     */
    public static function register($classname)
    {
        if(defined('MODULE_ROOT')) {
            $classPath = MODULE_ROOT.str_replace('\\', '/', $classname).'.php';
            return self::_include($classPath);
        }
        return false;
    }

    /**
     * 加载文件
     *
     * @param $path
     * @param array $ext
     * @return int
     */
    public static function loadFile($path, $ext = ['php', 'inc.php'])
    {
        $loadCount = 0;
        $inc_list = self::listFile($path);
        foreach ($inc_list as $key => $files) {
            if($files['isFile'] && in_array($files['ext'], $ext)) {
                Loader::_include($files['pathname']);
                $loadCount ++;
            }
        }

        return $loadCount;
    }

    /**
     * 列出本地目录的文件
     *
     * @param string $path
     * @param string $pattern
     *
     * @return array
     */
    public static function listFile($path, $pattern = '*')
    {
        if (strpos($pattern, '|') !== false) {
            $patterns = explode('|', $pattern);
        } else {
            $patterns [0] = $pattern;
        }
        $i   = 0;
        $dir = [];
        if (is_dir($path)) {
            $path = rtrim($path, '/') . '/';
        }
        foreach ($patterns as $pattern) {
            $list = glob($path . $pattern);
            if ($list !== false) {
                foreach ($list as $file) {
                    $dir [$i] ['filename']   = basename($file);
                    $dir [$i] ['path']       = dirname($file);
                    $dir [$i] ['pathname']   = realpath($file);
                    $dir [$i] ['size']       = filesize($file);
                    $dir [$i] ['type']       = filetype($file);
                    $dir [$i] ['ext']        = is_file($file) ? strtolower(substr(strrchr(basename($file), '.'), 1)) : '';
                    $dir [$i] ['isFile']     = is_file($file);
                    $dir [$i] ['name']       = basename($file,'.'.$dir [$i] ['ext']);
                    $i++;
                }
            }
        }
        return $dir;
    }


    public static function _include($path)
    {
        if(is_file($path)) {
            return include $path;
        }
        return false;
    }

    public static function _require($path)
    {
        if(is_file($path)) {
            return require $path;
        }
        return false;
    }
}