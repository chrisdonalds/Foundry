<?php
/**
 * Copyright 2006 - 2010 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org)
 * 
 * TubePress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * TubePress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with TubePress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

function_exists('tubepress_load_classes')
    || require(dirname(__FILE__) . '/../../../../tubepress_classloader.php');
tubepress_load_classes(array('org_tubepress_options_storage_AbstractStorageManager',
    'org_tubepress_options_reference_SimpleOptionsReference'));

/**
 * Implementation of org_tubepress_options_storage_StorageManager that just keeps everything
 * in memory
 *
 */
class org_tubepress_options_storage_MemoryStorageManager extends org_tubepress_options_storage_AbstractStorageManager {
	
	private $_options = array();
	
	public function __construct()
	{
	    $this->setOptionsReference(new org_tubepress_options_reference_SimpleOptionsReference());
		$this->init();
	}
	
	protected function setOption($optionName, $optionValue)
	{
		$this->_options[$optionName] = $optionValue;
	}
	
	public function exists($optionName)
	{
		return array_key_exists($optionName, $this->_options);
	}
	
	protected function create($optionName, $optionValue)
	{
		$this->_options[$optionName] = $optionValue;
	}
	
	protected function delete($optionName)
	{
		unset($this->_options[$optionName]);
	}
	
	public function get($optionName) {
		return isset($this->_options[$optionName]) ?
		  $this->_options[$optionName] : '';
	}
}
?>