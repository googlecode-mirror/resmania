<?php
class RM_Admin_EmailNotificationsController extends RM_Controller
{

    /**
     * Returns JSON for the email notification list.
     *
     *
     */
    public function listJsonAction(){

        $model = new RM_EmailNotifications();
        $rows = $model->fetchAll()->toArray();
        
        return array(
            'data' => $rows
        );

    }

    public function listeventnamesJsonAction(){
        $model = new RM_Events();
        $rows = $model->fetchAll("enabled='1'")->toArray();

        return array(
            'data' => $rows
        );
    }

    /**
     * Return JSON object for setuping email notifcation edit page
     *
     * @return JSON object in format:
     * {
     *  messages: array(
     *   {event_name: <event name>, message: <notification message in iso language>}
     *   ...
     *  )
     *  language: <iso language name>
     */
    public function editJsonAction()
    {
        $id = $this->_getParam('id');
        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());
        
        $model = new RM_EmailNotifications();
        $rows = $model->fetchAll("id='".$id."'")->toArray();

        $json = new stdClass();
        $json->id = $id;
        $json->message = $rows[0][$iso];
        $json->language = $iso;

        return array(
            'data' => $json
        );
    }

    /**
     * Update notification messages and enabled options
     * @return JSON object success
     */
    public function updatelistJsonAction()
    {
        $dataJson = $this->_getParam('data', '[]');
        $data = Zend_Json::decode($dataJson);

        $model = new RM_EmailNotifications();
        foreach ($data as $row){

            if ($row['id'] == 0) {
                unset($row['id']);
                $model->insert($row);
            } else {
                $dbRow = $model->find($row['id'])->current();
                if ($dbRow == null) continue;
                foreach ($row as $key => $value) {
                    $dbRow->$key = $value;
                }
                $dbRow->save();
            }
        }

        return array('data' => array('success' => true));
    }

    public function deleteJsonAction()
    {
        $ids = $this->_getParam('ids', array());
        $model = new RM_EmailNotifications();

        foreach ($ids as $id) {
            $row = $model->find($id)->current();
            if ($row === null) continue;
            $row->delete();
        }

        return array(
            'data' => array('success' => true)
        );
    }
    
    /**
     * Update notification messages and enabled options     
     * @return JSON object success
     */
    public function updateJsonAction()
    {
        $id = $this->_getParam('id');
        $iso = $this->_getParam('iso', RM_Environment::getInstance()->getLocale());
        $message = $this->_getParam('message');

        $model = new RM_EmailNotifications();

        $dbRow = $model->fetchByID($id);

        if ($dbRow == null) {
            return array(
                'data' => array('success' => false)
            );
        }
        $dbRow->$iso = $message;
        $dbRow->save();        

        return array(
            'data' => array('success' => true)
        );
    }
}