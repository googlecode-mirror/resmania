<?php
/**
 * Static class contains all information about naming
 * generation for PHP and Javascript end.
 *
 * In javascript all methods for generating names should be invokes like this
 * RM_Form_Naming_Manager.<methodname>(<method parameters>);
 */
class RM_Form_Naming_Manager {
    public static function generatePanelID($panelXType){
        $xtypeChunks = explode('_', $panelXType);
        unset($xtypeChunks[0]); //'rm' chunk
        unset($xtypeChunks[1]); //'formdesigner' chunk
        $panelID = implode('_', $xtypeChunks);
        return $panelID;
    }

    public static function generatePanelXType($panelID){
        return "rm_formdesigner_".$panelID;
    }

    public static function generatePanelClassName($panelID){
        return "Ext.rm.Formdesigner.".$panelID;
    }

    public static function generateButtonID($panelXType){
        return $panelXType + "_button";
    }

    public static function generateDivID($panelID){
        return "rm_formdesigner_".$panelID."_data";
    }

    /**
     * Returns all javascript code for naming form panels: xtype/divID/buttonID etc.
     * This code is also in the file module/formdesigner/js/edit.js
     * If this method will be changed you need to add changes in that js file.
     *
     * @return string JavaScript code
     */
    public static function getAllJavascriptCode(){
        return '
            RM_Form_Naming_Manager = {};

            RM_Form_Naming_Manager.generatePanelID = function(panelXType){
                var chunks = panelXType.split("_");
                chunks.shift();
                chunks.shift();
                return chunks.join("_");
            }

            RM_Form_Naming_Manager.generatePanelXType = function(panelID){
                return "rm_formdesigner_"+ panelID;
            }

            RM_Form_Naming_Manager.generatePanelClassName = function(panelID){
                return "Ext.rm.Formdesigner."+ panelID;
            }

            RM_Form_Naming_Manager.generateButtonID = function(panelXType){
                return panelXType + "_button";
            }

            RM_Form_Naming_Manager.generateDivID = function(panelID){
                return "rm_formdesigner_" + panelID + "_data";
            }
        ';
    }
}