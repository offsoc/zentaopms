<?php
declare(strict_types=1);
/**
 * The browse view file of repo module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Ke Zhao<zhaoke@easycorp.ltd>
 * @package     repo
 * @link        https://www.zentao.net
 */

namespace zin;

jsVar('path', $path);
jsVar('copied', $lang->repo->copied);
jsVar('base64BranchID', $base64BranchID);

$module = $app->tab == 'devops' ? 'repo' : $app->tab;
dropmenu
(
    set::module($module),
    set::tab($module),
    set::url(createLink($module, 'ajaxGetDropMenu', "objectID=$objectID&module={$app->rawModule}&method={$app->rawMethod}"))
);

/* Prepare repo select data. */
$branchMenus = array();
$tagMenus    = array();
$selected    = '';
foreach($branches as $branchName)
{
    $selected       = ($branchName == $branchID and $branchOrTag == 'branch') ? $branchName : $selected;
    $base64BranchID = helper::safe64Encode(base64_encode($branchName));
    $branchLink     = $this->createLink('repo', 'browse', "repoID=$repoID&branchID=$base64BranchID&objectID=$objectID");

    $branchMenus[] = array('text' => $branchName, 'id' => $branchName, 'keys' => zget(common::convert2Pinyin(array($branchName), $branchName), ''), 'url' => $branchLink, 'data-app' => $app->tab);
}
foreach($tags as $tagName)
{
    $selected    = ($tagName == $branchID and $branchOrTag == 'tag') ? $tagName : $selected;
    $base64TagID = helper::safe64Encode(base64_encode($tagName));
    $tagLink     = $this->createLink('repo', 'browse', "repoID=$repoID&branchID=$base64TagID&objectID=$objectID&path=&revision=HEAD&refresh=0&branchOrTag=tag");

    $tagMenus[] = array('text' => $tagName, 'id' => $tagName, 'keys' => zget(common::convert2Pinyin(array($tagName), $tagName), ''), 'url' => $tagLink, 'data-app' => $app->tab);
}

$tabs = array(array('name' => 'branch', 'text' => $lang->repo->branch), array('name' => 'tag', 'text' => $lang->repo->tag));
$menuData = array('branch' => $branchMenus, 'tag' => $tagMenus);

/* Prepare breadcrumb navigation data. */
$base64BranchID    = helper::safe64Encode(base64_encode($branchID));
$breadcrumbItems   = array();
$path ? $breadcrumbItems[] = h::a
(
    set::href($this->repo->createLink('browse', "repoID=$repoID&branchID=$base64BranchID&objectID=$objectID")),
    set('data-app', $app->tab),
    h::span('/', setStyle('margin', '0 5px'))
) : null;

$paths    = explode('/', $path);
$fileName = array_pop($paths);
$postPath = '';
foreach($paths as $index => $pathName)
{
    $postPath .= $pathName . '/';
    $breadcrumbItems[] = h::a
    (
        set::href($this->repo->createLink('browse', "repoID=$repoID&branchID=$base64BranchID&objectID=$objectID&path=" . $this->repo->encodePath($postPath))),
        set('data-app', $app->tab),
        trim($pathName, '/')
    );
    $breadcrumbItems[] = h::span('/', setStyle('margin', '0 5px'));
}
if($fileName) $breadcrumbItems[] = h::span($fileName);


/* zin: Define the set::module('repo') feature bar on main menu. */
\zin\featureBar(
    formGroup
    (
        set::className('repo-select'),
        set::required(true),
        (in_array($app->tab, array('project', 'execution')) && count($repoPairs) > 1) ? dropmenu
        (
            set::id('repoDropmenu'),
            set::text($repo->name),
            set::objectID($repo->id),
            set::url(createLink('repo', 'ajaxGetDropMenu', "repoID={$repo->id}&module=repo&method=browse&projectID={$objectID}"))
        ) : null,
        ($repo->SCM != 'Subversion' && ($branches || $tags)) ? dropmenu
        (
            setID('repoBranchDropMenu'),
            set::objectID($selected),
            set::text($selected),
            set::data(array('data' => $menuData, 'tabs' => $tabs))
        ) : null
    ),
    ...$breadcrumbItems
);

/* zin: Define the toolbar on main menu. */
$refreshLink   = $this->createLink('repo', 'browse', "repoID=$repoID&branchID=" . $base64BranchID . "&objectID=$objectID&path=" . $this->repo->encodePath($path) . "&revision=$revision&refresh=1");
$refreshItem   = array('text' => $lang->refresh, 'url' => $refreshLink, 'class' => 'primary', 'icon' => 'refresh', 'data-app' => $app->tab);

$createItem = array('text' => $lang->repo->createAction, 'url' => createLink('repo', 'create', "objectID={$objectID}"), 'data-app' => $app->tab);

$config->repo->repoDtable->fieldList['revision']['link'] = inLink('revision', "repoID={$repo->id}&objectID={$objectID}&revision={revision}");
$tableData = initTableData($infos, $config->repo->repoDtable->fieldList, $this->repo);

$downloadWg = div
(
    set::id('modal-downloadCode'),
    set::title($lang->repo->downloadCode),
    on('click', '#modal-downloadCode', array('capture' => true, 'prevent' => true, 'stop' => true)),
    on::click('.copy-btn')->call('copyLink', jsRaw('this')),
    !empty($cloneUrl->svn) ? div
    (
        p(set::className('repo-downloadCode'), $lang->repo->cloneUrl),
        div
        (
            setClass('flex space-between w-96'),
            div
            (
                setClass('flex-1'),
                input
                (
                    set::type('text'),
                    set::name('svnUrl'),
                    set::value($cloneUrl->svn),
                    set::readOnly(true)
                )
            ),
            div
            (
                set::width('50px'),
                btn
                (
                    set::className('copy-btn'),
                    set::icon('copy')
                )
            )
        )
    ) : null,

    !empty($cloneUrl->ssh) ? div
    (
        p(set::className('repo-downloadCode'), $lang->repo->sshClone),
        div
        (
            setClass('flex space-between w-96'),
            div
            (
                setClass('flex-1'),
                input
                (
                    set::type('text'),
                    set::name('sshUrl'),
                    set::value($cloneUrl->ssh),
                    set::readOnly(true)
                )
            ),
            div
            (
                set::width('50px'),
                btn
                (
                    set::className('copy-btn'),
                    set::icon('copy')
                )
            )
        )
    ) : null,

    !empty($cloneUrl->http) ? div
    (
        p(set::className('repo-downloadCode'), $lang->repo->httpClone),
        div
        (
            setClass('flex space-between w-96'),
            div
            (
                setClass('flex-1'),
                input
                (
                    set::type('text'),
                    set::name('httpUrl'),
                    set::value($cloneUrl->http),
                    set::readOnly(true)
                )
            ),
            div
            (
                set::width('50px'),
                btn
                (
                    set::className('copy-btn'),
                    set::icon('copy')
                )
            )
        )
    ) : null,
    div
    (
        setStyle(array('margin-top' => '20px')),
        btn
        (
            on::click()->call('downloadZip'),
            set::icon('down-circle'),
            set::className('downloadZip-btn'),
            set::text($lang->repo->downloadZip)
        )
    )
);

toolbar
(
    a(
        set::className('last-sync-time'),
        empty($lastRevision->link) ? null : set::href($lastRevision->link),
        $lang->repo->notice->lastSyncTime . (isset($lastRevision->time) ? date('m-d H:i', strtotime($lastRevision->time)) : date('m-d H:i'))
    ),
    !in_array($repo->SCM, $config->repo->notSyncSCM) ? item(set($refreshItem)) : null,
    dropdown
    (
        set::staticMenu(true),
        btn
        (
            setClass('primary download-btn'),
            set::icon('download'),
            $lang->repo->download
        ),
        to::items
        (
            array($downloadWg)
        )
    ),
    hasPriv('repo', 'create') && $app->tab == 'project' ? item
    (
        set($createItem + array
        (
            'icon'  => 'plus',
            'class' => 'btn primary'
        )),
        set('data-app', $this->app->tab)
    ) : null
);

jsVar('tableData', $tableData);
dtable
(
    set::cols($config->repo->repoDtable->fieldList),
    set::data($tableData),
    set::afterRender(jsRaw('window.afterRender')),
    set::onRenderCell(jsRaw('window.renderCell')),
    set::canRowCheckable(jsRaw('function(rowID){return false;}')),
    set::footPager(false)
);

/* zin: Define the sidebar in main content. */
$encodePath  = $this->repo->encodePath($path);
$diffLink    = $this->repo->createLink('diff', "repoID=$repoID&objectID=$objectID&entry=" . $encodePath . "&oldrevision={oldRevision}&newRevision={newRevision}");

jsVar('repo',     $repo);
jsVar('appTab',   $app->tab);
jsVar('branch',   $branchID);
jsVar('diffLink', $diffLink);
jsVar('sortLink', helper::createLink('repo', 'browse', "repoID={$repoID}&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}&pageID={$pager->pageID}"));

/* Disbale check all checkbox of table header */
$config->repo->commentDtable->fieldList['id']['checkbox'] = jsRaw('(rowID) => rowID !== \'HEADER\'');

if(in_array($repo->SCM, $config->repo->notSyncSCM)) unset($config->repo->commentDtable->fieldList['commit']);
$commentsTableData = initTableData($revisions, $config->repo->commentDtable->fieldList, $this->repo);

$readAllLink = $this->repo->createLink('log', "repoID=$repoID&branchID=$base64BranchID&objectID=$objectID&entry=" . $encodePath . "&source=browse");
$footToolbar['items'][] = array('text' => $lang->repo->diff, 'className' => "btn primary size-sm btn-diff", 'btnType' => 'primary', 'onClick' => jsRaw('window.diffClick'));
$footToolbar['items'][] = array('text' => $lang->repo->allLog, 'url' => $readAllLink, 'data-app' => $this->app->tab);

sidebar
(
    set::side('right'),
    set::width(500),
    set::maxWidth(500),
    set::preserve(false),
    dtable
    (
        set::id('repo-comments-table'),
        set::cols($config->repo->commentDtable->fieldList),
        set::data($commentsTableData),
        set::onRenderCell(jsRaw('window.renderCommentCell')),
        set::onCheckChange(jsRaw('window.checkedChange')),
        set::canRowCheckable(jsRaw('window.canRowCheckable')),
        set::footToolbar($footToolbar),
        set::footer(array('toolbar', 'flex', 'pager')),
        set::footPager(usePager('pager', 'noTotalCount')),
        set::showToolbarOnChecked(false)
    )
);
