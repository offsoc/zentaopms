#!/usr/bin/env php
<?php

/**

title=批量编辑项目测试
timeout=0
cid=73

- 校验项目名称不能为空
 - 测试结果 @批量编辑项目表单页提示信息正确
 - 最终测试状态 @SUCCESS
- 校验计划开始不能为空
 - 测试结果 @批量编辑项目表单页提示信息正确
 - 最终测试状态 @SUCCESS
- 校验计划完成不能为空
 - 测试结果 @批量编辑项目表单页提示信息正确
 - 最终测试状态 @SUCCESS
- 批量编辑项目最终测试状态 @SUCCESS

*/
chdir(__DIR__);
include '../lib/batcheditproject.ui.class.php';

zendata('project')->loadYaml('execution', false, 2)->gen(10);
$tester = new batchEditProjectTester();
$tester->login();

$project = array(
    array('name' => ''),
    array('name' => '敏捷项目1', 'begin' => '', 'end' => '2022-01-31'),
    array('name' => '敏捷项目1', 'begin' => '2020-11-01', 'end' => ''),
    array('name' => '编辑敏捷项目1', 'begin' => '2020-11-02', 'end' => '2022-01-31'),
);

r($tester->batchEditProject($project['0'])) && p('message,status') && e('批量编辑项目表单页提示信息正确, SUCCESS'); //校验项目名称不能为空
r($tester->batchEditProject($project['1'])) && p('message,status') && e('批量编辑项目表单页提示信息正确, SUCCESS'); //校验计划开始不能为空
r($tester->batchEditProject($project['2'])) && p('message,status') && e('批量编辑项目表单页提示信息正确, SUCCESS'); //校验计划完成不能为空
r($tester->batchEditProject($project['3'])) && p('status') && e('SUCCESS'); //批量编辑项目

$tester->closeBrowser();
