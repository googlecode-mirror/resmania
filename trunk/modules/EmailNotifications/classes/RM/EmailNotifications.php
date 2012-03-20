<?php
class RM_EmailNotifications extends RM_Model_Multilingual {
    protected $_name = 'rm_email_notifications';
    protected $_rowClass = 'RM_EmailNotifications_Row';

    const REGULAR_USER = 0;
    const ADMINISTRATOR = 99;
    const UNIT_MANAGER = 0;

    /**
     * We extends supper to change type of a column, from varchar to text
     * @param string $iso
     */
    function addLanguage($iso)
    {
        $adapter = $this->getAdapter();
        $sql = "ALTER TABLE `".$this->_name."` ADD `".$iso."` TEXT NOT NULL ";
        $adapter->query($sql);
    }

    /**
     * Return email template by it's name /and destination
     *
     * @param string $eventName
     * @param int $destination on of the RM_EmailNotifications constants
     * @return RM_EmailNotifications_Row
     */
    function fetchByName($eventName, $destination = null){
        $select = $this->select()->where('event_name=?', $eventName);
        if ($destination !== null) {
            $select->where('destination=?', $destination);
        }
        $val = $this->fetchAll($select);
        return $val;
    }

    /**
     * Return template by it's id
     *
     * @param string $id
     * @return RM_EmailNotifications_Row
     */
    function fetchByID($id){
        return $this->fetchRow($this->select()->where('id=?', $id));
    }


}
