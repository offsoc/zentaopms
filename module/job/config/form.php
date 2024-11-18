<?php
$now = helper::now();

$config->job->form = new stdclass();

$config->job->form->create = array();
$config->job->form->create['name']            = array('type' => 'string', 'required' => true);
$config->job->form->create['engine']          = array('type' => 'string', 'required' => true);
$config->job->form->create['repo']            = array('type' => 'int',    'required' => true);
$config->job->form->create['reference']       = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->create['frame']           = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->create['product']         = array('type' => 'int',    'required' => false, 'default' => 0);
$config->job->form->create['sonarqubeServer'] = array('type' => 'int',    'required' => false, 'default' => 0);
$config->job->form->create['projectKey']      = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->create['jkServer']        = array('type' => 'int',    'required' => false, 'default' => 0);
$config->job->form->create['jkTask']          = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->create['createdDate']     = array('type' => 'string', 'required' => false, 'default' => $now);

$config->job->form->edit = array();
$config->job->form->edit['name']            = array('type' => 'string', 'required' => true);
$config->job->form->edit['engine']          = array('type' => 'string', 'required' => true);
$config->job->form->edit['repo']            = array('type' => 'int',    'required' => true);
$config->job->form->edit['reference']       = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->edit['frame']           = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->edit['triggerType']     = array('type' => 'array',  'required' => false, 'default' => array(), 'filter' => 'join');
$config->job->form->edit['svnDir']          = array('type' => 'array',  'required' => false, 'default' => array(), 'filter' => 'join');
$config->job->form->edit['product']         = array('type' => 'int',    'required' => false, 'default' => 0);
$config->job->form->edit['sonarqubeServer'] = array('type' => 'int',    'required' => false, 'default' => 0);
$config->job->form->edit['projectKey']      = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->edit['comment']         = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->edit['triggerActions']  = array('type' => 'array',  'required' => false, 'default' => array(), 'filter' => 'join');
$config->job->form->edit['atDay']           = array('type' => 'array',  'required' => false, 'default' => array(), 'filter' => 'join');
$config->job->form->edit['atTime']          = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->edit['jkServer']        = array('type' => 'int',    'required' => false, 'default' => 0);
$config->job->form->edit['jkTask']          = array('type' => 'string', 'required' => false, 'default' => '');
$config->job->form->edit['paramName']       = array('type' => 'array',  'required' => false, 'default' => array());
$config->job->form->edit['paramValue']      = array('type' => 'array',  'required' => false, 'default' => array());
$config->job->form->edit['autoRun']         = array('type' => 'int',    'required' => false, 'default' => 1);
$config->job->form->edit['editedDate']      = array('type' => 'string', 'required' => false, 'default' => $now);
