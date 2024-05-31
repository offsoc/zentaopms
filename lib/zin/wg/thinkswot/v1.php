<?php
declare(strict_types=1);
namespace zin;

class thinkSwot extends wg
{
    protected static array $defineProps = array(
        'mode?: string', // 模型展示模式。 preview 后台设计预览 | view 前台结果展示
        'blocks: array', // 模型节点
    );

    public static function getPageCSS(): ?string
    {
        return file_get_contents(__DIR__ . DS . 'css' . DS . 'v1.css');
    }

    protected function buildItem(int $order, $blockID): node
    {
        global $app, $lang;
        $app->loadLang('thinkwizard');

        list($mode, $blocks) = $this->prop(array('mode', 'blocks'));
        $defaultTitle = $mode == 'preview' ? $lang->thinkwizard->unAssociated : '';
        $blockTitle   = !empty($blocks[$blockID]) ? $blocks[$blockID] : $defaultTitle;
        return div
        (
            setClass('relative p-1 bg-canvas border border-gray-200 model-block', "block-$order"),
            setStyle(array('width' => '50%', 'height' => '127px')),
            div
            (
                setClass('h-full'),
                div(setClass('item-step-title text-center text-sm text-clip'), set::title($blockTitle), $blockTitle),
                div(setClass('item-step-answer h-5/6'))
            )
        );
    }

    protected function buildBody(): array
    {
        $blocks     = $this->prop('blocks');
        $blocks     = array_keys($blocks);
        $modelItems = array();
        for($i = 0; $i < 4; $i++)
        {
            $modelItems[] = $this->buildItem($i, $blocks[$i] ?? '');
        }
        return $modelItems;
    }

    protected function build(): array
    {
        global $app, $lang;
        $app->loadLang('thinkwizard');

        $mode  = $this->prop('mode');
        $model = array(
            div
            (
                setClass('model-swot my-1 flex flex-wrap justify-between'),
                setStyle(array('min-height' => '254px')),
                $this->buildBody()
            )
        );
        if($mode == 'preview')
        {
            array_unshift($model, div(setClass('flex justify-between text-gray-400'), span($lang->thinkwizard->block . $lang->thinkwizard->blockList[0]), span($lang->thinkwizard->block . $lang->thinkwizard->blockList[1])));
            $model[] = div(setClass('flex justify-between text-gray-400'), span($lang->thinkwizard->block . $lang->thinkwizard->blockList[2]), span($lang->thinkwizard->block . $lang->thinkwizard->blockList[3]));
        }
        return $model;
    }
}