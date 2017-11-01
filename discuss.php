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
 * Displays a post, and all the posts below it.
 * If no post is given, displays all posts in a discussion
 *
 * @package   mod_reactforum
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$d      = required_param('d', PARAM_INT);                // Discussion ID
$parent = optional_param('parent', 0, PARAM_INT);        // If set, then display this post and all children.
$mode   = optional_param('mode', 0, PARAM_INT);          // If set, changes the layout of the thread
$move   = optional_param('move', 0, PARAM_INT);          // If set, moves this discussion to another reactforum
$mark   = optional_param('mark', '', PARAM_ALPHA);       // Used for tracking read posts if user initiated.
$postid = optional_param('postid', 0, PARAM_INT);        // Used for tracking read posts if user initiated.
$pin    = optional_param('pin', -1, PARAM_INT);          // If set, pin or unpin this discussion.

$url = new moodle_url('/mod/reactforum/discuss.php', array('d'=>$d));
if ($parent !== 0) {
    $url->param('parent', $parent);
}
$PAGE->set_url($url);

$discussion = $DB->get_record('reactforum_discussions', array('id' => $d), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $discussion->course), '*', MUST_EXIST);
$reactforum = $DB->get_record('reactforum', array('id' => $discussion->reactforum), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('reactforum', $reactforum->id, $course->id, false, MUST_EXIST);

require_course_login($course, true, $cm);

// move this down fix for MDL-6926
require_once($CFG->dirroot.'/mod/reactforum/lib.php');

reactforum_include_styles();

$modcontext = context_module::instance($cm->id);
require_capability('mod/reactforum:viewdiscussion', $modcontext, NULL, true, 'noviewdiscussionspermission', 'reactforum');

if (!empty($CFG->enablerssfeeds) && !empty($CFG->reactforum_enablerssfeeds) && $reactforum->rsstype && $reactforum->rssarticles) {
    require_once("$CFG->libdir/rsslib.php");

    $rsstitle = format_string($course->shortname, true, array('context' => context_course::instance($course->id))) . ': ' . format_string($reactforum->name);
    rss_add_http_header($modcontext, 'mod_reactforum', $reactforum, $rsstitle);
}

// Move discussion if requested.
if ($move > 0 and confirm_sesskey()) {
    $return = $CFG->wwwroot.'/mod/reactforum/discuss.php?d='.$discussion->id;

    if (!$reactforumto = $DB->get_record('reactforum', array('id' => $move))) {
        print_error('cannotmovetonotexist', 'reactforum', $return);
    }

    require_capability('mod/reactforum:movediscussions', $modcontext);

    if ($reactforum->type == 'single') {
        print_error('cannotmovefromsinglereactforum', 'reactforum', $return);
    }

    if (!$reactforumto = $DB->get_record('reactforum', array('id' => $move))) {
        print_error('cannotmovetonotexist', 'reactforum', $return);
    }

    if ($reactforumto->type == 'single') {
        print_error('cannotmovetosinglereactforum', 'reactforum', $return);
    }

    // Get target reactforum cm and check it is visible to current user.
    $modinfo = get_fast_modinfo($course);
    $reactforums = $modinfo->get_instances_of('reactforum');
    if (!array_key_exists($reactforumto->id, $reactforums)) {
        print_error('cannotmovetonotfound', 'reactforum', $return);
    }
    $cmto = $reactforums[$reactforumto->id];
    if (!$cmto->uservisible) {
        print_error('cannotmovenotvisible', 'reactforum', $return);
    }

    $destinationctx = context_module::instance($cmto->id);
    require_capability('mod/reactforum:startdiscussion', $destinationctx);

    if (!reactforum_move_attachments($discussion, $reactforum->id, $reactforumto->id)) {
        echo $OUTPUT->notification("Errors occurred while moving attachment directories - check your file permissions");
    }
    // For each subscribed user in this reactforum and discussion, copy over per-discussion subscriptions if required.
    $discussiongroup = $discussion->groupid == -1 ? 0 : $discussion->groupid;
    $potentialsubscribers = \mod_reactforum\subscriptions::fetch_subscribed_users(
        $reactforum,
        $discussiongroup,
        $modcontext,
        'u.id',
        true
    );

    // Pre-seed the subscribed_discussion caches.
    // Firstly for the reactforum being moved to.
    \mod_reactforum\subscriptions::fill_subscription_cache($reactforumto->id);
    // And also for the discussion being moved.
    \mod_reactforum\subscriptions::fill_subscription_cache($reactforum->id);
    $subscriptionchanges = array();
    $subscriptiontime = time();
    foreach ($potentialsubscribers as $subuser) {
        $userid = $subuser->id;
        $targetsubscription = \mod_reactforum\subscriptions::is_subscribed($userid, $reactforumto, null, $cmto);
        $discussionsubscribed = \mod_reactforum\subscriptions::is_subscribed($userid, $reactforum, $discussion->id);
        $reactforumsubscribed = \mod_reactforum\subscriptions::is_subscribed($userid, $reactforum);

        if ($reactforumsubscribed && !$discussionsubscribed && $targetsubscription) {
            // The user has opted out of this discussion and the move would cause them to receive notifications again.
            // Ensure they are unsubscribed from the discussion still.
            $subscriptionchanges[$userid] = \mod_reactforum\subscriptions::REACTFORUM_DISCUSSION_UNSUBSCRIBED;
        } else if (!$reactforumsubscribed && $discussionsubscribed && !$targetsubscription) {
            // The user has opted into this discussion and would otherwise not receive the subscription after the move.
            // Ensure they are subscribed to the discussion still.
            $subscriptionchanges[$userid] = $subscriptiontime;
        }
    }

    $DB->set_field('reactforum_discussions', 'reactforum', $reactforumto->id, array('id' => $discussion->id));
    $DB->set_field('reactforum_read', 'reactforumid', $reactforumto->id, array('discussionid' => $discussion->id));

    // Delete the existing per-discussion subscriptions and replace them with the newly calculated ones.
    $DB->delete_records('reactforum_discussion_subs', array('discussion' => $discussion->id));
    $newdiscussion = clone $discussion;
    $newdiscussion->reactforum = $reactforumto->id;
    foreach ($subscriptionchanges as $userid => $preference) {
        if ($preference != \mod_reactforum\subscriptions::REACTFORUM_DISCUSSION_UNSUBSCRIBED) {
            // Users must have viewdiscussion to a discussion.
            if (has_capability('mod/reactforum:viewdiscussion', $destinationctx, $userid)) {
                \mod_reactforum\subscriptions::subscribe_user_to_discussion($userid, $newdiscussion, $destinationctx);
            }
        } else {
            \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($userid, $newdiscussion, $destinationctx);
        }
    }

    $params = array(
        'context' => $destinationctx,
        'objectid' => $discussion->id,
        'other' => array(
            'fromreactforumid' => $reactforum->id,
            'toreactforumid' => $reactforumto->id,
        )
    );
    $event = \mod_reactforum\event\discussion_moved::create($params);
    $event->add_record_snapshot('reactforum_discussions', $discussion);
    $event->add_record_snapshot('reactforum', $reactforum);
    $event->add_record_snapshot('reactforum', $reactforumto);
    $event->trigger();

    // Delete the RSS files for the 2 reactforums to force regeneration of the feeds
    require_once($CFG->dirroot.'/mod/reactforum/rsslib.php');
    reactforum_rss_delete_file($reactforum);
    reactforum_rss_delete_file($reactforumto);

    redirect($return.'&move=-1&sesskey='.sesskey());
}
// Pin or unpin discussion if requested.
if ($pin !== -1 && confirm_sesskey()) {
    require_capability('mod/reactforum:pindiscussions', $modcontext);

    $params = array('context' => $modcontext, 'objectid' => $discussion->id, 'other' => array('reactforumid' => $reactforum->id));

    switch ($pin) {
        case REACTFORUM_DISCUSSION_PINNED:
            // Pin the discussion and trigger discussion pinned event.
            reactforum_discussion_pin($modcontext, $reactforum, $discussion);
            break;
        case REACTFORUM_DISCUSSION_UNPINNED:
            // Unpin the discussion and trigger discussion unpinned event.
            reactforum_discussion_unpin($modcontext, $reactforum, $discussion);
            break;
        default:
            echo $OUTPUT->notification("Invalid value when attempting to pin/unpin discussion");
            break;
    }

    redirect(new moodle_url('/mod/reactforum/discuss.php', array('d' => $discussion->id)));
}

// Trigger discussion viewed event.
reactforum_discussion_view($modcontext, $reactforum, $discussion);

unset($SESSION->fromdiscussion);

if ($mode) {
    set_user_preference('reactforum_displaymode', $mode);
}

$displaymode = get_user_preferences('reactforum_displaymode', $CFG->reactforum_displaymode);

if ($parent) {
    // If flat AND parent, then force nested display this time
    if ($displaymode == REACTFORUM_MODE_FLATOLDEST or $displaymode == REACTFORUM_MODE_FLATNEWEST) {
        $displaymode = REACTFORUM_MODE_NESTED;
    }
} else {
    $parent = $discussion->firstpost;
}

if (! $post = reactforum_get_post_full($parent)) {
    print_error("notexists", 'reactforum', "$CFG->wwwroot/mod/reactforum/view.php?f=$reactforum->id");
}

if (!reactforum_user_can_see_post($reactforum, $discussion, $post, null, $cm)) {
    print_error('noviewdiscussionspermission', 'reactforum', "$CFG->wwwroot/mod/reactforum/view.php?id=$reactforum->id");
}

if ($mark == 'read' or $mark == 'unread') {
    if ($CFG->reactforum_usermarksread && reactforum_tp_can_track_reactforums($reactforum) && reactforum_tp_is_tracked($reactforum)) {
        if ($mark == 'read') {
            reactforum_tp_add_read_record($USER->id, $postid);
        } else {
            // unread
            reactforum_tp_delete_read_records($USER->id, $postid);
        }
    }
}

$searchform = reactforum_search_form($course);

$reactforumnode = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
if (empty($reactforumnode)) {
    $reactforumnode = $PAGE->navbar;
} else {
    $reactforumnode->make_active();
}
$node = $reactforumnode->add(format_string($discussion->name), new moodle_url('/mod/reactforum/discuss.php', array('d'=>$discussion->id)));
$node->display = false;
if ($node && $post->id != $discussion->firstpost) {
    $node->add(format_string($post->subject), $PAGE->url);
}

$PAGE->set_title("$course->shortname: ".format_string($discussion->name));
$PAGE->set_heading($course->fullname);
$PAGE->set_button($searchform);
$renderer = $PAGE->get_renderer('mod_reactforum');

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($reactforum->name), 2);
echo $OUTPUT->heading(format_string($discussion->name), 3, 'discussionname');

// is_guest should be used here as this also checks whether the user is a guest in the current course.
// Guests and visitors cannot subscribe - only enrolled users.
if ((!is_guest($modcontext, $USER) && isloggedin()) && has_capability('mod/reactforum:viewdiscussion', $modcontext)) {
    // Discussion subscription.
    if (\mod_reactforum\subscriptions::is_subscribable($reactforum)) {
        echo html_writer::div(
            reactforum_get_discussion_subscription_icon($reactforum, $post->discussion, null, true),
            'discussionsubscription'
        );
        echo reactforum_get_discussion_subscription_icon_preloaders();
    }
}


/// Check to see if groups are being used in this reactforum
/// If so, make sure the current person is allowed to see this discussion
/// Also, if we know they should be able to reply, then explicitly set $canreply for performance reasons

$canreply = reactforum_user_can_post($reactforum, $discussion, $USER, $cm, $course, $modcontext);
if (!$canreply and $reactforum->type !== 'news') {
    if (isguestuser() or !isloggedin()) {
        $canreply = true;
    }
    if (!is_enrolled($modcontext) and !is_viewing($modcontext)) {
        // allow guests and not-logged-in to see the link - they are prompted to log in after clicking the link
        // normal users with temporary guest access see this link too, they are asked to enrol instead
        $canreply = enrol_selfenrol_available($course->id);
    }
}

// Output the links to neighbour discussions.
$neighbours = reactforum_get_discussion_neighbours($cm, $discussion, $reactforum);
$neighbourlinks = $renderer->neighbouring_discussion_navigation($neighbours['prev'], $neighbours['next']);
echo $neighbourlinks;

/// Print the controls across the top
echo '<div class="discussioncontrols clearfix"><div class="controlscontainer m-b-1">';

if (!empty($CFG->enableportfolios) && has_capability('mod/reactforum:exportdiscussion', $modcontext)) {
    require_once($CFG->libdir.'/portfoliolib.php');
    $button = new portfolio_add_button();
    $button->set_callback_options('reactforum_portfolio_caller', array('discussionid' => $discussion->id), 'mod_reactforum');
    $button = $button->to_html(PORTFOLIO_ADD_FULL_FORM, get_string('exportdiscussion', 'mod_reactforum'));
    $buttonextraclass = '';
    if (empty($button)) {
        // no portfolio plugin available.
        $button = '&nbsp;';
        $buttonextraclass = ' noavailable';
    }
    echo html_writer::tag('div', $button, array('class' => 'discussioncontrol exporttoportfolio'.$buttonextraclass));
} else {
    echo html_writer::tag('div', '&nbsp;', array('class'=>'discussioncontrol nullcontrol'));
}

// groups selector not needed here
echo '<div class="discussioncontrol displaymode">';
reactforum_print_mode_form($discussion->id, $displaymode);
echo "</div>";

if ($reactforum->type != 'single'
            && has_capability('mod/reactforum:movediscussions', $modcontext)) {

    echo '<div class="discussioncontrol movediscussion">';
    // Popup menu to move discussions to other reactforums. The discussion in a
    // single discussion reactforum can't be moved.
    $modinfo = get_fast_modinfo($course);
    if (isset($modinfo->instances['reactforum'])) {
        $reactforummenu = array();
        // Check reactforum types and eliminate simple discussions.
        $reactforumcheck = $DB->get_records('reactforum', array('course' => $course->id),'', 'id, type');
        foreach ($modinfo->instances['reactforum'] as $reactforumcm) {
            if (!$reactforumcm->uservisible || !has_capability('mod/reactforum:startdiscussion',
                context_module::instance($reactforumcm->id))) {
                continue;
            }
            $section = $reactforumcm->sectionnum;
            $sectionname = get_section_name($course, $section);
            if (empty($reactforummenu[$section])) {
                $reactforummenu[$section] = array($sectionname => array());
            }
            $reactforumidcompare = $reactforumcm->instance != $reactforum->id;
            $reactforumtypecheck = $reactforumcheck[$reactforumcm->instance]->type !== 'single';
            if ($reactforumidcompare and $reactforumtypecheck) {
                $url = "/mod/reactforum/discuss.php?d=$discussion->id&move=$reactforumcm->instance&sesskey=".sesskey();
                $reactforummenu[$section][$sectionname][$url] = format_string($reactforumcm->name);
            }
        }
        if (!empty($reactforummenu)) {
            echo '<div class="movediscussionoption">';
            $select = new url_select($reactforummenu, '',
                    array('/mod/reactforum/discuss.php?d=' . $discussion->id => get_string("movethisdiscussionto", "reactforum")),
                    'reactforummenu', get_string('move'));
            echo $OUTPUT->render($select);
            echo "</div>";
        }
    }
    echo "</div>";
}

if (has_capability('mod/reactforum:pindiscussions', $modcontext)) {
    if ($discussion->pinned == REACTFORUM_DISCUSSION_PINNED) {
        $pinlink = REACTFORUM_DISCUSSION_UNPINNED;
        $pintext = get_string('discussionunpin', 'reactforum');
    } else {
        $pinlink = REACTFORUM_DISCUSSION_PINNED;
        $pintext = get_string('discussionpin', 'reactforum');
    }
    $button = new single_button(new moodle_url('discuss.php', array('pin' => $pinlink, 'd' => $discussion->id)), $pintext, 'post');
    echo html_writer::tag('div', $OUTPUT->render($button), array('class' => 'discussioncontrol pindiscussion'));
}


echo "</div></div>";

if (reactforum_discussion_is_locked($reactforum, $discussion)) {
    echo html_writer::div(get_string('discussionlocked', 'reactforum'), 'discussionlocked');
}

if (!empty($reactforum->blockafter) && !empty($reactforum->blockperiod)) {
    $a = new stdClass();
    $a->blockafter  = $reactforum->blockafter;
    $a->blockperiod = get_string('secondstotime'.$reactforum->blockperiod);
    echo $OUTPUT->notification(get_string('thisreactforumisthrottled','reactforum',$a));
}

if ($reactforum->type == 'qanda' && !has_capability('mod/reactforum:viewqandawithoutposting', $modcontext) &&
            !reactforum_user_has_posted($reactforum->id,$discussion->id,$USER->id)) {
    echo $OUTPUT->notification(get_string('qandanotify', 'reactforum'));
}

if ($move == -1 and confirm_sesskey()) {
    echo $OUTPUT->notification(get_string('discussionmoved', 'reactforum', format_string($reactforum->name,true)), 'notifysuccess');
}

$canrate = has_capability('mod/reactforum:rate', $modcontext);
reactforum_print_discussion($course, $cm, $reactforum, $discussion, $post, $displaymode, $canreply, $canrate);

echo $neighbourlinks;

// Add the subscription toggle JS.
$PAGE->requires->yui_module('moodle-mod_reactforum-subscriptiontoggle', 'Y.M.mod_reactforum.subscriptiontoggle.init');
$PAGE->requires->js(new moodle_url("/mod/reactforum/script.js"));

echo $OUTPUT->footer();
