<?php
namespace Whilegit\Math\Probility;

/**
 * 泊松(Poisson)分布 
 * @desc <br>分布列p(i) = e^(-lamda) * lamda ^(i) / i!
 * @author Lzr
 *
 */
class Poisson{
    
    /**
     * 计算分布列的概率
     * @param int $lamda
     * @param int $from
     * @param int $to   如不提供，则只计算$from的分布列
     * @return number
     */
    public static function prob($lamda, $from, $to = null){
        if($to === null){
            $to = $from;
        }
        $prob = 0.0;
        for($i = $from; $i<=$to; $i++){
            $prob += exp(-$lamda) * pow($lamda, $i) / Combination::factorial($i); 
        }
        return $prob;
    }
}