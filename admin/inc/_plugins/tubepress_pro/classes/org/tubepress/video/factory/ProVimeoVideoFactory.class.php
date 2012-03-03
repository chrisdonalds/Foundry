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
tubepress_load_classes(array('org_tubepress_video_factory_VimeoVideoFactory',
    'org_tubepress_options_category_Display'));

/**
 * Pro video factory for Vimeo
 */
class org_tubepress_video_factory_ProVimeoVideoFactory extends org_tubepress_video_factory_VimeoVideoFactory
{
    protected function _getThumbnailUrl($entry)
    {
        if (!$this->getOptionsManager()->get(org_tubepress_options_category_Display::HQ_THUMBS)) {
            return parent::_getThumbnailUrl($entry);
        }
        $thumbnailArray = $entry->thumbnails->thumbnail;
        $size = count($thumbnailArray);
        do {
            $size--;
            $thumb = $thumbnailArray[$size]->_content;
            $width = $thumbnailArray[$size]->width;
        } while (strpos($thumb, 'defaults') !== FALSE || intval($width) > 640);
        
        return $thumbnailArray[$size]->_content;
    }
}
