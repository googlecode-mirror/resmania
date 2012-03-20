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
 * @access      public
 * @author      Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */
class RM_Config_Form extends Zend_Dojo_Form
{
    /**
     * Options to use with select elements
     */
    protected $_selectOptions = array(
        'red'    => 'Rouge',
        'blue'   => 'Bleu',
        'white'  => 'Blanc',
        'orange' => 'Orange',
        'black'  => 'Noir',
        'green'  => 'Vert',
    );

    /**
     * Form initialization
     *
     * @return void
     */
    public function init()
    {    	     	    	   	    	 
    	$translate = RM_Environment::getInstance()->getTranslation(RM_Environment::TRANSLATE_MAIN);
		$this->setTranslator($translate);
    	        
		$this->setMethod('post');
				
        $this->setDecorators(array(
            'FormElements',
            array('TabContainer', array(
                'id' => 'tabContainer',
                'style' => 'width: 600px; height: 500px;',
                'dijitParams' => array(
                    'tabPosition' => 'top'
                ),
            )),
            'DijitForm',
        ));       
        
        
		$this->addElement(
		    'HorizontalSlider',
		    'horizontal',
		    array(
		        'label'                     => 'HorizontalSlider',
		        'value'                     => 5,
		        'minimum'                   => -10,
		        'maximum'                   => 10,
		        'discreteValues'            => 5,
		        'intermediateChanges'       => true,
		        'showButtons'               => true,
		        'topDecorationDijit'        => 'HorizontalRuleLabels',
		        'topDecorationContainer'    => 'topContainer'        
		    )
		);         

		$this->addElement('editor', 'content', array(
    		'plugins'            => array('undo', '|', 'bold', 'italic'),
    		'editActionInterval' => 2,
    		'focusOnLoad'        => true,
    		'height'             => '250px',
    		'inheritWidth'       => true,
    		'styleSheets'        => array('/js/custom/editor.css'),
		));
    }
}