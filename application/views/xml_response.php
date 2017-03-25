<?php
header('Content-Type: application/xml');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: http://bahadircyildiz.com:8100');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
echo $data;
?>