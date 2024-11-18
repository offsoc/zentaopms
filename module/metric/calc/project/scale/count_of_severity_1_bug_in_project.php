<?php
/**
 * 按项目统计的严重程度为1级的Bug数。
 * Count of severity 1 bug in project.
 *
 * 范围：project
 * 对象：bug
 * 目的：scale
 * 度量名称：按项目统计的严重程度为1级的Bug数
 * 单位：个
 * 描述：按项目统计的严重程度为1级的Bug数是指在项目开发过程中发现的、对项目功能或性能产生重大影响的Bug数量。这些Bug可能会导致系统崩溃、功能无法正常运行、数据丢失等严重问题。统计这些Bug的数量可以帮助评估项目的稳定性和可靠性。
 * 定义：项目中Bug的个数求和\n 严重程度为1级\n 过滤已删除的Bug\n 过滤已删除的项目\n
 *
 * @copyright Copyright 2009-2024 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @author    songchenxuan <songchenxuan@easycorp.ltd>
 * @package
 * @uses      func
 * @license   ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @Link      https://www.zentao.net
 */
class count_of_severity_1_bug_in_project extends baseCalc
{
    public $dataset = 'getProjectBugs';

    public $fieldList = array('t1.severity', 't1.project');

    public $result = array();

    public function calculate($data)
    {
        $severity = $data->severity;
        $project  = $data->project;

        if(!isset($this->result[$project])) $this->result[$project] = 0;

        if($severity == '1') $this->result[$project] += 1;
    }

    public function getResult($options = array())
    {
        $records = array();
        foreach($this->result as $project => $value)
        {
            $records[] = array('project' => $project, 'value' => $value);
        }

        return $this->filterByOptions($records, $options);
    }
}
