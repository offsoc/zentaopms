#!/usr/bin/env php
<?php

/**

title=设置瀑布项目阶段测试
timeout=0
cid=1

*/
chdir(__DIR__);
include '../lib/createstage.ui.class.php';

zendata('project')->loadYaml('project', false, 2)->gen(10);
$tester = new createstageTester();
$tester->login();

$waterfall = array(
    array('name_0' => '', 'begin_0' => date('Y-m-d'), 'end_0' => date('Y-m-d', strtotime('+30 days'))),
    array('begin_0' => '', 'end_0' => date('Y-m-d', strtotime('+30 days'))),
    array('begin_0' => date('Y-m-d'), 'end_0' => ''),
    array('begin_0' => '2024-08-02', 'end_0' => '2024-08-01'),
    array('name_0' => '需求阶段', 'begin_0' => '2024-06-27', 'end_0' => '2024-06-30'),
);

r($tester->createStage($waterfall['0'])) && p('message,status') && e('创建阶段表单页提示信息正确，success');                //校验阶段名称不能为空
r($tester->createStage($waterfall['1'])) && p('message,status') && e('创建阶段表单页提示信息正确，success');                //校验计划开始必填
r($tester->createStage($waterfall['2'])) && p('message,status') && e('创建阶段表单页提示信息正确，success');                //校验计划完成必填
r($tester->createStage($waterfall['3'])) && p('message,status') && e('创建阶段表单页提示信息正确，success');                //校验计划完成必须大于计划开始
r($tester->createStage($waterfall['4'])) && p('status') && e('success');                                                    //创建需求阶段

$tester->closeBrowser();