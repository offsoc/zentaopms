<?php
declare(strict_types=1);
/**
 * The mydoclist view file of doc module of ZenTaoPMS.
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Shujie Tian<tianshujie@easycorp.ltd>
 * @package     doc
 * @link        https://www.zentao.net
 */
namespace zin;

jsVar('spaceMethodList', $config->doc->spaceMethod);
jsVar('myspacePriv', common::hasPriv('doc', 'myspace'));
jsVar('productspacePriv', common::hasPriv('doc', 'productspace'));
jsVar('projectspacePriv', common::hasPriv('doc', 'projectspace'));
jsVar('teamspacePriv', common::hasPriv('doc', 'teamspace'));

$cols = array();
$config->doc->dtable->fieldList['actions']['menu'] = array('edit', 'movedoc', 'delete');
foreach($config->doc->dtable->fieldList as $colName => $col)
{
    if($type == 'mine' && in_array($colName, array('objectName', 'module', 'editedBy'))) continue;
    if($colName == 'addedBy' && in_array($type, array('mine', 'createdby'))) continue;

    if($canExport && $colName == 'id') $col['type'] = 'checkID';
    $cols[$colName] = $col;
}

$params        = "libID={$libID}&moduleID={$moduleID}&browseType={$browseType}&param={$param}&orderBy={$orderBy}&recTotal={recTotal}&recPerPage={recPerPage}&pageID={page}";
$sortParams    = "libID={$libID}&moduleID={$moduleID}&browseType={$browseType}&param={$param}&orderBy={name}_{sortType}&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}&pageID={$pager->pageID}";
$tableData     = empty($docs) ? array() : initTableData($docs, $cols);
$createType    = empty($lib) ? '' : $lib->type;
$createDocLink = '';
if($browseType != 'bysearch' && $libID && common::hasPriv('doc', 'create')) $createDocLink = createLink('doc', 'create', "objectType={$createType}&objectID={$objectID}&libID={$libID}&moduleID={$moduleID}&type=html");
if($app->rawMethod == 'myspace')
{
    $sortParams = "type={$type}&" . $sortParams;
    $params     = "type={$type}&" . $params;
}

/* Remove move doc action when doc under project/product/execution. */
foreach($tableData as $key => $row)
{
    if(($row->project || $row->product || $row->execution) && isset($row->actions))
    {
        foreach($row->actions as $id => $action)
        {
            if($action['name'] == 'movedoc')
            {
                $tableData[$key]->actions[$id]['disabled'] = true;
                break;
            }
        }
    }
}

$docContent = dtable
(
    setID('docTable'),
    set::iconList($config->doc->iconList),
    set::draftText($lang->doc->draft),
    set::canViewDoc(common::hasPriv('doc', 'view')),
    set::canCollect(common::hasPriv('doc', 'collect') && $libType && $libType != 'api'),
    set::currentAccount($app->user->account),
    set::currentTab($app->tab),
    set::userMap($users),
    set::cols($cols),
    set::data($tableData),
    set::checkable($canExport),
    set::onRenderCell(jsRaw('window.rendDocCell')),
    set::emptyTip($lang->doc->noDoc),
    set::createLink($createDocLink),
    set::createTip($lang->doc->create),
    set::orderBy($orderBy),
    set::sortable(boolval($canUpdateOrder)),
    set::onSortEnd(jsRaw('window.onSortEnd')),
    set::plugins(array('sortable')),
    set::sortLink(createLink('doc', $app->rawMethod, $sortParams)),
    set::footPager(usePager(array('linkCreator' => helper::createLink('doc', $app->rawMethod, $params))))
);
