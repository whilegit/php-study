<?php
namespace Whilegit\Math\Probility;

/**
 * 组合数学相关计算
 * @author Lzr
 *
 */
class Combination{
    /**
     * 组合数的计算
     * @param int $n  总数
     * @param int $x  所取的个数
     * @return number 返回组合数
     */
    public static function calc($n, $x) {
        $val = 1.0;
        for($i = 0; $i<$x; $i++) {
            $val *= ($n-$i) / ($x-$i);
        }
        
        return round($val);
    }
    
    /**
     * 阶乘函数
     * @param int $k
     * @return number
     */
    public static function factorial($k){
        $v = 1;
        if($k>1){
            for($i = 2; $i<=$k; $i++){
                $v *= $i;
            }
        }
        return $v;
    }
}