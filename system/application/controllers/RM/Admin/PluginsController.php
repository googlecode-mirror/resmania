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
 * Admin Plugin Contoller
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Admin_PluginsController extends RM_Controller
{
    public function listJsonAction()
	{
        $offset = $this->_getParam('start');
        $count = $this->_getParam('limit');
        $sort = $this->_getParam('sort', 'name');
        $direction = $this->_getParam('dir', 'DESC');
        $filters = $this->_getParam('filter', array());

        $order = $sort . ' ' . $direction;
        $dao = new RM_Plugins;

        $total = $dao->filterAll($order, null, null, $filters)->count();
        $rows = $dao->filterAll($order, $count, $offset, $filters)->toArray();

        $extensions = RM_Environment::getInstance()->getOutOfDateExtensions();
        foreach ($rows as $key => $row) {
            $rows[$key]['upgrade'] = in_array($row['name'], $extensions['plugins']);
        }

        $json = new stdClass;
        $json->total = $total;
        $json->data = $rows;

        return array(
            'data' => $json
        );
	}

    public function autoupgradeJsonAction()
    {
        $json = new stdClass();
        $json->success = 1;
        $json->msg = array();

        $config = new RM_Config();
        $licenseKey = $config->getValue('rm_config_licensekey');
        if ($licenseKey==""){
            $json->success = 0;
            $json->msg[] = "License Key Not Entered";
            return array('data' => $json);
        }

        $ids = $this->_getParam('ids', array());
        $manager = new RM_Plugin_Manager($this->_translate);
        try {
            $dao = new RM_Plugins();
            foreach ($ids as $id) {
                $row = $dao->find($id)->current();
                $manager->autoUpgrade($row, $json);
            }
        } catch (Exception $e) {
            $json->success = 0;
            $json->msg[] = $e->getMessage();
        }
        return array('data' => $json);
    }

    public function upgradeJsonAction()
    {
        $json = new stdClass();
        $json->success = 1;
        $json->msg = array();

        $manager = new RM_Plugin_Manager($this->_translate);
        $json = $manager->uploadUpgrade('rm_pages_plugins_upgrade_form_upload', $json);
        return array('data' => $json);
    }

    public function installJsonAction()
    {
        return array(
            'data' => array('success' => true)
        );
    }

    public function uploadJsonAction()
    {
        $json = new stdClass();
        $json->success = 1;
        $json->msg = array();

        $manager = new RM_Plugin_Manager($this->_translate);
        $json = $manager->uploadInstall('rm_pages_plugins_install_form_upload', $json);
        return array('data' => $json);
    }

    public function uninstallJsonAction()
    {
        $json = new stdClass();
        $json->success = 1;
        $json->msg = array();

        $ids = $this->_getParam('ids', array());
        $manager = new RM_Plugin_Manager($this->_translate);

        $dao = new RM_Plugins();
        foreach ($ids as $id) {
            $row = $dao->find($id)->current();
            try {
                $json->success = $json->success && $manager->uninstall($row);
                RM_Loader::clearCache();
            } catch (Exception $e) {
                $json->success = 0;
                $json->msg[] = $e->getMessage();
            }
        }

       return array('data' => $json);
    }

    public function enableJsonAction()
    {
        $ids = $this->_getParam('ids', array());
        $model = new RM_Plugins();

        $unresolvedDependencies = array();
        foreach ($ids as $id) {
            $row = $model->find($id)->current();
            $result = $model->enable($row);
            if ($result !== true) {
                $unresolvedDependencies[$row->name] = array();
                foreach ($result as $unresolvedDependency){
                    $unresolvedDependencies[$row->name][] = (string)$unresolvedDependency;
                }
            }
        }

        $languageManager = new RM_Language_Manager();
        $languageManager->clearCache();

        // clear the css cache
        $model->clearCSSCache();

        return array(
            'data' => array('success' => true, 'errors' => $unresolvedDependencies)
        );
    }

    public function disableJsonAction()
    {
        $json = new stdClass();
        $json->success = 1;
        $json->msg = array();

        $ids = $this->_getParam('ids', array());
        $manager = new RM_Plugin_Manager($this->_translate);

        $model = new RM_Plugins();
        foreach ($ids as $id) {
            $row = $model->find($id)->current();
            try {
                $manager->disable($row);
            } catch (Exception $e) {
                $json->success = 0;
                $json->msg[] = $e->getMessage();
            }
        }

        return array(
            'data' => $json
        );
    }
}