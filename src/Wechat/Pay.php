<?php
namespace Whilegit\Wechat;

use Whilegit\Utils\Comm;
use Whilegit\Utils\IArray;
use Whilegit\Utils\IXml;
use Whilegit\Utils\Misc;

class Pay{
    protected $wechat;
    protected $mchid;
    protected $signkey;
    
    public function __construct(Wechat $wechat, $mchid, $signkey){
        $this->wechat = $wechat;
        $this->mchid = $mchid;
        $this->signkey = $signkey;
    }
    
    /**
     * 生成预支付订单号
     * @param string $openid       待支付人的openid
     * @param string $ordersn      内部订单号
     * @param string $body         此处填写商品名称
     * @param float $money         金额(单位元)
     * @param string $notify_url   通知地址
     * @param string $trade_type   交易类型(公众号网页支付填JSAPI, 还有NATIVE，APP等)
     * @param string $device_info  交易设备号，自定义字段，可以简单输入WEB
     * @param number $expire       支付有效期
     * @return array 返回空数组或有内容的数据。如返回的数组中有键值 prepay_id字段，则表明调用成功。
     */
    public function getPrepayId($openid, $ordersn, $body, $money, $notify_url, $trade_type = 'JSAPI', $device_info = 'WEB', $expire = 300){
        $data = array();
        $data['appid'] = $this->wechat->getAppId();
        $data['mch_id'] = $this->mchid; 
        $data['device_info'] = $device_info;
        $data['nonce_str'] = Misc::random(32);
        $data['sign_type'] = 'MD5';
        $data['body'] = $body;//'微动云商收银台';
        $data['out_trade_no'] = $ordersn;
        $data['total_fee'] = intval($money * 100);
        $data['spbill_create_ip'] = Misc::real_ip();
        $data['time_start'] = date('YmdHis');
        $data['time_expire'] = date('YmdHis', time() + $expire);
        $data['notify_url'] = $notify_url;
        $data['trade_type'] = $trade_type;
        $data['openid'] = $openid;
        $data['sign'] = $this->wechat->sign($data, $this->signkey);   //注：key为商户平台设置的密钥key
        $xml = IArray::toXml($data, 'xml');
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $result = Comm::post($url, $xml);
        $pack = array();
        if(is_array($result) && isset($result['content'])){
            $pack = IXml::xml2array($result['content']);
        }
        return $pack;
    }
    
    /**
     * 成生网页调起微信支付的参数数组
     * @param string $prepay_id  预支付码
     * @example <pre>
     * WeixinJSBridge.invoke(
     *      'getBrandWCPayRequest', 
     *      {
     *          'appId': wechat.appId,
     *          'timeStamp': wechat.timeStamp,
     *          'nonceStr': wechat.nonceStr,
     *          'package': wechat.package,
     *          'signType': wechat.signType,
     *          'paySign': wechat.paySign,
     *      }, 
     *      function (res) {
     *          if (res.err_msg == 'get_brand_wcpay_request:ok') {
     *          } else if(res.err_msg=='get_brand_wcpay_request:cancel') {
     *          } else {
     *          }
     *      }); </pre>
     * @return array 供js发起支付调用的关键参数
     */
    public function jsapiBridgeParams($prepay_id){
        $data = array();
        $data['appId'] = $this->wechat->getAppId();
        $data['timeStamp'] = ''. time();
        $data['nonceStr'] = Misc::random(32);
        $data['package'] = 'prepay_id=' . $prepay_id;
        $data['signType'] = 'MD5';
        $data['paySign'] = $this->wechat->sign($data, $this->signkey);   //注：key为商户平台设置的密钥key
        return $data;
    }
    
    /**
     * 支付后的微信通知, 转化并验证签名。成功时，返回数组，验签失败返回false。
     * @return NULL|array
     * @example <pre>
     * array (
     *    'appid' => 'wx00071da2ab2f2e3d',
     *    'bank_type' => 'CFT',
     *    'cash_fee' => '1',
     *    'device_info' => 'WEB',
     *    'fee_type' => 'CNY',
     *    'is_subscribe' => 'Y',
     *    'mch_id' => '1402291602',
     *    'nonce_str' => 'MZuuPYAL7rWuKXbKBUJFTChAnYghhXc4',
     *    'openid' => 'o1siSwZCEApI-VoJHKgesS_e-VnQ',
     *    'out_trade_no' => 'XX20180127150726766848',
     *    'result_code' => 'SUCCESS',
     *    'return_code' => 'SUCCESS',
     *    'sign' => 'A3F44DC61EA1F0B16057915E254F5F44',
     *    'time_end' => '20180127150730',
     *    'total_fee' => '1',
     *    'trade_type' => 'JSAPI',
     *    'transaction_id' => '4200000079201801271842834168',
     *  )
     *  </pre>
     */
    public function notifySignCheck(){
        $ret = null;
        $xml = file_get_contents('php://input');
        $data = IArray::parseXml($xml);
        if(!empty($data) && isset($data['sign'])){
            $inputSign = $data['sign'];
            unset($data['sign']);
            $calcSign = $this->wechat->sign($data, $this->signkey);   //注：key为商户平台设置的密钥key
            file_put_contents('not.txt', $calcSign . "\r\n" . $inputSign . "\r\n", FILE_APPEND);
            if($inputSign == $calcSign && $data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS'){
                $ret = $data;
            }
        }
        return $ret;
    }
}