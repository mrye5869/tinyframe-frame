<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe    <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\http;


class View
{
    /**
     * html内容
     * @var
     */
    protected $html;

    /**
     * 应用对象
     * @var App
     */
    protected $app;

    /**
     * 请求对象
     * @var
     */
    protected $request;

    /**
     * 模板配置
     * @var array
     */
    protected $config = [];


    public function __construct(App $app, Request $request, $config = [])
    {
        $this->app = $app;
        $this->request = $request;
        $this->config = array_merge($config, $this->config);
    }

    /**
     * 解析
     * @param $html
     * @param bool $inmodule
     * @return null|string|string[]
     */
    public function parse($html, $inmodule = false)
    {
        $html = preg_replace('/<!--{(.+?)}-->/s', '{$1}', $html);
        $html = preg_replace('/{include\s+(.+?)}/', '<?php echo view_content($1); ?>', $html);
        $html = preg_replace('/{template\s+(.+?)}/', '<?php (!empty($this) && $this instanceof WeModuleSite || '.intval($inmodule).') ? (include $this->template($1, TEMPLATE_INCLUDEPATH)) : (include template($1, TEMPLATE_INCLUDEPATH));?>', $html);
        $html = preg_replace('/{php\s+(.+?)}/', '<?php $1?>', $html);
        $html = preg_replace('/{if\s+(.+?)}/', '<?php if($1) { ?>', $html);
        $html = preg_replace('/{else}/', '<?php } else { ?>', $html);
        $html = preg_replace('/{else ?if\s+(.+?)}/', '<?php } else if($1) { ?>', $html);
        $html = preg_replace('/{\/if}/', '<?php } ?>', $html);
        $html = preg_replace('/{loop\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1) || $1 instanceof og\db\Model) { if($1 instanceof og\db\Model) { $1 = $1->toArray(); } foreach($1 as $2) { ?>', $html);
        $html = preg_replace('/{loop\s+(\S+)\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)  || $1 instanceof og\db\Model) {if($1 instanceof og\db\Model) { $1 = $1->toArray(); } foreach($1 as $2 => $3) { ?>', $html);
        $html = preg_replace('/{\/loop}/', '<?php } } ?>', $html);
        $html = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/', '<?php echo $1;?>', $html);
        $html = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]\'\"\$]*)}/', '<?php echo $1;?>', $html);
        $html = preg_replace('/{url\s+(\S+)}/', '<?php echo url($1);?>', $html);
        $html = preg_replace('/{url\s+(\S+)\s+(array\(.+?\))}/', '<?php echo url($1, $2);?>', $html);
        $html = preg_replace('/{media\s+(\S+)}/', '<?php echo tomedia($1);?>', $html);
        $html = preg_replace_callback('/<\?php([^\?]+)\?>/s', [$this, 'addquote'], $html);
        $html = preg_replace_callback('/{hook\s+(.+?)}/s', [$this, 'modulehookParser'], $html);
        $html = preg_replace('/{\/hook}/', '<?php ; ?>', $html);
        $html = preg_replace('/{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)}/s', '<?php echo $1;?>', $html);
        $html = str_replace('{##', '{', $html);
        $this->html = str_replace('##}', '}', $html);

        return $this->html;
    }

    /**
     * 加引号
     * @param $matchs
     * @return mixed
     */
    protected function addquote($matchs)
    {
        $code = "<?php {$matchs[1]}?>";
        $code = preg_replace('/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\](?![a-zA-Z0-9_\-\.\x7f-\xff\[\]]*[\'"])/s', "['$1']", $code);

        return str_replace('\\\"', '\"', $code);
    }

    /**
     * 模块钩子分析
     * @param array $params
     * @return string
     */
    protected function modulehookParser($params = array())
    {
        load()->model('module');
        if (empty($params[1])) {
            return '';
        }
        $params = explode(' ', $params[1]);
        if (empty($params)) {
            return '';
        }
        $plugin = array();
        foreach ($params as $row) {
            $row = explode('=', $row);
            $plugin[$row[0]] = str_replace(array("'", '"'), '', $row[1]);
            $row[1] = urldecode($row[1]);
        }
        $plugin_info = module_fetch($plugin['module']);
        if (empty($plugin_info)) {
            return false;
        }

        if (empty($plugin['return']) || $plugin['return'] == 'false') {
        } else {
        }
        if (empty($plugin['func']) || empty($plugin['module'])) {
            return false;
        }

        if (defined('IN_SYS')) {
            $plugin['func'] = "hookWeb{$plugin['func']}";
        } else {
            $plugin['func'] = "hookMobile{$plugin['func']}";
        }

        $plugin_module = \WeUtility::createModuleHook($plugin_info['name']);
        if (method_exists($plugin_module, $plugin['func']) && $plugin_module instanceof \WeModuleHook) {
            $hookparams = var_export($plugin, true);
            if (!empty($hookparams)) {
                $hookparams = preg_replace("/'(\\$[a-zA-Z_\x7f-\xff\[\]\']*?)'/", '$1', $hookparams);
            } else {
                $hookparams = 'array()';
            }
            $php = "<?php \$plugin_module = WeUtility::createModuleHook('{$plugin_info['name']}');call_user_func_array(array(\$plugin_module, '{$plugin['func']}'), array('params' => {$hookparams})); ?>";
            return $php;
        } else {
            $php = "<!--模块 {$plugin_info['name']} 不存在嵌入点 {$plugin['func']}-->";
            return $php;
        }
    }


}