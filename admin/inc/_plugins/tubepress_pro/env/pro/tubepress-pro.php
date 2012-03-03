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

/* include the class-loader if we haven't already */
function_exists('tubepress_load_classes')
    || require(dirname(__FILE__) . '/../../classes/tubepress_classloader.php');
tubepress_load_classes(array('org_tubepress_ioc_ProInWordPressIocService',
    'org_tubepress_ioc_ProIocService',
    'org_tubepress_ioc_IocService',
    'org_tubepress_options_category_Advanced',
    'org_tubepress_gallery_TubePressGalleryImpl'));

/* Check to see if we're running TubePress Pro inside a WordPress environment */
if (strpos(realpath(__FILE__), 'wp-content/plugins') !== FALSE) {
    require dirname(__FILE__) . '/../../../../../wp-blog-header.php';
}

/**
 * Returns the HTML for a TubePress gallery
 *
 * @param unknown_type $raw_shortcode
 * @return unknown
 */
function tubepressGallery($raw_shortcode)
{
    /* pad the shortcode if it doesn't start and end with the right stuff */
    $shortcode = _tubepressCleanShortcode($raw_shortcode);

    /* whip up the IOC service (depending on environment) */
    if (strpos(realpath(__FILE__), 'wp-content/plugins') !== FALSE) {
        $ioc = new org_tubepress_ioc_ProInWordPressIocService();
    } else {
        $ioc = new org_tubepress_ioc_ProIocService();
    }
    
    /* grab some of our newly built objects */
    $shortcodeService   = $ioc->get(org_tubepress_ioc_IocService::SHORTCODE_SERVICE);
    $tpom               = $ioc->get(org_tubepress_ioc_IocService::OPTIONS_MANAGER);

    /* Turn on logging if we need to */
    $log = $ioc->get(org_tubepress_ioc_IocService::LOG);
    $log->setEnabled($tpom->get(org_tubepress_options_category_Advanced::DEBUG_ON),$_GET);
    
    /* parse the shortcode and return the output */
    return $shortcodeService->getHtml($shortcode, $ioc);
}

/**
 * Returns the HTML for the TubePress head contents (CSS, JS, etc)
 *
 * @param unknown_type $player
 * @return unknown
 */
function tubepressHeadElements($includeJquery, $playerName = "normal")
{
    return org_tubepress_gallery_TubePressGalleryImpl::printHeadElements($includeJquery, $_GET);
}

/**
 * Pads the user-supplied shortcode, if necessary
 *
 * @param unknown_type $shortcode
 * @return unknown
 */
function _tubepressCleanShortcode($shortcode)
{
    /* make sure it starts with [tubepress */
    if (substr($shortcode, 0, 11) != '[tubepress ') {
        $shortcode = "[tubepress $shortcode";
    }

    /* make sure it ends with a bracket */
    if (substr($shortcode, strlen($shortcode) - 1) != ']') {
        $shortcode = "$shortcode]";
    }
    return $shortcode;
}

?>
