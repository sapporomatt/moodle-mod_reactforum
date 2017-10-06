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
 * ReactForum external functions and service definitions.
 *
 * @package    mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    'mod_reactforum_get_reactforums_by_courses' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'get_reactforums_by_courses',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Returns a list of reactforum instances in a provided set of courses, if
            no courses are provided then all the reactforum instances the user has access to will be
            returned.',
        'type' => 'read',
        'capabilities' => 'mod/reactforum:viewdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_reactforum_get_reactforum_discussion_posts' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'get_reactforum_discussion_posts',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Returns a list of reactforum posts for a discussion.',
        'type' => 'read',
        'capabilities' => 'mod/reactforum:viewdiscussion, mod/reactforum:viewqandawithoutposting',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_reactforum_get_reactforum_discussions_paginated' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'get_reactforum_discussions_paginated',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Returns a list of reactforum discussions optionally sorted and paginated.',
        'type' => 'read',
        'capabilities' => 'mod/reactforum:viewdiscussion, mod/reactforum:viewqandawithoutposting',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_reactforum_view_reactforum' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'view_reactforum',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Trigger the course module viewed event and update the module completion status.',
        'type' => 'write',
        'capabilities' => 'mod/reactforum:viewdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_reactforum_view_reactforum_discussion' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'view_reactforum_discussion',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Trigger the reactforum discussion viewed event.',
        'type' => 'write',
        'capabilities' => 'mod/reactforum:viewdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_reactforum_add_discussion_post' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'add_discussion_post',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Create new posts into an existing discussion.',
        'type' => 'write',
        'capabilities' => 'mod/reactforum:replypost',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_reactforum_add_discussion' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'add_discussion',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Add a new discussion into an existing reactforum.',
        'type' => 'write',
        'capabilities' => 'mod/reactforum:startdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_reactforum_can_add_discussion' => array(
        'classname' => 'mod_reactforum_external',
        'methodname' => 'can_add_discussion',
        'classpath' => 'mod/reactforum/externallib.php',
        'description' => 'Check if the current user can add discussions in the given reactforum (and optionally for the given group).',
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);
