<?php
declare(strict_types=1);
/**
 * The view file of kanban module of ZenTaoPMS.
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Sun Guangming <sunguangming@easycorp.ltd>
 * @package     kanban
 * @link        https://www.zentao.net
 */
namespace zin;

$canModify = !empty($this->config->CRKanban) || $kanban->status != 'closed';

$laneCount = 0;
$groupCols = array(); // 卡片可以移动到的同一group下的列。
foreach($kanbanList as $current => $region)
{
    foreach($region['items'] as $index => $group)
    {
        $groupID = $group['id'];

        foreach($group['data']['cols'] as $colIndex => $col)
        {
            if($col['parent'] != '-1') $groupCols[$groupID][$col['id']] = $col['title'];
        }

        $kanbanList[$current]['items'][$index] = $group;
        $kanbanList[$current]['kanbanProps']['getLane']       = jsRaw('window.getLane');
        $kanbanList[$current]['kanbanProps']['getCol']        = jsRaw('window.getCol');
        $kanbanList[$current]['kanbanProps']['getItem']       = jsRaw('window.getItem');
        $kanbanList[$current]['kanbanProps']['colWidth']      = 'auto';
        $kanbanList[$current]['kanbanProps']['laneHeight']    = 'auto';
        $kanbanList[$current]['kanbanProps']['minColWidth']   = $kanban->fluidBoard == '0' ? $kanban->colWidth : $kanban->minColWidth;
        $kanbanList[$current]['kanbanProps']['maxColWidth']   = $kanban->fluidBoard == '0' ? $kanban->colWidth : $kanban->maxColWidth;
        $kanbanList[$current]['kanbanProps']['maxLaneHeight'] = '500';
        $kanbanList[$current]['kanbanProps']['colProps']      = array('actions' => jsRaw('window.getColActions'), 'titleAlign' => $kanban->alignment);
        $kanbanList[$current]['kanbanProps']['laneProps']     = array('actions' => jsRaw('window.getLaneActions'));
        $kanbanList[$current]['kanbanProps']['itemProps']     = array('actions' => jsRaw('window.getItemActions'));

        if($canModify)
        {
            $kanbanList[$current]['kanbanProps']['canDrop'] = jsRaw('window.canDrop');
            $kanbanList[$current]['kanbanProps']['onDrop']  = jsRaw('window.onDrop');
        }
    }

    $laneCount += $region['laneCount'];
}

$regionMenu   = array();
$regionMenu[] = li(set::className($regionID == 'all' ? 'active' : ''), a(set::href('javascript:;'), span(set::title($lang->kanbanregion->all), $lang->kanbanregion->all)), set('data-on', 'click'), set('data-call', 'clickRegionMenu'), set('data-params', 'event'), set('data-region', 'all'));
foreach($regions as $currentRegionID => $regionName) $regionMenu[] = li(set::className($regionID == $currentRegionID ? 'active' : ''), a(set::href('javascript:;'), span(set::title($regionName), $regionName)), set('data-on', 'click'), set('data-call', 'clickRegionMenu'), set('data-params', 'event'), set('data-region', $currentRegionID));

$app->loadLang('release');
$app->loadLang('execution');
$this->loadModel('productplan');
jsVar('laneCount',  $laneCount);
jsVar('kanbanLang', $lang->kanban);
jsVar('columnLang', $lang->kanbancolumn);
jsVar('laneLang', $lang->kanbanlane);
jsVar('cardLang', $lang->kanbancard);
jsVar('executionLang', $lang->execution);
jsVar('releaseLang', $lang->release);
jsVar('productplanLang', $lang->productplan);
jsVar('ticketLang', isset($lang->ticket) ? $lang->ticket : '');
jsVar('kanbanID', $kanban->id);
jsVar('kanban', $kanban);
jsVar('groupCols', $groupCols);
jsVar('vision', $config->vision);
jsVar('colorList', $config->kanban->cardColorList);
jsVar('futureDate', $config->productplan->future);
jsVar('canMoveCard', common::hasPriv('kanban', 'moveCard'));
jsVar('canModify', (!empty($this->config->CRKanban) || $kanban->status != 'closed'));
jsVar('canViewPlan', common::hasPriv('productplan', 'view') && $config->vision != 'lite');
jsVar('canViewRelease', common::hasPriv('release', 'view') && $config->vision != 'lite');
jsVar('canViewExecution', common::hasPriv('execution', 'task') && $config->vision != 'lite');
jsVar('canViewBuild', common::hasPriv('build', 'view') && $config->vision != 'lite');
jsVar('canViewTicket', common::hasPriv('ticket', 'view'));
jsVar('userList', $userList);

dropmenu(set::tab('kanban'), set::objectID($kanban->id));

ul
(
    set::className('regionMenu'),
    $regionMenu
);

div
(
    set::id('kanbanList'),
    zui::kanbanList
    (
        set::key('kanban'),
        set('$replace', false),
        set::items($kanbanList),
        set::height('calc(100vh - 120px)')
    )
);

div(set::id('archivedCards'));
div(set::id('archivedColumns'));
