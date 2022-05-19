<?php
$data = curlPost('http://127.0.0.1:81/api/pay/unifiedorder',$_POST);
echo $data;die();

function curlPost($url = '', $postData = '', $options = array(),$timeOut=5)
{
    if (is_array($postData)) {
        $postData = http_build_query($postData);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //设置cURL允许执行的最长秒数
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    if (!empty($options)) {
        curl_setopt_array($ch, $options);
    }
    //https请求 不验证证书和host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);

    $headers = curl_getinfo($ch);
//        echo json_encode($headers);
    curl_close($ch);
    return $data;
}

