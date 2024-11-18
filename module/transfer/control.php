<?php
declare(strict_types=1);
/**
 * The control file of transfer module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     transfer
 * @link        https://www.zentao.net
 */
class transfer extends control
{
    /**
     * 导出数据
     * Export datas.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function export(string $module = '')
    {
        if(!empty($_POST))
        {
            $this->transfer->export($module);
            $this->fetch('file', 'export2' . $_POST['fileType'], $_POST);
        }
    }

    /**
     * 导出模板
     * Export Template.
     *
     * @param  string $module
     * @param  string $params
     * @access public
     * @return void
     */
    public function exportTemplate(string $module, string $params = '')
    {
        if(!empty($_POST))
        {
            $this->loadModel($module);

            /* 获取工作流字段。*/
            /* Get workflow fields by module. */
            if($this->config->edition != 'open')
            {
                $groupID = $this->loadModel('workflowgroup')->getGroupIDByData($module, null);
                $action  = $this->loadModel('workflowaction')->getByModuleAndAction($module, 'exportTemplate', $groupID);

                if(!empty($action->extensionType) && $action->extensionType == 'extend')
                {
                    $appendFields = $this->loadModel('workflowaction')->getPageFields($module, 'exportTemplate', true, null, 0, $groupID);
                    foreach($appendFields as $appendField)
                    {
                        $this->lang->$module->{$appendField->field} = $appendField->name;
                        $this->config->$module->templateFields .= ',' . $appendField->field;
                    }
                }
            }

            /* 将参数转成变量并存到SESSION中。*/
            /* Set SESSION. */
            $params = $this->transferZen->saveSession($module, $params);
            extract($params);

            /* 获取系统内置字段列表. */
            /* Get system built-in field list. */
            $this->config->transfer->sysDataList = $this->transfer->initSysDataFields();

            /* 获取导出模板字段。*/
            /* Get export template fields. */
            $fields = $this->config->$module->templateFields;
            if($module == 'task' and isset($executionID)) $fields = $this->transferZen->processTaskTemplateFields((int)$executionID, $fields);

            /* 初始化字段列表并拼接下拉菜单数据。*/
            /* Init field list and append dropdown menu data. */
            $this->transferZen->initTemplateFields($module, $fields);

            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        $this->display();
    }

    /**
     * 导入数据。
     * Import.
     *
     * @param  string $module
     * @param  string $locate
     * @access public
     * @return void
     */
    public function import(string $module, string $locate = '')
    {
        $locate = $locate ? $locate : $this->session->showImportURL;
        if($_FILES)
        {
            $file = $this->loadModel('file')->getUpload('file');
            if(!empty($file['error'])) return $this->send(array('result' => 'fail', 'message' => $this->lang->file->uploadError[$file['error']]));

            $file      = $file[0];
            $shortName = $this->file->getSaveName($file['pathname']);
            if(empty($shortName)) return $this->send(array('result' => 'fail', 'message' => $this->lang->excel->emptyFileName));

            /* 将文件从临时目录移动到保存目录。 */
            /* Move file from temp dir to save dir. */
            $extension = $file['extension'];
            $fileName  = $this->file->savePath . $shortName;
            move_uploaded_file($file['tmpname'], $fileName);

            /* 读取Excel文件。*/
            /* Read Excel file. */
            $phpExcel  = $this->app->loadClass('phpexcel');
            $phpReader = new PHPExcel_Reader_Excel2007();
            if(!$phpReader->canRead($fileName))
            {
                $phpReader = new PHPExcel_Reader_Excel5();
                if(!$phpReader->canRead($fileName)) return $this->send(array('result' => 'fail', 'message' => $this->lang->excel->canNotRead));
            }

            /* 将文件目录和后缀信息保存到SESSION。*/
            /* Save file directory and extension info to session. */
            $this->session->set('fileImportFileName', $fileName);
            $this->session->set('fileImportExtension', $extension);

            return $this->send(array('result' => 'success', 'load' => $locate, 'closeModal' => true));
        }

        /* 项目和执行中的需求导入时应选择需求类型. */
        if($module == 'story')
        {
            $this->app->loadLang('story');

            $storyType = '';
            if($this->app->tab == 'project')
            {
                $project   = $this->loadModel('project')->fetchByID($this->session->project);
                $storyType = $project->storyType;
            }

            if($storyType)
            {
                foreach($this->lang->story->typeList as $key => $value)
                {
                    if(strpos(",$storyType,", ",$key,") === false) unset($this->lang->story->typeList[$key]);
                }
            }

            $this->view->isProjectStory = $this->app->tab == 'project';
            $this->view->typeList       = $this->lang->story->typeList;
        }

        $this->view->title = $this->lang->transfer->importCase;
        $this->display();
    }

    /**
     * Ajax get Tbody.
     *
     * @param  string $module
     * @param  int    $lastID
     * @param  int    $pagerID
     * @access public
     * @return void
     */
    public function ajaxGetTbody(string $module = '', int $lastID = 0, int $pagerID = 1)
    {
        $filter = '';
        if($module == 'task') $filter = 'estimate';
        if($module == 'story' and $this->session->storyType == 'requirement') $this->loadModel('story')->replaceUserRequirementLang();

        /* 获取该模块导入字段数据。*/
        /* Get the import field data of the module. */
        $this->loadModel($module);
        $importFields = !empty($_SESSION[$module . 'TemplateFields']) ? $_SESSION[$module . 'TemplateFields'] : $this->config->$module->templateFields;
        if($module == 'testcase' and !empty($_SESSION[$module . 'TemplateFields']) and is_array($importFields))
        {
            $this->config->$module->templateFields = implode(',', $importFields);
            $this->config->testcase->datatable->fieldList['branch']['dataSource']['params'] = '$productID&active';
        }

        /* 初始化字段列表。*/
        /* Init field list. */
        $fields      = $this->transfer->initFieldList($module, $importFields, false);
        $formatDatas = $this->transfer->format($module, $filter);
        $datas       = $this->transfer->getPageDatas($formatDatas, $pagerID);
        $fields      = $this->transferZen->formatFields($module, $fields);

        /* 处理任务统计列的数据。*/
        /* Process task statistics column data. */
        if($module == 'task') $datas = $this->task->processDatas4Task($datas);

        /* 构建导入表单。*/
        /* Build import form. */
        $html = $this->transfer->buildNextList($datas->datas, $lastID, $fields, $pagerID, $module);
        return print($html);
    }

    /**
     * Ajax 获取某个字段下拉菜单。
     * Ajax Get Options.
     *
     * @param  string $module
     * @param  string $field
     * @param  string $value
     * @param  string $index
     * @access public
     * @return void
     */
    public function ajaxGetOptions($module = '', $field = '', $value = '', $index = '')
    {
        $this->loadModel($module);
        $fields = $this->config->$module->templateFields;

        /* 如果是付费版，拼接工作流字符。*/
        /* If it is paid version, append workflow field. */
        if($this->config->edition != 'open')
        {
            $appendFields = $this->loadModel('workflowaction')->getFields($module, 'showimport', false);
            foreach($appendFields as $appendField) $fields .= ',' . $appendField->field;
        }

        $fieldList = $this->transfer->initFieldList($module, $fields, false);

        if(empty($fieldList[$field]['values'])) $fieldList[$field]['values'] = array('' => '');
        if(!in_array($field, $this->config->transfer->requiredFields)) $fieldList[$field]['values'][''] = '';

        /* 如果字段是多选下拉则字段名称为$field[$index][]。*/
        /* If the field is multiple select, the field name is $field[$index][]. */
        $multiple = $fieldList[$field]['control'] == 'multiple' ? 'multiple' : '';
        $name     = $field . "[$index]";
        if($multiple == 'multiple') $name .= "[]";

        return print(html::select($name, $fieldList[$field]['values'], $value, "class='form-control picker-select' data-field='$field' data-index='$index' $multiple"));
    }
}
