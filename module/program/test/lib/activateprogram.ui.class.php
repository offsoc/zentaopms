<?php
include dirname(__FILE__, 5) . '/test/lib/ui.php';
class createProgramTester extends tester
{
    /**
     * 激活项目集。
     *
     * @param  string $programName
     * @param  array  $whitelist
     * @access public
     * @return void
     */
    public function activateProgram($programName)
    {
        $this->openUrl('program', 'browse');
        $browsePage = $this->loadPage('program', 'browse');
        $browsePage->dom->fstStartBtn->click();
        $browsePage->wait(1);
        $browsePage->dom->startProgramBtn->click();
        $browsePage->wait(1);
        $browsePage->dom->fstStartBtn->click();
        $browsePage->wait(1);
        $browsePage->dom->startProgramBtn->click();
        $browsePage->dom->search(array("项目集名称,=,{$programName->name}"));
        $browsePage->wait(1);
        $browsePage->dom->fstStartBtn->click();
        $browsePage->wait(1);
        $browsePage->dom->startProgramBtn->click();
        $browsePage->wait(1);

        if($browsePage->dom->fstStatus->getText() != '进行中') return $this->failed('激活项目集失败');
        return $this->success('激活项目集成功');
    }
}