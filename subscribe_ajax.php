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
 * Subscribe to or unsubscribe from a reactforum discussion.
 *
 * @package    mod_reactforum
 * @copyright  2014 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require(__DIR__.'/../../config.php');
require_once($CFG->dirroot . '/mod/reactforum/lib.php');

$reactforumid        = required_param('reactforumid', PARAM_INT);             // The reactforum to subscribe or unsubscribe.
$discussionid   = optional_param('discussionid', null, PARAM_INT);  // The discussionid to subscribe.
$includetext    = optional_param('includetext', false, PARAM_BOOL);

$reactforum          = $DB->get_record('reactforum', array('id' => $reactforumid), '*', MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $reactforum->course), '*', MUST_EXIST);
if (!$discussion = $DB->get_record('reactforum_discussions', array('id' => $discussionid, 'reactforum' => $reactforumid))) {
    print_error('invaliddiscussionid', 'reactforum');
}
$cm             = get_coursemodule_from_instance('reactforum', $reactforum->id, $course->id, false, MUST_EXIST);
$context        = context_module::instance($cm->id);

require_sesskey();
require_login($course, false, $cm);
require_capability('mod/reactforum:viewdiscussion', $context);

$return = new stdClass();

if (is_guest($context, $USER)) {
    // is_guest should be used here as this also checks whether the user is a guest in the current course.
    // Guests and visitors cannot subscribe - only enrolled users.
    throw new moodle_exception('noguestsubscribe', 'mod_reactforum');
}

if (!\mod_reactforum\subscriptions::is_subscribable($reactforum)) {
    // Nothing to do. We won't actually output any content here though.
    echo json_encode($return);
    die;
}

if (\mod_reactforum\subscriptions::is_subscribed($USER->id, $reactforum, $discussion->id, $cm)) {
    // The user is subscribed, unsubscribe them.
    \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($USER->id, $discussion, $context);
} else {
    // The user is unsubscribed, subscribe them.
    \mod_reactforum\subscriptions::subscribe_user_to_discussion($USER->id, $discussion, $context);
}

// Now return the updated subscription icon.
$return->icon = reactforum_get_discussion_subscription_icon($reactforum, $discussion->id, null, $includetext);
echo json_encode($return);
die;
