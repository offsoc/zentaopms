<?php
declare(strict_types=1);
/**
 * The link story view file of execution module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Sun Guangming<sunguangming@easycorp.ltd>
 * @package     execution
 * @link        https://www.zentao.net
 */

namespace zin;

featureBar(li
(
    setClass('nav-item'),
    a(setClass('active'), $lang->execution->linkStory)
));

searchForm
(
    set::module('story'),
    set::simple(true),
    set::show(true),
    set::onSearch(jsRaw("window.onSearchLinks.bind(null, 'story')"))
);

$cols = $config->execution->linkStory->dtable->fieldList;
$cols['module']['map']  = $modules;
$cols['product']['map'] = $productPairs;

if($productType != 'normal')
{
    $cols['branch']['title'] = $lang->product->branchName[$productType];
}
else
{
    unset($cols['branch']);
}

jsVar('branchGroups', $branchGroups);
jsVar('gradeGroup', $gradeGroup);
jsVar('showGrade', $showGrade);

$footToolbar['items'][] = array(
    'text'      => $lang->save,
    'className' => 'btn secondary batch-btn ajax-btn link-story-btn size-sm',
    'data-url'  => createLink('execution', 'linkStory', "projectID=$object->id&browseType={$browseType}&param={$param}&orderBy={$orderBy}&recPerPage={$pager->recPerPage}&pageID={$pager->pageID}&extra=&storyType=$storyType")
);
if(!isInModal())
{
    $footToolbar['items'][] = array(
        'text'      => $lang->goback,
        'className' => 'btn btn-info size-sm text-gray',
        'url'       => $browseLink,
        'btnType'   => 'info',
        'data-app'  => $app->tab
    );
}

$objectType = $object->type == 'project' ? 'projectstory' : 'execution';

dtable
(
    set::groupDivider(true),
    set::userMap($users),
    set::cols($cols),
    set::data($allStories),
    set::orderBy($orderBy),
    set::sortLink(createLink($objectType, 'linkStory', "objectID={$object->id}&browseType={$browseType}&param={$param}&orderBy={name}_{sortType}&recPerPage={$pager->recPerPage}&pageID={$pager->pageID}&extra={$extra}&storyType={$storyType}")),
    set::checkable(true),
    set::onRenderCell(jsRaw('window.onRenderLinkStoryCell')),
    set::showToolbarOnChecked(false),
    set::footToolbar($footToolbar),
    set::noNestedCheck(),
    set::footPager(usePager(array(
        'linkCreator' => helper::createLink($objectType, 'linkStory', "objectID={$object->id}&browseType={$browseType}&param={$param}&orderBy=$orderBy&recPerPage={recPerPage}&page={page}&extra=$extra&storyType={$storyType}") . "#app={$app->tab}"
    )))
);

render();
