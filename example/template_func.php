<?php
use Whilegit\Utils\IString;

function tomedia($src, $local_path = false){
    global $_W;
    if (empty($src)) {
        return '';
    }
    if (IString::exists($src, 'addons/')) {
        return $_W['siteroot'] . substr($src, strpos($src, 'addons/'));
    }
    if (IString::exists($src, $_W['siteroot']) && !IString::exists($src, '/addons/')) {
        $urls = parse_url($src);
        $src = $t = substr($urls['path'], strpos($urls['path'], 'images'));
    }
    $t = strtolower($src);
    if (IString::exists($t, 'https://mmbiz.qlogo.cn') || strexists($t, 'http://mmbiz.qpic.cn')) {
        return url('utility/wxcode/image', array('attach' => $src));
    }
    if (IString::exists($t, 'http://') || IString::exists($t, 'https://')) {
        return $src;
    }
    if ($local_path || empty($_W['setting']['remote']['type']) || file_exists(IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/' . $src)) {
        $src = $_W['siteroot'] . $_W['config']['upload']['attachdir'] . '/' . $src;
    } else {
        $src = $_W['attachurl_remote'] . $src;
    }
    return $src;
}

function set_medias($list = array(), $fields = null){
    if (empty($fields)) {
        foreach ($list as &$row) {
            $row = tomedia($row);
        }
        return $list;
    }
    if (!is_array($fields)) {
        $fields = explode(',', $fields);
    }
    if (is_array($list)) {
        foreach ($list as $key => &$value) {
            foreach ($fields as $field) {
                if (is_array($value) && isset($value[$field])) {
                    $value[$field] = tomedia($value[$field]);
                }
            }
        }
        return $list;
    }
}