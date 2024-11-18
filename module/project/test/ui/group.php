#!/usr/bin/env php
<?php

/**

title=创建项目权限分组
timeout=0
cid=1

- 创建项目分组提示信息检查测试结果 @项目创建分组提示信息正确
- 创建项目分组成功测试结果 @项目分组创建成功

*/

chdir(__DIR__);
include '../lib/group.ui.class.php';

zendata('project')->loadYaml('project', false, 1)->gen(1);
$tester = new groupTester();
$tester->login();

//设置项目分组数据
$project = array(
    array('groupname' => '', 'groupdesc' => ''),
    array('groupname' => '项目分组1', 'groupdesc' => '一个分组描述哈哈哈222'),
);

r($tester->createGroup($project['0'])) && p('message') && e('项目创建分组提示信息正确');  // 创建项目分组提示信息检查
r($tester->createGroup($project['1'])) && p('message') && e('项目分组创建成功');          // 创建项目分组成功

$tester->closeBrowser();
