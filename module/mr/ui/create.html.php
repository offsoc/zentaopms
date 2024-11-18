<?php
declare(strict_types=1);
/**
 * The create view file of mr module of ZenTaoPMS.
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Zeng Gang<zenggang@easycorp.ltd>
 * @package     mr
 * @link        https://www.zentao.net
 */
namespace zin;

h::importJs('js/misc/base64.js');
jsVar('hostType', strtolower($repo->SCM));
jsVar('hostID', $repo->gitService);
jsVar('repo', $repo);
jsVar('jobPairs', $jobPairs);
jsVar('objectID', $objectID);
jsVar('projectID', $project->id);
jsVar('mrLang', $lang->mr);
jsVar('branchPrivs', array());
jsVar('projectNamespace', in_array($repo->SCM, array('Gitea', 'Gogs')) ? $project->name_with_namespace : '');

$module = $app->tab == 'devops' ? 'repo' : $app->tab;
dropmenu
(
    set::module($module),
    set::tab($module),
    set::url(createLink($module, 'ajaxGetDropMenu', "objectID=$objectID&module={$app->rawModule}&method={$app->rawMethod}"))
);

if(in_array($repo->SCM, array('Gitea', 'Gogs')))
{
    $projectItem = array($project->name_with_namespace => $project->name_with_namespace);
}
else
{
    $projectItem = array($project->id => $project->name_with_namespace);
}
formPanel
(
    set::title($lang->mr->create),
    set::labelWidth($app->clientLang == 'zh-cn' ? '6em' : '10em'),
    count($repoPairs) > 1 ? formGroup(
        set::label($lang->repo->common),
        set::width('1/2'),
        picker
        (
            setClass('font-normal w-36'),
            set::name('repoID'),
            set::items($repoPairs),
            set::value($repo->id),
            set::required(true),
            on::change('changeRepo')
        )
    ) : null,
    formGroup
    (
        setClass('hidden'),
        set::name('hostID'),
        set::value($repo->gitService)
    ),
    formGroup
    (
        set::width('1/2'),
        set::readonly(true),
        set::label($lang->repo->common),
        set::name('sourceProject'),
        set::id('sourceProject'),
        set::items($projectItem),
        set::value($repo->id),
        setClass(count($repoPairs) > 1 ? 'hidden' : '')
    ),
    formGroup
    (
        setClass('hidden'),
        set::name('targetProject'),
        set::id('targetProject'),
        set::items($projectItem),
        set::value($repo->id)
    ),
    formRow
    (
        formGroup
        (
            set::width('1/2'),
            set::labelWidth($app->clientLang == 'zh-cn' ? '6em' : '9em'),
            set::required(true),
            set::label($lang->mr->sourceBranch),
            set::name('sourceBranch'),
            set::items(array())
        ),
        formGroup
        (
            set::width('1/2'),
            set::labelWidth($app->clientLang == 'zh-cn' ? '6em' : '9em'),
            set::required(true),
            set::label($lang->mr->targetBranch),
            set::name('targetBranch'),
            set::items(array())
        )
    ),
    formGroup
    (
        set::required(true),
        set::name('title'),
        set::label($lang->mr->title)
    ),
    formGroup
    (
        set::width('1/2'),
        set::required(true),
        set::name('assignee'),
        set::label($lang->mr->reviewer),
        set::control('picker'),
        set::items($users)
    ),
    formRow
    (
        formGroup
        (
            set::label($lang->mr->submitType),
            set::name('needCI'),
            set::width('270px'),
            set::control(array('control' => 'checkbox', 'text' => $lang->mr->needCI, 'value' => '1')),
            on::change('onNeedCiChange')
        ),
        formGroup
        (
            set::name('removeSourceBranch'),
            set::width('150px'),
            set::control(array('control' => 'checkbox', 'text' => $lang->mr->removeSourceBranch, 'value' => '1'))
        ),
        formGroup
        (
            set::name('squash'),
            set::control(array('control' => 'checkbox', 'text' => $lang->mr->squash, 'value' => '1')),
            btn
            (
                icon('help'),
                setClass('text-gray size-sm mt-1 ghost'),
                set('data-placement', 'right'),
                set('data-type', 'white'),
                set('data-class-name', 'text-gray border border-light'),
                toggle::tooltip(array('title' => $lang->mr->squashHelp)),
            )
        )
    ),
    formGroup
    (
        setID('jobID'),
        setClass('hidden'),
        set::width('1/2'),
        set::required(true),
        set::name('jobID'),
        set::label($lang->mr->pipeline),
        set::items($jobPairs)
    ),
    formGroup
    (
        set::name('description'),
        set::label($lang->mr->description),
        set::control('textarea')
    ),
    formRow
    (
        setClass('hidden'),
        formGroup
        (
            set::name('repoID'),
            set::label($lang->devops->repo),
            set::value($repo->id)
        )
    ),
    formRow
    (
        setClass('hidden'),
        formGroup
        (
            set::name('executionID'),
            set::label(''),
            set::value($executionID)
        )
    ),
    set::actions(array(
        'submit',
        array(
            'text'     => $lang->goback,
            'class'    => 'btn',
            'data-app' => $app->tab,
            'url'      => createLink($app->rawModule, 'browse', "repoID=" . ($executionID ? 0 : $repo->id) . "&mode=status&param=opened&objectID={$executionID}")
        )
    ))
);

render();
