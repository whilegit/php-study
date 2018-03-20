十方创客ewei_shopv2的数据字典
========================================
> author: Linzhongren
## ewei_shop_member表
> 此表存放用户数据
* __id__ _INT(11)_ 主键
* __groupid__ _INT(11)_ ???
* __level__ _INT(11)_ ???
* __isagent__ _TINYINT(1)_ 是否为创客 (0:不是 1是)
* __agentid__ _INT(11)_ 上级创客
  >关联本表id字段   
  >这是最终认定的上级，分销提成直接发给他的
* __credit1__ _DECIMAL(10,2)_ 积分
* __credit2__ _DECIMAL(12,2)_ 余额
  > 本表credit1和credit2永远为0.0元  
  > 当用户登入时，会将credit1和credit2的余额转移到mc_member表的相应字段中，  
  > 而被转移字段会被清0
* __credit3__ _DECIMAL(10,2)_ 不可提现余额  
* __credit4__ _DECIMAL(10,2)_ 红包额
* __noticeset__ _TEXT_ 通知设置(数组序列化字段)
  > 'key'='recharge_ok' 非空时，设置不会发送短信(逻辑可有问题)。相关代码位置notice.php->sendMemberLogMessage() 第1078行  
  > 当后台充值时，将检查本字段的设置
* __fixagentid__ _TINYINT(3)_ 是否固定上级
  >0:不固定，1：固定    
  >固定上级后，任何条件也无法改变其上级。  
  >如果不选择上级创客，且固定上级，则上级 永远为总店（是创客）或无上线（非创客）  
  > 如不固定，则创客任何时候购买商品后，都将成为对方的下线
  
        // commission/core/model.php --> checkOrderConfirm($orderid)
        if ($parent_is_agent) { //对方必须是创客
    		if ($become_child == 1) {  //????
    			if (empty($member['agentid']) && ($member['id'] != $parent['id'])) { //不是对方的下线
    				if (empty($member['fixagentid'])) {  //自己不固定上级
    				    ....成为他的下线...
    	}	}	}	}

* __inviter__ _INT(11)_ 成为创客的邀请者,关联本表id字段
* __status__ _TINYINT(1)_ 状态    
  > 1表示有效(？),本记录创建时值为0. 其它值未知     
  > 在判断是否是创客时，需要验证 _(__isagent__==1 && __status__ == 1)_     
  > 此字段跟创客申请审核有关，0可能表示未审核
* __clickcount__ _INT(11)_ 点击次数，具体功能不知
* __openid__ _VARCHAR(50)_ 微信相关字段
* __nickname__ _VARCHAR(255)_ 微信相关字段
* __avatar__ _VARCHAR(255)_ 头像，微信相关字段
* __gender__ _TINYINT(3)_ 性别，微信相关字段sex
* __province__ _VARCHAR(255)_ 微信相关字段
* __city__ _VARCHAR(255)_ 微信相关字段
* __realname__ _VARCHAR(20)_ 姓名，初次申请时必填
* __mobile__ _VARCHAR(11)_ 手机号，初次申请时必填
* __mobileverify__ _TINYINT(3)_ 手机号是否验证
* __weixin__ _VARCHAR(100)_ 微信号，初次申请时必填
* __createtime__ _INT(10)_ 记录创建时间
* __agenttime__ _INT(10)_ 成为（审核通过）创客的时间
* __isblack__ _INT(11)_ 是否关小黑屋（1显示：暂时无法访问，请稍后再试!）
* __agentlevel__ _INT(11)_ 创客自定义级别(如有)
  >创客自定义级别，对应commission_level.id  
  >自定义级别相比默认级别，可以规定特殊的佣金比率
* __openid_qq__ _VARCHAR(50)_ 备用的qq openid,历史遗留字段，通常为null
* __openid_wx__ _VARCHAR(50)_ 备用的wx openid,历史遗留字段, 通常为null

## mc_mapping_fans 微信基础用户表
   > 本表跟随微信服务器的推送  
   > 通常本表的记录和mc_member的记录同时创建
* __fanid__ _INT(11)_ 主键
* __uid__ _INT(11)_ 对应mc_member.uid
* __openid__ _VARCHAR(50)_ 
* __nickname__ _VARCHAR(50)_ 
* __groupid__ _INT(11)_ 用户组，对应mc_groups.groupid，功能未知
* __salt__ _VARCHAR(8)_ 盐值，随机生成的字符串
* __follow__ _TINYINT(1)_ 是否关注
* __followtime__ _INT(11)_ 关注时间
* __unfollowtime__ _INT(11)_ 取消关注时间
* __tag__ _VARCHAR(1000)_ 微信userinfo的base64(serialize(...))结果
* __updatetime__ _INT(11)_ 最后更新时间

## mc_members 基础用户表
* __uid__ _INT(10)_ 主键
* __email__ _VARCHAT(50)_ 

      $email = md5($openid) . '@we7.cc';
      
* __password__ _VARCHAR(32)_ 密码(md5结果)
* __salt__ _VARCHAR(8)_ 盐值
* __groupid__ _INT(11)_ 用户组，对应me_groups.groupid
* __credit1__ _DECIMAL(10,2)_ 积分  
* __credit2__ _DECIMAL(10,2)_ 余额  
* __credit3__ _DECIMAL(10,2)_ 【新增】不可提现余额  
* __credit4__ _DECIMAL(10,2)_ 【新增】红包额  
* __credit5__ _DECIMAL(10,2)_ 
* __credit6__ _DECIMAL(10,2)_ 
  > creditX系列字段，只有此字段可以为负值 (/framework/model/mc.mod.php 529)  
  > 用户的各项资金/积分值以本表为准。
* __nickname__ _VARCHAR(20)_ 昵称,若来自微信则取userinfo.nickname
* __avatar__ _VARCHAR(255)_ 头像，若来自微信则取userinfo.headimgurl
* __gender__ _TINYINT(1)_ 性别 0未知 1男 2女，若来自微信则取userinfo.sex
* __nationality__ _VARCHAR(30)_ 国籍，若来自微信则取userinfo.country
* __resideprovince__ _VARCHAR(30)_ 常住省份，若来自微信则取userinfo.province+'省'
* __residecity__ _VARCHAR(30)_ 常住城市，若来自微信则取userinfo.city + '市'
* __createtime__ _INT(11)_ 创建时间

## ewei_shop_member_log   eweishop会员日志
* __id__ _INT(11)_ 主键
* __openid__ _VARCHAR(50)_ 所属用户
* __type_ _TINYINT(3)_ ????
  > 0: 余额充值  
  > 1: 余额提现  
  > 2: 奖金打款  
  > 3: 不可提现余额充值  
  > 4: 红包充值  
  #### 日志记录
  > 余额和不可提现余额的充值和提现，记录在两张表中，  
  > ewei_shop_member_log表和ewei_shop_perm_log中。前一张表记录变动情况，后一张表记录操作背景。
  > 账户资金变动明细的日志在 mc_credits_records 表中 
* __logno__ _VARCHAR(255)_ 记录号(两位大写字的类型号+YmdHis+六位随机数)
  > 类型号RC表示余额充值记录  
  > 类型号RW表示余额提现记录
* __title__ _VARCHAR(255)_ 日志标题
  > 充值时的标题是：十方创客会员充值
* __createtime__ _INT(11)_ 创建时间
* __status__ _INT(11)_ ?????
  > 1: 余额充值已完成，并发送微信通知和短信通知  
  >      如果是提现时，表示已提现成功。  
  > 3: 充值退款成功，并发送微信通知和短信通知   
  > 0: 提现申请已成功提交  
  > -1: 提现审核失败
* __money__ _DECIMAL(10,2)_ 变动资金 
  > 实际到帐金额 money + gives
* __rechargetype__ _VARCHAR(255)_ 充值类型
  > system: 后台充值  
  > wechat: 微信支付  
  > alipay: 支付宝
* __deductionmoney__ _DECIMAL(10,2)_ 提现扣款
* __gives__ _DECIMAL(10,2)_ 赠送的金额
* __remark__ _VARCHAR(255)_ 备注
* __applytype__ _TINYINT(3)_ 提现打款类型
  > 0: 提现到微信钱包    
  > 2: 提现到支付宝  
  > 3: 提现到银行卡  


## mc_credits_records 资金/积分变动日志
> 修改资金变动应使用 /framework/model/mc.mod.php --> mc_credit_update(...)函数  
> mc_credit_update(...)第4个参数,应符合如下格式：
```PHP 
    $log = array(
        10086,             //operator操作员id，用户自主操作时填0
        '更新了xxxx操作',   //remark备注
        'ewei_shopv2',     //module模块名
        10086,             //clerk_id员工编号，用户自主操作时填0
        0,                 //store_id，不清楚怎么用
        1,                 //clerk_type 员工操作的类型
    );
``` 
* __id__ _INT(11)_ 主键
* __uid__ _INT(11)_ 用户uid，对应mc_members.id
* __credittype__ _VARCHAR(10)_ credit类型,值为credit1,credit2,...,credit6
* __num__ _DECIMAL(10,2)_ 变动值(正增加，负减少)
* __createtime__ _INT(11)_ 创建时间
* __operator__ _INT(11)_  操作员id，若用户自主行为，则此处为同本记录uid
  > 后台充值时，此处填写登入后台帐号的uid
* __module__ _VARCHAR(30)_ 模块名,比如ewei_shopv2
* __clerk_id__ _INT(11)_ 操作员工编号，若为用户自主行为，则此处为0
* __store_id__ _INT(11)_ 通常都是0，作用未知
* __clerk_type__ _TINYINT(3)_ 员工操作的类型或性质
  > 0: 不适用  1: 线上操作   2： 系统后台   3: 店员
* __remark__ _VARCHAR(200)_ 备注

## ims_stat_fans 公众号粉丝增减日志
> 本表已完全。后台入口的几张图表数据来源于此表。
* __id__ _INT(11)_ 主键
* __uniacid__ _INT(11)_ 公众号id
* __new__ _INT(11)_ 新关注人数
* __cancel__ _INT(11)_ 取消关注人数
* __cumluate__ _INT(11)_ 累积关注
* __date__ _VARCHAR(8)_ 日期(yyyyMMdd)

## ewei_shop_commission_shop
> 此表存放创客店铺商品
* __id__ _INT(11)_ 主键
* __mid__ _INT(10)_ 店铺主人 member.id
* __selectgoods__ _TINYINT(3)_ 是否开启小店
* __selectcategory__ _TINYINT(3) 是否开启分类
* __goodsids _TEXT_ 店铺商品
  > goods.id之间用逗号隔开
  
## ewei_shop_commission_level
> 此表存放自定义用户级别的定义,对应member.agentLevel  
> 自定义级别相比默认级别，可以规定特殊的佣金比率  
> 系统的默认等级有需要时也会映射到本表的id=0
* __id__ _INT(11)_ 主键
* __levelname__ _VARCHAR(50)_ 等级名称(对应：默认等级)
* __commission1__   _DECIMAL(12,2)_ 一级佣金比率
* __commission2__   _DECIMAL(12,2)_ 二级佣金比率
* __commission3__   _DECIMAL(12,2)_ 三级佣金比率
* __commission_px__ _DECIMAL(12,2)_ 旁系佣金比率


## ewei_shop_order
> 此表存放订单
* __id__ _INT(11)_ 主键
* __openid__ _VARCHAR(50)_ 买家的openid
* __agentid__ _INT(11)_
  > 通常为该创客的直接上级创客的 member.id  
  > 如果开放内购，则此字段可以是买家自己的member.id
* __pxagentid__ _INT(11)_ 【新增】
  > 售货的旁系创客member.id  
  > 如果买家无法成为其下线(原因可能是固定上级)  
* __ordersn__ _VARCHAR(30)_ 订单号
  > 平台其它商户的订单号以ME开头  
  > 自营的订单号以SH开头
* __price__ _DECIMAL(10,2)_ 订单金额或应收款
  ```PHP
    /*
    price  = 商品金额(goods_price)  
           + 运费(dispatchprice) 
           - 任务活动优惠(taskiscountprice) 
           - 会员折扣(discountprice) 
           - 积分抵扣(deductprice)
           - 余额抵扣(deductcredit2) ????
           - 商城满额立减(deductenough)
           - 商户满额立减(merchdeductenough)
           - 优惠券优惠(couponprice)
           - 促销优惠(isdiscountprice)
           - 重复购买优惠(buyagainprice)
        (+/-)卖家改价(changeprice)
        (+/-)卖家改运费(changedispatchprice)
    */
  ```    
* __goodsprice__ _DECIMAL(10,2)_ 订单的商品金额
* __dispatchprice__ _DECIMAL(10,2)_ 快递费
* __status__  _TINYINT(3)_ 订单状态
  > 0：待付款    
  > 1：待发货   
  > 2：待收货   
  > 3：已完成   
  >-1：已关闭(订单取消时会设为此值)
* __paytype__ _TINYINT(1)_ 支付方式(实际上此处数据类型是__TINYINT(3))
  > 0: 未支付  
  > 1: 余额支付  
  > 3: 货到付款  
  > 11: 后台付款   
  > 21: 微信支付  
  > 22: 支付宝支付      
  > 23: 银联支付  
* __finishtime__ _INT(11)_ 完成时间(也就是用户确认收货时)
* __addressid__ _INT(11)_ 收货地址，对应member_address.id
* __refundid__ _INT(11)_ 退货（退款）流程id, 对应order_refund.id 为0表时不适用
* __refundstate__ _TINYINT(3)_ 退款状态
  > 0: 确认收货时该值设为0  
  > 1: 发现退款申请时设为1
* __taskiscountprice__ _DECIMAL(10,2)_ 任务活动优惠
* __discountprice__ _DECIMAL(10,2)_ 会员折扣
* __deductprice__ _DECIMAL(10,2)_ 积分抵扣
* __deductcredit__ _INT(10)_ 使用掉的积分(转化成deductprice)
* __deductcredit2__ _DECIMAL(10,2)_ 余额抵扣
  > 该字段的设计初衷应该是为了订单使用的余额credit2,但是数据表中的所有该字段都为0.00，  
  > 系统支持混合支付，但猜测可能是觉得过于复杂被废弃。
* __deductenough__ _DECIMAL(10,2)_ 商城满额立减 
  > 在创建订单时，将会检查是否设置了满额立减  
  > 请在营销模块中设置满额立减  
* __credit3pay__ _DECIMAL(10,2)_ 【新增】使用不可提现余额支付额 
* __credit4pay__ _DECIMAL(10,2)_ 【新增】使用红包支付额
* __merchdeductenough__ _DECIMAL(10,2)_ 商户满额立减
* __couponprice__ _DECIMAL(10,2)_ 优惠券优惠(参见本表couponid字段)
* __isdiscountprice__ _DECIMAL(10,2)_ 促销优惠
* __buyagainprice__ _DECIMAL(10,2)_ 重复购买优惠
* __changeprice__ _DECIMAL(10,2)_ 卖家改价
* __changedispatchprice__ _DECIMAL(10,2)_ 卖家改运费
* __couponid__ _INT(11)_ 使用掉的优惠券id(参见本表couponprice字段)。对应ewei_shop_coupon_data.id
* __canceltime__ _INT(11)_ 订单取消的时间
* __closereason__ _TEXT_ 订单关闭的原因
  > 订单取消时，会设置本字段
* __isparent__ _TINYINT(1)_ 是否为父订单
* __parentid__ _INT(11)_ 父订单id
* __verifycode__ _VARCHAR(255)_ 自提码（通常为8位数字）

## ewei_shop_order_goods
> 此表存放订单中的商品  
> 各级创客的可提现佣金、待收货佣金等都在此表查询（使用 __status[1/2/3]__ 字段判断状态）。
* __orderid__ _INT(11)_ 订单编号，对应order.id
* __parentorderid__ _INT(11)_ 所属的总订单id
  > 如果订单被拆分，此处设置总订单的编号。总订单在查询旗下所有商品时，可以使用此字段。  
* __goodsid__ _INT(11)_ 商品id，对应goods.id
* __optionid__ _INT(11)_ 商品的细分规格id，对应goods_option.id
* __goodssn__ _VARCHAR(255)_ 商品编号
* __productsn__ _VARCHAR(255)_ 商品条码
* __price__  _DECIMAL(12,5)_ 价格
  > = goods.marketprice * goods.total
* __total__ _INT(11)_ 购买的商品数量
* __realprice__ _DECIMAL(10,2)_ 真实价格
  > 貌似也是商品总价的东西
* __commissions__ _TEXT_ 佣金列表(序列化)
  > ```PHP
  > //反序列化后
  >  { 'level1'=>105.35, 'level2'=>105.35, 'level3'=>0 }
  > //反序列化前
  >  a:3:{s:6:"level1";d:105.35;s:6:"level2";d:105.35;s:6:"level3";i:0;}
  > ```
  > 各级创客可获得的佣金表。如没有对应的级别，则使用default默认设置。  
  > 本字段在最终计算某个创客佣金时，比本表 __commission(1/2/3)__ 字段的优先级要高
* __commission1__ _TEXT_ 一级创客佣金（序列化字段）
   ```PHP
      a:1:{s:7:"default";d:5.2000000000000002;}
   ```
  > 在计算创客总佣金中的一级佣金时，如果 __commissions__ 字段为空，则使用本字段。
* __applytime1__ _INT(11)_ 一级佣金申请时间
* __checktime1__ _INT(11)_ 一级佣金审核通过时间
* __paytime1__ _INT(11)_   一级佣金支付时间
* __status1__ _TINYINT(3)_ 一级佣金支付状态
  > 0: 提现可申请  
  > 1: 提现申请中  
  > 2: 提现待确认  
  > 3: 提现已支付   
  > -1: 无效奖金
* __content1__ _TEXT_ 一级佣金备注
* __commission2__ _TEXT_ 二级创客佣金(序列化字段，参见 __commission1__
* __applytime2__ _INT(11)_ 二级佣金申请时间
* __checktime2__ _INT(11)_ 二级佣金审核通过时间
* __paytime2__ _INT(11)_   二级佣金支付时间
* __status2__ _TINYINT(3)_ 二级佣金支付状态
  > 含义参考 __status1__
* __content2__ _TEXT_ 二级佣金备注
* __commission3__ _TEXT_   三级创客佣金(序列化字段，参见 __commission1__
* __applytime3__ _INT(11)_ 三级佣金申请时间
* __checktime3__ _INT(11)_ 三级佣金审核通过时间
* __paytime3__ _INT(11)_   三级佣金支付时间
* __status3__ _TINYINT(3)_ 三级佣金支付状态
  > 含义参考 __status1__
* __content3__ _TEXT_ 三级佣金备注
* __commissionpx__ _TEXT_   【新增】旁系佣金(序列化字段，参见 __commission1__
* __applytimepx__ _INT(11)_ 【新增】旁系佣金申请时间
* __checktimepx__ _INT(11)_ 【新增】旁系佣金审核通过时间
* __paytimepx__ _INT(11)_   【新增】旁系佣金支付时间
* __statuspx__ _TINYINT(3)_ 【新增】旁系佣金支付状态
  > 含义参考 __status1__
* __contentpx__ _TEXT_ 【新增】旁系佣金备注
* __nocommission__ _TINYINT(3)_ 是否没有佣金
  > 如果本字段为1，则在统计创客的奖金时略过本条记录

## ewei_shop_order_refund 退货（退款）流程表
* __id__ _INT(11)_ 主键
* __orderid__ _INT(11)_ 订单编号，对应order.id
* __refundno__ _VARCHAR(255)_ 退款申请号(或维权编号)
* __price__ _VARCHAR(255)_ 退款金额(订单金额)
  > 该字段实际上存储了数字  
* __reason__ _VARCHAR(255)_ 理由
* __content_ _TEXT_ 维权说明
* __createtime__ _INT(11)_ 维权创建的时间戳
* __refundtype__ _TINYINT(3)_ 退款方式
  > 注：本字段通常在后台处理退款后设置。  
  > 0: 退回到余额（或不可提现余额)  
  > 1: 微信企业付款退款  
  > 2: 退回到微信钱包  
* __rtype__ _TINYINT(3)_ 售后类型
  > 0: 退款   1: 退货退款   2:换货
* __status__ _TINYINT(3)_ 处理状态
  > -2: 客户取消  
  > -1: 已拒绝  
  >  0: 等待商家处理申请  
  >  1: 完成  
  >  3: 等待客户退回物品  
  >  4: 客户退回物品，等待商家重新发货  
  >  5: 等待客户收货  
* __applyprice__ _DECIMAL(10,2)_ 申请退款金额
* __refundaddress__ _TEXT__
* __expresscom__ _VARCHAR(100)_ 客户退货的快递公司名称
* __expresssn__ _VARCHAR(100)_  客户退货的快递单号
* __sendtime__ _INT(11)_ 客户发货时间
* __rexpresscom__ _VARCHAR(100)_ 商家回寄的快递公司名称
* __rexpresssn__ _VARCHAR(100)_  商家回寄的快递单号
* __returntime__ _INT(11)_ 商家回寄的时间

## ewei_shop_commission_apply 提现申请
* __id__ _INT(11)_ 主键
* __applyno__ _VARCHAR(255)_ 提现申请号
* __mid__ _INT(11)_ 申请人id, 对应member.id
* __orderids__ _TEXT_ 本次提现对应的订单号(序列化字段)
    ```PHP
    array (
       0 =>  array ('orderid' => '30', 'level' => 1),
       1 =>  array ('orderid' => '31', 'level' => 1),
       2 =>  array ('orderid' => '37', 'level' => 1),
    )
    ```
* __commission__ _DECIMAL(12,4)_ 提现申请的金额
* __type__ _TINYINT(3)_ 提现到账类型
  > 0: 余额  
  > 1: 提到到微信  
  > 2: 提现到支付宝  
  > 3: 提到到银行卡  
* __commission_pay__ _DECIMAL(12,4)_ 提现给付的金额???
* __status__ _TINYINT(3)_ 提取状态
  > 1: 平台待审核   2: 待打款   3: 已完成   -1:关闭
* __realmoney__ _DECIMAL(10,2)_ 扣税（如有）后的实际到账金额
* __deductionmoney__ _DECIMAL(10,2)_ 缴纳的个人所得税
* __charge__ _DECIMAL(10,2)_ 提现手续费
* __applytime__ _INT(11)_ 申请时间
* __checktime__ _INT(11)_ 审核时间
* __paytime__   _INT(11)_ 支付时间

ewei_shop_goods 商品表
======================================================
* __id__ _INT(11)_ 主键
* __displayorder__ _INT(11)_ 显示顺序
* __title__ _VARCHAR(100)_ 商品名称
* __subtitle__ _VARCHAR(255)_ 商品副标题
* __shorttitle__ _VARCHAR(255)_ 商品短标题
* __unit__ _VARCHAR(5)_ 商品单位 (若不设置，默认为'件')
* __type__ _TINYINT(1)_ 商品类型
  > 1: 实体商品  
  > 2: 虚拟商品  
  > 3: 虚拟物品(卡密)  
  > 注：类型2和类型3，免邮费，不能加入购物车，只能单独购买。
* __virtualsend__ _TINYINT(1)_ 虚拟商品的自动发货(仅当type=2时有意义)
* __virtualsendcontent__ _TEXT_ 虚拟商品的自动发货的内容(仅当type=2时有意义)
* __keywords__ _VARCHAR(255)_ 关键字(多个时以|分隔)
* __cates__ _TEXT_ 商品所属的分类，各分类id用逗号隔开。对应category.id
* __isrecommand__ _TINYINT(1)_ 是否推荐(此处拼写错误，正确的英文是recommend)
* __isnew__ _TINYINT(1)_ 是否新品
* __ishot__ _TINYINT(1)_ 是否热卖
* __issendfree__ _TINYINT(1)_ 是否包邮
* __isnodiscount__ _TINYINT(3)_ 是否不参与会员折扣
* __marketprice__ _DECIMAL(10,2)_ 售价(实际销售价格)
* __productprice__ _DECIMAL(10,2)_ 原价
* __costprice__ _DECIMAL(10,2)_ 进货价
* __thumb__ _VARCHAR(255)_ 商品头图(第一张商品图片)
* __thumb_url__ _TEXT_ 商品图片（序列化字段）
* __thumb_first__ _TINYINT(3)_ 详情显示首图
* __sales__ _INT(11)_ 销量(后台可以设定一个初值，然后会跟据totalcnf进行增减)
* __dispatchtype__ _TINYINT(1)_ 运费设置
  > 0: 使用运费模板  
  > 1: 统一运费，运费就是 __dispatchprice__  
  > 2: 自提
* __dispatchprice__ _decimal(10,2)_ 快递费
* __dispatchid__ _TINYINT(11)_ 运费模板对应ewei_shop_dispatch.id
* __province__ _VARCHAR(255)_ 商品所在的省份 
* __city__ _VARCHAR(255)_ 商品所有的城市
* __cash__ _TINYINT(3)_ ???
  > 2: 支持货到付款  
* __invoice__ _TINYINT(3)_ 开具发票
* __quality__ _TINYINT(3)_ 标志：正品保证
* __seven__ _TINYINT(3)_   标志：七天包退换标志
* __repair__ _TINYINT(3)_  标志：保修
* __labelname__ _TEXT_ 自定义标签(json格式)
* __cannotrefund__ _TINYINT(3)_ 退换货支持
* __autoreceive__ _INT(11)_ 确认收货的天数
  > 0: 读取系统设置   
  > -1: 不自动收货  
  > 其他值：自动确认收货的时间
* __status__ _TINYINT(1)_ 状态
  > 0: 已经下架  
  > 1: 表示正常  
  > 2: marketprice清零，作为赠品销售
--------------------------------------------------------------------------------------
* __goodssn__ _VARCHAR(50)_ 商品编码
* __productsn__ _VARCHAR(50)_ 商品条码
* __weight__ _DECIMAL(10,2)_ 重量(单位：克)
* __total__ _INT(11)_ 库存
  > 如果商品没有选项option，则库存以此为准  
  > 参见 ewei_shop_goods_option.stock
* __showtotal__ _TINYINT(1)_ 是否显示库存
* __totalcnf__ _INT(11)_ 减库存方式
  > 0 拍下减库存 1 付款减库存 2 永不减库存
* __hasoption__ _INT(11)_ 是否启用多规格
  > 非0表示有多规格
--------------------------------------------------------------------------------------
* 参数编辑参见：ewei_shop_goods_param表
--------------------------------------------------------------------------------------
* __content__ _TEXT_ 商品详情(通常由html标签的<p>和<img>构成)
* __buyshow__ _TINYINT(1)_ 是否购买过的商品才显示以上的详情
--------------------------------------------------------------------------------------
* __maxbuy__ _INT(11)_ 单次最多购买(用户单次购买此商品数量限制)
  > 为0时表示不限制
* __minbuy__ _INT(11)_ 单次最低购买(用户单次必须最少购买此商品数量限制)
  > 为0时表示不限制
* __usermaxbuy__ _int(11)_ 最多购买(用户购买过的此商品数量限制)
* > 为0时表示不限制
* __showlevels__ _TEXT_ 向某个等级的用户开放显示
  > 各等级间用,隔开  
  > 非空，member.level在其间，则必定向该用户显示该商品  
  > 空, 则看 __showgroups__，如也为空，则允许显示
* __buylevels__ _TEXT_ 允许购买的会员等级
  > 为空时表示不限制，各等级间以，隔开 
* __showgroups__ _TEXT_ 向某个组别的用户开放显示
  > 各组别使用,隔开  
  > 非空，member.groupid在其间，则必定向该用户显示  
  > 空，则看 __showlevels__, 如也为空，则允许显示  
  > 联合 __showlevels__ 字段查看是否显示该商品  
  ```PHP
  $showlevels = (($goods['showlevels'] != '' ? explode(',', $goods['showlevels']) : array()));
  $showgroups = (($goods['showgroups'] != '' ? explode(',', $goods['showgroups']) : array()));
  $showgoods = 0;
  	
  if ((!empty($showlevels) && in_array($member['level'], $showlevels)) ||    //等级符合
      (!empty($showgroups) && in_array($member['groupid'], $showgroups)) ||  //组别符合
      (empty($showlevels) && empty($showgroups))){                            //都为空
     $showgoods = 1;
  }   
   ```
 * __buygroups__ _TEXT_ 允许购买的会员组别
   > 为空时表示不限制，各等级间以,隔开  
   > (__buylevels__, __buygroups__) ~ (__showlevels__, __showgroups__) 
--------------------------------------------------------------------------------------
* __isdiscount__ _TINYINT(1)_         促销方式：是否促销(本条与__istime__ 是互斥关系)
* __isdiscount_time__ _INT(11)_       促销截止时间
* __isdiscount_title__ _VARCHAR(255)_ 促销标题
* __isdiscount_discounts__ _TEXT_     促销价员(分会员等级)
  > 可能单规格商品无效?????  查看core/web/goods/post.php:450行左右  
  ```PHP
    {
        "type":1,   //1:有效  0:无效
        "default":{"option211":"25.9","option212":"25.9"}   //请区分纯数字和带%的数字
    }
  ```
* __istime__ _TINYINT(1)_     促销方式：是否限时购
* __timestart__ _INT(11)_     限时购的起始时间
* __timeend__ _INT(11)_       限时购的结束时间
* __credit__ _VARCHAR(255)_ 购买此商品赠送的积分
  > 此字段有两种格式，一种是纯数字，赠送的积分=goods.credit*goods.realprice  
  > 另外一种形式是xx%，赠送的积分=xx/100*goods.realprice  
  > 注：赠送的积分直接进入mc_members.credit1中
* __money__ _VARCHAR(255)_ 购买此商品后余额返现（或称赠送的余额） 
  > 可以设置成xxx%的形式，最终的返现额 = money * order_goods.realprice  
  > 也可以设置成数定，最终的返现额 = money * order_goods.total  
  > 注：余额返现直接进入mc_members.credit2中
* __deduct__ _DECIMAL(10,2)_ 积分最多抵扣货款
* __manydeduct__ _TINYINT(1)_ 允许多件累计抵扣
* __ednum__ _INT(11)_ 单品满件包邮的件数(0或空表示不支持)
* __edmoney__ _DECIMAL(10,2)_ 单品满额包邮(0或空表示不支持)
* __edareas__ _TEXT_ 不参与单品包邮的地区(多个以;分隔)
* __buyagain__ _DECIMAL(10,2)_ 重复购买折扣
* __buyagain_islong__ _TINYINT(1)_ 购买一次后,以后都使用这个折扣
  > 意思是：此商品新设置的重复购买折扣率不适用老用户??
* __buyagain_condition__ _TINYINT(1)_ 重复购买使用条件,是付款后还是完成后 , 默认是付款后
* __buyagain_sale__ _TINYINT(1)_ 重复购买时,是否与其他优惠共享!其他优惠享受后,在使用这个折扣
--------------------------------------------------------------------------------------
* __discounts__ _TEXT_ 会员折扣(json格式)
  ```PHP
    //分规格详细设置折扣
    {
        "type":1,   
        "default":{"option374":"10","option375":"10"},  //分规格设置，全都是折扣
        "level1":{"option374":"9","option375":"9"},
        "level2":{"option374":"8","option375":"8"},
        "level3":{"option374":"7","option375":"7"},
        "level4":{"option374":"6","option375":"6"}
    }
    //统一设置折扣
    {
        "type":"0",
        "default":"10",     //折扣率, 若折扣和会员价都设置，则优先适用折扣
        "default_pay":"10", //会员价
        "level1":"9",
        "level1_pay":"11",
        "level2":"8",
        "level2_pay":"10",
        "level3":"7",
        "level3_pay":"9",
        "level4":"6","level4_pay":"8"
    }

  ```
--------------------------------------------------------------------------------------
* __needfollow__ _TINYINT(3)_ 购买此商品是否需要关注
* __followtip__ _VARCHAR(255)_ 关注提示
* __followurl__ _VARCHAR(255)_ 关注引用页地址(如无，则使用默认的关注页面)
* __share_title__ _VARCHAR(255)_ 分享标题(如无则为商品名称)
* __share_icon__ _VARCHAR(255)_ 分享图标(如无则为商品缩略图片)
* __description__ _VARCHAR(1000)_ 分享描述
--------------------------------------------------------------------------------------
* __noticeopenid__ _VARCHAR(255)_ 应通知的商家openid
* __noticetype__ _TEXT_ 订单通知时机
  > 0:下单通知   1:付款通知    2:买家收货通知  
  > 此字段可以由多个通知时机组成，由逗号分隔
--------------------------------------------------------------------------------------
* __nocommission__ _TINYINT(3)_ 是否不参与分销
  > 1:不参与分销，此商品不产生佣金
* __sharebtn__ _TINYINT(1)_ 
  > 0: 弹出关注提示层  
  > 1: 跳转至商品海报
* __commission_thumb__ _VARCHAR(255)_ 海报图片(为空默认缩略图)
* __hascommission__ _TINYINT(3)_ 启用独立佣金设置
  > 指本商品以下的设置覆盖系统的佣金率设置
* __commission1_rate__ _DECIMAL(10,2)_ 统一设置：一级分销佣金率
* __commission1_pay__ _DECIMAL(10,2)_  统一设置：一级分销佣金额
* __commission2_rate__ _DECIMAL(10,2)_ 统一设置：二级分销佣金率
* __commission2_pay__ _DECIMAL(10,2)_  统一设置：二级分销佣金额
* __commission3_rate__ _DECIMAL(10,2)_ 统一设置：三级分销佣金率
* __commission3_pay__ _DECIMAL(10,2)_  统一设置：三级分销佣金额
* __commissionpx_rate__ _DECIMAL(10,2)_ 【新增】统一设置：旁系分销佣金率
  > 旁系分销是指售货给已成为他人粉丝（或创客）的分销行为。  
  > 通常上下线关系设置为： 固定上级  
  > 若不固定上级，买了某创客的商品就会成为他的粉丝，此时的佣金依据一二三级分销计算，不会再有旁系佣金。
* __commissionpx_pay__  _DECIMAL(10,2)__ 【新增】统一设置：旁系分销佣金额
* __commission__ _TEXT_ 分规格详细分销佣金设置
  ```php
   {
        "type":1,                           //此处为0时本字段无效，计算佣金时应适用统一设置
        "default":{                         //此处default表示默认分组，还有可能会是level1,level2等
            "option374":["1","2","3"],      //分别为一级，二级，旁系
            "option375":["1.1","1.2","1.3"] //如加上%号，则佣金是商品金额的百分比
        }
   }
  ```
--------------------------------------------------------------------------------------
* __isverify__ _TINYINT(3)_ 是否支持线下核销(指线上支付线下消费或提货)
* __verifytype__ _TINYINT(1)_ 核销类型（参数的具体作用未知）????
  > 0: 按订单核销，不管购买多少一次核销完成  
  > 1: 按次核销，一个消费码使用多次(购买的数量)  
  > 2: 按消费码核销，多个消费码一次核销一个
* __storeids__ _TEXT_ 支持的核销门店id，多个时以逗号分隔
--------------------------------------------------------------------------------------
* __diyformtype__ _TINYINT(1)_ 自定义表单形式
  > 0: 关闭  
  > 1: 使用模版  
  > 2: 自定义
* __diyformid__ _INT(11)_ 自定义模版id
* __diysave__ _TINYINT(1)_ 自定义模版保存为表单模版(插入ewei_shop_diyform_type表)
--------------------------------------------------------------------------------------
* __detail_shopname__ _VARCHAR(255)_ 店铺名称
* __detail_totaltitle__ _VARCHAR(255)_ 店铺描述
* __detail_btntext1__ _VARCHAR(255)_ 按钮一的文字(若空，则为“全部商品”)
* __detail_btnurl1__ _VARCHAR(255)_ 按钮一的链接(若空，则为“全部商品”的链接)
* __detail_btntext2__ _VARCHAR(255)_ 按钮二的文字(若空，则为“进店逛逛”)
* __detail_btnurl2__ _VARCHAR(255)_ 按钮二的链接(若空，则为默认的小店或商城链接)
--------------------------------------------------------------------------------------
* __diypage__ _INT(11)_ 自定义模版(参见ewei_shop_diypage表)
  > 如果系统设置详情页模板，商品也设置，则商品的优先级高于系统的。
--------------------------------------------------------------------------------------
* __deleted__ _TINYINT(3)_ 删除状态
    ```PHP
      status == 1 && empty( deleted ) //商品可购买
    ```
* __minprice__ _DECIMAL(10,2)_ 商品低价
* __maxprice__ _DECIMAL(10,2)_ 商品高价
* __merchid__ _INT(11)_ 商户的id 
  > 平台自营 此值为0  
  > 需要开启多商户插件
* __checked__ _TINYINT(3)_ 商品审核
  > 可能仅适用于其它商户（开启多商户）
* __salesreal__ _INT(11)_ 真实销量(sql如下)
 ```PHP
    $salesreal = pdo_fetchcolumn(
        'select ifnull(sum(total),0) from ' .tablename('ewei_shop_order_goods') . ' og ' . 
            ' left join ' . tablename('ewei_shop_order') . ' o on o.id = og.orderid ' . 
            ' where og.goodsid=:goodsid and o.status>=1 and o.uniacid=:uniacid limit 1', 
        array(':goodsid' => $g['goodsid'], ':uniacid' => $_W['uniacid']));
  ```
* __nohongbaouse__ _TINYINT(3)_ 【新增】本商品禁止使用红包抵扣(默认1)
* __specifyhongbao__ _TINYINT(3)_ 【新增】启用独立的红包抵扣设置(默认0)
* __hongbaoamount__ _VARCHAR(45)_ 【新增】统一设置的红包抵扣额或抵扣比例(加上%表示抵扣比例)
* __hongbaoconfig__ _TEXT_ 【新增】分规格设置红包的抵扣政策，json数组。
 ```php
   {
        "type":1,                           
        //此处为0时本字段无效，计算时应适用统一红包抵扣设置
        "default":{           //此处default表示默认分组，还有可能会是level1,level2等
            "option374":"1"，
            "option375":"10%" //如加上%号，则佣金是商品金额的百分比
        }
   }
  ```
 * __gethongbao__ _DECIMAL(10,2)_ 【新增】购买此商品后，获得的红包奖励
 * __gethongbaotype__ _TINYINT(3)_ 【新增】获赠红包的方式，0确认收货后，1下单后
 * __becomeck__ _TINYINT(3)_ 【新增】购买后是否成为创客
 * __becomecktype__ _TINYINT(3)_ 【新增】成为创客的方式，0确认收货后，1下单后

## ewei_shop_goods_spec 商品规格大类表
> 指一个商品拥有哪些规格类，比如'颜色','口味','大小'等大类
* __id__ _INT(11)_ 主键
* __goodsid__ _INT(11)_ 商品id， 同 goods.id
* __title__ _VARCHAR(50)_ 规格名称
* __displayorder__ _INT(11)_ 显示次序
* __content__ _INT(11)_ 细分规格项(序列化字段)
  > {i:0;s:3:"287";i:1;s:3:"288";} 对应spec_item表

## ewei_shop_goods_spec_item
> 细分规格项表(完整)
* __id__ _INT(11)_ 主键
* __specid__ _INT(11)_ 规格大类表，对应spec表
* __title__ _VARCHAR(255)_ 名称
* __thumb__ _VARCHAR(255)_ 图片url
* __show__ _INT(11)_ 是否显示
* __displayorder__ _INT(11)_ 显示顺序
* __valueId__ _VARCHAR(255)_ ?????
* __virtual__ _INT(11)_  ??? 对应virtual_type表

## ewei_shop_virtual_type
> 此表还没有内容
* __id__ _INT(11)_ 主键
* __title__ _VARCHAR(255)_ 名称

## ewei_shop_goods_param 商品参数表
* __id__ _INT(11)_ 主键
* __goodsid__ _INT(11)_ 商品id，同 goods.id
* __title__ _VARCHAR(50)_ 参数标题
* __value__ _TEXT_ 参数值
* __displayorder__ _INT(11)_ 显示次序

## ewei_shop_goods_option 商品选项表
> 当商品启用goods_option时，goods表的stock库存字段失效。
* __id__ _INT(11)_ 主键
* __goodsid__ _INT(11)_ 商品id，同goods.id
* __stock__ _INT(11)_ 商品库存
* __displayorder__ _INT(11)_ 显示排序
* __specs__ _TEXT_ 商品规定项

## ewei_shop_member_cart 购物车
* __id__ _INT(11)_  主键
* __openid__ _VARCHAR(100)_ 本记录所属的用户
* __goodsid__ _INT(11)_ 购物车上的商品goods.id
* __total__ _INT(11)_ 商品数量
* __marketprice__ _DECIMAL(10,2)_ 单价
* __deleted__ _TINYINT(1)_ 是否删除
* __optionid__ _INT(11)_ 商品具体规格.为0表示该商品无规格

## ewei_shop_category 商品分类
> ewei_shopv2实现了四级商品分类,一二三四级分类，其中一级分类没有parentid=0
* __id__ _INT(11)_ 主键
* __name__ _VARCHAR(50)_ 分类名称
* __thumb__ _VARCHAR(255)_ 图标
* __parentid__ _INT(11)_ 上级id,同本表id字段(顶级分类parentid=0)
* __displayorder__ _TINYINT(3)_ 排序
* __enabled__ _TINYINT(1)_ 启用标志

## ewei_shop_package 营销套餐

## ewei_shop_pachage_goods 

## ewei_shop_package_goods_option

## ewei_shop_member_address 地址
* __id__ _INT(11)_ 主键
* __openid__ _VARCH(50)_ 
* __realname__ _VARCHAR(20)_ 真实姓名
* __mobile__ _VARCHAR(11)_ 手机号码
* __province__ _VARCHAR(30)_ 省份
* __city__ _VARCHAR(30)_ 城市
* __area__ _VARCHAR(30)_ 区?
* __address__ _VARCHAR(300)_ 详细地址

## ims_account_wechats 微信公众号记录

## ewei_shop_sms_set 短信设置(各公众号一套独立设置)
* __id__ _INT(11)_ 主键
* __uniacid__ __INT(11)__ 公众号主号id
* __juhe__ _TINYINT(3)_ 网关聚合 非空即开启
* __juhe_key__ _VARCHAR(255)_ 网关聚合key
* __dayu__ _TINYINT(3)_ 阿里大鱼 非空即开启
* __dayu_key__ _VARCHAR(255)_ 阿里大鱼key
* __dayu_secret__ _VARCHAR(255)_ 阿里大鱼secret
* __emay__ _TINYINT(3)_ 亿美软通 非空即开启
*
*  ...其余都是关于亿美的设置 

## ewei_shop_sms 短信模板设置
* __id__ _INT(11)_ 主键，模板id
* __uniacid__ _INT(11)_ 公众号主号id
* __data__ _TEXT_ ？？？ 
* __status__ _TINYINT(3)_ 状态  0未启用，非0已启用
* __type__ _VARCHAR(10)_  类型
  > dayu: 阿里大鱼
  > juhe: 聚合
  > emay: 亿美软通
* __smssign__ _VARCHAR(255)_ 短信签名
  > 短信签名用于阿里大鱼和亿美网通，聚合好象没有使用

## ewei_shop_perm_log 权限使用日志
   > 本表已完全
* __id__ _INT(11)_ 主键
* __uid__ _INT(11)_ 用户id
* __uniacid__ _INT(11)_ 主公众号id
* __name__ _VARCHAR(255)_ 权限名称(中文)
* __type__ _VARCHAR(255)_ 权限类别(英文，如goods.edit之类)
* __op__ _TEXT_ 操作内容详细记录
* __createtime__ _INT(11)_ 创建时间
* __ip__ _VARCHAR(255)_ 操作的ip地址

## core_paylog 支付日志
* __plid__ _INT(11)_ 主键
* __type__ _VARCHAR(20)_ 支付方式
  > cash: 现金支付(余额)  
  > wechat: 微信支付
* __openid__ _VARCHAR(40)_ 微信openid
  > 实际使用时，该字段总是存放整数(member.uid)   
  > 对应mc_members.uid字段
* __tid__ _VARCHAR(128)_ 订单号 ordersn 
* __module__ _VARCHAR(50)_ 模块名，通常填ewei_shopv2
* __fee__ _DECIMAL(10,2)_ 订单金额(或应付款)
* __status__ _TINYINT(4)_ 支付状态
  > 0: 未支付  
  > 1: 已支付  
* __tag__ _VARCHAR(200)_ 标记（序列化关联数组）
  > 如果使用微信支付、支付宝、银联等第三方支付，可能会有一个回调过程  
  > 本字段记录一些必要的数据。如: trasaction_id等。  
  > 特别地，使用余额支付时保存credit2(可提现余额)和credit3(不可提现余额)  
  > 的支付情况。

## ewei_shop_groups_goods  拼团商品
* __id__ _INT(11)_ 主键
* __title__ _VARCHAR(255)_ 商品名称
* __category__ _TINYINT(3)_ 商品分类id，对应groups_category.id
* __deleted__ _TINYINT(3)_ 是否下降，非0值表示下架
* __stock__ _INT(11)_ 库存数量
* __sales__ _INT(11)_ 销量
* __teamnum__ _INT(11)_ 拼团销量
  > 一次订单支付后，stock--, sales++, teamnum++
* __price__ _DECIMAL(10,2)_ 原价
* __groupprice__ _DECIMAL(10,2)_ 拼团价

## ewei_shop_groups_category 拼团分类
* __id__ _INT(11)_ 主键
* __name__ _VARCHAR(50)_ 分类名称

## ewei_shop_groups_order  拼团订单
* __id__ _INT(11)_ 主键
* __openid__ _VARCHAR(45)_  买家的openid
* __orderno__ _VARCHAR(45)_ 订单号(以PT开头)
* __goodid__ _INT(11)_ 商品id，对应group_goods.id
* __teamid__ _INT(11)_ 拼团的团号
* __groupnum__ _INT(11)_ 拼团的最低有效订单数
  > 如果某个teamid的订单总数小于groupnum，则表示组团失败。
* __price__ _DECIMAL(11,2)_ 订单金额
  > 注意：在ewei_shop_order里，price字段包含运费。而团购订单的price不包含运费。  
  > 如需计算订单金额应加上运费 price + freight  
  > 应收款 = price + freight - creditmoney
* __freight__ _DECIMAL(11,2)_ 运费
* __discount__ _DECIMAL(10,2)_ 团长优惠
  > 商品小计 = price + discount  
  > 估计 团长的discount值应为负值，而团员的discount值应为0.00
* __creditmoney__ _DECIMAL(11,2)_ 积分抵扣
* __credit__ _INT(11)_ 订单使用掉的积分,支付完成后将从member.credit1中扣减
* __status__ _INT(9)_ 订单状态
  > 0: 买家下单后  
  > 1: 卖家付款后  
  > 2: 卖家发货后  
  > 3: 订单完成后  
  >-1: 已关闭
* __pay_type__ _VARCHAR(45)_ 订单支付类型
  > #空#：未支付   
  > credit: 余额支付  
  > wechat: 微信支付  
  > alipay: 支付宝支付  
  > system: 系统虚拟  
  > #其它#: 其他方式 
* __success__ _INT(2)_ ?
  > 当该值为-1时可能表示活动失效  
  > 1: 拼团成功，同时活动结束，该teamid不可再接纳订单
  > 当 0 时表示正在进行??
  > 该字段可能表示是否拼团成功
* __address__ _VARCHAR(255)_ 详细地址(序列化字段)
  > 应有province, city, area, address字段
* __addressid__ _INT(11)_ 地址编号，对应member_address.id
  > 该字段的优先级小于address字段。若address字段非空，则忽略此字段。
* __createtime__ _INT(11)_ 订单创建时间
* __paytime__ _INT(11)_    订单支付时间
* __sendtime__ _INT(45)_   订单发货时间
* __finishtime__ _INT(11)_ 订单完成时间
* __refundtime__ _INT(11)_ 订单退款时间
* __starttime__ _INT(11)_ ?
  > 该值在支付完成后设置成等于paytime
* __expresssn__ _VARCHAR(45)_   快递单号
* __expresscom__ _VARCHAR(100)_ 快递公司名称
* __express__ _VARCHAR(45)_     快递公司代号，用于查询物流状态时使用
* __remark__ _VARCHAR(255)_ 备注

## ewei_shop_groups_paylog 拼团支付日志
* __plid__ _INT(11)_ 主键
* __openid__ _VARCHAR(40)_ 日志所属的微信openid
* __tid__  _VARCHAR(64)_ 订单号，对应groups_order.orderno
* __credit__ _INT(10)_ ?
  > 对应groups_order.credit字段
* __creditmoney__ _DECIMAL(10,2)_ 积分抵扣额，对应groups_order.creditmoney
* __fee__ _DECIMAL(10,2)_ 应收金额
  > fee = groups_order.price(商品总价) - creditmoney(积分抵扣额) + groups_order.freght(运费)
* __status__ _TINYINT(4)_ 支付状态
  > 0: 未支付  
  > 1: 已支付  
* __module__ _VARCHAR(50)_ 模块名，此处通常填groups


## ewei_shop_groups_order_refund 拼团订单退款表
* __id__ _INT(11)_ 主键
* __openid__ _VARCHAR(45)_ 申请退款的微信用户openid
* __orderid__ _INT(11)_ 拼团订单编号
* __refundno__ _VARCHAR(45)_ 退款编号(以PR开头)


## ewei_shop_gift
* __id__ _INT(11)_ 主键
* __title__ _VARCHAR(255)_ 赠品标题
* __goodsid__ _VARCHAR(255)_ 指定哪些商品附带本记录指明的赠品
  > 多个商品以逗号隔开
* __giftgoodsid__ _VARCHAR(255)_ 指定哪个商品作为赠品
  > 赠品也是作为一个商品，存放于ewei_shop_goods中,但其goods.status=2  
  > 多个赠品时以逗号分隔
* __activity__ _TINYINT(3)_ ?
* __status__ _TINYINT(3)_   ?
  > 商品详情页，搜索时有条件 activity = 2 And status = 1
* __thumb__ _VARCHAR(255)_ 赠品缩略图
* __starttime__ _INT(11)_ 开始赠送时间
* __endtime__ _INT(11)_ 结束赠送时间


## ewei_shop_coupon_data 用户优惠券持有/使用表
* __id__ _INT(11)_ 主键
* __openid__ _VARCHAR(255)_ 持有人
* __couponid__ _INT(11)_ 具体优惠券，对应ewei_shop_coupon.id
* __gettype__ _TINYINT(3)_ 获取方式 
  > 0: 发放  
  > 1: 领取  
  > 2: 积分商城
* __used__ _INT(11)_ 是否使用, 实际上本字段仅存放0和1两个值，可当作TINYINT(3)来使用
* __usetime__ _INT(11)_ 使用时间
* __gettime__ _INT(11)_ 获取时间
* __ordersn__ _VARCHAR(255)_ 订单编号,对应order.ordersn


## ewei_shop_coupon 优惠券表
* __id__ _INT(11)_ 主键
* __catid__ _INT(11)_ 类型，对应ewei_shop_coupon_category.id
* __couponname__ _VARCHAR(255)_ 优惠券名称
* __gettype__ _TINYINT(3)_ 本优惠券的获取方式
* 

## ewei_shop_coupon_category 优惠券分类表


## ewei_shop_coupon_log 优惠券日志


## ewei_shop_diypage 商城DIY页面
* __id__ _INT(11)_ 主键
* __type__ _TINYINT(1)_ 页面性质
  > 页面性质定义在/diypage/core/model.php的函数getPageType()  
  > $_S['diypage']['page']  存放商城首页、会员中心、创客中心和商品详情页的模板配置  
  >    
  >      array(
  >        "home" => "5",       //商城首页，使用ewei_shop_diypage.id=5的记录
  >        "member" => "",      //使用默认模板
  >        "commission" => "",
  >        "detail" => "11"     //商品详情页，使用ewei_shop_diypage.id=11的记录
  >      );
  >
  > 1: 自定义 diy('')  
  > 2: 商城首页 sys(success)  
  > 3: 会员中心 sys(primary)  
  > 4: 创客中心 sys(warning)  
  > 5: 商品详细页 sys(danger)  
  > 6: 商品列表页 sys(info)  
  >99: 公用模块页面 mod('')  
  > 
* __name__ _VARCHAR(255)_ 页面名称
* __keyword__ _VARCHAR(255)_ 关键字(有什么用？)
* __data__ _LONGTEXT_ 页内数据
  > 编码格式：base64_encode(json_encode($data))，取出时需先base64解码再json解码  
  > 典型结构为(未json_encode和未base64_encode之前)：  
  >
  >     array(
  >         "page"  => array(
  >             "type" => "5",               //type含义同本表type字段
  >             "title" => "商品详情", 
  >             "name" => "商品详情页111",   //同本表name字段
  >             "desc" => "",
  >             "icon" => "",
  >             "keyword" => "",
  >             "background" => "#fafafa", 
  >             "diymenu" => "-1",
  >             "followbar" => "0",
  >             "visit" => "0",
  >             "visitlevel" => array("member"=>"", "commission"=>""),
  >             "novisit" => array("title" => "", "link" => "")
  >         ),
  >         "items" => array(
  >             "M1477539768093" => array(
  >                 "type" => "5",
  >                 "max" => "1",
  >                 "params" => array(
  >                      "goodsdata" => "0", //当id='goods'时存在
  >                                           // 0: items.M1477539768093.data存放商品id
  >                                           // 1: 请读取params.cateid并处理之
  >                                           // 2: 请读取params.groupid并处理之
  >                                           // >=3: 特定临时商品组合(不同条件的sql从goods表查出)
  >                                           //                      同时使用goodssort排序
  >                                           //   3:  新品 isnew=1
  >                                           //   4:  热门 ishot=1
  >                                           //   5:  推荐 isrecommand=1 *ewei_shopv2单词拼写错误*
  >                                           //   6:  折扣 isdiscount=1
  >                                           //   7:  包邮 issendfree=1
  >                                           //   8:       istime=1
  >                      "cateid" => 66,
  >                      "catename" => "床上用品", //当成功读取了cateid分类，则设置此值
  >                      "groupid" => 3,   //商品组合，从数据库加载完组合商品后,
  >                                        //商品记录覆盖items.M1477539768093.data
  >                      "goodsnum" => 4,  //商品组合的显示数量(用在sql的limit子句上)
  >                      "goodssort" => 0,  //商品组合的排序(用在sql上)
  >                                         // 0: ' order by displayorder desc'
  >                                         // 1: ' order by sales desc, displayorder desc'
  >                                         // 2: ' order by minprice desc, displayorder desc'
  >                                         // 3: ' order by minprice asc, displayorder desc'
  >                      "noticedata" => "0", //当id='notice'时存在，结果覆盖items.M1477539768093.data
  >                                           // 0: 倒序查询notice表，limit限值为noticenum的记录
  >                      "noticenum" =>  "3", //若empty(...)，则默认此值为5，
  >                                           //用于查询ewei_shop_notice表的sql的limit子句
  >                      "content" => "xxxxxxxx", // 当id='richtext'时存在，表示富文本
  >                                               // 存放在数据库是其base64_encode版本
  >                      "linkurl" = > '?', //当id='bindmobile'时，将设置本项数据至mobileUrl('member/bind')
  >                      "bindurl" => '?',    //当id='logout'时，将被设置成member.changepwd(有绑定)或bind页面
  >                      "logouturl" => '?',  //当id='logouot'时，将设置登出url
  >                      "avatar" => $member['avatar'],              //当id='memberc'时(即创客会员)，
  >                      "canwithdraw" => $member['commission_ok'],  //一些$member和$commission的数据设置于此
  >                       ...                                        //这里省略一些创客信息
  >                  ),
  >                 "data" => array(    //本数据若被goods或notice表覆盖，索引key是C1000000000~C9999999999
  >                                     //当id='listmenu'时，将直接读取本数据.
  >                                     //当id='icongroup'时，将直接读取本数据.
  >                                     //当id='blockgroup'时，将直接读取本数据.
  >                      array(
  >                         "gid" => 878,        //当id='goods'时存在本项
  >                         "text" => "xx",      //当id='listmenu'时存在本项
  >                         "linkurl" => "xxx",  //当id='listmenu'或'icongroup'或'blockgroup'时存在本项
  >                         "dotnum" => '?',     //当id='listmenu'或'icongroup'时存在本项，并经处理后添加
  >                         "iconclass" => '?',  //当id='icongroup'或'blockgroup'时存在本项
  >                         "tipnum" => '?',     //当id='blockgroup'时存在本项
  >                         "tiptext" => '?'     //当id='blockgroup'时存在本项
  >                      ), 
  >                      ...//重复以上
  >                 ),
  >                 "style" => array(
  >                      "dotstyle" => "round",
  >                      "dotalign" => "left",
  >                      "background" => "#ffffff",
  >                      "leftright" => "10",
  >                      "bottom" => "10",
  >                      "opacity" => "0.8"
  >                 ),
  >                 "info" => array(  //当id='member'时，将新增本数据项,$member为当前登入用户
  >						'avatar' => $member['avatar'], 
  >                     'nickname' => $member['nickname'], 
  >                     'levelname' => $member['levelname'], 
  >                     'textmoney' => $_W['shopset']['trade']['moneytext'], 
  >                     'textcredit' => $_W['shopset']['trade']['credittext'], 
  >                     'money' => $member['credit2'], 
  >                     'credit' => intval($member['credit1']));
  >                 ),
  >                 "id" => "detail_swipe", //可能值为：(对应的模板可能在
  >                                         //diypage/template/mobile/default/template/中找到,如tpl_<id>.html)
  >                                         //  goods,diymod,richtext,
  >                                         //  notice,listmenu,member,icongroup,bindmobile,
  >                                         //  logout,memberc,blockgroup,
  >                                         //  detail_swipe,detail_info,detail_sale,detail_spec,detail_package,
  >                                         //  detail_shop,picture,detail_store,detail_buyshow,detail_comment,
  >                                         //  detail_pullup,detail_navbar,detail_tab,
  >                                         //  <其它值>
  >             ),
  >             ... //重复以上
  >         ),
  >     )
* __createtime__ _INT(11)_ 创建时间
* __lastedittime__ _INT(11)_ 最后更新时间
 

## ewei_shop_diypage_menu  diypage菜单
* __id__ _INT(11)_ 主键
* __name__ _VARCHAR(255)_ 名称

## ewei_shop_diypage_template_category diypage模板分类
* __id__ _INT(11)_ 主键
* __name__ _VARCHAR(255)_ 名称

## ewei_shop_goods_group 商品组合
* __id__ _INT(11)_ 主键
* __name__ _VARCHAR(255)_ 组合名称
* __goodsids__ _VARCHAR(255)_ 组合的商品
  > 以逗号分隔各个商品id

## ewei_shop_notice 平台公告
* __id__ _INT(11)_ 主键
* __displayorder__ _INT(11)_ 排序
* __title__ _VARCHAR(255)_ 标题
* 
