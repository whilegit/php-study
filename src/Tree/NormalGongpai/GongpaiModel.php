<?php
namespace Whilegit\Tree\NormalGongpai;

use Whilegit\Database\Model;

class GongpaiModel extends Model{
    
    //以下四个变量，必须要在子类的定义中抄一遍，即使不赋值。原因是Model应用静态变量的迟绑定技术(参考self和static关键字的区别)
    protected static $table = 'while_gongpai'; // 数据表名称，子类若不告知，默认为模型名称的小写化
    protected static $pk = "id";      // 主键名称,子类若不告知，查表的主键
    protected static $fields = null;  // 表的字段，子类若不告知，查表
    protected static $redirect_map = array('user_id'=>'\Whilegit\Model\Virtual\While_Member');
    
    // 打散后关联数组，对应 $this->data['grandParents']      5:8,4:6,3:3,2:2,1:1
    protected $grandParentsAry;

    // 从自己向下发展的子树，调用一次 $this->buildChildrenTree() 后初始化以下各属性，并置位 $this->buildChildren
    protected $buildChildren = false; 
    protected $children = array('left'=>null, 'right'=>null);  //直接子结点
    protected $childrenLevelCountInfo = array();               //子结点各层级的数量，键名是子结点的$level值
    protected $childrenLevelFirstItemInfo = array();           //子结点各层级的第一个结点，仅用于判定最后一层是否已排满。
    
    // 从自己往上的各层级父类，调用一次 $this->buildParentTree() 后初始化以下各属性，并置位 $this->buildParents
    protected $buildParents = false;
    protected $parent;   //直接上级结点
    
    /*
     * 字段：
     *       id : 编号
     *  user_id : 会员ID
     *    level : 公排等级
     *  floor_sn: 层级排序(绝对定位)
     * parentUserId : 父级user_id
     * grandParents: 所有上级的组成的字符串，如 4:xxxx;3:xxxx;2:xxxx;1:xxxx;  适合sql查询
     *
     */
    
    /**
     * 构造函数
     * @param array $data
     */
    public function __construct($data = array()){
        parent::__construct($data);
        $this->parseGrandParentIds();
    }

    /**
     * 在自己的树中，加入一个结点，并选择合适的位置插入(从上到下，从左到右)
     * @param int $user_id
     * @return GongpaiModel 返回当前新插入的结点
     */
    public function addSub($user_id){
        $tmp = self::get(array('user_id' => $user_id));
        if(!empty($tmp)){
            die('ERROR: Already exists! user_id=' . $user_id);
        }
        $entry = $this->findPosition();
        if(empty($entry)){
            die('ERROR: 不能找到插入点! user_id=' . $user_id . ', tid= ' . $this->data['user_id']);
        }
        
        $child = new self();
        $child->setAttr('user_id', $user_id);
        $child->setAttr('level', $entry['level'] + 1);
        $child->setAttr('parentUserId', $entry['user_id']);
        //设置上级序列 
        $grandParents = $entry['level'] . ':' . $entry['user_id'] . ',' . $entry['grandParents'];
        $child->setAttr('grandParents', rtrim($grandParents, ','));
        //计算 floor_sn 的顺序
        $leftOrRight = empty($entry->children['left']) ? 0 : 1;
        $child->setAttr('floor_sn', $entry['floor_sn'] * 2 + $leftOrRight);
        $child->save();
        
        //设置子结点和父结点的相互关系
        $child->parent = $entry;
        $leftOrRight = ($leftOrRight == 0) ? 'left' : 'right';
        $entry->children[$leftOrRight] = $child;
        return $child;
    }
    

    /**
     * 寻找当前公排树(自己作为树根)的空缺位置
     * @desc <br> 依赖于子结点树的建立 见 function buildChildrenTree()
     * @return GongpaiModel
     */
    private function findPosition(){
        $ret = null;
        if($this->buildChildren == false) $this->buildChildrenTree();
        
        if(empty($this->children['left']) || empty($this->children['right'])){
            return $this;
        }
        
        $destLevel = null;
        $tmp = null;
        foreach($this->childrenLevelCountInfo as $k=>$v){
            if($tmp === null) {
                $tmp = $v;
                continue;
            }
            //子树的各层级的数量，都应有 2^(相对level) 个数量，如不满足则此层必有空缺
            if($v != $tmp * 2){
                $destLevel = $k-1;
                break;
            } else {
                $tmp = $v;
            }
        }
        
        if($destLevel === null){
            //刚好全排列，取最后一排的第一个位置作为新插入的父级
            $ret = end($this->childrenLevelFirstItemInfo);
            reset($this->childrenLevelFirstItemInfo);
        } else {
            //中间有空位
            $ret = $this->findPositionRecur($destLevel);
        }
        return $ret;
    }
    
    /**
     * 建立当前对象的整个子树
     * @desc <br>对每个对象设置 $object->parent 和 $object->children['left'或'right']，使整颗子树相互关联
     * @desc <br>同时设置 $this->childrenLevelCountInfo (存储每一个子层级的结点数量)
     * @desc <br>同时设置 $this->childrenLevelFirstItemInfo (存储每一个子层级的第一个结点)
     */
    public function buildChildrenTree(){
        $tmp = "{$this->data['level']}:{$this->data['user_id']}";
        //按 level和floor_sn排列
        $children = self::ls(array('grandParents FIND_IN_SET'=>$tmp), null, 'user_id', array('level','floor_sn'));
        //本级不全，直接返回
        if(empty($children)){
            return;
        } else if(count($children) == 1){
            $child = array_pop($children);
            $child->parent = $this;
            $this->children['left'] = $child;
            $this->childrenLevelCountInfo = array($child['level'] => 1);
            $this->childrenLevelFirstItemInfo = array($child['level'] => $child);
            return;
        } else {
            foreach($children as $child){
                $pid = $child['parentUserId'];
                $cid = $child['user_id'];
                $level = $child['level'];
                
                //增加每层的结点数量
                if(empty($this->childrenLevelCountInfo[$level])) $this->childrenLevelCountInfo[$level] = 1;
                else $this->childrenLevelCountInfo[$level] ++;
                //获取每层的第一个结点
                if(empty($this->childrenLevelFirstItemInfo[$level])) $this->childrenLevelFirstItemInfo[$level] = $child;

                $parent = ($pid == $this->data['user_id']) ? $this : $children[$pid];
                if(empty($parent)){
                    die("[{$cid}]的父级为[{$pid}]，但是[$pid]不存在于树中。插入点为[{$this->data['user_id']}]");
                }
                $child['parent'] = $parent;
                $parent_child_position = ($child['floor_sn'] % 2 == 0) ? 'left' : 'right';
                $parent->children[$parent_child_position] = $child;
            }
            ksort($this->childrenLevelCountInfo);
            ksort($this->childrenLevelFirstItemInfo);
        }
        $this->buildChildren = true;
    }
    
    /**
     * 递归插找空缺位置
     * @param int $destLevel 目录层级
     * @return NULL|GongpaiModel
     */
    private function findPositionRecur($destLevel){
        $ret = null;
        
        $left = $this->children['left'];
        $right = $this->children['right'];
        $level = $this->data['level'];
        
        if($level > $destLevel){
            //当前对象的层级较深，肯定不是目标空缺
            $ret = null;
        } else if($level == $destLevel) {
            //当前对象的层级相同，如果左子结点或右子结点为空，则此结子即为可以新增子结点的结点
            $ret = (empty($left) || empty($right)) ? $this : null;
        } else {
            //当前子结点层级较高，直接继续递归查找
            $ret = $left->findPositionRecur($destLevel);
            if($ret == null) {
                $ret = $right->findPositionRecur($destLevel);
            }
        }
        return $ret;
    }
    
    /**
     * 查找本对象的所有上级，并建立父树
     * @desc 设置自己的 $this->parent, 自己的直接上级 $this->parent->children['left'或'right']，一直到 根结点
     */
    public function buildParentTree(){
        $this->buildParents = true;
        //自己是根结点，直接退出
        if(empty($this->grandParentsAry)){
            return; 
        }
        
        //获取所有上级
        $parents = self::ls(array('user_id' => $this->grandParentsAry), null, 'user_id');
        
        //自己和某个直接上级挂购
        $meid = $this->data['user_id'];
        $me_pid = $this->data['parentUserId'];
        $me_child_position = ($this->data['floor_sn'] % 2 == 0) ? 'left' : 'right';
        $this->parent = $parents[$me_pid];
        $this->parent->children[$me_child_position] = $this;
        
        //各上级相互挂购（子结点存有父结点的信息，父结点不存有子结点的信息）
        foreach($parents as $p){
            $ppid = $p['parentUserId'];
            $plevel = $p['level'];
            
            if($ppid == '-1' || $plevel == 1){
                //自己是根结点，不处理
                continue;
            } else {
                $pfloor_sn = $p['floor_sn'];
                $p->parent = $parents[$ppid];
                $child_position = ($pfloor_sn % 2 == 0) ? 'left' : 'right';
                $p->parent->children[$child_position] = $p;
            }
        }
    }

    /**
     * 探测增加自己后，哪些父级获得层奖(按位置发放层奖)
     * @desc <br> 每个新会员的加入，只会发出一个层奖（某一层的左区和右区都有会员入驻时发出）；为什么？!
     * @return false|GongpaiModel|NULL   返回GongpaiModel类型表示该会员获得层奖，返回null表示无人获得层奖
     */
    public function detectFloorAward(){
        if($this->buildParents == false) $this->buildParentTree();
        //没有父级，出错返回
        if($this->parent == null){
            return false;
        }
        $parent = $this->parent;
        $curLevel = $this->data['level'];
        $curFloorSn = $this->data['floor_sn'];

        while($parent != null){
            $parentLevel = $parent->data['level'];
            $parentSn = $parent->data['floor_sn'];
            //计算几个floor_sn的边界
            $num = pow(2, $curLevel - $parentLevel);
            $leftStartSn = $parentSn * $num;
            $leftEndSn = $leftStartSn + $num / 2 - 1;
            $rightStartSn = $leftEndSn + 1;
            $rightEndSn = $rightStartSn + $num /2 - 1;
            
            //左区数量
            $condition = array();
            $condition['level'] = $curLevel;
            $condition['floor_sn >='] = $leftStartSn;
            $condition['floor_sn <='] = $leftEndSn;
            $leftCounts = self::$iPdo->getcount(self::$table, $condition);
            
            //右区数量
            $condition = array();
            $condition['level'] = $curLevel;
            $condition['floor_sn >='] = $rightStartSn;
            $condition['floor_sn <='] = $rightEndSn;
            
            $rightCounts = self::$iPdo->setSqlDebug(false)->getcount(self::$table, $condition);            
            
            //自己处在左区，且左区只有自己
            $flagLeft = ($curFloorSn <= $leftEndSn && $leftCounts == 1);
            //自己处在右区，且右区只有自己
            $flagRight = ($curFloorSn >= $rightStartSn && $rightCounts == 1);
            
            //自己所在的区只有自己一个人，才需要考虑层奖。自己的直接上级肯定符合条件，必须进行考察
            if( $flagLeft || $flagRight ) {  
                $oppoCounts = $flagLeft ? $rightCounts : $leftCounts;

                if($oppoCounts >= 1){
                    //这个父级获得层奖，继续往上推没有必要了，肯定不可能再符合层奖要求了
                    return $parent;
                } else {
                    $parent = $parent->parent;
                    if(empty($parent)){
                        return null;
                    }
                    continue;
                }
            } else {
                break;
            }
        }
        return null;
    }
    
    /**
     * 探测增加自己后，哪些父级获得对碰奖(按位置对碰)
     */
    public function detectPairAward(){
        $curLevel = $this->data['level'];
        $curFloorSn = $this->data['floor_sn'];
        $pairParents = array();
        // 计算所有可能会对碰的楼层序号
        $siblings = array();
        for($i = 0; $i < $curLevel - 1; $i++){
            $roads = array();
            $curSn = $curFloorSn;
            //从当前结点往上走，并记录向上运动的轨迹; 直接父级向上走一次，直接父级的直接父级向上走两次
            for($j = 0; $j<$i+1; $j++){
                //$lr 0表示左上  1右上, 走的路径记下来push到$roads里面
                $lr = $curSn % 2;
                array_push($roads, $lr);
                //上一级的层级序号 = 下级的floor_sn 整除 2
                $curSn = intval($curSn / 2);
            }
            //到达目录父级后，然后跟据之前的轨迹又往下走
            for($j = 0; $j<$i+1; $j++){
                $lr = array_pop($roads);
                $curSn *= 2;
                //第一步往下走时，选择另一条路；其他原路返回
                if($j == 0){
                    $curSn += 1 - $lr;
                } else {
                    $curSn += $lr;
                }
            }
            $siblings[] = $curSn;
        }
        //搜索可能对碰的楼层序号
        $sibs = self::ls(array('floor_sn IN' => $siblings, 'level'=>$curLevel), null, 'floor_sn');
        
        if(!empty($sibs)){
            //未建立父系的，立即建立
            if($this->buildParents == false) $this->buildParentTree();
            foreach($sibs as $floor_sn=>$item){
                $diff = abs($floor_sn - $curFloorSn);
                // $diff == 1时，表示同一直接上级；$diff == 2时，表示同一祖父；$diff == 4时，表示同一祖祖父；
                // $diff == 8时，表示同一祖祖祖父；依次类推...
                $diff = round(log($diff, 2)) + 1;
                $award = $this;
                
                for($i = 0; $i<$diff; $i++){
                    $award = $award->parent;
                }
                $uid = $award['user_id'];
                $pairParents[$uid] = $award;
            }
        }
        return $pairParents;
    }
    
    
    /**
     * 当调用$this->setAttr('grandParents', xxxx)时自动回调本函数
     * @param string|array $value 如1:11;2:31;或array('1'=>11, '2'=>31)
     */
    protected function setGrandParents($value){
        if(is_array($value)){
            $str = '';
            foreach($value as $k=>$v){
                $str .= "{$k}:{$v},";
            }
            $this->grandParentsAry = $value;
            $this->data['grandParents'] = rtrim($str,',');
        } else {
            $this->data['grandParents'] = $value;
            $this->parseGrandParentIds();
        }
    }
    
    
    /**
     * 将形如 1:11;2:31; 的字符串转化成 array('1'=>11,'2'=>31) 类型的数组
     */
    private function parseGrandParentIds(){
        if(!empty($this->data['grandParents'])){
            $this->grandParentsAry = array();
            $tmp1 = explode(',', $this->data['grandParents']);
            foreach($tmp1 as $item){
                $tmp2 = explode(':', $item);
                if(count($tmp2) == 2){
                    $level = trim($tmp2[0]);
                    $this->grandParentsAry[$level] = trim($tmp2[1]);
                }
            }
        }
    }
    
    
    /**
     * 输出公排图
     * @param GongpaiModel|int|null $entry object|user_id|null  如输入null则查找根结点
     * @return string
     */
    public static function plot($entry = null){
        if(!$entry instanceof self){
            if(empty($entry)) $entry = self::get(array('level'=>1, 'parentUserId'=>-1));
            else $entry = self::get(array('user_id'=>intval($entry)));
        }
        
        if($entry->buildChildren == false) $entry->buildChildrenTree();
        $str = "【{$entry['user_id']}】\r\n";
        $str .= "<div>".$entry->plot_cur(1) . "</div>";
        return $str;
    }
    
    
    /**
     * 输出公排图的递归
     * @param int $viewLevel 递归的层级
     * @return string
     */
    private function plot_cur($viewLevel){
        $str = '';
        $pad_str0 = str_pad('', 12 * ($viewLevel-1), '|           ', STR_PAD_LEFT);
        $pad_str0 = str_replace(' ', '&nbsp;', $pad_str0);
        if(!empty($this->children['left'])){
            $user_id = $this->children['left']['user_id'];
            
            $str .= "<div class='item'>
                        <div class='key'>
                            <span>{$pad_str0}o-【{$user_id}】</span>
                        </div>
                        <div class='sub'>".
                        $this->children['left']->plot_cur($viewLevel + 1).
                        "</div>
                    </div>";
        }
        if(!empty($this->children['right'])){
            $user_id = $this->children['right']['user_id'];
            $str .= "<div class='item'>
                        <div class='key'>
                            <span>{$pad_str0}o-【{$user_id}】</span>
                        </div>
                        <div class='sub'>".
                        $this->children['right']->plot_cur($viewLevel + 1).
                        "</div>
                    </div>";
        }
        return $str;
    }
    
}
