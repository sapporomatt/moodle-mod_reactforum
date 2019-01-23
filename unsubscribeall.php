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
 * @copyright  2008 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$confirm = optional_param('confirm', false, PARAM_BOOL);

$PAGE->set_url('/mod/reactforum/unsubscribeall.php');

// Do not autologin guest. Only proper users can have reactforum subscriptions.
require_login(null, false);
$PAGE->set_context(context_user::instance($USER->id));

$return = $CFG->wwwroot.'/';

if (isguestuser()) {
    redirect($return);
}

$strunsubscribeall = get_string('unsubscribeall', 'reactforum');
$PAGE->navbar->add(get_string('modulename', 'reactforum'));
$PAGE->navbar->add($strunsubscribeall);
$PAGE->set_title($strunsubscribeall);
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strunsubscribeall);

if (data_submitted() and $confirm and confirm_sesskey()) {
    $reactforums = \mod_reactforum\subscriptions::get_unsubscribable_reactforums();

    foreach($reactforums as $reactforum) {
        \mod_reactforum\subscriptions::unsubscribe_user($USER->id, $reactforum, context_module::instance($reactforum->cm), true);
    }
    $DB->delete_records('reactforum_discussion_subs', array('userid' => $USER->id));
    $DB->set_field('user', 'autosubscribe', 0, array('id'=>$USER->id));

    echo $OUTPUT->box(get_string('unsubscribealldone', 'reactforum'));
    echo $OUTPUT->continue_button($return);
    echo $OUTPUT->footer();
    die;

} else {
    $count = new stdClass();
    $count->reactforums = count(\mod_reactforum\subscriptions::get_unsubscribable_reactforums());
    $count->discussions = $DB->count_records('reactforum_discussion_subs', array('userid' => $USER->id));

    if ($count->reactforums || $count->discussions) {
        if ($count->reactforums && $count->discussions) {
            $msg = get_string('unsubscribeallconfirm', 'reactforum', $count);
        } else if ($count->reactforums) {
            $msg = get_string('unsubscribeallconfirmreactforums', 'reactforum', $count);
        } else if ($count->discussions) {
            $msg = get_string('unsubscribeallconfirmdiscussions', 'reactforum', $count);
        }
        echo $OUTPUT->confirm($msg, new moodle_url('unsubscribeall.php', array('confirm'=>1)), $return);
        echo $OUTPUT->footer();
        die;

    } else {
        echo $OUTPUT->box(get_string('unsubscribeallempty', 'reactforum'));
        echo $OUTPUT->continue_button($return);
        echo $OUTPUT->footer();
        die;
    }
}
