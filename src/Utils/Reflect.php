<?php
namespace Whilegit\Utils;


class Reflect{
    
    public static function func($callable){
        $ret = array('name'=>'', 'params' => array(), 'func'=>null);
        $rf = new \ReflectionFunction($callable);
        $ret['name'] = $rf->getName();
        $ret['func'] = $rf;
        $params = $rf->getParameters();
        foreach($params as $p){
            $name = $p->getName();
            $has_default = $p->isDefaultValueAvailable();
            $expect_array = false;
            if($has_default){
                $default_value = $p->getDefaultValue();
                $t = gettype($default_value);
                
                switch($t){
                    case 'string':  break;
                    case 'integer': break;
                    case 'double': break;
                    case 'array':  $expect_array = true; break;
                    case 'object':  break;
                    case 'NULL':    break;
                    case 'boolean': break;
                    case 'resource':break;
                    default: break;
                }
                
            }
            $ret['params'][$name] = array('has_default'=>$has_default, 'expect_array'=>$expect_array);
        }
        return $ret;
    }
    
}

/*
$default_value = $p->getDefaultValue();
						$t = gettype($default_value);
						switch($t){
							case 'string': $default_value = "'$default_value'";break;
							case 'integer':
							case 'double': $default_value = ($default_value == PHP_INT_MAX) ? 'PHP_INT_MAX' : $default_value; break;
							case 'array': $default_value=empty($default_value) ? '[]':'[ARRAY]';break;
							case 'object': $default_value='[OBJECT]';break;
							case 'NULL': $default_value='NULL';break;
							case 'boolean': $default_value=$default_value?'true':'false';break;
							case 'resource':  $default_value='[RESOURCE]';break;
							default: $default_value='[UNKONW TYPE]';break;
						}
*/