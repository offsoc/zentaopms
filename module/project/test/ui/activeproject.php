#!/usr/bin/env php
<?php

/**

title=激活项目测试
timeout=0
cid=73

- 激活项目成功  测试结果 @激活项目成功

*/
chdir(__DIR__);
include '../lib/activeproject.ui.class.php';

$tester = new activeProjectTester();
$tester->login();

$project = array();

r($tester->activeProject($project)) && p('message') && e('激活项目成功');                      //激活项目

$tester->closeBrowser();