#!/usr/bin/env php
<?php

/**

title=管理产品线
timeout=0
cid=0

*/
chdir(__DIR__);
include '../lib/manageline.ui.class.php';

$tester = new manageLineTester();
$tester->login();

$line = new stdClass();
$line->name = '2024产品线';

r($tester->createLine($line)) && p('message,status') && e('产品线创建成功,SUCCESS');//创建产品线
r($tester->createLine($line)) && p('message,status') && e('产品线提示信息正确,SUCCESS');//产品线重名校验
r($tester->delLine($line))    && p('message,status') && e('删除成功,SUCCESS');//删除产品线
$tester->closeBrowser();
