<?php
include dirname(__FILE__, 5) . '/test/lib/ui.php';
class buildLinkStoryTester extends tester
{
    /**
     * Projectbuild link story.
     * 项目版本关联需求
     *
     * @access public
     * @return object
     */
    public function linkStory()
    {
        $form = $this->initForm('projectbuild', 'view', array('buildID' => 1), 'appIframe-project');
        $form->dom->linkStoryBtn->click();
        $form->wait(2);
        $form->dom->searchBtn->click();
        $form->dom->selectAllStory->click(); // 点击全选按钮
        $form->dom->linkStoryBtnBottom->click();
        // 断言检查版本关联需求数量是否成功
        $viewPage = $this->initForm('projectbuild', 'view', array('projectID' => 1), 'appIframe-project');
        return ($viewPage->dom->finishedStoryNum === '0') ? $this->failed('版本关联需求失败') : $this->success('版本关联需求成功');
    }

    /**
     * Unlink a story of project build.
     * 版本单个移除需求
     *
     * @access public
     * @return object
     */
    public function unlinkStory()
    {
        $form          = $this->initForm('projectbuild', 'view', array('buildID' => 1), 'appIframe-project');
        $linkNumBefore = $form->dom->finishedStoryNum->getText(); // 记录移除需求前版本关联的需求数量
        $form->dom->unlinkFirBtn->click(); // 点击第一行的单个移除按钮
        $form->wait(1);
        $form->dom->alertModal(); // 模态框中点击确定
        $form->wait(2);
        $linkNumAfter = $form->dom->finishedStoryNum->getText(); // 记录移除需求后版本关联的需求数量
        // 断言检查单个移除需求是否成功
        return ($linkNumAfter == $linkNumBefore - 1) ? $this->success('单个移除需求成功') : $this->failed('单个移除需求失败');
    }

    /**
     * unlink all story of project build.
     * 移除全部需求
     *
     * @return object
     */
    public function batchUnlinkStory()
    {
        $form = $this->initForm('projectbuild', 'view', array('buildID' => 1), 'appIframe-project');
        $form->wait(1);
        $form->dom->allFinishedStoryBtn->click(); // 全选需求
        $form->dom->batchUnlinkBtn->click(); // 点击批量移除按钮
        $form->wait(2);
        // 断言检查移除全部需求是否成功
        return ($form->dom->finishedStoryNum === false) ? $this->success('移除全部需求成功') : $this->failed('移除全部需求失败');
    }
}
