<?php
include dirname(__FILE__, 5) . '/test/lib/ui.php';
class createDocTester extends tester
{
    /**
     * 编辑文档。
     * Edit a doc.
     *
     * @param  string $editDocName
     * @param  string $docName
     * @access public
     * @return void
     */
    public function editDoc($docName, $editDocName)
    {
        /*进入我的空间下创建文档*/
        $this->openUrl('doc', 'myspace', array('objectType' => 'mine'));
        $form = $this->loadPage('doc', 'myspace', array('objectType' => 'mine'));
        $form->dom->createDocBtn->click();
        $form->wait(1);
        $form->dom->showTitle->setValue($docName->dcName);
        $form->dom->saveBtn->click();
        $form->wait(1);
        $form->dom->releaseBtn->click();

        /*编辑文档*/
        $this->openUrl('doc', 'mySpace', array('type' => 'mine'));
        $form = $this->loadPage('doc', 'mySpace', array('type' => 'mine'));
        $form->dom->fstEditBtn->click();
        $form->wait(1);
        $form->dom->title->setValue($editDocName->editName);
        $form->dom->saveDraftBtn->click();
        $form->wait(1);

        $this->openUrl('doc', 'mySpace', array('objectType' => 'editedby'));
        $form = $this->loadPage('doc', 'mySpace', array('objectType' => 'editedby'));
        $form->dom->search(array("文档标题,=,{$editDocName->editName}"));
        $form->wait(1);

        if($form->dom->fstDocName->getText() != $editDocName->editName) return $this->failed('编辑文档失败');
        return $this->success('编辑文档成功');
    }

    /**
     * 移动文档。
     * Move a doc.
     *
     * @param  string $libName
     * @access public
     * @return void
     */
    public function moveDoc($libName)
    {
        /*创建一个文档库*/
        $this->openUrl('doc', 'mySpace', array('type' => 'mine'));
        $form = $this->loadPage('doc', 'mySpace', array('type' => 'mine'));
        $form->dom->createLibBtn->click();
        $form->dom->name->setValue($libName->myDocLib);
        $form->dom->btn($this->lang->save)->click();
        $form->wait(1);

        /*移动文档*/
        $this->openUrl('doc', 'mySpace', array('type' => 'mine'));
        $form = $this->loadPage('doc', 'mySpace', array('type' => 'mine'));
        $form->dom->fstMoveBtn->click();
        $form->wait(1);
        $form->dom->lib->picker($libName->myDocLib);
        $form->wait(1);
        $form->dom->btn($this->lang->save)->click();
        $form->wait(2);

        if($form->dom->leftListHeader->getText() != $libName->myDocLib) return $this->failed('移动文档失败');
        return $this->success('移动文档成功');
    }

    /**
     * 删除文档
     * Delete a doc.
     *
     * @access public
     * @return void
     */
    public function deleteDoc()
    {
        $this->openUrl('doc', 'mySpace', array('type' => 'mine'));
        $form = $this->loadPage('doc', 'mySpace', array('type' => 'mine'));
        $form->dom->fstDocLib->click();
        $form->wait(1);
        $form->dom->fstDeleteBtn->click();
        $form->dom->deleteAccept->click();
        $form->wait(1);

        if($form->dom->formText->getText() != '暂时没有文档。') return $this->failed('删除文档失败');
        return $this->success('删除文档成功');
    }
}
