<?php
declare(strict_types=1);
/**
 * The show script view file of testcase module of ZenTaoPMS.
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Gang Liu <liugang@easycorp.ltd>
 * @package     testcase
 * @link        https://www.zentao.net
 */
namespace zin;

modalHeader
(
    set::title($lang->testcase->showScript),
    set::entityID($case->id),
    set::entityText($case->title)
);

if(empty($case->script))
{
    div
    (
        setClass('dtable-empty-tip'),
        div
        (
            setClass('row gap-4 items-center'),
            span
            (
                setClass('text-gray'),
                $lang->noData
            )
        )
    );
}
else
{
    $rows  = array();
    $lines = explode("\n", $case->script);
    foreach($lines as $key => $line)
    {
        $rows[] = h::tr
        (
            h::td
            (
                setClass('align-top text-right border-r gray-pale px-1 py-0'),
                ++$key
            ),
            h::td
            (
                setClass('px-1 py-0 w-full'),
                html_entity_decode(str_replace(' ', '&nbsp;', $line))
            )
        );
    }

    h::table
    (
        setClass('border'),
        $rows
    );
}

render();
