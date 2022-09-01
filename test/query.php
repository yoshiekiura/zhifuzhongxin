<?php
$mchid = '100242';
$Md5key = '5d7f51a80cd1634bba628af8e2c1483b';
$requestUrl = 'http://www.zhongtongpay.com/api/pay/query';

$data = array(
    'o_trade_no' => '2205242132363432',
    'channel' => "h5_zfb",
    'mid' => $mchid
);

ksort($data);
$signData = http_build_query($data);

$signData = $signData."&key=".$Md5key;

$sign = md5($signData);
$data['sign'] = $sign;

print_r($data);die();
print_r($data);
//初始化
$curl = curl_init();
//设置抓取的url
curl_setopt($curl, CURLOPT_URL, $requestUrl);
//设置头文件的信息作为数据流输出
curl_setopt($curl, CURLOPT_HEADER, 0);
//设置获取的信息以文件流的形式返回，而不是直接输出。
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//设置post方式提交
curl_setopt($curl, CURLOPT_POST, 1);

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//执行命令
$data = curl_exec($curl);
//关闭URL请求
curl_close($curl);
//显示获得的数据
$data = json_decode($data, true);

var_dump($data);
?>
