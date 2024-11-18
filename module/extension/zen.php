<?php
declare(strict_types=1);
/**
 * The zen file of extension module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yuting Wang<wangyuting@easycorp.ltd>
 * @package     extension
 * @link        https://www.zentao.net
 */
class extensionZen extends extension
{
    /**
     * 安全性校验。
     * Check safe.
     *
     * @access protected
     * @return void
     */
    protected function checkSafe()
    {
        /* 判断是否要跳转到安全校验页面。 */
        $statusFile = $this->loadModel('common')->checkSafeFile();
        if($statusFile) die($this->fetch('extension', 'safe', 'statusFile=' . helper::safe64Encode($statusFile)));
    }

    /**
     * 根据插件代号获取指定的钩子文件地址。
     * Get hook file for install or uninstall.
     *
     * @param  string       $extension
     * @param  string       $hook      preinstall|postinstall|preuninstall|postuninstall
     * @access protected
     * @return string|false
     */
    protected function getHookFile(string $extension, string $hook): string|false
    {
        $hookFile = $this->extension->pkgRoot . "$extension/hook/$hook.php";
        if(file_exists($hookFile)) return $hookFile;
        return false;
    }

    /**
     * 根据数据库数据获取依赖当前插件的其他插件。
     * Get depends extension by database.
     *
     * @param  string    $extension
     * @access protected
     * @return array
     */
    protected function getDependsByDB(string $extension): array
    {
        $extensionInfo = $this->extension->getInfoFromDB($extension);
        $dependsList   = $this->extension->getDependsExtension($extension);

        $result = array();
        if($dependsList)
        {
            foreach($dependsList as $dependsExtension)
            {
                $depends = json_decode($dependsExtension->depends, true);
                if(empty($depends[$extension])) continue;

                if($this->compareForLimit($extensionInfo->version, $depends[$extension])) $result[] = $dependsExtension->name;
            }
        }

        return $result;
    }

    /**
     * 插件安装前的合规校验。
     * Check before installation.
     *
     * @param  string    $extension
     * @param  string    $ignoreCompatible
     * @param  string    $ignoreLink
     * @param  string    $overrideFile
     * @param  string    $overrideLink
     * @param  string    $installType
     * @access protected
     * @return bool
     */
    protected function checkExtension(string $extension, string $ignoreCompatible, string $ignoreLink, string $overrideFile, string $overrideLink, string $installType): bool
    {
        /* 安全性校验。 */
        $statusFile = $this->loadModel('common')->checkSafeFile();
        if($statusFile)
        {
            $this->view->error = sprintf($this->lang->extension->noticeOkFile, $statusFile, $statusFile);
            return false;
        }

        /* Check the package file exists or not. */
        $packageFile = $this->extension->getPackageFile($extension);
        if(!file_exists($packageFile))
        {
            $this->view->error = sprintf($this->lang->extension->errorPackageNotFound, $packageFile);
            return false;
        }

        /* Checking the extension paths. */
        $return = $this->checkExtensionPaths($extension);
        if($this->session->dirs2Created == false) $this->session->set('dirs2Created', $return->dirs2Created, 'admin');    // Save the dirs to be created.
        if($return->result != 'ok')
        {
            $this->view->error = $return->errors;
            return false;
        }

        /* Extract the package. */
        $return = $this->extractPackage($extension);
        if($return->result != 'ok')
        {
            $this->view->error = sprintf($this->lang->extension->errorExtracted, $packageFile, $return->error);
            return false;
        }

        /* Get condition. e.g. zentao|depends|conflicts. */
        $condition     = $this->extension->getCondition($extension);
        $installedExts = $this->extension->getLocalExtensions('installed');

        if(!$this->checkCompatible($extension, $condition, $ignoreCompatible, $ignoreLink, $installType)) return false;
        if(!$this->checkConflicts($condition, $installedExts))                                            return false;
        if(!$this->checkDepends($condition, $installedExts))                                              return false;
        if(!$this->checkFile($extension, $overrideFile, $overrideLink))                                   return false;

        return true;
    }

    /**
     * 检查插件包的目录结构是否禅道目录结构冲突。
     * Check files in the package conflicts with exists files or not.
     *
     * @param  string    $extension
     * @access protected
     * @return object
     */
    protected function checkFileConflict(string $extension): object
    {
        $return = new stdclass();
        $return->result = 'ok';
        $return->error  = '';

        $appRoot        = $this->app->getAppRoot();
        $extensionFiles = $this->extension->getFilesFromPackage($extension);
        foreach($extensionFiles as $extensionFile)
        {
            $compareFile = $appRoot . str_replace($this->extension->pkgRoot . $extension . DS, '', $extensionFile);
            if(!file_exists($compareFile)) continue;

            if(md5_file($extensionFile) != md5_file($compareFile)) $return->error .= $compareFile . '<br />';
        }

        if($return->error != '') $return->result = 'fail';
        return $return;
    }

    /**
     * 插件插件的hook文件到禅道目录。
     * Copy hookFiles to zentao.
     *
     * @param  string $extension
     * @access public
     * @return void
     */
    public function copyHookFiles(string $extension)
    {
        $extHookPath = $this->extension->pkgRoot . $extension . DS . 'hook' . DS;
        $hookPath    = $this->app->getBasePath() . DS . 'hook' . DS;
        if(!is_dir($extHookPath)) return;
        if(!is_dir($hookPath)) return;

        foreach(glob($hookPath . '*') as $hookFile) $this->extension->classFile->removeFile($hookFile);
        $this->extension->classFile->copyDir($extHookPath, $hookPath);
    }

    /**
     * 执行插件安装程序。
     * Install extension.
     *
     * @param  string    $extension
     * @param  string    $type
     * @param  string    $upgrade
     * @access protected
     * @return void
     */
    protected function installExtension(string $extension, string $type, string $upgrade): bool
    {
        /* The preInstall hook file. */
        $hook = $upgrade == 'yes' ? 'preupgrade' : 'preinstall';
        if($preHookFile = $this->getHookFile($extension, $hook)) include $preHookFile;

        /* Save to database. */
        $this->extension->saveExtension($extension, $type);

        /* Copy files to target directory. */
        $this->view->files = $this->copyPackageFiles($extension);

        /* Execute the install.sql. */
        $needExecuteDB = file_exists($this->extension->getDBFile($extension, 'install'));
        if($upgrade == 'no' && $needExecuteDB)
        {
            $return = $this->extension->executeDB($extension, 'install');
            if($return->result != 'ok')
            {
                $this->view->error = sprintf($this->lang->extension->errorInstallDB, $return->error);
                return false;
            }
        }

        /* Update status, dirs, files and installed time. */
        $data = array();
        $data['code']          = $extension;
        $data['status']        = 'installed';
        $data['dirs']          = $this->session->dirs2Created;
        $data['files']         = $this->view->files;
        $data['installedTime'] = helper::now();
        $this->extension->updateExtension($data);

        $this->session->set('dirs2Created', array(), 'admin');   // clean the session.
        $this->view->downloadedPackage = false;

        /* The postInstall hook file. */
        $hook = $upgrade == 'yes' ? 'postupgrade' : 'postinstall';
        if($postHookFile = $this->getHookFile($extension, $hook)) include $postHookFile;

        return true;
    }

    /**
     * 解压插件包到pkg目录。
     * Extract an extension.
     *
     * @param  string    $extension
     * @access protected
     * @return object
     */
    protected function extractPackage(string $extension): object
    {
        $return = new stdclass();
        $return->result = 'ok';
        $return->error  = '';

        /* 验证extension目录是否允许写入。 */
        $extensionRoot = $this->app->getExtensionRoot();
        if(is_dir($extensionRoot) && !is_writable($extensionRoot))
        {
            return (object)array('result' => 'fail', 'error' => strip_tags(sprintf($this->lang->extension->errorDownloadPathNotWritable, $extensionRoot, $extensionRoot)));
        }

        /* try remove pre extracted files. */
        $extensionPath = $this->extension->pkgRoot . $extension;
        if(is_dir($extensionPath)) $this->extension->classFile->removeDir($extensionPath);

        /* 获取插件包所在目录。 */
        $packageFile = $this->extension->getPackageFile($extension);

        /* 解压插件包到extensionPath目录。 */
        $this->app->loadClass('pclzip', true);
        $zip        = new pclzip($packageFile);
        $files      = $zip->listContent();
        $pathinfo   = pathinfo($files[0]['filename']);
        $removePath = isset($pathinfo['dirname']) && $pathinfo['dirname'] != '.' ? $pathinfo['dirname'] : $pathinfo['basename'];
        if($zip->extract(PCLZIP_OPT_PATH, $extensionPath, PCLZIP_OPT_REMOVE_PATH, $removePath) == 0)
        {
            $return->result = 'fail';
            $return->error  = $zip->errorInfo(true);
        }

        return $return;
    }

    /**
     * 复制插件包文件到禅道目录。
     * Copy package files.
     *
     * @param  string    $extension
     * @access protected
     * @return array
     */
    protected function copyPackageFiles(string $extension): array
    {
        $appRoot      = $this->app->getAppRoot();
        $extensionDir = $this->extension->pkgRoot . $extension . DS;
        $paths        = scandir($extensionDir);
        $copiedFiles  = array();
        foreach($paths as $path)
        {
            if($path == 'db' || $path == 'doc' || $path == 'hook' || $path == '..' || $path == '.') continue;

            $result      = $this->extension->classFile->copyDir($extensionDir . $path, $appRoot . $path, true);
            $copiedFiles = zget($result, 'copiedFiles', array());
        }

        foreach($copiedFiles as $key => $copiedFile)
        {
            $copiedFiles[$copiedFile] = md5_file($copiedFile);
            unset($copiedFiles[$key]);
        }
        return $copiedFiles;
    }

    /**
     * 卸载插件前备份即将删除的表。
     * Backup db when uninstall extension.
     *
     * @param  string       $extension
     * @access protected
     * @return string|false
     */
    protected function backupDB(string $extension): string|false
    {
        $sqls = file_get_contents($this->extension->getDBFile($extension, 'uninstall'));
        $sqls = explode(';', $sqls);

        /* Get tables for backup. */
        $backupTables = array();
        foreach($sqls as $sql)
        {
            $sql = str_replace('zt_', $this->config->db->prefix, $sql);
            $sql = preg_replace('/IF EXISTS /i', '', trim($sql));
            if(preg_match('/TABLE +`?([^` ]*)`?/i', $sql, $out))
            {
                if(!empty($out[1])) $backupTables[$out[1]] = $out[1];
            }
        }

        /* Back up database. */
        $zdb = $this->app->loadClass('zdb');
        if($backupTables)
        {
            $backupFile = $this->app->getTmpRoot() . $extension . '.' . date('Ymd') . '.sql';
            $result     = $zdb->dump($backupFile, $backupTables);
            if($result->result) return $backupFile;
        }
        return false;
    }

    /**
     * 标记此插件是否被禁用。
     * Mark package active or disabled
     *
     * @param  string    $extension
     * @param  string    $action     disabled|active
     * @access protected
     * @return bool
     */
    protected function togglePackageDisable(string $extension, string $action = 'disabled'): bool
    {
        if(!is_dir($this->extension->pkgRoot . $extension)) return true;

        $disabledFile = $this->extension->pkgRoot . $extension . DS . 'disabled';
        if($action == 'disabled') touch($disabledFile);
        if($action == 'active' && file_exists($disabledFile)) unlink($disabledFile);
        return true;
    }

    /**
     * 检查安装前的文件夹权限。
     * Check extension files.
     *
     * @param  string  $extension
     * @access private
     * @return object
     */
    private function checkExtensionPaths(string $extension): object
    {
        $checkResult = new stdclass();
        $checkResult->result        = 'ok';
        $checkResult->errors        = '';
        $checkResult->mkdirCommands = '';
        $checkResult->chmodCommands = '';
        $checkResult->dirs2Created  = array();

        /* 如果extension目录没有创建pkg文件夹并且创建pkg文件夹失败。 */
        if(!is_dir($this->extension->pkgRoot) && !mkdir($this->extension->pkgRoot))
        {
            $checkResult->errors        .= sprintf($this->lang->extension->errorTargetPathNotExists, $this->extension->pkgRoot) . '<br />';
            $checkResult->mkdirCommands .= "sudo mkdir -p {$this->extension->pkgRoot}<br />";
            $checkResult->chmodCommands .= "sudo chmod -R 777 {$this->pkgRoot}<br />";
        }

        /* 如果extension目录有pkg文件夹但是pkg文件夹不可写。 */
        if(is_dir($this->extension->pkgRoot) && !is_writable($this->extension->pkgRoot))
        {
            $checkResult->errors        .= sprintf($this->lang->extension->errorTargetPathNotWritable, $this->extension->pkgRoot) . '<br />';
            $checkResult->chmodCommands .= "sudo chmod -R 777 {$this->extension->pkgRoot}<br />";
        }

        /* 检查插件目录对应的禅道目录权限。 */
        $checkResult = $this->checkExtractPath($extension, $checkResult);

        if($checkResult->errors) $checkResult->result = 'fail';

        $checkResult->mkdirCommands = empty($checkResult->mkdirCommands) ? '' : '<code>' . str_replace('/', DIRECTORY_SEPARATOR, $checkResult->mkdirCommands) . '</code>';
        $checkResult->errors       .= $this->lang->extension->executeCommands . $checkResult->mkdirCommands;
        if(PHP_OS == 'Linux') $checkResult->errors .= empty($checkResult->chmodCommands) ? '' : '<code>' . $checkResult->chmodCommands . '</code>';

        return $checkResult;
    }

    /**
     * 检查安装插件时对应的禅道目录权限。
     * Check extension path read-write permission.
     *
     * @param  string  $extension
     * @param  object  $checkResult
     * @access private
     * @return object
     */
    private function checkExtractPath(string $extension, object $checkResult): object
    {
        $appRoot = $this->app->getAppRoot();
        $paths   = $this->extension->getPathsFromPackage($extension);
        foreach($paths as $path)
        {
            if($path == 'db' || $path == 'doc') continue;

            $path = rtrim($appRoot . $path, '/');
            if(is_dir($path))
            {
                /* 检查插件包里的代码文件夹对应禅道目录是否可写。 */
                if(!is_writable($path))
                {
                    $checkResult->errors        .= sprintf($this->lang->extension->errorTargetPathNotWritable, $path) . '<br />';
                    $checkResult->chmodCommands .= "sudo chmod -R 777 $path<br />";
                }
            }
            else
            {
                /* 检查插件包里的代码文件的父目录对应禅道目录是否可写。 */
                $parentDir = dirname($path);
                while(!file_exists($parentDir)) $parentDir = dirname($parentDir);
                if(!is_writable($parentDir))
                {
                    $checkResult->errors        .= sprintf($this->lang->extension->errorTargetPathNotWritable, $path) . '<br />';
                    $checkResult->chmodCommands .= "sudo chmod -R 777 $path<br />";
                    $checkResult->errors        .= sprintf($this->lang->extension->errorTargetPathNotExists, $path) . '<br />';
                    $checkResult->mkdirCommands .= "sudo mkdir -p $path<br />";
                }
                elseif(!mkdir($path, 0777, true))
                {
                    /* 如果目录不存在并且创建目录失败。 */
                    $checkResult->errors        .= sprintf($this->lang->extension->errorTargetPathNotExists, $path) . '<br />';
                    $checkResult->mkdirCommands .= "sudo mkdir -p $path<br />";
                }
                if(file_exists($path) && realpath($path) != $this->extension->pkgRoot) $checkResult->dirs2Created[] = $path;
            }
        }

        return $checkResult;
    }

    /**
     * 插件兼容性检查。
     * Extension compatibility check.
     *
     * @param  string  $extension
     * @param  object  $condition
     * @param  string  $ignoreCompatible
     * @param  string  $ignoreLink
     * @param  string  $installType
     * @access private
     * @return bool
     */
    private function checkCompatible(string $extension, object $condition, string $ignoreCompatible, string $ignoreLink, string $installType): bool
    {
        /* 不兼容版本检查。 */
        /* Check version incompatible */
        $incompatible = $condition->zentao['incompatible'];
        if($this->extension->checkVersion($incompatible))
        {
            $this->view->error = $this->lang->extension->errorIncompatible;
            return false;
        }

        /* 兼容版本检查。 */
        /* Check version compatible. */
        $zentaoCompatible = $condition->zentao['compatible'];
        if(!$this->extension->checkVersion((string)$zentaoCompatible) && $ignoreCompatible == 'no')
        {
            $this->view->error = sprintf($this->lang->extension->errorCheckIncompatible, $installType, $ignoreLink, $installType, inlink('obtain'));
            return false;
        }

        return true;
    }

    /**
     * 插件与插件之间的冲突检查。
     * Check conflicts.
     *
     * @param  object  $condition
     * @param  array   $installedExts
     * @access private
     * @return bool
     */
    private function checkConflicts(object $condition, array $installedExts): bool
    {
        $conflicts = $condition->conflicts;
        if($conflicts)
        {
            $conflictsExt = '';
            foreach($conflicts as $code => $limit)
            {
                if(isset($installedExts[$code]))
                {
                    if($this->compareForLimit($installedExts[$code]->version, $limit)) $conflictsExt .= $installedExts[$code]->name . " ";
                }
            }

            if($conflictsExt)
            {
                $this->view->error = sprintf($this->lang->extension->errorConflicts, $conflictsExt);
                return false;
            }
        }
        return true;
    }

    /**
     * 相关依赖插件检查。
     * Check depends.
     *
     * @param  object  $condition
     * @param  array   $installedExts
     * @access private
     * @return bool
     */
    private function checkDepends(object $condition, array $installedExts): bool
    {
        $depends = $condition->depends;
        if($depends)
        {
            $dependsExt = '';
            foreach($depends as $code => $limit)
            {
                $noDepends = false;
                if(isset($installedExts[$code]))
                {
                    if($this->compareForLimit($installedExts[$code]->version, $limit, 'noBetween')) $noDepends = true;
                }
                else
                {
                    $noDepends = true;
                }

                $extVersion = '';
                if($limit != 'all')
                {
                    $extVersion .= '(';
                    if(!empty($limit['min'])) $extVersion .= '>=v' . $limit['min'];
                    if(!empty($limit['max'])) $extVersion .= ' <=v' . $limit['max'];
                    $extVersion .=')';
                }
                if($noDepends) $dependsExt .= $code . $extVersion . ' ' . html::a(inlink('obtain', 'type=bycode&param=' . helper::safe64Encode($code)), $this->lang->extension->installExt, '_blank') . '<br />';
            }

            if($noDepends)
            {
                $this->view->error = sprintf($this->lang->extension->errorDepends, $dependsExt);
                return false;
            }
        }
        return true;
    }

    /**
     * 插件文件和禅道已有文件的冲突检查。
     * Check files in the package conflicts with exists files or not.
     *
     * @param  string  $extension
     * @param  string  $overrideFile
     * @param  string  $overrideLink
     * @access private
     * @return bool
     */
    private function checkFile(string $extension, string $overrideFile, string $overrideLink): bool
    {
        if($overrideFile == 'no')
        {
            $return = $this->checkFileConflict($extension);
            if($return->result != 'ok')
            {
                $this->view->error = sprintf($this->lang->extension->errorFileConflicted, $return->error, $overrideLink, inlink('obtain'));
                return false;
            }
        }

        return true;
    }

    /**
     * 检查当前版本是否包含在指定版本内。
     * Compare for limit data.
     *
     * @param  string       $version
     * @param  array|string $limit
     * @param  string       $type
     * @access private
     * @return bool
     */
    private function compareForLimit(string $version, array|string $limit, string $type = 'between'): bool
    {
        $result = false;
        if(empty($limit))   return true;
        if($limit == 'all') return true;

        if(!empty($limit['min']) && $version >= $limit['min'])           $result = true;
        if(!empty($limit['max']) && $version <= $limit['max'])           $result = true;
        if(!empty($limit['max']) && $version > $limit['max'] && $result) $result = false;

        /* 如果取的不是被包含则返回取反的布尔值。 */
        if($type != 'between') return !$result;

        return $result;
    }
}
