<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/reactforum/lib.php');

    $settings->add(new admin_setting_configselect('reactforum_displaymode', get_string('displaymode', 'reactforum'),
                       get_string('configdisplaymode', 'reactforum'), REACTFORUM_MODE_NESTED, reactforum_get_layout_modes()));

    $settings->add(new admin_setting_configcheckbox('reactforum_replytouser', get_string('replytouser', 'reactforum'),
                       get_string('configreplytouser', 'reactforum'), 1));

    // Less non-HTML characters than this is short
    $settings->add(new admin_setting_configtext('reactforum_shortpost', get_string('shortpost', 'reactforum'),
                       get_string('configshortpost', 'reactforum'), 300, PARAM_INT));

    // More non-HTML characters than this is long
    $settings->add(new admin_setting_configtext('reactforum_longpost', get_string('longpost', 'reactforum'),
                       get_string('configlongpost', 'reactforum'), 600, PARAM_INT));

    // Number of discussions on a page
    $settings->add(new admin_setting_configtext('reactforum_manydiscussions', get_string('manydiscussions', 'reactforum'),
                       get_string('configmanydiscussions', 'reactforum'), 100, PARAM_INT));

    if (isset($CFG->maxbytes)) {
        $maxbytes = 0;
        if (isset($CFG->reactforum_maxbytes)) {
            $maxbytes = $CFG->reactforum_maxbytes;
        }
        $settings->add(new admin_setting_configselect('reactforum_maxbytes', get_string('maxattachmentsize', 'reactforum'),
                           get_string('configmaxbytes', 'reactforum'), 512000, get_max_upload_sizes($CFG->maxbytes, 0, 0, $maxbytes)));
    }

    // Default number of attachments allowed per post in all reactforums
    $settings->add(new admin_setting_configtext('reactforum_maxattachments', get_string('maxattachments', 'reactforum'),
                       get_string('configmaxattachments', 'reactforum'), 9, PARAM_INT));

    // Default Read Tracking setting.
    $options = array();
    $options[REACTFORUM_TRACKING_OPTIONAL] = get_string('trackingoptional', 'reactforum');
    $options[REACTFORUM_TRACKING_OFF] = get_string('trackingoff', 'reactforum');
    $options[REACTFORUM_TRACKING_FORCED] = get_string('trackingon', 'reactforum');
    $settings->add(new admin_setting_configselect('reactforum_trackingtype', get_string('trackingtype', 'reactforum'),
                       get_string('configtrackingtype', 'reactforum'), REACTFORUM_TRACKING_OPTIONAL, $options));

    // Default whether user needs to mark a post as read
    $settings->add(new admin_setting_configcheckbox('reactforum_trackreadposts', get_string('trackreactforum', 'reactforum'),
                       get_string('configtrackreadposts', 'reactforum'), 1));

    // Default whether user needs to mark a post as read.
    $settings->add(new admin_setting_configcheckbox('reactforum_allowforcedreadtracking', get_string('forcedreadtracking', 'reactforum'),
                       get_string('forcedreadtracking_desc', 'reactforum'), 0));

    // Default number of days that a post is considered old
    $settings->add(new admin_setting_configtext('reactforum_oldpostdays', get_string('oldpostdays', 'reactforum'),
                       get_string('configoldpostdays', 'reactforum'), 14, PARAM_INT));

    // Default whether user needs to mark a post as read
    $settings->add(new admin_setting_configcheckbox('reactforum_usermarksread', get_string('usermarksread', 'reactforum'),
                       get_string('configusermarksread', 'reactforum'), 0));

    $options = array();
    for ($i = 0; $i < 24; $i++) {
        $options[$i] = sprintf("%02d",$i);
    }
    // Default time (hour) to execute 'clean_read_records' cron
    $settings->add(new admin_setting_configselect('reactforum_cleanreadtime', get_string('cleanreadtime', 'reactforum'),
                       get_string('configcleanreadtime', 'reactforum'), 2, $options));

    // Default time (hour) to send digest email
    $settings->add(new admin_setting_configselect('digestmailtime', get_string('digestmailtime', 'reactforum'),
                       get_string('configdigestmailtime', 'reactforum'), 17, $options));

    if (empty($CFG->enablerssfeeds)) {
        $options = array(0 => get_string('rssglobaldisabled', 'admin'));
        $str = get_string('configenablerssfeeds', 'reactforum').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');

    } else {
        $options = array(0=>get_string('no'), 1=>get_string('yes'));
        $str = get_string('configenablerssfeeds', 'reactforum');
    }
    $settings->add(new admin_setting_configselect('reactforum_enablerssfeeds', get_string('enablerssfeeds', 'admin'),
                       $str, 0, $options));

    if (!empty($CFG->enablerssfeeds)) {
        $options = array(
            0 => get_string('none'),
            1 => get_string('discussions', 'reactforum'),
            2 => get_string('posts', 'reactforum')
        );
        $settings->add(new admin_setting_configselect('reactforum_rsstype', get_string('rsstypedefault', 'reactforum'),
                get_string('configrsstypedefault', 'reactforum'), 0, $options));

        $options = array(
            0  => '0',
            1  => '1',
            2  => '2',
            3  => '3',
            4  => '4',
            5  => '5',
            10 => '10',
            15 => '15',
            20 => '20',
            25 => '25',
            30 => '30',
            40 => '40',
            50 => '50'
        );
        $settings->add(new admin_setting_configselect('reactforum_rssarticles', get_string('rssarticles', 'reactforum'),
                get_string('configrssarticlesdefault', 'reactforum'), 0, $options));
    }

    $settings->add(new admin_setting_configcheckbox('reactforum_enabletimedposts', get_string('timedposts', 'reactforum'),
                       get_string('configenabletimedposts', 'reactforum'), 1));
}

