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
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once('../../config.php');
    require_once('lib.php');
    require_once($CFG->libdir.'/completionlib.php');

    $id          = optional_param('id', 0, PARAM_INT);       // Course Module ID
    $f           = optional_param('f', 0, PARAM_INT);        // ReactForum ID
    $mode        = optional_param('mode', 0, PARAM_INT);     // Display mode (for single reactforum)
    $showall     = optional_param('showall', '', PARAM_INT); // show all discussions on one page
    $changegroup = optional_param('group', -1, PARAM_INT);   // choose the current group
    $page        = optional_param('page', 0, PARAM_INT);     // which page to show
    $search      = optional_param('search', '', PARAM_CLEAN);// search string

    $params = array();
    if ($id) {
        $params['id'] = $id;
    } else {
        $params['f'] = $f;
    }
    if ($page) {
        $params['page'] = $page;
    }
    if ($search) {
        $params['search'] = $search;
    }
    $PAGE->set_url('/mod/reactforum/view.php', $params);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('reactforum', $id)) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
            print_error('coursemisconf');
        }
        if (! $reactforum = $DB->get_record("reactforum", array("id" => $cm->instance))) {
            print_error('invalidreactforumid', 'reactforum');
        }
        if ($reactforum->type == 'single') {
            $PAGE->set_pagetype('mod-reactforum-discuss');
        }
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
        $strreactforums = get_string("modulenameplural", "reactforum");
        $strreactforum = get_string("modulename", "reactforum");
    } else if ($f) {

        if (! $reactforum = $DB->get_record("reactforum", array("id" => $f))) {
            print_error('invalidreactforumid', 'reactforum');
        }
        if (! $course = $DB->get_record("course", array("id" => $reactforum->course))) {
            print_error('coursemisconf');
        }

        if (!$cm = get_coursemodule_from_instance("reactforum", $reactforum->id, $course->id)) {
            print_error('missingparameter');
        }
        // move require_course_login here to use forced language for course
        // fix for MDL-6926
        require_course_login($course, true, $cm);
        $strreactforums = get_string("modulenameplural", "reactforum");
        $strreactforum = get_string("modulename", "reactforum");
    } else {
        print_error('missingparameter');
    }

    if (!$PAGE->button) {
        $PAGE->set_button(reactforum_search_form($course, $search));
    }

    $context = context_module::instance($cm->id);
    $PAGE->set_context($context);

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->reactforum_enablerssfeeds) && $reactforum->rsstype && $reactforum->rssarticles) {
        require_once("$CFG->libdir/rsslib.php");

        $rsstitle = format_string($course->shortname, true, array('context' => context_course::instance($course->id))) . ': ' . format_string($reactforum->name);
        rss_add_http_header($context, 'mod_reactforum', $reactforum, $rsstitle);
    }

/// Print header.

    $PAGE->set_title($reactforum->name);
    $PAGE->add_body_class('reactforumtype-'.$reactforum->type);
    $PAGE->set_heading($course->fullname);

    // Some capability checks.
    $courselink = new moodle_url('/course/view.php', ['id' => $cm->course]);

    if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"), $courselink);
    }

    if (!has_capability('mod/reactforum:viewdiscussion', $context)) {
        notice(get_string('noviewdiscussionspermission', 'reactforum'), $courselink);
    }

    // Mark viewed and trigger the course_module_viewed event.
    reactforum_view($reactforum, $course, $cm, $context);

    echo $OUTPUT->header();

    echo $OUTPUT->heading(format_string($reactforum->name), 2);
    if (!empty($reactforum->intro) && $reactforum->type != 'single' && $reactforum->type != 'teacher') {
        echo $OUTPUT->box(format_module_intro('reactforum', $reactforum, $cm->id), 'generalbox', 'intro');
    }

/// find out current groups mode
    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/reactforum/view.php?id=' . $cm->id);

    $SESSION->fromdiscussion = qualified_me();   // Return here if we post or set subscription etc


/// Print settings and things across the top

    // If it's a simple single discussion reactforum, we need to print the display
    // mode control.
    if ($reactforum->type == 'single') {
        $discussion = NULL;
        $discussions = $DB->get_records('reactforum_discussions', array('reactforum'=>$reactforum->id), 'timemodified ASC');
        if (!empty($discussions)) {
            $discussion = array_pop($discussions);
        }
        if ($discussion) {
            if ($mode) {
                set_user_preference("reactforum_displaymode", $mode);
            }
            $displaymode = get_user_preferences("reactforum_displaymode", $CFG->reactforum_displaymode);
            reactforum_print_mode_form($reactforum->id, $displaymode, $reactforum->type);
        }
    }

    if (!empty($reactforum->blockafter) && !empty($reactforum->blockperiod)) {
        $a = new stdClass();
        $a->blockafter = $reactforum->blockafter;
        $a->blockperiod = get_string('secondstotime'.$reactforum->blockperiod);
        echo $OUTPUT->notification(get_string('thisreactforumisthrottled', 'reactforum', $a));
    }

    if ($reactforum->type == 'qanda' && !has_capability('moodle/course:manageactivities', $context)) {
        echo $OUTPUT->notification(get_string('qandanotify','reactforum'));
    }

    switch ($reactforum->type) {
        case 'single':
            if (!empty($discussions) && count($discussions) > 1) {
                echo $OUTPUT->notification(get_string('warnformorepost', 'reactforum'));
            }
            if (! $post = reactforum_get_post_full($discussion->firstpost)) {
                print_error('cannotfindfirstpost', 'reactforum');
            }
            if ($mode) {
                set_user_preference("reactforum_displaymode", $mode);
            }

            $canreply    = reactforum_user_can_post($reactforum, $discussion, $USER, $cm, $course, $context);
            $canrate     = has_capability('mod/reactforum:rate', $context);
            $displaymode = get_user_preferences("reactforum_displaymode", $CFG->reactforum_displaymode);

            echo '&nbsp;'; // this should fix the floating in FF
            reactforum_print_discussion($course, $cm, $reactforum, $discussion, $post, $displaymode, $canreply, $canrate);
            break;

        case 'eachuser':
            echo '<p class="mdl-align">';
            if (reactforum_user_can_post_discussion($reactforum, null, -1, $cm)) {
                print_string("allowsdiscussions", "reactforum");
            } else {
                echo '&nbsp;';
            }
            echo '</p>';
            if (!empty($showall)) {
                reactforum_print_latest_discussions($course, $reactforum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                reactforum_print_latest_discussions($course, $reactforum, -1, 'header', '', -1, -1, $page, $CFG->reactforum_manydiscussions, $cm);
            }
            break;

        case 'teacher':
            if (!empty($showall)) {
                reactforum_print_latest_discussions($course, $reactforum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                reactforum_print_latest_discussions($course, $reactforum, -1, 'header', '', -1, -1, $page, $CFG->reactforum_manydiscussions, $cm);
            }
            break;

        case 'blog':
            echo '<br />';
            if (!empty($showall)) {
                reactforum_print_latest_discussions($course, $reactforum, 0, 'plain', 'd.pinned DESC, p.created DESC', -1, -1, -1, 0, $cm);
            } else {
                reactforum_print_latest_discussions($course, $reactforum, -1, 'plain', 'd.pinned DESC, p.created DESC', -1, -1, $page,
                    $CFG->reactforum_manydiscussions, $cm);
            }
            break;

        default:
            echo '<br />';
            if (!empty($showall)) {
                reactforum_print_latest_discussions($course, $reactforum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                reactforum_print_latest_discussions($course, $reactforum, -1, 'header', '', -1, -1, $page, $CFG->reactforum_manydiscussions, $cm);
            }


            break;
    }

    // Add the subscription toggle JS.
    $PAGE->requires->yui_module('moodle-mod_reactforum-subscriptiontoggle', 'Y.M.mod_reactforum.subscriptiontoggle.init');

    echo $OUTPUT->footer($course);
