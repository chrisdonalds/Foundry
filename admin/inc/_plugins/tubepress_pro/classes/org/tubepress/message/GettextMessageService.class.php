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

/* include the gettext replacement if we're outside of WordPress */
if (!function_exists('gettext') && strpos(realpath(__FILE__), 'wp-content/plugins') === FALSE) {
    require dirname(__FILE__) . '/../../../../env/pro/lib/php-gettext-1.0.9/gettext.inc';    
}

/**
 * Gettext functionality for TubePress
 */
class org_tubepress_message_GettextMessageService extends org_tubepress_message_AbstractMessageService
{
    public function __construct()
    {
        if (strpos(realpath(__FILE__), 'wp-content/plugins') !== FALSE) {
            /* WordPress will handle language switching */
            return;
        }
        
        $lang = getenv('LANG');
        if ($lang == '') {
            $lang = 'en';
        }
        setlocale(LC_ALL, $lang);
        bindtextdomain('tubepress', dirname(__FILE__) . '/../../../../i18n');
        textdomain('tubepress');
        bind_textdomain_codeset('tubepress', 'UTF-8');
    }
    
    /**
     * Retrieves a message for TubePress
     *
     * @param string $msgId The message ID
     *
     * @return string The corresponding message, or "" if not found
     */
    public function _($msgId)
    {
        $message = $this->_keyToMessage($msgId);
        return $message == '' ? '' : gettext($message);
    }
}
