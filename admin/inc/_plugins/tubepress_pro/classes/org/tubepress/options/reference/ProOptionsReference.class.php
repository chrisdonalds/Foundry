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
tubepress_load_classes(array('org_tubepress_options_reference_SimpleOptionsReference'));

/**
 * The master reference for TubePress options - their names, deprecated
 * names, default values, types, etc.
 *
 */
class org_tubepress_options_reference_ProOptionsReference extends org_tubepress_options_reference_SimpleOptionsReference
{
    /**
     * Given the name of an "enum" type option, return
     *  the valid values that this option may take on.
     *
     * @param $optionName The name of the option to look up
     *
     * @return array The valid option values for the given option
     */
    function getValidEnumValues($optionType)
    {
        if ($optionType == org_tubepress_options_Type::PLAYER) {
            return array('normal', 'popup','shadowbox','jqmodal', 'youtube', 'static', 'solo', 'vimeo', 'tinybox', 'fancybox');
        }
        return parent::getValidEnumValues($optionType);
    }
}
