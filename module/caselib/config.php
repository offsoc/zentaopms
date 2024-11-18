<?php
$config->caselib = new stdclass();
$config->caselib->create     = new stdclass();
$config->caselib->edit       = new stdclass();
$config->caselib->createcase = new stdclass();
$config->caselib->create->requiredFields     = 'name';
$config->caselib->edit->requiredFields       = 'name';
$config->caselib->createcase->requiredFields = 'title,type';

$config->caselib->editor = new stdclass();
$config->caselib->editor->create = array('id' => 'desc', 'tools' => 'simpleTools');
$config->caselib->editor->edit   = array('id' => 'desc', 'tools' => 'simpleTools');

$config->caselib->datatable = new stdclass();
$config->caselib->datatable->defaultField = array('id', 'pri', 'title', 'type', 'assignedTo', 'lastRunner', 'lastRunDate', 'lastRunResult', 'status', 'bugs', 'results', 'actions');

$config->caselib->custom = new stdclass();
$config->caselib->custom->createFields = 'stage,pri,keywords';
$config->caselib->customCreateFields   = 'stage,pri,keywords';

include dirname(__FILE__) . DS . 'config' . DS . 'form.php';

$config->caselib->actionList['edit']['icon']      = 'edit';
$config->caselib->actionList['edit']['hint']      = $lang->caselib->edit;
$config->caselib->actionList['edit']['text']      = $lang->edit;
$config->caselib->actionList['edit']['url']       = helper::createLink('caselib', 'edit', 'libID={id}');
$config->caselib->actionList['edit']['data-load'] = 'modal';

$config->caselib->actionList['delete']['icon']         = 'trash';
$config->caselib->actionList['delete']['hint']         = $lang->caselib->delete;
$config->caselib->actionList['delete']['text']         = $lang->caselib->delete;
$config->caselib->actionList['delete']['url']          = helper::createLink('caselib', 'delete', 'libID={id}');
$config->caselib->actionList['delete']['className']    = 'ajax-submit';
$config->caselib->actionList['delete']['data-confirm'] = array('message' => $lang->caselib->libraryDelete, 'icon' => 'icon-exclamation-sign', 'iconClass' => 'warning-pale rounded-full icon-2x');

$config->caselib->exportTemplateFields = array('module', 'title', 'precondition', 'stepDesc', 'stepExpect', 'keywords', 'pri', 'type', 'stage');

$config->caselib->exportFields = '
    id, module, title, precondition, stepDesc, stepExpect, keywords,
    pri, type, stage, status, stepNumber, openedBy, openedDate,
    lastEditedBy, lastEditedDate, version, linkCase, files';

$config->caselib->actions = new stdclass();
$config->caselib->actions->view['mainActions'] = array('edit', 'delete');
