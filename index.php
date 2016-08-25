<?php
/**
  * wechat php test
  */
/*
1）将token、timestamp、nonce三个参数进行字典序排序
2）将三个参数字符串拼接成一个字符串进行sha1加密
3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
*/
//define your token
define("TOKEN", "lmts");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->valid();

class wechatCallbackapiTest
{
public function valid()
{
$echoStr = $_GET["echostr"];

//valid signature , option
if($this->checkSignature()&&$echoStr){
echo $echoStr;
exit;
}else{
$this->responseMsg();
}
}

public function responseMsg()
{
//get post data, May be due to the different environments
$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

//extract post data
if (!empty($postStr)){

$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$fromUsername = $postObj->FromUserName;
$toUsername = $postObj->ToUserName;
$keyword = trim($postObj->Content);
$time = time();
$textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>0</FuncFlag>
</xml>";
if(!empty( $keyword ))
{
$msgType = "text";
$ch = curl_init();
$url = 'http://apis.baidu.com/apistore/weatherservice/cityname?cityname='.$keyword;
$header = array(
'apikey: ac748c23d4ae12dcd0e6ddc89f1ad9e6'
);
// 添加apikey到header
curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 执行HTTP请求
curl_setopt($ch , CURLOPT_URL , $url);
$res = curl_exec($ch);

$ay=json_decode($res,true)['retData'];

$contentStr =$ay['city'].'现在天气'.$ay['weather'].',气温'.$ay['temp'].'度，最高温度'.$ay['h_tmp'].'度，最低温度'.$ay['l_tmp'].',风向'.$ay['WD'].',风力'.$ay['WS'];
$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
echo $resultStr;
}else{
echo "Input something...";
}

}else {
echo "";
exit;
}
}

private function checkSignature()
{
$signature = $_GET["signature"];
$timestamp = $_GET["timestamp"];
$nonce = $_GET["nonce"];

$token = TOKEN;
$tmpArr = array($token, $timestamp, $nonce);
sort($tmpArr);
$tmpStr = implode( $tmpArr );
$tmpStr = sha1( $tmpStr );

if( $tmpStr == $signature ){
return true;
}else{
return false;
}
}
}

?>