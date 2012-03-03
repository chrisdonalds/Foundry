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
    || require(dirname(__FILE__) . '/../../../tubepress_classloader.php');
tubepress_load_classes(array('org_tubepress_ioc_ProInWordPressIocService',
    'org_tubepress_ioc_IocService',
    'org_tubepress_message_GettextMessageService',
    'org_tubepress_options_storage_MemoryStorageManager',
    'org_tubepress_ioc_Setters'));

/**
 * Dependency injector for TubePress Pro in a standalone environment
 */
class org_tubepress_ioc_ProIocService extends org_tubepress_ioc_ProInWordPressIocService
{
    function __construct()
    {
        parent::__construct();

        /* override the message service */
        $this->def(org_tubepress_ioc_IocService::MESSAGE_SERVICE,
            $this->impl('org_tubepress_message_GettextMessageService'));
        
        /* override the storage manager */
        $this->def(org_tubepress_ioc_IocService::STORAGE_MANAGER,
            $this->impl('org_tubepress_options_storage_MemoryStorageManager', 
                array(
                    org_tubepress_ioc_Setters::OPTIONS_REFERENCE  => $this->ref(org_tubepress_ioc_IocService::OPTIONS_REFERENCE),
                    org_tubepress_ioc_Setters::INPUT_VALIDATION => $this->ref(org_tubepress_ioc_IocService::VALIDATION_SERVICE)
                )
            )
        );
    }
}
