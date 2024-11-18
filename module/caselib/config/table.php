<?php
global $app, $lang;
$app->loadLang('testcase');

$config->caselib->testcase = new stdclass();

if($config->testcase->needReview || !empty($config->testcase->forceReview))
{
    $config->caselib->testcase->actionList['review']['icon']        = 'glasses';
    $config->caselib->testcase->actionList['review']['text']        = $lang->testcase->review;
    $config->caselib->testcase->actionList['review']['hint']        = $lang->testcase->review;
    $config->caselib->testcase->actionList['review']['url']         = array('module' => 'testcase', 'method' => 'review', 'params' => 'caseID={id}');
    $config->caselib->testcase->actionList['review']['data-toggle'] = 'modal';
}

$config->caselib->testcase->actionList['edit']['icon']  = 'edit';
$config->caselib->testcase->actionList['edit']['hint']  = $lang->testcase->edit;
$config->caselib->testcase->actionList['edit']['text']  = $lang->testcase->edit;
$config->caselib->testcase->actionList['edit']['url']   = array('module' => 'caselib', 'method' => 'editCase', 'params' => 'caseID={id}');
$config->caselib->testcase->actionList['edit']['order'] = 5;

$config->caselib->testcase->actionList['delete']['icon']         = 'trash';
$config->caselib->testcase->actionList['delete']['hint']         = $lang->testcase->delete;
$config->caselib->testcase->actionList['delete']['text']         = $lang->testcase->delete;
$config->caselib->testcase->actionList['delete']['url']          = array('module' => 'testcase', 'method' => 'delete', 'params' => 'caseID={id}');
$config->caselib->testcase->actionList['delete']['order']        = 10;
$config->caselib->testcase->actionList['delete']['className']    = 'ajax-submit';
$config->caselib->testcase->actionList['delete']['data-confirm'] = array('message' => $lang->testcase->confirmDelete, 'icon' => 'icon-exclamation-sign', 'iconClass' => 'warning-pale rounded-full icon-2x');

$config->caselib->dtable = new stdclass();
$config->caselib->dtable->fieldList = array();
$config->caselib->dtable->fieldList['id']       = $config->testcase->dtable->fieldList['id'];
$config->caselib->dtable->fieldList['title']    = $config->testcase->dtable->fieldList['title'];
$config->caselib->dtable->fieldList['pri']      = $config->testcase->dtable->fieldList['pri'];
$config->caselib->dtable->fieldList['type']     = $config->testcase->dtable->fieldList['type'];
$config->caselib->dtable->fieldList['status']   = $config->testcase->dtable->fieldList['status'];
$config->caselib->dtable->fieldList['openedBy'] = $config->testcase->dtable->fieldList['openedBy'];
$config->caselib->dtable->fieldList['actions']  = $config->testcase->dtable->fieldList['actions'];

$config->caselib->dtable->fieldList['id']['name'] = 'id';

$config->caselib->dtable->fieldList['title']['link']         = array('module' => 'caselib', 'method' => 'viewCase', 'params' => "caseID={id}");
$config->caselib->dtable->fieldList['title']['nestedToggle'] = false;

$config->caselib->dtable->fieldList['type']['show']   = true;
$config->caselib->dtable->fieldList['status']['show'] = true;

$config->caselib->dtable->fieldList['actions']['list']  = $config->caselib->testcase->actionList;
$config->caselib->dtable->fieldList['actions']['menu']  = array('review', 'edit', 'delete');
$config->caselib->dtable->fieldList['actions']['width'] = '80';
