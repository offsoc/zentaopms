<?php
declare(strict_types=1);
/**
 * The zen file of mr module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yanyi Cao <caoyanyi@easycorp.ltd>
 * @package     mr
 * @link        https://www.zentao.net
 */
class mrZen extends mr
{
    /**
     * 获取合并请求的代码库项目信息。
     * Get the code base project information of the merge request.
     *
     * @param  object    $repo
     * @access protected
     * @return array
     */
    protected function getAllProjects(object $repo): array
    {
        $methodName = 'get' . ucfirst($repo->SCM) . 'Projects';
        return $this->mr->{$methodName}((int)$repo->serviceHost, array($repo->serviceProject => $repo->serviceProject));
    }

    /**
     * 向编辑合并请求页面添加数据。
     * Add data to the edit merge request page.
     *
     * @param  object    $MR
     * @param  string    $scm
     * @access protected
     * @return void
     */
    protected function assignEditData(object $MR, string $scm): void
    {
        $this->app->loadConfig('pipeline');

        $MR->canDeleteBranch = true;
        if(in_array($scm, $this->config->pipeline->formatTypeService)) $MR->sourceProject = (int)$MR->sourceProject;
        $branchPrivs = $this->loadModel($scm)->apiGetBranchPrivs($MR->hostID, $MR->sourceProject);
        foreach($branchPrivs as $priv)
        {
            if($MR->canDeleteBranch && $priv->name == $MR->sourceBranch) $MR->canDeleteBranch = false;
        }

        $sourceProject = $targetProject = $MR->sourceProject;
        if($MR->sourceProject != $MR->targetProject) $targetProject = $MR->targetProject;
        if(in_array($scm, $this->config->pipeline->formatTypeService))
        {
            $project = $this->loadModel($scm)->apiGetSingleProject($MR->hostID, (int)$MR->sourceProject);
            $targetProject = $sourceProject = zget($project, 'name_with_namespace', '');
            if($MR->sourceProject != $MR->targetProject)
            {
                $project = $this->loadModel($scm)->apiGetSingleProject($MR->hostID, (int)$MR->targetProject);
                $targetProject = zget($project, 'name_with_namespace', '');
            }

        }

        $branches = array();
        $jobList  = array();
        if($MR->repoID)
        {
            $rawJobList = $this->loadModel('job')->getListByRepoID($MR->repoID);
            foreach($rawJobList as $rawJob) $jobList[$rawJob->id] = "[$rawJob->id] $rawJob->name";

            $repo = $this->loadModel('repo')->getByID($MR->repoID);
            $scm  = $this->app->loadClass('scm');
            $scm->setEngine($repo);
            $branches = $scm->branch();
            $this->view->repo = $repo;
        }

        $this->view->title         = $this->lang->mr->edit;
        $this->view->MR            = $MR;
        $this->view->users         = $this->loadModel('user')->getPairs('noletter|noclosed');
        $this->view->jobList       = $jobList;
        $this->view->branches      = $branches;
        $this->view->sourceProject = $sourceProject;
        $this->view->targetProject = $targetProject;
        $this->display();
    }

    /**
     * 构造关联需求的搜索表单。
     * Build the search form of the associated story.
     *
     * @param  int       $MRID
     * @param  object    $product
     * @param  string    $orderBy
     * @param  int       $queryID
     * @access protected
     * @return void
     */
    protected function buildLinkStorySearchForm(int $MRID, object $product, string $orderBy, int $queryID = 0)
    {
        if(empty($this->product))     $this->loadModel('product');
        if(empty($this->lang->story)) $this->app->loadLang('story');

        $storyStatusList = $this->lang->story->statusList;
        unset($storyStatusList['closed']);

        $modules = $this->loadModel('tree')->getOptionMenu($product->id, 'story');
        unset($this->config->product->search['fields']['product']);
        $this->config->product->search['actionURL']                   = $this->createLink($this->app->rawModule, 'linkStory', "MRID={$MRID}&productID={$product->id}&browseType=bySearch&param=myQueryID&orderBy={$orderBy}");
        $this->config->product->search['queryID']                     = $queryID;
        $this->config->product->search['style']                       = 'simple';
        $this->config->product->search['params']['product']['values'] = array($product) + array('all' => $this->lang->product->allProductsOfProject);
        $this->config->product->search['params']['plan']['values']    = $this->loadModel('productplan')->getForProducts(array($product->id => $product->id));
        $this->config->product->search['params']['module']['values']  = $modules;
        $this->config->product->search['params']['status']            = array('operator' => '=', 'control' => 'select', 'values' => $storyStatusList);

        if($product->type == 'normal')
        {
            unset($this->config->product->search['fields']['branch']);
            unset($this->config->product->search['params']['branch']);
        }
        else
        {
            $this->product->setMenu($product->id, 0);
            $this->config->product->search['fields']['branch']           = $this->lang->product->branch;
            $this->config->product->search['params']['branch']['values'] = $this->loadModel('branch')->getPairs($product->id, 'noempty');
        }
        $this->loadModel('search')->setSearchParams($this->config->product->search);
    }

    /**
     * 构造关联bug的搜索表单。
     * Build the search form of the associated bug.
     *
     * @param  int       $MRID
     * @param  object    $product
     * @param  string    $orderBy
     * @param  int       $queryID
     * @access protected
     * @return void
     */
    protected function buildLinkBugSearchForm(int $MRID, object $product, string $orderBy, int $queryID = 0)
    {
        if(empty($this->product)) $this->loadModel('product');
        $modules = $this->loadModel('tree')->getOptionMenu($product->id, 'bug');

        $this->config->bug->search['actionURL']                         = $this->createLink($this->app->rawModule, 'linkBug', "MRID={$MRID}&productID={$product->id}&browseType=bySearch&param=myQueryID&orderBy={$orderBy}");
        $this->config->bug->search['queryID']                           = $queryID;
        $this->config->bug->search['style']                             = 'simple';
        $this->config->bug->search['params']['plan']['values']          = $this->loadModel('productplan')->getForProducts(array($product->id => $product->id));
        $this->config->bug->search['params']['module']['values']        = $modules;
        $this->config->bug->search['params']['execution']['values']     = $this->product->getExecutionPairsByProduct($product->id);
        $this->config->bug->search['params']['openedBuild']['values']   = $this->loadModel('build')->getBuildPairs($product->id, 'all', 'releasetag');
        $this->config->bug->search['params']['resolvedBuild']['values'] = $this->config->bug->search['params']['openedBuild']['values'];

        unset($this->config->bug->search['fields']['product']);
        if($product->type == 'normal')
        {
            unset($this->config->bug->search['fields']['branch']);
            unset($this->config->bug->search['params']['branch']);
        }
        else
        {
            $this->product->setMenu($product->id, 0);
            $this->config->bug->search['fields']['branch']           = $this->lang->product->branch;
            $this->config->bug->search['params']['branch']['values'] = $this->loadModel('branch')->getPairs($product->id, 'noempty');
        }
        $this->loadModel('search')->setSearchParams($this->config->bug->search);
    }

    /**
     * 构造关联任务的搜索表单。
     * Build the search form of the associated task.
     *
     * @param  int       $MRID
     * @param  object    $product
     * @param  string    $orderBy
     * @param  int       $queryID
     * @param  array     $productExecutions
     * @access protected
     * @return void
     */
    protected function buildLinkTaskSearchForm(int $MRID, object $product, string $orderBy, int $queryID, array $productExecutions)
    {
        $modules = $this->loadModel('tree')->getOptionMenu($product->id, 'task');

        $this->config->execution->search['actionURL']                     = $this->createLink($this->app->rawModule, 'linkTask', "MRID={$MRID}&productID={$product->id}&browseType=bySearch&param=myQueryID&orderBy={$orderBy}");
        $this->config->execution->search['queryID']                       = $queryID;
        $this->config->execution->search['params']['module']['values']    = $modules;
        $this->config->execution->search['params']['execution']['values'] = array_filter($productExecutions);
        $this->loadModel('search')->setSearchParams($this->config->execution->search);
    }

    /**
     * 处理关联任务页面分页数据。
     * Process the pagination data of the associated task page.
     *
     * @param  int       $recTotal
     * @param  int       $recPerPage
     * @param  int       $pageID
     * @param  array     $allTasks
     * @access protected
     * @return void
     */
    protected function processLinkTaskPager(int $recTotal, int $recPerPage, int $pageID, array $allTasks)
    {
        $this->app->loadClass('pager', true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $pager->setRecTotal(count($allTasks));
        $pager->setPageTotal();
        if($pager->pageID > $pager->pageTotal) $pager->setPageID($pager->pageTotal);
        $count    = 1;
        $limitMin = ($pager->pageID - 1) * $pager->recPerPage;
        $limitMax = $pager->pageID * $pager->recPerPage;
        foreach($allTasks as $key => $task)
        {
            if($count <= $limitMin || $count > $limitMax) unset($allTasks[$key]);

            $count ++;
        }

        $this->view->allTasks = $allTasks;
        $this->view->pager    = $pager;
    }

    /**
     * 检查是否有权限编辑项目。
     * Check if you have permission to edit the project.
     *
     * @param  string    $hostType
     * @param  object    $sourceProject
     * @param  object    $MR
     * @access protected
     * @return bool
     */
    protected function checkProjectEdit(string $hostType, object $sourceProject, object $MR): bool
    {
        if($hostType == 'gitlab')
        {
            $groupIDList = array(0 => 0);
            $groups      = $this->loadModel('gitlab')->apiGetGroups($MR->hostID, 'name_asc', 'developer');
            foreach($groups as $group) $groupIDList[] = $group->id;

            $isDeveloper = $this->gitlab->checkUserAccess($MR->hostID, 0, $sourceProject, $groupIDList, 'developer');
            $gitUsers    = $this->loadModel('pipeline')->getUserBindedPairs($MR->hostID, 'gitlab');
            if(isset($gitUsers[$this->app->user->account]) && $isDeveloper) return true;
        }
        elseif($hostType == 'gitea')
        {
            return (isset($sourceProject->allow_merge_commits) && $sourceProject->allow_merge_commits == true);
        }
        elseif($hostType == 'gogs')
        {
            return (isset($sourceProject->permissions->push) && $sourceProject->permissions->push);
        }

        return false;
    }

    /**
     * 获取代码分支的访问地址。
     * Get repo branch url.
     *
     * @param  object     $host
     * @param  int|string $projectID $projectID is an int in gitlab and a string in gitea or gogs.
     * @param  string     $branch
     * @access protected
     * @return string
     */
    protected function getBranchUrl(object $host, int|string $projectID, string $branch): string
    {
        $branch = $this->loadModel($host->type)->apiGetSingleBranch($host->id, $projectID, $branch);
        return $branch ? zget($branch, 'web_url', '') : '';
    }

    /**
     * 检查是否有新的提交。
     * Check if there are new commits.
     *
     * @param  string    $hostType
     * @param  int       $hostID
     * @param  string    $projectID
     * @param  int       $mriid
     * @param  string    $lastTime
     * @access protected
     * @return bool
     */
    protected function checkNewCommit(string $hostType, int $hostID, string $projectID, int $mriid, string $lastTime): bool
    {
        $commitLogs = $this->mr->apiGetMRCommits($hostID, $projectID, $mriid);
        if($commitLogs)
        {
            $lastCommit = zget($commitLogs[0], 'committed_date', '');
            if(in_array($hostType, array('gitea', 'gogs'))) $lastCommit = $commitLogs[0]->author->committer->date;

            if($lastCommit > $lastTime) return true;
        }

        return false;
    }

    /**
     * 保存合并请求数据.
     * Save merge request data.
     *
     * @param  object    $repo
     * @param  array     $rawMRList
     * @access protected
     * @return bool
     */
    protected function saveMrData(object $repo, array $rawMrList): bool
    {
        $now = helper::now();
        $this->loadModel('action');
        foreach($rawMrList as $rawMR)
        {
            $MR = new stdclass();
            $MR->hostID        = $repo->serviceHost;
            $MR->mriid         = $rawMR->iid;
            $MR->sourceProject = $rawMR->source_project_id;
            $MR->sourceBranch  = $rawMR->source_branch;
            $MR->targetProject = $rawMR->target_project_id;
            $MR->targetBranch  = $rawMR->target_branch;
            $MR->title         = $rawMR->title;
            $MR->repoID        = $repo->id;
            $MR->createdBy     = $this->app->user->account;
            $MR->createdDate   = $now;
            $MR->assignee      = $MR->createdBy;
            $MR->mergeStatus   = $rawMR->merge_status ?: '';
            $MR->status        = $rawMR->state ?: '';
            $MR->isFlow        = empty($rawMR->flow) ? 0 : 1;
            if($MR->status == 'open') $MR->status = 'opened';

            $mrID = $this->mr->insertMr($MR);
            if($mrID) $this->action->create(empty($rawMR->flow) ? 'mr' : 'pullreq', $mrID, 'opened');

            if(dao::isError()) return false;
        }

        return true;
    }
}
