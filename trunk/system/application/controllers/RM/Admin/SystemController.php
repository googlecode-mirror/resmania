<?php

/**
 * ResMania - Reservation System Framework http://resmania.com
 * Copyright (C) 2011  ResMania Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *
 * Admin System Controller
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_SystemController extends RM_Controller {

    function checksqlAction() {
        $system = new RM_System();
        $sql = file_get_contents(dirname(__FILE__) . '/../../../../sql/install.sql');
        $queries = $system->splitSQL($sql);
        echo "System parsed SQL qieries: " . count($queries) . "<br/>";
        foreach ($queries as $key => $query) {
            echo ($key + 1) . ": $query<br/>";
        }
        die();
    }

    function mediamanagerJsonAction() {
        //check for a new images
        $manager = new RM_Media_Manager();
        return array('data' => array('success' => true, 'newImages' => $manager->newImages()));
    }

    function mediamanagerreccanJsonAction() {
        $manager = new RM_Media_Manager();
        $manager->rescan();
        return array('data' => array('success' => true));
    }

    /**
     * Upload and install language
     *
     * @return array
     */
    function mediamanageruploadJsonAction() {
        $manager = new RM_Media_Manager();
        try {
            $filename = $manager->upload();
            return array('data' => array('success' => true));
        } catch (RM_Exception $exception) {
            return array('data' => array('success' => false, 'error' => $exception->getMessage()));
        }
    }

    function thumbsJsonAction() {
        $manager = new RM_Media_Manager;
        if ($manager->newImages()) {
            $manager->rescan();
        }
        $images = $manager->getList();

        $jsonImages = array();
        foreach ($images as $image) {
            $imageArray = (array) $image;
            $imagePreferences = array();
            foreach ($imageArray as $key => $value) {
                $imagePreferences[] = "$key:'" . $value . "'";
            }
            $jsonImages[] = "{" . implode(',', $imagePreferences) . "}";
        }

        return array('data' => "{images:[" . implode(',', $jsonImages) . "]}", 'encoded' => true);
    }

    function deleteselectedimageJsonAction() {
        $images = $this->_getParam('images');
        foreach ($images as $imageName) {
            $rmMedia = new RM_Media_Manager();
            $rmMedia->deleteImageFiles($imageName);
        }
        return array('data' => array('success' => false));
    }

    /**
     * Check resmania server for a changelog file if user have an old version of a core
     *
     * @return json
     * success : true/false, if 'false' - there is no connection to resmania server or we have the latest version
     * changelog : text - changelog file from resmania sever
     * msg : text - if we have latest version
     */
    public function upgradechangelogJsonAction() {

        // check the license is valid
        $config = new RM_Config();
        $licenseKey = $config->getValue('rm_config_licensekey');
        if ($licenseKey == ""){return array('data' => array('success' => false, 'message' => $this->_translate->_('Common', 'NotLicensed')));}
        $path = RM_Environment::getInstance()->getDistroURL()."d.php?d=".base64_encode( '{"d": "'.$_SERVER['SERVER_NAME'].'", "l": "'.$licenseKey.'", "p": "core", "v":"'.RM_Environment::getInstance()->getVersion().'","qt":"size"}');
        $upgradeAvailable = @file_get_contents($path);
        if ($upgradeAvailable === "false"){return array('data' => array('success' => false, 'license'=> false, 'message' => $this->_translate->_('Common', 'LicenseNotValid')));}

        $oldVersion = RM_Environment::getInstance()->checkVersion();
        $changelogURL = Zend_Registry::get('config')->get('changelog')->get('url');

        if ($oldVersion == false) {
            return array(
                'data' => array(
                    'success' => false,
                    'license'=> true,
                    'message' => $this->_translate->_('Admin.System.Upgrade', 'ChangelogLatest') . '<a href="' . $changelogURL . '" target="blank">' . RM_Environment::getInstance()->getVersion() . '</a>'
                )
            );
        }

        $changelog = @file_get_contents($changelogURL);
        if ($changelog == null) {
            return array(
                'data' => array(
                    'success' => false,
                    'license'=> true,
                    'message' => $this->_translate->_('Admin.System.Upgrade', 'ChangelogError')
                )
            );
        }

        return array(
            'data' => array(
                'success' => true,
                'license'=> true,
                'changelog' => $changelog
            )
        );
    }

    /** Download the Core Zip File
     *  step 1
     *
     * @return json
     */
    public function downloadcoreJsonAction() {

        $tmpPath = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'userdata',
                    'temp'
                ));

        if (is_writable($tmpPath)) {
            if (!file_exists($tmpPath . DIRECTORY_SEPARATOR . "upgrade")) {
                if (!@mkdir($tmpPath . DIRECTORY_SEPARATOR . "upgrade"))
                    return array('data' => array('success' => false, 'msg' => 'Could not create temp upgrade folder'));
            }
        } else {
            return array('data' => array('success' => false, 'msg' => 'Temp Not Writable'));
        }

        $version = RM_Environment::getInstance()->getLatestVersion();

        $config = new RM_Config();
        $licenseKey = $config->getValue('rm_config_licensekey');
        $sourceFileSize = @file_get_contents(RM_Environment::getInstance()->getDistroURL()."d.php?d=".base64_encode( '{"d": "'.$_SERVER['SERVER_NAME'].'", "l": "'.$licenseKey.'", "p": "core", "v":"'.$version.'", "qt": "size"}'));
        if ($sourceFileSize === "false"){
            return array('data' => array('success' => false, 'msg' => $this->_translate->_('Common', 'LicenseNotValid')));
        }

        $sourceFile = RM_Environment::getInstance()->getDistroURL()."d.php?d=".base64_encode( '{"d": "'.$_SERVER['SERVER_NAME'].'", "l": "'.$licenseKey.'", "p": "core", "v":"'.$version.'"}');
        $this->_setSourceSize($sourceFileSize, $this->_getParam('sessionid'));

        $destinationFile = $tmpPath . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR . 'core.zip';

        // remove any old versions of core.zip
        unlink($destinationFile);

        if (function_exists('copy')) { // check this is enabled
            $copyResult = copy($sourceFile, $destinationFile);
        } else {
            // try to use fopen to copy this instead...
            $rmfs = new RM_Filesystem();
            $copyResult = $rmfs->fileDownload($sourceFile, $destinationFile);
        }

        if (!$copyResult) {
            RM_Log::toLog('Upgrade - Core Download Failed (source: ' . $sourceFile . " destination: " . $destinationFile . ")", RM_Log::ERR);
            return array('data' => array('success' => false, 'msg' => 'Download Failed'));
        }

        RM_Log::toLog('Upgrade - Core Download Completed OK', RM_Log::INFO);
        return array('data' => array('success' => true));
    }

    /** Used to return the file download progress
     *
     * @return JSON
     */
    public function downloadprogressJsonAction() {

        @session_start($this->_getParam('sessionid')); // resume the session

        $destinationFile = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'userdata',
                    'temp',
                    'upgrade',
                    'core.zip'
                ));

        $destinationSize = filesize($destinationFile);
        $sourceSize = $this->_getSourceSize();


        if ($sourceSize > 0 && $destinationSize > 0) {
            $percentComplete = (round((100 / $sourceSize) * $destinationSize, 0)) / 100; // extJs ProgressBar required 0 to 1
        } else {
            $percentComplete = 0;
        }

        $json = new stdClass;
        $json->percentcomplete = $percentComplete;

        return array(
            'data' => $json
        );
    }

    /** Unzip the Core.zip file after download
     *  Step 2
     *
     * @return json
     */
    public function unzipcorezipJsonAction() {

        $updateFolder = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'userdata',
                    'temp',
                    'upgrade'
                ));

        if (RM_Filesystem::unZip($updateFolder . DIRECTORY_SEPARATOR . 'core.zip', $updateFolder)) {
            RM_Log::toLog('Upgrade - Core Unzip Completed OK', RM_Log::INFO);
            return array('data' => array('success' => true));
        } else {
            RM_Log::toLog('Upgrade - Core Unzip Failed!', RM_Log::ERR);
            return array('data' => array('success' => false));
        }
    }

    /** Copy the new Core files to the RM folder and upgrade the files
     *  step 4
     *
     * @return json
     */
    public function copyfilesJsonAction() {
        $core = $this->_getParam('core');
        $modules = $this->_getParam('modules');
        $plugins = $this->_getParam('plugins');
        $userdata = $this->_getParam('userdata');

        // sources
        $coreSource = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'userdata', 'temp', 'upgrade', 'system'));
        $moduleSource = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'userdata', 'temp', 'upgrade', 'modules'));
        $pluginSource = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'userdata', 'temp', 'upgrade', 'plugins'));
        $userdataSource = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'userdata', 'temp', 'upgrade', 'userdata'));

        // destinations
        $coreDest = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'system'));
        $moduleDest = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'modules'));
        $pluginDest = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'plugins'));
        $userdataDest = implode(DIRECTORY_SEPARATOR, array(RM_Environment::getConnector()->getRootPath(), 'RM', 'userdata'));

        // this is any file excludes
        //$systemExclude = array('application'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'RM'.DIRECTORY_SEPARATOR.'Admin');
        $userDataExclude = array(
            'backups',
            'images' . DIRECTORY_SEPARATOR . 'media',
            'images' . DIRECTORY_SEPARATOR . 'units',
            'logs',
            'css' . DIRECTORY_SEPARATOR . 'user_overrides.css',
            'views' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'invoice.phtml'
        );

        $msg = 0;
        $RMFS = new RM_Filesystem();

        if ($modules) {
            $copyResult = $RMFS->recursivecopy($moduleSource, $moduleDest);
            if (!$copyResult)
                $msg.=" Errors occured in copying upgrade files: " . $copyResult;
        }
        if ($plugins) {
            $copyResult = $RMFS->recursivecopy($pluginSource, $pluginDest);
            if (!$copyResult)
                $msg.=" Errors occured in copying upgrade files: " . $copyResult;
        }
        if ($userdata) {
            $copyResult = $RMFS->recursivecopy($userdataSource, $userdataDest, $userDataExclude);
            if (!$copyResult)
                $msg.=" Errors occured in copying upgrade files: " . $copyResult;
        }

        // core must be last, as the script running the upgrade will be replaced
        if ($core) {
            // !!!MUST BE LAST!!!!
            $copyResult = $RMFS->recursivecopy($coreSource, $coreDest);
            if (!$copyResult)
                $msg.=" Errors occured in copying upgrade files: " . $copyResult;
        }

        if ($msg === 0) {
            RM_Environment::getInstance()->clearCache();
            RM_Log::toLog('Upgrade - File Copy Completed OK', RM_Log::INFO);
            return array('data' => array('success' => true));
        } else {
            RM_Log::toLog('Upgrade - File Copy Failed', RM_Log::ERR);
            return array('data' => array('success' => false, 'msg' => $msg));
        }
    }

    /** This performs the DB Upgrade
     *  step 3
     *  This process will apply the upgrade sql files in sequence. So that all
     *  updates are applied from one version to the next.
     *
     * @return json
     */
    public function upgradedatabaseJsonAction() {
        // get the current db version
        $system = new RM_System();
        $dbVersion = $system->getDBVersion()->current()->db_version;
        // get the latest version
        $version = RM_Environment::getInstance()->getLatestVersion();

        if ($dbVersion == $version) {
            RM_Log::toLog('Upgrade - DB upgrade not required as DB is at latest revision: ' . $dbVersion, RM_Log::INFO);
            return array('data' => array(
                    'success' => 1,
                    'error' => "No DB Upgrade Action Required as DB Version is at Latest Revision"
            ));
        }

        // Remove all dots from version so we will get the integer of the version. ie: 1211630
        $dbCurrentVersionNumber = (int) str_replace(".", "", $dbVersion);

        $sqlPath = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'userdata',
                    'temp',
                    'upgrade',
                    'system',
                    'sql'
                ));

        // if this is the stable release switch to the upgrades folder...
        if (version_compare($dbVersion, "1.0.0", '>=')) {
            $sqlPath = $sqlPath . DIRECTORY_SEPARATOR . "upgrades";
        }

        $filesContent = array();
        $files = RM_Filesystem::getFiles($sqlPath, array('sql'));
        $numbers = array();
        foreach ($files as $file) {
            $chunks = explode('.', $file);
            if (count($chunks) == 3 && $chunks[1] == 'upgrade') {
                $numbers[] = $chunks[0];
            }
        }

        sort($numbers);
        foreach ($numbers as $number) {
            if ($number > $dbCurrentVersionNumber) {
                $sqlFile = $sqlPath . DIRECTORY_SEPARATOR . $number . ".upgrade.sql";
                RM_Log::toLog('Upgrade - Applying DB upgrade file: ' . $sqlFile, RM_Log::INFO);
                if (file_exists($sqlFile)) {
                    $filesContent[] = file_get_contents($sqlFile);
                }
            }
        }
        foreach ($filesContent as $fileContent) {
            try {
                $result = $system->parseSQL($fileContent);
            } catch (Exception $e) {
                $result = false;
            }
            if (!$result) {
                RM_Log::toLog('Upgrade - DB Upgrade has failed!', RM_Log::ERR);
                $msg = 'The database query for installation/upgrade has failed! More Information about the exception thrown will be in the systemlog';
                return array('data' => array('success' => false, 'msg' => $msg));
            }
        }

        return array('data' => array('success' => true));
    }

    /*
     * Store the source file path and size
     *
     * @params  string  $sourceFile The filename and path of the source file
     */

    private function _setSourceSize($sourceSize, $sessionID) {

        if ($sourceSize == 0) {
            return false;
        }

        if (isset($_SESSION['RM_sourceFileSize'])) {
            unset($_SESSION['RM_sourceFileSize']);
        } //clear it, if it's found

        $_SESSION['RM_sourceFileSize'] = $sourceSize;

        session_write_close();
    }

    /*
     * Get the source file properties
     *
     * @return  array   containing source path and source filesize
     */

    private function _getSourceSize() {

        // resume a session
        @session_start();

        if (isset($_SESSION['RM_sourceFileSize'])) {
            return $_SESSION['RM_sourceFileSize'];
        } else {
            return 0;
        }
    }

    /**
     * @param string
     * @return array
     */
    private function _splitSQL($sql) {
        $sql = trim($sql);
        $sql = preg_replace("/\n\#[^\n]*/", '', "\n" . $sql);
        $buffer = array();
        $ret = array();
        $in_string = false;

        for ($i = 0; $i < strlen($sql) - 1; $i++) {
            if ($sql[$i] == ";" && !$in_string) {
                $ret[] = substr($sql, 0, $i);
                $sql = substr($sql, $i + 1);
                $i = 0;
            }

            if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
                $in_string = false;
            } elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
                $in_string = $sql[$i];
            }
            if (isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
        }

        if (!empty($sql)) {
            $ret[] = $sql;
        }
        return ($ret);
    }

    /**
     * this invokes the post upgrade PHP script.
     */
    public function postupgradescriptJsonAction(){

        $postupgradeScript = implode(DIRECTORY_SEPARATOR, array(
            RM_Environment::getConnector()->getRootPath(),
            'RM',
            'userdata',
            'temp',
            'upgrade',
            'userdata',
            'temp',
            'upgrade',
            'postupgrade.php'
        ));

        if (file_exists($postupgradeScript)){
            include_once $postupgradeScript;
            $model = new ResMania_Post_Upgrade();
            $result = $model->postUpgradeAction();

            if ($result){
                return array('data' => array('success' => true));
            }

            return array('data' => array('success' => false));
        }

        return array('data' => array('success' => true));
    }

    /**
     * clear up
     * remove old upgrade files
     */
    public function cleanupJsonAction() {
        // upgrade temp file path
        $filePath = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getRootPath(),
                    'RM',
                    'userdata',
                    'temp',
                    'upgrade'
                ));

        $deleteArray = array(
            $filePath . DIRECTORY_SEPARATOR . "core.zip",
            $filePath . DIRECTORY_SEPARATOR . "modules",
            $filePath . DIRECTORY_SEPARATOR . "plugins",
            $filePath . DIRECTORY_SEPARATOR . "system",
            $filePath . DIRECTORY_SEPARATOR . "tests",
            $filePath . DIRECTORY_SEPARATOR . "userdata",
            $filePath . DIRECTORY_SEPARATOR . "license",
            $filePath . DIRECTORY_SEPARATOR . "postupgrade.php"
        );

        foreach ($deleteArray as $item) {
            RM_Log::toLog('Upgrade - temp file/folder deleted: ' . $item, RM_Log::INFO);
            RM_Filesystem::deleteFolder($item);
        }

        return array('data' => array('success' => true));
    }

}