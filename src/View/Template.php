<?php
namespace Whilegit\View;

use Whilegit\Utils\Reflect;

class Template{
   
    /**
     * @desc 注入的特殊模板函数集合<pre>
     *  array (
     *      'url' => array          // 方法名
     *          'params' => array   // 参数数量
     *              'url' => 
     *                  'has_default' => false,   //是否有默认变量
     *                  'is_array' => false,      //默认变量是否是数组
     *              'url2' => array
     *                  'has_default' => true,
     *                  'expect_array' => true,
     *          'func' => {closure} // 回调体
     *      'tomedia' => array
     *          'params' => array
     *              'src' => array
     *                  'has_default' => false,
     *                  'expect_array' => false,
     *          'func' => {closure}
     *  ) </pre>
     */
    protected static $specialFuncAry = array();
    
    /**
     * 注入的自定义指令
     * @example array(
     *              '/{ifp\s+(.+?)}/' => '<?php if(cv($1)) { ?>',
     *              '/{ifpp\s+(.+?)}/'=> '<?php if(cp($1)) { ?>',
     *          )
     */
    protected static $specialDirectiveAry = array();
    
    /**
     * 配置数组
     */
    public static $config = array(
        //加载图片的时候，如果本地无图片资源，修改 img.src 指向远程服务器，调试时可用
        'remote_type' => 0,   // 0关闭  1开启
        'attachurl_local' => 'http://localhost',
        'attachurl_remote' => 'http://www.exexpert.cn',
        //编译模板文件的目录
        'compile_path' => null,
        //模板文件的目录
        'template_path' => null,
        //{Template xxx} 子模板文件的目录
        'include_template_path' => null,
        //{Template xxx} 子模板文件的编译目录
        'include_compile_path' => null
    );
    
    /**
     * 路径回调(指寻找模板路径和编译路径)，如若未提供该回调，则使用self::$config参数提供的路径
     * 回调原型： function func($path, $type = 0) 
     * 若未找到请返回false(找到时返回全路径),Template再到self::$config参数指定的位置查找模板或编译文件
     * $type = 0:  普通模板路径
     * $type = 1:  普通模板编译路径
     * $type = 2:  包含子模板的路径
     * $type = 3:  包含子模板的编译路径   
     */
    protected static $pathCallback = null;
    
    /**
     * 初始化函数
     * @param array $config
     */
    public static function init($config = array(), $pathCallback = null){
        self::$config = array_merge(self::$config, $config);
        if(empty(self::$config['compile_path']) || empty(self::$config['template_path'])){
            die('模板路径未指定');
        }
        self::$pathCallback = $pathCallback;
    }
    
    /**
     * 渲染模板文件
     * @param string $template       模板文件相对于模板目录的路径
     * @param boolean $include_flag  是否是子模板文件
     * @param boolean $force_compile 强制重新编译模板文件
     * @return string  返回编译文件的绝对路径
     * @example  include Template::render('order/detail');
     */
    public static function render($template, $include_flag = false, $force_compile = false){
        $compile = '';
        $source = '';
        if(!empty(self::$pathCallback)){
            if($include_flag == false){
                $source = call_user_func(self::$pathCallback, $template, 0);
                $compile = call_user_func(self::$pathCallback, $template, 1);
            } else {
                $source = call_user_func(self::$pathCallback, $template, 2);
                $compile = call_user_func(self::$pathCallback, $template, 3);
            }
        }
        if(empty($source) || empty($compile) || !is_file($source)){
            if($include_flag == false){
                $source = self::$config['template_path'] . '/' . $template . '.html';
                $compile = self::$config['compile_path'] . '/' . $template . '.php';
            } else {
                $source = self::$config['include_template_path'] . '/' . $template . '.html';
                $compile = self::$config['include_compile_path'] . '/' . $template . '.php';
            }
        }
        if(!file_exists($source)){
            die('template file not exists - ' . $source);
        }
        if($force_compile || !is_file($compile) || filemtime($source) > filemtime($compile)) {
            self::compile($source, $compile);
        }
        return $compile;
    }
    
    /**
     * 编译模板，并把编译好了的内容写入目标文件中
     * @param string $from  读取的模板文件路径
     * @param string $to   存放模板编译文件的路径
     */
    protected static function compile($from, $to){
        $path = dirname($to);
        if (!is_dir($path))  mkdir($path, 0777, true);
        $content = self::parse(file_get_contents($from));
        file_put_contents($to, $content);
    }
    
    /**
     * 实际编译模板
     * @param string $str  未编译的模板字符串
     * @return string  编译好了的字符串
     */
    protected static function parse($str) {
        $cls = get_called_class();
        //不再支持 <!--{$val}--> 至  {$val} 这样的写法
        //$str = preg_replace('/<!--{(.+?)}-->/s', '{$1}', $str);
        //加载子模板
        $str = preg_replace('/{template\s+(.+?)}/', '<?php include '.$cls.'::render($1, true);?>', $str);
        /* 支持模板中直接嵌入php代码    {php var_dump(...);}   至    <?php var_dump(...);?>         */
        $str = preg_replace('/{php\s+(.+?)}/', '<?php $1?>', $str);
        
        // #### if - elseif - else -/if  ####
        /*   {if $a == 1}       至         <?php if($a == 1){ ?>              */
        $str = preg_replace('/{if\s+(.+?)}/', '<?php if($1) { ?>', $str);
        /*   {else}             至        <?php } else { ?>                   */
        $str = preg_replace('/{else}/', '<?php } else { ?>', $str);
        /*   {elseif $a == 2}   至        <?php } else if($a == 2) { ?>       */
        $str = preg_replace('/{else ?if\s+(.+?)}/', '<?php } else if($1) { ?>', $str);
        /*   {/if}              至        <?php } ?>                          */
        $str = preg_replace('/{\/if}/', '<?php } ?>', $str);
        
        // #### loop 语句  ####
        /* {loop $ary $item}    至        <?php if(is_array($ary)) { foreach($ary as $item) { ?>                  */
        $str = preg_replace('/{loop\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2) { ?>', $str);
        /* {loop $ary $k $v}    至        <?php if(is_array($ary)) { foreach($ary as $k => $v) { ?>               */
        $str = preg_replace('/{loop\s+(\S+)\s+(\S+)\s+(\S+)}/', '<?php if(is_array($1)) { foreach($1 as $2 => $3) { ?>', $str);
        /* {/loop}              至        <?php }} ?>                                                             */
        $str = preg_replace('/{\/loop}/', '<?php } } ?>', $str);
        
        // 变量替换
        /* {$abcd}   至      <?php echo $abcd;  ?>    */
        $str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)}/', '<?php echo $1;?>', $str);
        /* {$abcd['def']}  至      <?php echo $abcd['def'];?>     */
        /* {$abcd[$def]}   至      <?php echo $abcd[$def];?>      */
        $str = preg_replace('/{(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]\'\"\$]*)}/', '<?php echo $1;?>', $str);
        
        //特殊用途的函数
        foreach(self::$specialFuncAry as $funcName=>$funcInfo){
            $str_temp = '';
            $str_temp2 = '';
            $i = 0;
            foreach($funcInfo['params'] as $k=>$v){
                if(!$v['expect_array']){
                    $str_temp .= '\s+(\S+)';
                } else {
                    $str_temp .= '\s+(array\(.+?\))';
                }
                if($i!=0) $str_temp2.= ',';
                $str_temp2 .= '$' . ($i+1);
                $str = preg_replace('/{'.$funcName.$str_temp . '}/', '<?php echo '.$cls.'::'.$funcName.'('.$str_temp2.');?>', $str);
                $i++;
            }
        }
        
        /*  增加 <?php ?>代码块中，增加数组下标的单引号        */
        $str = preg_replace_callback('/<\?php([^\?]+)\?>/s', "self::addquote", $str);
        /*  常量替换 */
        $str = preg_replace('/{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)}/s', '<?php echo $1;?>', $str);
        
        $str = str_replace('{##', '{', $str);
        $str = str_replace('##}', '}', $str);
        
        if(!empty(self::$specialDirectiveAry)){
            foreach(self::$specialDirectiveAry as $k=>$v){
                $str = preg_replace($k, $v, $str);
            }
        }
        
        //适合图片路径替换
        if (!empty(self::$config['remote_type'])) {
            $str = str_replace('</body>', 
                "\r\n<script>
                     $(function(){
                        \$('img').attr('onerror', '').on('error', 
                            function(){
                                if (!\$(this).data('check-src') && (this.src.indexOf('http://') > -1 || this.src.indexOf('https://') > -1)) {
                                    this.src = this.src.indexOf('".self::$config['attachurl_local'] . "') == -1 
                                               ? 
                                               this.src.replace('".self::$config['attachurl_remote']. "', '" . self::$config['attachurl_local']."') 
                                               : 
                                               this.src.replace('".self::$config['attachurl_local']. "', '" . self::$config['attachurl_remote'] . "');
                                    \$(this).data('check-src', true);
                                }
                            });
                     });
                 </script>
                 </body>", 
            $str);
        }
        $str = "<?php defined('IN_IA') or exit('Access Denied');?>" . $str;
        return $str;
    }
    
    /**
     * 增加 <?php ?>代码块中，增加数组下标的单引号
     * @param array $matchs
     * @return mixed
     */
    protected static function addquote($matchs) {
        $code = "<?php {$matchs[1]}?>";
        $code = preg_replace('/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\](?![a-zA-Z0-9_\-\.\x7f-\xff\[\]]*[\'"])/s', "['$1']", $code);
        return str_replace('\\\"', '\"', $code);
    }
    
    /**
     * 添加特别指令
     * @param string $search    源字符串(支持正则表达式)
     * @param string $replace   替换后的字符串
     * @example  $str = preg_replace('/{ifp\s+(.+?)}/', '<?php if(cv($1)) { ?>', $str); <br>
        $str = preg_replace('/{ifpp\s+(.+?)}/', '<?php if(cp($1)) { ?>', $str); <br>
        $str = preg_replace('/{ife\s+(\S+)\s+(\S+)}/', '<?php if( ce($1 ,$2) ) { ?>', $str);
     */
    public static function addSpecialDirective($search, $replace){
        self::$specialDirectiveAry[$search] = $replace;
    }
    
    /**
     * 注入特殊模板函数
     * @param string $funcName    函数名
     * @param \Closure $callback  可选
     * @example addSpecial('tomedia');  已定义好全局变量名时可用 <br>
     *          addSpecial('url', function($val){...});   提供函数名或匿名函数
     */
    public static function addSpecialFunc($funcName, $callback = null){
        /*    {url 'mobile/order/detail'}    至       <?php echo url('mobile/order/detail'); ?>      */
        /* $str = preg_replace('/{url\s+(\S+)}/', '<?php echo '.$cls.'::url($1);?>', $str); */
        /*    {url 'mobile/order/detail' array($aaa)}      至       <?php echo url('mobile/order/detail', array($aaa))    */
        /* $str = preg_replace('/{url\s+(\S+)\s+(array\(.+?\))}/', '<?php echo '.$cls.'url($1, $2);?>', $str); */
        /*    {media 'mobile/order/detail'}    至    <?php echo tomedia('mobile/order/detail');?>   */
        /* $str = preg_replace('/{media\s+(\S+)}/', '<?php echo tomedia($1);?>', $str); */   
        if(is_string($funcName) && $callback == null){
            $callback = $funcName;
        }
        $result = Reflect::func($callback);
        if(!empty($result)){
            $name = $callback != null ? $funcName : $result['name'];
            self::$specialFuncAry[$name] = array('params'=>$result['params'], 'func'=>$result['func']);
        }
    }
    
    /**
     * 特殊模板函数的魔术方法
     * @param string $method
     * @param array $args
     */
    public static function __callStatic($method, $args){
        if(!empty(self::$specialFuncAry[$method])){
            return self::$specialFuncAry[$method]['func']->invokeArgs($args);
        }
    }
}

