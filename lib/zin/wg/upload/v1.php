<?php
declare(strict_types=1);
namespace zin;

/**
 * @deprecated Use `fileSelector` instead.
 */
class upload extends wg
{
    protected static array $defineProps = array(
        'name: string="files[]"',          // 字段名
        'id?: string',                     // 元素 ID
        'icon?: string',                   // 文件图标
        'showIcon?: bool=true',            // 是否展示文件图标
        'showSize?: bool=true',            // 是否展示文件大小
        'multiple?: bool=true',            // 是否启用多文件上传
        'listPosition?: string="bottom"',  // 文件列表位置
        'uploadText?: string',             // 上传按钮文本
        'uploadIcon?: string',             // 上传按钮图标
        'renameBtn?: bool=true',           // 是否启用重命名按钮
        'renameIcon?: string',             // 重命名图标
        'renameText?: string',             // 重命名文本
        'renameClass?: string',            // 重命名按钮类
        'deleteBtn?: bool=true',           // 是否启用删除按钮
        'deleteIcon?: string',             // 删除图标
        'deleteText?: string',             // 删除文本
        'deleteClass?: string',            // 删除按钮类
        'confirmText?: string',            // 确认按钮文本
        'cancelText?: string',             // 取消按钮文本
        'useIconBtn?: string',             // 是否启用图标按钮
        'tip?: string',                    // 提示文本
        'btnClass?: string',               // 上传按钮类
        'onAdd?: callable',                // 添加文件回调
        'onDelete?: callable',             // 删除文件回调
        'onRename?: callable',             // 重命名文件回调
        'onSizeChange?: callable',         // 文件大小变更回调
        'draggable?: bool=true',           // 是否启用拖拽上传
        'limitCount?: int',                // 上传文件数量限制
        'accept?: string',                 // input accept 属性
        'defaultFileList?: object[]',      // 默认文件列表
        'limitSize?: false|string=false',  // 上传尺寸限制
        'duplicatedHint?: string',         // 文件名重复提示
        'exceededSizeHint?: string',       // 上传超出大小限制提示
        'exceededCountHint?: string'       // 上传超出个数限制提示
    );

    protected function created()
    {
        global $lang, $app;

        $app->loadLang('file');

        /* Check file type. */
        $checkFiles = jsCallback('file')
            ->const('dangerFileTypes', ",{$app->config->file->dangers},")
            ->const('dangerFile', $lang->file->dangerFile)
            ->do(<<<'JS'
            const typeIndex = file.name.lastIndexOf(".");
            const fileType  = file.name.slice(typeIndex + 1);
            if(dangerFileTypes.indexOf(fileType) > -1)
            {
                zui.Modal.alert(dangerFile);
                return false;
            }
            JS);

        /* Get onAdd function.*/
        $onAdd = $this->prop('onAdd');
        if($onAdd)
        {
            if(is_object($onAdd))
            {
                /*
                 * 获取在 ui 界面上通过 jsCallback 和 js 定义的 onAdd 函数。
                 * eg: 1. $onAdd = jsCallbakc()..;
                 *         fileSelector(set::onAdd($onAdd));
                 *     2. $onAdd = js()..;
                 *         fileSelector(set::onAdd($onAdd));
                 */
                $objectClass = get_class($onAdd);
                if($objectClass == 'zin\js')         $onAdd = $onAdd->toJS();
                if($objectClass == 'zin\jsCallback') $onAdd = $onAdd->buildBody();
                if(!is_object($onAdd)) $checkFiles = $checkFiles->do($onAdd);
            }
            else
            {
                /* 获取在 ui 界面上通过 jsRaw 定义的 onAdd 函数。 eg: fileSelector(set::onAdd(jsRaw('window.onAdd'))); */
                $onAdd      = js::value($onAdd);
                $checkFiles = $checkFiles->call($onAdd, jsRaw('file'));
            }
        }
        $checkFiles = $checkFiles->do('return file');
        $this->setProp('onAdd', $checkFiles);
    }

    protected function build(): zui
    {
        global $lang, $app;

        if(!$this->prop('class')) $this->setProp('class', 'w-full');
        if(!$this->prop('icon'))  $this->setProp('icon',  'paper-clip');
        if(!$this->prop('tip'))   $this->setProp('tip', sprintf($lang->noticeDrag, strtoupper(ini_get('upload_max_filesize'))));
        if($this->prop('limitCount') && !$this->prop('exceededCountHint'))
        {
            $app->loadLang('file');
            $this->setProp('exceededCountHint', sprintf($lang->file->errorFileCount, $this->prop('limitCount')));
        }

        $name = $this->prop('name');
        if($this->prop('multiple') && strpos($name, '[]') === false) $this->setProp('name', $name . '[]');

        $otherProps = $this->getRestProps();
        return zui::upload(inherit($this), set('_props', $otherProps));
    }
}
