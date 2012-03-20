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
 *
 * Class for handling all DAO method for Units
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
class RM_Units extends RM_Model_Flexible {

    /**
     * Database table name
     *
     * @var string
     */
    protected $_name = 'rm_units';
    protected $_rowClass = 'RM_Unit_Row';
    protected static $_defaultRowClass = null;

    /**
     * Database primary key column name
     *
     * @var string
     */
    protected $_primary = 'id';

    /**
     * This method sets row class for any new objects so it possible
     * to change this row class on a fly during script invocation
     *
     * @static
     * @param  $className
     * @return void
     */
    public static function setDefaultRowClass($className) {
        self::$_defaultRowClass = $className;
    }

    /**
     * @param array $config
     * @return void
     */
    public function __construct($config = array()) {
        if (self::$_defaultRowClass !== null) {
            $config[self::ROW_CLASS] = self::$_defaultRowClass;
        }
        return parent::__construct($config);
    }

    public function deleteRow(RM_Unit_Row $unit) {
        $folderName = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getCorePath(),
                    'userdata',
                    'images',
                    'units',
                    $unit->id
                ));
        $fileSystem = new RM_Filesystem();
        $fileSystem->deleteFolder($folderName);
        return $unit->delete();
    }

    /**
     * Create a copy of unit object
     *
     * @throw RM_Exception - if we have an error that we can't handle internally
     * @param RM_Unit_Row $unit - unit that we need to copy
     * @return bool - true if copy was made without any errors, if there will be some errors method will
     * throw an exception
     */
    public function copyRow(RM_Unit_Row $unit) {
        $copy = $this->_copyRow($unit);
        $this->_copyLanguage($unit, $copy);
        $this->_copyMedia($unit, $copy);
        $this->_copyExtensions($unit, $copy);
        return true;
    }

    protected function _copyExtensions(RM_Unit_Row $original, RM_Unit_Row $copy) {
        $modulesModel = new RM_Modules();
        $modules = $modulesModel->getAll();
        foreach ($modules as $module) {
            if ($module instanceof RM_Unit_Copy_Interface) {
                $module->copyInformation($original, $copy);
            }
        }

        $pluginsModel = new RM_Plugins();
        $plugins = $pluginsModel->getAll();
        foreach ($plugins as $plugin) {
            if ($plugin instanceof RM_Unit_Copy_Interface) {
                $plugin->copyInformation($original, $copy);
            }
        }
    }

    protected function _copyRow(RM_Unit_Row $unit) {
        $copyData = $unit->toArray();
        unset($copyData['id']);

        // make sure that language dependant data is removed
        unset($copyData['name']);
        unset($copyData['summary']);
        unset($copyData['description']);

        $id = parent::insert($copyData);
        $copyUnit = $this->find($id)->current();
        return $copyUnit;
    }

    protected function _copyMediaFiles(RM_Unit_Row $original, RM_Unit_Row $copy) {
        $rmConfig = new RM_Config();
        $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'), 8);

        $destination = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getCorePath(),
                    'userdata',
                    'images',
                    'units',
                    $copy->getId()
                ));
        $result = mkdir($destination, $chmodOctal, true);
        if (!$result) {
            throw new RM_Exception(
                    RM_Environment::getInstance()->getTranslation()->_('Admin.Units.List', 'CopyErrorMediaFolderCreation') . ' ' . $destination
            );
        }

        $fileSystem = new RM_Filesystem();
        $source = implode(DIRECTORY_SEPARATOR, array(
                    RM_Environment::getConnector()->getCorePath(),
                    'userdata',
                    'images',
                    'units',
                    $original->getId()
                ));
        $result = $fileSystem->recursivecopy($source, $destination);
        if ($result == false) {
            throw new RM_Exception(
                    sprintf(RM_Environment::getInstance()->getTranslation()->_('Admin.Units.List', 'CopyErrorMediaFilesCopy'), $source, $destination)
            );
        }
    }

    protected function _copyMediaData(RM_Unit_Row $original, RM_Unit_Row $copy) {
        $unitMediaFilesModel = new RM_UnitMediaFiles();
        $unitMediaFiles = $unitMediaFilesModel->get($original);

        $unitMediaFileTypesModel = new RM_UnitMediaFileTypes();
        foreach ($unitMediaFiles as $unitMediaFile) {
            $copyMediaFileData = $unitMediaFile->toArray();
            unset($copyMediaFileData['id']);
            $copyMediaFileData['unit_id'] = $copy->getId();
            $copyMediaFile = $unitMediaFilesModel->createRow($copyMediaFileData);
            $copyMediaFileID = $copyMediaFile->save();

            $unitMediaFileTypes = $unitMediaFileTypesModel->getByFile($unitMediaFile);
            foreach ($unitMediaFileTypes as $unitMediaFileType) {
                $copyUnitMediaFileTypeData = $unitMediaFileType->toArray();
                unset($copyUnitMediaFileTypeData['id']);
                $copyUnitMediaFileTypeData['file_id'] = $copyMediaFileID;
                $copyUnitMediaFileType = $unitMediaFileTypesModel->createRow($copyUnitMediaFileTypeData);
                $copyUnitMediaFileType->save();
            }
        }
        return true;
    }

    /**
     * Copy all media content from original to a copy of an unit
     *
     * @throws RM_Exception
     * @param RM_Unit_Row $original
     * @param RM_Unit_Row $copy
     * @return bool
     */
    protected function _copyMedia(RM_Unit_Row $original, RM_Unit_Row $copy) {
        $this->_copyMediaFiles($original, $copy);
        $this->_copyMediaData($original, $copy);
        return true;
    }

    /**
     * Copy all language dependent information from one unit to another
     *
     * @param RM_Unit_Row $unit
     * @param  $copyUnitID
     * @return bool
     */
    protected function _copyLanguage(RM_Unit_Row $original, RM_Unit_Row $copy) {
        $languageDetailsModel = new RM_UnitLanguageDetails();
        $languages = $languageDetailsModel->fetchByUnit($original);
        foreach ($languages as $language) {
            $copyLanguageData = $language->toArray();
            unset($copyLanguageData['id']);
            $copyLanguageData['unit_id'] = $copy->getId();
            $copyLanguage = $languageDetailsModel->createRow($copyLanguageData);
            $copyLanguage->save();
        }
        return true;
    }

    /**
     * Checks if in system exists any reservation with this unitID
     *
     * @param int $unitID unit primary key value
     * @return bool
     */
    public function isReserved($unitID) {
        $unitModel = new RM_Units();
        $unit = $unitModel->find($unitID)->current();
        if ($unit == null)
            return false;

        $model = new RM_ReservationDetails();
        $details = $model->fetchAllBy($unit);

        return (count($details) > 0);
    }

    /**
     * Insert a unit by its primary key
     *
     * @param array $unit Array with all unit information
     * @param string $iso ISO language code ('cause we got all unit info with language dependent fields)
     * @param boolean $fromGUI This parameter told us was that data from GUI or it's internal usage.
     * @return mixed The primary key of the row inserted.
     */
    public function insert($unit, $iso, $fromGUI = false) {
        $unitTypeDAO = new RM_UnitTypes();
        $type = $unitTypeDAO->find($unit['type_id'])->current();

        list($fields, $languageFields) = $this->_getFieldsByType($type);

        $unitArray = array();
        $languageArray = array();

        foreach ($fields as $field) {
            $unitArray[$field->column_name] = $unit[$field->column_name];
        }

        foreach ($languageFields as $field) {
            $languageArray[$field->column_name] = $unit[$field->column_name];
        }

        //TODO: this code need to be more flexible to do not use UnitTypeManager module
        $manager = new RM_Module_UnitTypeManager();
        $unitArray['type_id'] = $manager->getDefaultUnitType()->id;

        if ($fromGUI) {
            $languageArray['unit_id'] = $this->insertFromGUI($unitArray);
        } else {
            $languageArray['unit_id'] = parent::insert($unitArray);
        }

        $unitModel = new RM_Units();
        $newUnit = $unitModel->createRow(array('id' => $languageArray['unit_id']));

        $mediaManager = new RM_Media_Unit_Manager($newUnit);
        $mediaManager->createFolder();

        //we need to create a unit detail row for every language installed in the system
        $languageModel = new RM_Languages();
        $languages = $languageModel->fetchAll();
        $unitLanguageModel = new RM_UnitLanguageDetails();
        foreach ($languages as $language) {
            $languageArray['iso'] = $language->iso;
            $unitLanguageModel->insert($languageArray);
        }
        return $languageArray['unit_id'];
    }

    /**
     * Update a unit
     *
     * @param array $unit Array with all unit information + with ISO code that we need
     * to update language dependent fields
     * @return int The total number of rows updated in different tables.
     */
    public function updateUnit($unit) {
        list($fields, $languageFields) = $this->_getFields($unit['id']);

        $unitArray = array();
        $languageArray = array();

        foreach ($fields as $field) {
            $unitArray[$field->column_name] = $unit[$field->column_name];
        }

        foreach ($languageFields as $field) {
            $languageArray[$field->column_name] = $unit[$field->column_name];
        }

        $languageArray['iso'] = $unit['iso'];
        $languageArray['unit_id'] = $unit['id'];

        $languageModel = new RM_UnitLanguageDetails();
        $updatedRows = $languageModel->update($languageArray);

        $updatedRows+= parent::updateFromGUI($unitArray);
        return $updatedRows;
    }

    /**
     * Returns all fields information belongs to a different type of a unit.
     *
     * @param Zend_Db_Table_Row $type Unit type
     * @return array Array with keys:
     * 0 => Zend_Db_Table_Rowset with language undependent fields
     * 1 => Zend_Db_Table_Rowset with language dependent fields
     */
    protected function _getFieldsByType($type) {
        $configModel = new RM_UnitConfig();
        $fields = $configModel->getFields($type->id);
        $languageFields = $configModel->getLanguageFields($type->id);

        return array($fields, $languageFields);
    }

    /**
     * Returns all fields belongs to a unit by it primary key value.
     *
     * @param int $unitID Unit type
     * @return array Array with keys:
     * 0 => Zend_Db_Table_Rowset with language undependent fields
     * 1 => Zend_Db_Table_Rowset with language dependent fields
     */
    protected function _getFields($unitID) {
        $unit = $this->find($unitID)->current();

        $unitTypeDAO = new RM_UnitTypes();
        $type = $unitTypeDAO->getByUnit($unit);

        return $this->_getFieldsByType($type);
    }

    /**
     * Returns only unit IDs of that unit that have images
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function getWithImages() {
        $sql = "SELECT 
            DISTINCT(ru.id)
        FROM
            rm_units ru
        LEFT OUTER JOIN
            rm_unit_media_files rumf ON rumf.unit_id = ru.id
        INNER JOIN
            rm_unit_media_file_types rumft ON rumft.file_id = rumf.id";
        $result = $this->_getBySQL($sql);

        $ids = array();
        foreach ($result as $row) {
            $ids[] = $row->id;
        }
        return $ids;
    }

    /*
      public function getAll(RM_Unit_Search_Criteria $criteria){
      //TODO: here will be a lot of code of creation SQL by criteria values
      //most of the code need to copy from old getALL method
      }
     */

    /**
     * Returns all unit objects by it's type
     *
     * @param object    $criteria this object contains the specification for the
     * search.
     * @return Zend_Db_Table_Rowset All units objects
     */
    public function getAll(RM_Unit_Search_Criteria $criteria) {

        $fieldsNames = array();
        $fieldsNames[] = 'rm_units.*';

        $languageFieldsNames = array();
        $languageFieldsNames[] = 'rm_unit_language_details.*';

        if ($criteria->offset === null) {
            $criteria->offset = 0;
        }

        $sql = "
            SELECT
                " . implode(',', $fieldsNames);

        if (count($languageFieldsNames) > 0) {
            $sql .= ", " . implode(',', $languageFieldsNames);
        }

        if (!$criteria->language) {
            $criteria->language = RM_Environment::getInstance()->getLocale();
        }

        $sql .= "
            FROM
                rm_units
            INNER JOIN
                rm_unit_language_details ON
                rm_units.id = rm_unit_language_details.unit_id";

        $sql.= "
            AND rm_unit_language_details.iso = '$criteria->language'
                WHERE 1=1 ";

        if ($criteria->type) {
            $sql.= " AND type_id = '" . $criteria->type->id . "' ";
        }

        // this gets the units that are unavailable so these are excluded from the results

        if ($criteria->start_datetime && $criteria->end_datetime) {
            $reservationPeriod = new RM_Reservation_Period(
                            new RM_Date(strtotime($criteria->start_datetime)),
                            new RM_Date(strtotime($criteria->end_datetime))
            );

            if ($criteria->flexible) {
                //My dates are flexible: every unit with at least one day available should we shown
                $availableUnitIDs = $this->getFlexibleAvailable($reservationPeriod);
                if (count($availableUnitIDs) == 0) {
                    $sql.= " AND 1=0 "; //This will returns 0 rows
                    return $this->_getBySQL($sql);
                } else {
                    $sql.= "
                        AND rm_units.id IN (" . implode(',', $availableUnitIDs) . ")
                    ";
                }
            } else {
                $reservedUnits = $this->getReservedUnits($reservationPeriod)->toArray();

                $unitsWithAvailbilityCheckDisabled = RM_Environment::getInstance()->getPriceSystem()->getUnitWithAvailabiltyCheckDisabled();

                if (count($reservedUnits) > 0){
                    $reservedUnits = array_diff($reservedUnits, $unitsWithAvailbilityCheckDisabled);
                }

                if (count($reservedUnits) > 0) {
                    $reservedUnitIDs = array();
                    foreach ($reservedUnits as $unit) {
                        if ($unit['id']) {
                            $reservedUnitIDs[] = $unit['id'];
                        }
                    }
                    $reservedUnitsCSV = implode(',', $reservedUnitIDs);
                    if ($reservedUnitsCSV != "," && $reservedUnitsCSV != "") {
                        $sql.= "
                            AND rm_units.id NOT IN (" . $reservedUnitsCSV . ")
                        ";
                    }
                }

                //we need to check min period length
                $manager = RM_Prices_Manager::getInstance();
                $minPeriodNotUnitIDs = $manager->getByMinPeriod($reservationPeriod);
                if (count($minPeriodNotUnitIDs) > 0) {
                    $minPeriodNotUnitIDsCSV = implode(',', $minPeriodNotUnitIDs);
                    if ($minPeriodNotUnitIDsCSV != "," && $minPeriodNotUnitIDsCSV != "") {
                        $sql.= "
                            AND rm_units.id NOT IN (" . $minPeriodNotUnitIDsCSV . ")
                        ";
                    }
                }
            }
        }

        if ($criteria->image == 1) {
            $unitIDsWithImages = $this->getWithImages();
            if (count($unitIDsWithImages) == 0) {
                $sql.= " AND 1=0 "; //This will returns 0 rows
                return $this->_getBySQL($sql);
            } else {
                $sql.= "
                    AND rm_units.id IN (" . implode(',', $unitIDsWithImages) . ")
                ";
            }
        }

        //3. prices: from - to
        if ((int) $criteria->prices_from != 0 || (int) $criteria->prices_to != 99999999) {
            if ($criteria->prices_from || $criteria->prices_to) {
                if ($criteria->start_datetime && $criteria->end_datetime) {
                    $reservationPeriod = new RM_Reservation_Period(
                        new RM_Date(strtotime($criteria->start_datetime)),
                        new RM_Date(strtotime($criteria->end_datetime))
                    );
                } else {
                    $reservationPeriod = null;
                }

                $priceRangeUnitIDs = RM_Environment::getInstance()->getPriceSystem()->getByPriceRange(
                                $criteria->prices_from,
                                $criteria->prices_to,
                                $reservationPeriod
                );
                if (count($priceRangeUnitIDs) == 0) {
                    $sql.= " AND 1=0 "; //This will returns 0 rows
                    return $this->_getBySQL($sql);
                } else {
                    $sql.= "
                        AND rm_units.id IN (" . implode(',', $priceRangeUnitIDs) . ")
                    ";
                }
            }
        }

        //4. check all plugins and modules to extend advanced search
        $model = new RM_Modules();
        $modules = $model->getAllEnabled();
        foreach ($modules as $module) {
            if ($module instanceof RM_Search_Advanced_Interface) {
                $unitIDs = $module->getAdvancedSearchUnitIDs($criteria);
                if ($unitIDs !== false) {
                    if (count($unitIDs) == 0) {
                        $sql.= " AND 1=0 "; //This will returns 0 rows
                        return $this->_getBySQL($sql);
                    } elseif (count($unitIDs) > 0) {
                        $sql.= "
                            -- added by modules: ".$module->name."
                            AND rm_units.id IN (" . implode(',', $unitIDs) . ")
                        ";
                    }
                }
            }
        }
        $pluginModel = new RM_Plugins();
        $plugins = $pluginModel->getAllEnabled();
        foreach ($plugins as $plugin) {
            if ($plugin instanceof RM_Search_Advanced_Interface) {
                $unitIDs = $plugin->getAdvancedSearchUnitIDs($criteria);
                if ($unitIDs !== false) {
                    if (count($unitIDs) == 0) {
                        $sql.= " AND 1=0 "; //This will returns 0 rows
                        return $this->_getBySQL($sql);
                    } elseif (count($unitIDs) > 0) {
                        $sql.= "
                            -- added by modules: ".$plugin->name."
                            AND rm_units.id IN (" . implode(',', $unitIDs) . ")
                        ";
                    }
                }
            }
        }

        if ($criteria->publishedOnly === true) {
            $sql .=" AND rm_units.published='1'";
        }

        if (count($criteria->filters) > 0) {
            $filterConditions = array();
            foreach ($criteria->filters as $filter) {
                $filterConditions = array_merge($filterConditions, $this->_getConditions($filter));
            }
            $filterSQL = implode(" AND ", $filterConditions);
            $sql.= " AND " . $filterSQL;
        }

        if ($criteria->order !== null) {
            if ($criteria->order == 'random') {
                $sessionID = session_id();
                $random = hexdec($sessionID[0]);
                $sql.= " ORDER BY RAND(" . $random . ") ";
            } elseif ($criteria->order != 'price') {
                $sql.= " ORDER BY $criteria->order ";
            }
        }

        if ($criteria->count !== null) {
            $sql.= " LIMIT $criteria->offset, $criteria->count ";
        }

        return $this->_getBySQL($sql);
    }

    /**
     * Returns unit information by it's primary key and ISO language values
     *
     * @param int $id Unit type
     * @param string $language OPTIONAL Language ISO code.
     * @return RM_Unit_Row Unit object
     */
    public function get($id, $language = null, $excludeFields = array()) {
        if ($language == null) {
            $language = RM_Environment::getInstance()->getLocale();
        }

        $unitRow = $this->find($id)->current();
        
        if ($unitRow == null) {
            return null;
        }

        list($fields, $languageFields) = $this->_getFields($id);

        $fieldsNames = array();
        foreach ($fields as $field) {
            $fieldsNames[] = 'rm_units.' . $field->column_name . ' AS ' . $field->column_name;
        }

        $languageFieldsNames = array();
        foreach ($languageFields as $field) {
            if (!in_array($field->column_name, $excludeFields)){
                $languageFieldsNames[] = 'rm_unit_language_details.' . $field->column_name . ' AS ' . $field->column_name;
            }
        }

        $sql = "
            SELECT
                " . implode(',', $fieldsNames);

        if (count($languageFieldsNames) > 0) {
            $sql .= ", " . implode(',', $languageFieldsNames);
        }

        $sql .= "
            FROM
                rm_units
            INNER JOIN
                rm_unit_language_details ON
                rm_units.id = rm_unit_language_details.unit_id
                AND rm_unit_language_details.iso = '$language'
            WHERE rm_units.id = '$id' ";

        $unit = $this->_getBySQL($sql)->current();

        return $unit;
    }

    function getAllAvailableForReservation($reservation, $reservationModel) {
        $sql = "SELECT
                u.*
            FROM
            {$reservationModel->info('name')} AS r
            LEFT OUTER JOIN
            {$reservationModel->getDependentTableName()} rd ON rd.reservation_id=r.id
            INNER JOIN
            {$this->_name} u ON u.id=rd.unit_id
            WHERE
                ((UNIX_TIMESTAMP(r.start_datetime)<UNIX_TIMESTAMP('$reservation->end_datetime') AND UNIX_TIMESTAMP(r.start_datetime)>=UNIX_TIMESTAMP('$reservation->start_datetime')
                ) OR (
                        UNIX_TIMESTAMP(r.end_datetime)>UNIX_TIMESTAMP('$reservation->start_datetime') AND UNIX_TIMESTAMP(r.end_datetime)<=UNIX_TIMESTAMP('$reservation->end_datetime')
                ) OR (
                        UNIX_TIMESTAMP(r.start_datetime)<=UNIX_TIMESTAMP('$reservation->start_datetime') AND UNIX_TIMESTAMP(r.end_datetime)>=UNIX_TIMESTAMP('$reservation->end_datetime')
                ))
            ";

        $reserved = $this->_getBySQL($sql);
        $reservedIDs = array();
        foreach ($reserved as $unit) {
            $reservedIDs[] = $unit->id;
        }

        if (empty($reservedIDs)) {
            $ret = $this->fetchAll(); // this add's protection if no items are returned (all units are available)
        } else {
            $ret = $this->fetchAll('id NOT IN (' . implode(',', $reservedIDs) . ') ');
        }

        return $ret;
    }

    /**
     * Returns if a unit is available for a given period
     *
     * @param   RM_Unit_Row      $unit          the selected unit
     * @param   RM_Reservation_Period      $reservationPeriod   The selected date/time periods
     * @return   boolean    returns boolean (true=not available, false=available)
     */
    public function isAvailableUnitbyDate(RM_Unit_Row $unit, RM_Reservation_Period $reservationPeriod) {
        $row = $this->_isAvailable($reservationPeriod, $unit);
        $return = (count($row) == 0);
        return $return;
    }

    /**
     * Returns all Reserved Units for a given period.
     *
     * @param   RM_Reservation_Period      $reservationPeriod  The selected date/time periods
     * @return   Zend_Db_Table_Rowset
     */
    public function getReservedUnits(RM_Reservation_Period $reservationPeriod) {
        return $this->_isAvailable($reservationPeriod);
    }

    /**
     * Check if unit is flexible available in the period: at least one available day in given period.
     *
     * @param RM_Unit_Row $unit
     * @param RM_Reservation_Period $reservationPeriod
     * @return bool
     */
    public function isFlexibleAvailable(RM_Unit_Row $unit, RM_Reservation_Period $reservationPeriod) {
        $days = $reservationPeriod->getDays();
        foreach ($days as $day) {
            $end = clone $day->addDay(1);
            $currentReservationPeriod = new RM_Reservation_Period($day, $end);
            if ($this->isAvailableUnitbyDate($unit, $currentReservationPeriod)) {
                return true;
            }
        }
        return false;
    }

    public function getFlexibleAvailable(RM_Reservation_Period $reservationPeriod) {
        $units = $this->fetchAll();
        $unitIDs = array();
        foreach ($units as $unit) {
            if ($this->isFlexibleAvailable($unit, $reservationPeriod)) {
                $unitIDs[] = $unit->id;
            }
        }
        return $unitIDs;
    }

    /**
     * This checks if a unit/units is available for the selected dates/time
     *
     * @param   RM_Reservation_Period      $reservationPeriod  The selected date/time periods
     * @param   RM_Unit_Row      $unitObject  the unit object
     * @return 	Zend_Db_Table_Rowset   containing only reserved units ids
     */
    private function _isAvailable(RM_Reservation_Period $reservationPeriod, RM_Unit_Row $unit = null) {

        $sql = "SELECT
                rd.unit_id as id
            FROM
                rm_reservations AS r
            LEFT OUTER JOIN
                rm_reservation_details rd ON rd.reservation_id=r.id
            WHERE
                ((
                    UNIX_TIMESTAMP(rd.start_datetime)<UNIX_TIMESTAMP('" . $reservationPeriod->getEnd()->toMySQL() . "')
                        AND
                    UNIX_TIMESTAMP(rd.start_datetime)>=UNIX_TIMESTAMP('" . $reservationPeriod->getStart()->toMySQL() . "')
                ) OR (
                    UNIX_TIMESTAMP(rd.end_datetime)>UNIX_TIMESTAMP('" . $reservationPeriod->getStart()->toMySQL() . "')
                        AND
                    UNIX_TIMESTAMP(rd.end_datetime)<=UNIX_TIMESTAMP('" . $reservationPeriod->getEnd()->toMySQL() . "')
                ) OR (
                    UNIX_TIMESTAMP(rd.start_datetime)<=UNIX_TIMESTAMP('" . $reservationPeriod->getStart()->toMySQL() . "')
                        AND
                    UNIX_TIMESTAMP(rd.end_datetime)>=UNIX_TIMESTAMP('" . $reservationPeriod->getEnd()->toMySQL() . "')
                ))
                AND
                    r.confirmed = '1'
                ";

        if ($unit !== null) {
            $unitID = $unit->getId();
            $sql .= "AND
                rd.unit_id = '$unitID'
            ";
        }

        return $this->_getBySQL($sql);
    }

}