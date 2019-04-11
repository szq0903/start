<?php
/**
 * 微信支付接口
 *
 */
namespace Wechat;

class WechatPay extends Common{
    /**
     * 使用说明：
     * 一、先配置好：$appid，$mch_id，$partnerKey。如果要用到 退款，企业付款等需要证书的接口，则需要配置 $ssl_cer, $ssl_key
     */

    /** 支付接口基础地址 */
    const MCH_BASE_URL = 'https://api.mch.weixin.qq.com';

    /** 公众号appid */
    public $appid = '';

    /** 商户身份ID */
    public $mch_id = '';

    /** 商户支付密钥Key */
    public $partnerKey = '';

    /** 证书路径 */
    public $ssl_cer = EXTEND_PATH . 'wechat' .'cert' . '/apiclient_cert.pem';
    public $ssl_key = EXTEND_PATH . 'wechat' .'cert' .  '/apiclient_key.pem';

    /** 执行错误消息及代码 */
    public $errMsg;
    public $errCode;

    /**
     * WechatPay constructor.
     * @param array $options
     */
    public function __construct() {
        parent::__construct();
        $this->appid = wxpay_appid();
        $this->mch_id = wxpay_mch_id();
        $this->partnerKey = wxpay_key();
    }


    /**
     * 设置标配的请求参数，生成签名，生成接口参数xml
     * @param array $data
     * @return string
     */
    protected function createXml($data) {
        if (!isset($data['wxappid']) && !isset($data['mch_appid']) && !isset($data['appid'])) {
            $data['appid'] = $this->appid;
        }
        if (!isset($data['mchid']) && !isset($data['mch_id'])) {
            $data['mch_id'] = $this->mch_id;
        }
        isset($data['nonce_str']) || $data['nonce_str'] = $this->createNoncestr();
        $data["sign"] =$this->getPaySign($data, $this->partnerKey);
        return $this->arr2xml($data);
    }

    /**
     * POST提交XML
     * @param array $data
     * @param string $url
     * @return mixed
     */
    public function postXml($data, $url) {
        return $this->httpsPost($url,  $this->createXml($data));
    }

    /**
     * 使用证书post请求XML
     * @param array $data
     * @param string $url
     * @return mixed
     */
    function postXmlSSL($data, $url) {
        return $this->httpsPost($url, $this->createXml($data), $this->ssl_cer, $this->ssl_key);
    }

    /**
     * POST提交获取Array结果
     * @param array $data 需要提交的数据
     * @param string $url
     * @param string $method
     * @return array
     */
    public function getArrayResult($data, $url, $method = 'postXml') {
        return $this->xml2arr($this->$method($data, $url));
    }

    /**
     * 解析返回的结果
     * @param array $result
     * @return bool|array
     */
    protected function _parseResult($result) {
        if (empty($result)) {
            $this->errCode = 'result error';
            $this->errMsg = '解析返回结果失败';
            return false;
        }
        if ($result['return_code'] !== 'SUCCESS') {
            $this->errCode = $result['return_code'];
            $this->errMsg = $result['return_msg'];
            return false;
        }
        if (isset($result['err_code']) && $result['err_code'] !== 'SUCCESS') {
            $this->errMsg = $result['err_code_des'];
            $this->errCode = $result['err_code'];
            return false;
        }
        return $result;
    }




    /**
     * 支付XML统一回复
     * @param array $data 需要回复的XML内容数组
     * @param bool $isReturn 是否返回XML内容，默认不返回
     * @return string
     */
    public function replyXml(array $data, $isReturn = false) {
        $xml = $this->arr2xml($data);
        if ($isReturn) {
            return $xml;
        }
        ob_clean();
        exit($xml);
    }

    /**
     * 获取预支付ID
     * @param string $openid 用户openid，JSAPI必填
     * @param string $body 商品标题
     * @param string $out_trade_no 第三方订单号
     * @param int $total_fee 订单总价
     * @param string $notify_url 支付成功回调地址
     * @param string $trade_type 支付类型JSAPI|NATIVE|APP
     * @param string $goods_tag 商品标记，代金券或立减优惠功能的参数
     * @return bool|string
     */
    public function getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI", $goods_tag = null) {
        $postdata = array(
            "body"             => $body,
            "out_trade_no"     => $out_trade_no,
            "total_fee"        => $total_fee,
            "notify_url"       => $notify_url,
            "trade_type"       => $trade_type,
            "spbill_create_ip" =>$this->getAddress()
        );
        empty($goods_tag) || $postdata['goods_tag'] = $goods_tag;
        empty($openid) || $postdata['openid'] = $openid;
        $result = $this->getArrayResult($postdata, self::MCH_BASE_URL . '/pay/unifiedorder');
        if (false === $this->_parseResult($result)) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return in_array($trade_type, array('JSAPI', 'APP')) ? $result['prepay_id'] : $result['code_url'];
    }

    /**
     * 获取二维码预支付ID
     * @param string $openid 用户openid，JSAPI必填
     * @param string $body 商品标题
     * @param string $out_trade_no 第三方订单号
     * @param int $total_fee 订单总价
     * @param string $notify_url 支付成功回调地址
     * @param string $goods_tag 商品标记，代金券或立减优惠功能的参数
     * @return bool|string
     */
    public function getQrcPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $goods_tag = null) {
        $postdata = array(
            "body"             => $body,
            "out_trade_no"     => $out_trade_no,
            "total_fee"        => $total_fee,
            "notify_url"       => $notify_url,
            "trade_type"       => 'NATIVE',
            "spbill_create_ip" => $this->getAddress()
        );
        empty($goods_tag) || $postdata['goods_tag'] = $goods_tag;
        empty($openid) || $postdata['openid'] = $openid;
        $result = $this->getArrayResult($postdata, self::MCH_BASE_URL . '/pay/unifiedorder');
        if (false === $this->_parseResult($result) || empty($result['prepay_id'])) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return $result['prepay_id'];
    }

    /**
     * 获取支付二维码
     * @param string $openid 用户openid，JSAPI必填
     * @param string $body 商品标题
     * @param string $out_trade_no 第三方订单号
     * @param int $total_fee 订单总价
     * @param string $notify_url 支付成功回调地址
     * @param string $goods_tag 商品标记，代金券或立减优惠功能的参数
     * @return bool|string

     */
    public function getQrcPayUrl($body, $out_trade_no, $total_fee, $notify_url, $goods_tag = null) {
        $postdata = array(
            "body"             => $body,
            "out_trade_no"     => $out_trade_no,
            "total_fee"        => $total_fee,
            "notify_url"       => $notify_url,
            "trade_type"       => 'NATIVE',
            "spbill_create_ip" => $this->getAddress()
        );
        empty($goods_tag) || $postdata['goods_tag'] = $goods_tag;
        empty($openid) || $postdata['openid'] = $openid;
        $result = $this->getArrayResult($postdata, self::MCH_BASE_URL . '/pay/unifiedorder');
        if (false === $this->_parseResult($result) || empty($result['prepay_id'])) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return $result['code_url'];
    }


    /**
     * 创建JSAPI支付参数包
     * @param string $prepay_id
     * @return array
     */
    public function createMchPay($prepay_id) {
        $option = array();
        $option["appId"] = $this->appid;
        $option["timeStamp"] = (string)time();
        $option["nonceStr"] =  $this->createNoncestr();
        $option["package"] = "prepay_id={$prepay_id}";
        $option["signType"] = "MD5";
        $option["paySign"] = $this->getPaySign($option, $this->partnerKey);
        $option['timestamp'] = $option['timeStamp'];
        return $option;
    }
    /**
     * 创建APP支付参数包
     * @param string $prepay_id
     * @return array
     */
    public function createAppPay($prepay_id) {
        $option = array();
        $option["appId"] = $this->appid;
        $option["partnerid"] = $this->partnerKey;
        $option["timeStamp"] = (string)time();
        $option["nonceStr"] =  $this->createNoncestr();
        $option["package"] = "Sign=WXPay";
        $option["prepayid"] = $prepay_id;
        $option["paySign"] = $this->getPaySign($option, $this->partnerKey);
        return $option;
    }

    /**
     * 关闭订单
     * @param string $out_trade_no
     * @return bool
     */
    public function closeOrder($out_trade_no) {
        $data = array('out_trade_no' => $out_trade_no);
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/closeorder');
        if (false === $this->_parseResult($result)) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return ($result['return_code'] === 'SUCCESS');
    }

    /**
     * 查询订单详情
     * @param $out_trade_no
     * @return bool|array
     */
    public function queryOrder($out_trade_no) {
        $data = array('out_trade_no' => $out_trade_no);
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/orderquery');
        if (false === $this->_parseResult($result)) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return $result;
    }

    /**
     * 订单退款接口
     * @param string $out_trade_no 商户订单号
     * @param string $transaction_id 微信订单号
     * @param string $out_refund_no 商户退款订单号
     * @param int $total_fee 商户订单总金额
     * @param int $refund_fee 退款金额
     * @param int|null $op_user_id 操作员ID，默认商户ID
     * @param string $refund_account 退款资金来源
     *      仅针对老资金流商户使用
     *          REFUND_SOURCE_UNSETTLED_FUNDS --- 未结算资金退款（默认使用未结算资金退款）
     *          REFUND_SOURCE_RECHARGE_FUNDS --- 可用余额退款
     * @return bool
     */
    public function refund($out_trade_no, $transaction_id, $out_refund_no, $total_fee, $refund_fee, $op_user_id = null, $refund_account = '') {
        $data = array();
        $data['out_trade_no'] = $out_trade_no;
        $data['transaction_id'] = $transaction_id;
        $data['out_refund_no'] = $out_refund_no;
        $data['total_fee'] = $total_fee;
        $data['refund_fee'] = $refund_fee;
        $data['op_user_id'] = empty($op_user_id) ? $this->mch_id : $op_user_id;
        !empty($refund_account) && $data['refund_account'] = $refund_account;
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/secapi/pay/refund', 'postXmlSSL');
        if (false === $this->_parseResult($result)) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return ($result['return_code'] === 'SUCCESS');
    }

    /**
     * 退款查询接口
     * @param string $out_trade_no
     * @return bool|array
     */
    public function refundQuery($out_trade_no) {
        $data = array();
        $data['out_trade_no'] = $out_trade_no;
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/refundquery');
        if (false === $this->_parseResult($result)) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return $result;
    }

    /**
     * 获取对账单
     * @param string $bill_date 账单日期，如 20141110
     * @param string $bill_type ALL|SUCCESS|REFUND|REVOKED
     * @return bool|array
     */
    public function getBill($bill_date, $bill_type = 'ALL') {
        $data = array();
        $data['bill_date'] = $bill_date;
        $data['bill_type'] = $bill_type;
        $result = $this->postXml($data, self::MCH_BASE_URL . '/pay/downloadbill');
        $json =  $this->xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            if ($this->debug) {
                return $json;
            }
            return false;
        }
        return $json;
    }

    /**
     * 发送现金红包
     * @param string $openid 红包接收者OPENID
     * @param int $total_amount 红包总金额
     * @param string $mch_billno 商户订单号
     * @param string $sendname 商户名称
     * @param string $wishing 红包祝福语
     * @param string $act_name 活动名称
     * @param string $remark 备注信息
     * @param null|int $total_num 红包发放总人数（大于1为裂变红包）
     * @param null|string $scene_id 场景id
     * @param string $risk_info 活动信息
     * @param null|string $consume_mch_id 资金授权商户号
     * @return array|bool
     * @link  https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_5
     */
    public function sendRedPack($openid, $total_amount, $mch_billno, $sendname, $wishing, $act_name, $remark, $total_num = 1, $scene_id = null, $risk_info = '', $consume_mch_id = null) {
        $data = array();
        $data['mch_billno'] = $mch_billno; // 商户订单号 mch_id+yyyymmdd+10位一天内不能重复的数字
        $data['wxappid'] = $this->appid;
        $data['send_name'] = $sendname; //商户名称
        $data['re_openid'] = $openid; //红包接收者
        $data['total_amount'] = $total_amount; //红包总金额
        $data['total_num'] = '1'; //发放人数据
        $data['wishing'] = $wishing; //红包祝福语
        $data['client_ip'] =  $this->getAddress(); //调用接口的机器Ip地址
        $data['act_name'] = $act_name; //活动名称
        $data['remark'] = $remark; //备注信息
        $data['total_num'] = $total_num;
        !empty($scene_id) && $data['scene_id'] = $scene_id;
        !empty($risk_info) && $data['risk_info'] = $risk_info;
        !empty($consume_mch_id) && $data['consume_mch_id'] = $consume_mch_id;
        if ($total_num > 1) {
            $data['amt_type'] = 'ALL_RAND';
            $api = self::MCH_BASE_URL . '/mmpaymkttransfers/sendgroupredpack';
        } else {
            $api = self::MCH_BASE_URL . '/mmpaymkttransfers/sendredpack';
        }
        $result = $this->postXmlSSL($data, $api);
        $json =  $this->xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            if ($this->debug) {
                return $json;
            }
            return false;
        }
        return $json;
    }


    /**
     * 现金红包状态查询
     * @param string $billno
     * @return bool|array
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_7&index=6
     */
    public function queryRedPack($billno) {
        $data['mch_billno'] = $billno;
        $data['bill_type'] = 'MCHT';
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/gethbinfo');
        $json =  $this->xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            if ($this->debug) {
                return $json;
            }
            return false;
        }
        return $json;
    }

    /**
     * 企业付款
     * @param string $openid 红包接收者OPENID
     * @param int $amount 红包总金额
     * @param string $billno 商户订单号
     * @param string $desc 备注信息
     * @return bool|array
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
     */
    public function transfers($openid, $amount, $billno, $desc) {
        $data = array();
        $data['mchid'] = $this->mch_id;
        $data['mch_appid'] = $this->appid;
        $data['partner_trade_no'] = $billno;
        $data['openid'] = $openid;
        $data['amount'] = $amount;
        $data['check_name'] = 'NO_CHECK'; #不验证姓名
        $data['spbill_create_ip'] =$this->getAddress(); //调用接口的机器Ip地址
        $data['desc'] = $desc; //备注信息
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/promotion/transfers');
        $json = $this->xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            if ($this->debug) {
                return $json;
            }
            return false;
        }
        return $json;
    }

    /**
     * 企业付款查询
     * @param string $billno
     * @return bool|array
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3
     */
    public function queryTransfers($billno) {
        $data['appid'] = $this->appid;
        $data['mch_id'] = $this->mch_id;
        $data['partner_trade_no'] = $billno;
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/gettransferinfo');
        $json =  $this->xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            if ($this->debug) {
                return $json;
            }
            return false;
        }
        return $json;
    }

    /**
     * 二维码链接转成短链接
     * @param string $url 需要处理的长链接
     * @return bool|string
     */
    public function shortUrl($url) {
        $data = array();
        $data['long_url'] = $url;
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/tools/shorturl');
        if (!$result || $result['return_code'] !== 'SUCCESS') {
            $this->errCode = $result['return_code'];
            $this->errMsg = $result['return_msg'];
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        if (isset($result['err_code']) && $result['err_code'] !== 'SUCCESS') {
            $this->errMsg = $result['err_code_des'];
            $this->errCode = $result['err_code'];
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return $result['short_url'];
    }

    /**
     * 发放代金券
     * @param int $coupon_stock_id 代金券批次id
     * @param string $partner_trade_no 商户此次发放凭据号（格式：商户id+日期+流水号），商户侧需保持唯一性
     * @param string $openid Openid信息
     * @param string $op_user_id 操作员帐号, 默认为商户号 可在商户平台配置操作员对应的api权限
     * @return bool|array
     * @link  https://pay.weixin.qq.com/wiki/doc/api/tools/sp_coupon.php?chapter=12_3
     */
    public function sendCoupon($coupon_stock_id, $partner_trade_no, $openid, $op_user_id = null) {
        $data = array();
        $data['appid'] = $this->appid;
        $data['coupon_stock_id'] = $coupon_stock_id;
        $data['openid_count'] = 1;
        $data['partner_trade_no'] = $partner_trade_no;
        $data['openid'] = $openid;
        $data['op_user_id'] = empty($op_user_id) ? $this->mch_id : $op_user_id;
        $result = $this->postXmlSSL($data, self::MCH_BASE_URL . '/mmpaymkttransfers/send_coupon');
        $json =  $this->xml2arr($result);
        if (!empty($json) && false === $this->_parseResult($json)) {
            if ($this->debug) {
                return $result;
            }
            return false;
        }
        return $json;
    }
    /**
     * 读取微信客户端IP
     * @return null|string
     */
     public function getAddress() {
        foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR') as $header) {
            if (!isset($_SERVER[$header]) || ($spoof = $_SERVER[$header]) === NULL) {
                continue;
            }
            sscanf($spoof, '%[^,]', $spoof);
            if (!filter_var($spoof, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $spoof = NULL;
            } else {
                return $spoof;
            }
        }
        return '0.0.0.0';
    }
}
