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
tubepress_load_classes(array('org_tubepress_ioc_DefaultIocService',
    'org_tubepress_ioc_IocService',
    'org_tubepress_options_reference_ProOptionsReference',
    'org_tubepress_player_impl_ModalPlayer',
    'org_tubepress_ioc_Setters',
    'org_tubepress_video_factory_ProYouTubeVideoFactory',
    'org_tubepress_video_factory_ProVimeoVideoFactory'));

/**
 * Dependency injector for TubePress Pro in a WordPress environment
 */
class org_tubepress_ioc_ProInWordPressIocService extends org_tubepress_ioc_DefaultIocService
{
    function __construct()
    {
        parent::__construct();

        /* override the options reference */
        $this->def(org_tubepress_ioc_IocService::OPTIONS_REFERENCE,
            $this->impl('org_tubepress_options_reference_ProOptionsReference'));

        $this->def(org_tubepress_ioc_IocService::YOUTUBE_VIDEO_FACTORY,
            $this->impl('org_tubepress_video_factory_ProYouTubeVideoFactory',
                array(
                    org_tubepress_ioc_Setters::LOG             => $this->ref(org_tubepress_ioc_IocService::LOG),
                    org_tubepress_ioc_Setters::OPTIONS_MANAGER => $this->ref(org_tubepress_ioc_IocService::OPTIONS_MANAGER)
                )
            )
        );
        $this->def(org_tubepress_ioc_IocService::VIMEO_VIDEO_FACTORY,
            $this->impl('org_tubepress_video_factory_ProVimeoVideoFactory',
                array(
                    org_tubepress_ioc_Setters::LOG             => $this->ref(org_tubepress_ioc_IocService::LOG),
                    org_tubepress_ioc_Setters::OPTIONS_MANAGER => $this->ref(org_tubepress_ioc_IocService::OPTIONS_MANAGER)
                )
            )
        );
            
        /* define pro-only players */
        $this->def(org_tubepress_player_Player::TINYBOX . "-player",
            $this->impl('org_tubepress_player_impl_ModalPlayer',
                array(
                    org_tubepress_ioc_Setters::OPTIONS_MANAGER => $this->ref(org_tubepress_ioc_IocService::OPTIONS_MANAGER),
                    org_tubepress_ioc_Setters::TEMPLATE        => $this->ref(org_tubepress_ioc_IocService::MODAL_PLAYER_TEMPLATE)
                )
            )
        );
        $this->def(org_tubepress_player_Player::FANCYBOX . "-player",
            $this->impl('org_tubepress_player_impl_ModalPlayer',
                array(
                    org_tubepress_ioc_Setters::OPTIONS_MANAGER => $this->ref(org_tubepress_ioc_IocService::OPTIONS_MANAGER),
                    org_tubepress_ioc_Setters::TEMPLATE        => $this->ref(org_tubepress_ioc_IocService::MODAL_PLAYER_TEMPLATE)
                )
            )
        );
    }
}
