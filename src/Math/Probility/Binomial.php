<?php
namespace Whilegit\Math\Probility;

class Binomial{
    /**
     * 计算二项分布的概率($from <= X <= $to)
     * @param n     次数
     * @param p     单次的成功概率
     * @param from  试验成功的起始次数
     * @param to    结束次数(如不提供，则此值为与$from相同)
     * @return
     */
    public static function prob($n, $p = 0.5, $from = 0, $to = null) {
        $prob = 0.00;
        if($to == null) $to = $from;
        for($i = $from; $i<=$to; $i++) {
            $prob += Combination::calc($n, $i) * pow($p, $i) * pow(1-$p, $n-$i);
        }
        return $prob;
    }
}