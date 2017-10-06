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
 * Subscribe to or unsubscribe from a reactforum or manage reactforum subscription mode
 *
 * This script can be used by either individual users to subscribe to or
 * unsubscribe from a reactforum (no 'mode' param provided), or by reactforum managers
 * to control the subscription mode (by 'mode' param).
 * This script can be called from a link in email so the sesskey is not
 * required parameter. However, if sesskey is missing, the user has to go
 * through a confirmation page that redirects the user back with the
 * sesskey.
 *
 * @package   mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/reactforum/lib.php');

$id             = required_param('id', PARAM_INT);             // The reactforum to set subscription on.
$mode           = optional_param('mode', null, PARAM_INT);     // The reactforum's subscription mode.
$user           = optional_param('user', 0, PARAM_INT);        // The userid of the user to subscribe, defaults to $USER.
$discussionid   = optional_param('d', null, PARAM_INT);        // The discussionid to subscribe.
$sesskey        = optional_param('sesskey', null, PARAM_RAW);
$returnurl      = optional_param('returnurl', null, PARAM_RAW);

$url = new moodle_url('/mod/reactforum/subscribe.php', array('id'=>$id));
if (!is_null($mode)) {
    $url->param('mode', $mode);
}
if ($user !== 0) {
    $url->param('user', $user);
}
if (!is_null($sesskey)) {
    $url->param('sesskey', $sesskey);
}
if (!is_null($discussionid)) {
    $url->param('d', $discussionid);
    if (!$discussion = $DB->get_record('reactforum_discussions', array('id' => $discussionid, 'reactforum' => $id))) {
        print_error('invaliddiscussionid', 'reactforum');
    }
}
$PAGE->set_url($url);

$reactforum   = $DB->get_record('reactforum', array('id' => $id), '*', MUST_EXIST);
$course  = $DB->get_record('course', array('id' => $reactforum->course), '*', MUST_EXIST);
$cm      = get_coursemodule_from_instance('reactforum', $reactforum->id, $course->id, false, MUST_EXIST);
$context = context_module::instance($cm->id);

if ($user) {
    require_sesskey();
    if (!has_capability('mod/reactforum:managesubscriptions', $context)) {
        print_error('nopermissiontosubscribe', 'reactforum');
    }
    $user = $DB->get_record('user', array('id' => $user), '*', MUST_EXIST);
} else {
    $user = $USER;
}

if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
    $groupmode = $cm->groupmode;
} else {
    $groupmode = $course->groupmode;
}

$issubscribed = \mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum, $discussionid, $cm);

// For a user to subscribe when a groupmode is set, they must have access to at least one group.
if ($groupmode && !$issubscribed && !has_capability('moodle/site:accessallgroups', $context)) {
    if (!groups_get_all_groups($course->id, $USER->id)) {
        print_error('cannotsubscribe', 'reactforum');
    }
}

require_login($course, false, $cm);

if (is_null($mode) and !is_enrolled($context, $USER, '', true)) {   // Guests and visitors can't subscribe - only enrolled
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);
    if (isguestuser()) {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('subscribeenrolledonly', 'reactforum').'<br /><br />'.get_string('liketologin'),
                     get_login_url(), new moodle_url('/mod/reactforum/view.php', array('f'=>$id)));
        echo $OUTPUT->footer();
        exit;
    } else {
        // There should not be any links leading to this place, just redirect.
        redirect(
                new moodle_url('/mod/reactforum/view.php', array('f'=>$id)),
                get_string('subscribeenrolledonly', 'reactforum'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
    }
}

$returnto = optional_param('backtoindex',0,PARAM_INT)
    ? "index.php?id=".$course->id
    : "view.php?f=$id";

if ($returnurl) {
    $returnto = $returnurl;
}

if (!is_null($mode) and has_capability('mod/reactforum:managesubscriptions', $context)) {
    require_sesskey();
    switch ($mode) {
        case REACTFORUM_CHOOSESUBSCRIBE : // 0
            \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_CHOOSESUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('everyonecannowchoose', 'reactforum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        case REACTFORUM_FORCESUBSCRIBE : // 1
            \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_FORCESUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('everyoneisnowsubscribed', 'reactforum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        case REACTFORUM_INITIALSUBSCRIBE : // 2
            if ($reactforum->forcesubscribe <> REACTFORUM_INITIALSUBSCRIBE) {
                $users = \mod_reactforum\subscriptions::get_potential_subscribers($context, 0, 'u.id, u.email', '');
                foreach ($users as $user) {
                    \mod_reactforum\subscriptions::subscribe_user($user->id, $reactforum, $context);
                }
            }
            \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_INITIALSUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('everyoneisnowsubscribed', 'reactforum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        case REACTFORUM_DISALLOWSUBSCRIBE : // 3
            \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_DISALLOWSUBSCRIBE);
            redirect(
                    $returnto,
                    get_string('noonecansubscribenow', 'reactforum'),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
            break;
        default:
            print_error(get_string('invalidforcesubscribe', 'reactforum'));
    }
}

if (\mod_reactforum\subscriptions::is_forcesubscribed($reactforum)) {
    redirect(
            $returnto,
            get_string('everyoneisnowsubscribed', 'reactforum'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
}

$info = new stdClass();
$info->name  = fullname($user);
$info->reactforum = format_string($reactforum->name);

if ($issubscribed) {
    if (is_null($sesskey)) {
        // We came here via link in email.
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();

        $viewurl = new moodle_url('/mod/reactforum/view.php', array('f' => $id));
        if ($discussionid) {
            $a = new stdClass();
            $a->reactforum = format_string($reactforum->name);
            $a->discussion = format_string($discussion->name);
            echo $OUTPUT->confirm(get_string('confirmunsubscribediscussion', 'reactforum', $a),
                    $PAGE->url, $viewurl);
        } else {
            echo $OUTPUT->confirm(get_string('confirmunsubscribe', 'reactforum', format_string($reactforum->name)),
                    $PAGE->url, $viewurl);
        }
        echo $OUTPUT->footer();
        exit;
    }
    require_sesskey();
    if ($discussionid === null) {
        if (\mod_reactforum\subscriptions::unsubscribe_user($user->id, $reactforum, $context, true)) {
            redirect(
                    $returnto,
                    get_string('nownotsubscribed', 'reactforum', $info),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
        } else {
            print_error('cannotunsubscribe', 'reactforum', get_local_referer(false));
        }
    } else {
        if (\mod_reactforum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion, $context)) {
            $info->discussion = $discussion->name;
            redirect(
                    $returnto,
                    get_string('discussionnownotsubscribed', 'reactforum', $info),
                    null,
                    \core\output\notification::NOTIFY_SUCCESS
                );
        } else {
            print_error('cannotunsubscribe', 'reactforum', get_local_referer(false));
        }
    }

} else {  // subscribe
    if (\mod_reactforum\subscriptions::subscription_disabled($reactforum) && !has_capability('mod/reactforum:managesubscriptions', $context)) {
        print_error('disallowsubscribe', 'reactforum', get_local_referer(false));
    }
    if (!has_capability('mod/reactforum:viewdiscussion', $context)) {
        print_error('noviewdiscussionspermission', 'reactforum', get_local_referer(false));
    }
    if (is_null($sesskey)) {
        // We came here via link in email.
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();

        $viewurl = new moodle_url('/mod/reactforum/view.php', array('f' => $id));
        if ($discussionid) {
            $a = new stdClass();
            $a->reactforum = format_string($reactforum->name);
            $a->discussion = format_string($discussion->name);
            echo $OUTPUT->confirm(get_string('confirmsubscribediscussion', 'reactforum', $a),
                    $PAGE->url, $viewurl);
        } else {
            echo $OUTPUT->confirm(get_string('confirmsubscribe', 'reactforum', format_string($reactforum->name)),
                    $PAGE->url, $viewurl);
        }
        echo $OUTPUT->footer();
        exit;
    }
    require_sesskey();
    if ($discussionid == null) {
        \mod_reactforum\subscriptions::subscribe_user($user->id, $reactforum, $context, true);
        redirect(
                $returnto,
                get_string('nowsubscribed', 'reactforum', $info),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
    } else {
        $info->discussion = $discussion->name;
        \mod_reactforum\subscriptions::subscribe_user_to_discussion($user->id, $discussion, $context);
        redirect(
                $returnto,
                get_string('discussionnowsubscribed', 'reactforum', $info),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
    }
}
