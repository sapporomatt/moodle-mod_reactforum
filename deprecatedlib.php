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

defined('MOODLE_INTERNAL') || die();

// Deprecated a very long time ago.

/**
 * How many posts by other users are unrated by a given user in the given discussion?
 *
 * @param int $discussionid
 * @param int $userid
 * @return mixed
 * @deprecated since Moodle 1.1 - please do not use this function any more.
 */
function reactforum_count_unrated_posts($discussionid, $userid) {
    global $CFG, $DB;
    debugging('reactforum_count_unrated_posts() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    $sql = "SELECT COUNT(*) as num
              FROM {reactforum_posts}
             WHERE parent > 0
               AND discussion = :discussionid
               AND userid <> :userid";
    $params = array('discussionid' => $discussionid, 'userid' => $userid);
    $posts = $DB->get_record_sql($sql, $params);
    if ($posts) {
        $sql = "SELECT count(*) as num
                  FROM {reactforum_posts} p,
                       {rating} r
                 WHERE p.discussion = :discussionid AND
                       p.id = r.itemid AND
                       r.userid = userid AND
                       r.component = 'mod_reactforum' AND
                       r.ratingarea = 'post'";
        $rated = $DB->get_record_sql($sql, $params);
        if ($rated) {
            if ($posts->num > $rated->num) {
                return $posts->num - $rated->num;
            } else {
                return 0;    // Just in case there was a counting error
            }
        } else {
            return $posts->num;
        }
    } else {
        return 0;
    }
}


// Since Moodle 1.5.

/**
 * Returns the count of records for the provided user and discussion.
 *
 * @global object
 * @global object
 * @param int $userid
 * @param int $discussionid
 * @return bool
 * @deprecated since Moodle 1.5 - please do not use this function any more.
 */
function reactforum_tp_count_discussion_read_records($userid, $discussionid) {
    debugging('reactforum_tp_count_discussion_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $cutoffdate = isset($CFG->reactforum_oldpostdays) ? (time() - ($CFG->reactforum_oldpostdays*24*60*60)) : 0;

    $sql = 'SELECT COUNT(DISTINCT p.id) '.
           'FROM {reactforum_discussions} d '.
           'LEFT JOIN {reactforum_read} r ON d.id = r.discussionid AND r.userid = ? '.
           'LEFT JOIN {reactforum_posts} p ON p.discussion = d.id '.
                'AND (p.modified < ? OR p.id = r.postid) '.
           'WHERE d.id = ? ';

    return ($DB->count_records_sql($sql, array($userid, $cutoffdate, $discussionid)));
}

/**
 * Get all discussions started by a particular user in a course (or group)
 *
 * @global object
 * @global object
 * @param int $courseid
 * @param int $userid
 * @param int $groupid
 * @return array
 * @deprecated since Moodle 1.5 - please do not use this function any more.
 */
function reactforum_get_user_discussions($courseid, $userid, $groupid=0) {
    debugging('reactforum_get_user_discussions() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;
    $params = array($courseid, $userid);
    if ($groupid) {
        $groupselect = " AND d.groupid = ? ";
        $params[] = $groupid;
    } else  {
        $groupselect = "";
    }

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_records_sql("SELECT p.*, d.groupid, $allnames, u.email, u.picture, u.imagealt,
                                   f.type as reactforumtype, f.name as reactforumname, f.id as reactforumid
                              FROM {reactforum_discussions} d,
                                   {reactforum_posts} p,
                                   {user} u,
                                   {reactforum} f
                             WHERE d.course = ?
                               AND p.discussion = d.id
                               AND p.parent = 0
                               AND p.userid = u.id
                               AND u.id = ?
                               AND d.reactforum = f.id $groupselect
                          ORDER BY p.created DESC", $params);
}


// Since Moodle 1.6.

/**
 * Returns the count of posts for the provided reactforum and [optionally] group.
 * @global object
 * @global object
 * @param int $reactforumid
 * @param int|bool $groupid
 * @return int
 * @deprecated since Moodle 1.6 - please do not use this function any more.
 */
function reactforum_tp_count_reactforum_posts($reactforumid, $groupid=false) {
    debugging('reactforum_tp_count_reactforum_posts() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;
    $params = array($reactforumid);
    $sql = 'SELECT COUNT(*) '.
           'FROM {reactforum_posts} fp,{reactforum_discussions} fd '.
           'WHERE fd.reactforum = ? AND fp.discussion = fd.id';
    if ($groupid !== false) {
        $sql .= ' AND (fd.groupid = ? OR fd.groupid = -1)';
        $params[] = $groupid;
    }
    $count = $DB->count_records_sql($sql, $params);


    return $count;
}

/**
 * Returns the count of records for the provided user and reactforum and [optionally] group.
 * @global object
 * @global object
 * @param int $userid
 * @param int $reactforumid
 * @param int|bool $groupid
 * @return int
 * @deprecated since Moodle 1.6 - please do not use this function any more.
 */
function reactforum_tp_count_reactforum_read_records($userid, $reactforumid, $groupid=false) {
    debugging('reactforum_tp_count_reactforum_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $cutoffdate = time() - ($CFG->reactforum_oldpostdays*24*60*60);

    $groupsel = '';
    $params = array($userid, $reactforumid, $cutoffdate);
    if ($groupid !== false) {
        $groupsel = "AND (d.groupid = ? OR d.groupid = -1)";
        $params[] = $groupid;
    }

    $sql = "SELECT COUNT(p.id)
              FROM  {reactforum_posts} p
                    JOIN {reactforum_discussions} d ON d.id = p.discussion
                    LEFT JOIN {reactforum_read} r   ON (r.postid = p.id AND r.userid= ?)
              WHERE d.reactforum = ?
                    AND (p.modified < $cutoffdate OR (p.modified >= ? AND r.id IS NOT NULL))
                    $groupsel";

    return $DB->get_field_sql($sql, $params);
}


// Since Moodle 1.7.

/**
 * Returns array of reactforum open modes.
 *
 * @return array
 * @deprecated since Moodle 1.7 - please do not use this function any more.
 */
function reactforum_get_open_modes() {
    debugging('reactforum_get_open_modes() is deprecated and will not be replaced.', DEBUG_DEVELOPER);
    return array();
}


// Since Moodle 1.9.

/**
 * Gets posts with all info ready for reactforum_print_post
 * We pass reactforumid in because we always know it so no need to make a
 * complicated join to find it out.
 *
 * @global object
 * @global object
 * @param int $parent
 * @param int $reactforumid
 * @return array
 * @deprecated since Moodle 1.9 MDL-13303 - please do not use this function any more.
 */
function reactforum_get_child_posts($parent, $reactforumid) {
    debugging('reactforum_get_child_posts() is deprecated.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_records_sql("SELECT p.*, $reactforumid AS reactforum, $allnames, u.email, u.picture, u.imagealt
                              FROM {reactforum_posts} p
                         LEFT JOIN {user} u ON p.userid = u.id
                             WHERE p.parent = ?
                          ORDER BY p.created ASC", array($parent));
}

/**
 * Gets posts with all info ready for reactforum_print_post
 * We pass reactforumid in because we always know it so no need to make a
 * complicated join to find it out.
 *
 * @global object
 * @global object
 * @return mixed array of posts or false
 * @deprecated since Moodle 1.9 MDL-13303 - please do not use this function any more.
 */
function reactforum_get_discussion_posts($discussion, $sort, $reactforumid) {
    debugging('reactforum_get_discussion_posts() is deprecated.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_records_sql("SELECT p.*, $reactforumid AS reactforum, $allnames, u.email, u.picture, u.imagealt
                              FROM {reactforum_posts} p
                         LEFT JOIN {user} u ON p.userid = u.id
                             WHERE p.discussion = ?
                               AND p.parent > 0 $sort", array($discussion));
}


// Since Moodle 2.0.

/**
 * Returns a list of ratings for a particular post - sorted.
 *
 * @param stdClass $context
 * @param int $postid
 * @param string $sort
 * @return array Array of ratings or false
 * @deprecated since Moodle 2.0 MDL-21657 - please do not use this function any more.
 */
function reactforum_get_ratings($context, $postid, $sort = "u.firstname ASC") {
    debugging('reactforum_get_ratings() is deprecated.', DEBUG_DEVELOPER);
    $options = new stdClass;
    $options->context = $context;
    $options->component = 'mod_reactforum';
    $options->ratingarea = 'post';
    $options->itemid = $postid;
    $options->sort = "ORDER BY $sort";

    $rm = new rating_manager();
    return $rm->get_all_ratings_for_item($options);
}

/**
 * Generate and return the track or no track link for a reactforum.
 *
 * @global object
 * @global object
 * @global object
 * @param object $reactforum the reactforum. Fields used are $reactforum->id and $reactforum->forcesubscribe.
 * @param array $messages
 * @param bool $fakelink
 * @return string
 * @deprecated since Moodle 2.0 MDL-14632 - please do not use this function any more.
 */
function reactforum_get_tracking_link($reactforum, $messages=array(), $fakelink=true) {
    debugging('reactforum_get_tracking_link() is deprecated.', DEBUG_DEVELOPER);

    global $CFG, $USER, $PAGE, $OUTPUT;

    static $strnotrackreactforum, $strtrackreactforum;

    if (isset($messages['trackreactforum'])) {
         $strtrackreactforum = $messages['trackreactforum'];
    }
    if (isset($messages['notrackreactforum'])) {
         $strnotrackreactforum = $messages['notrackreactforum'];
    }
    if (empty($strtrackreactforum)) {
        $strtrackreactforum = get_string('trackreactforum', 'reactforum');
    }
    if (empty($strnotrackreactforum)) {
        $strnotrackreactforum = get_string('notrackreactforum', 'reactforum');
    }

    if (reactforum_tp_is_tracked($reactforum)) {
        $linktitle = $strnotrackreactforum;
        $linktext = $strnotrackreactforum;
    } else {
        $linktitle = $strtrackreactforum;
        $linktext = $strtrackreactforum;
    }

    $link = '';
    if ($fakelink) {
        $PAGE->requires->js('/mod/reactforum/reactforum.js');
        $PAGE->requires->js_function_call('reactforum_produce_tracking_link', Array($reactforum->id, $linktext, $linktitle));
        // use <noscript> to print button in case javascript is not enabled
        $link .= '<noscript>';
    }
    $url = new moodle_url('/mod/reactforum/settracking.php', array(
            'id' => $reactforum->id,
            'sesskey' => sesskey(),
        ));
    $link .= $OUTPUT->single_button($url, $linktext, 'get', array('title'=>$linktitle));

    if ($fakelink) {
        $link .= '</noscript>';
    }

    return $link;
}

/**
 * Returns the count of records for the provided user and discussion.
 *
 * @global object
 * @global object
 * @param int $userid
 * @param int $discussionid
 * @return int
 * @deprecated since Moodle 2.0 MDL-14113 - please do not use this function any more.
 */
function reactforum_tp_count_discussion_unread_posts($userid, $discussionid) {
    debugging('reactforum_tp_count_discussion_unread_posts() is deprecated.', DEBUG_DEVELOPER);
    global $CFG, $DB;

    $cutoffdate = isset($CFG->reactforum_oldpostdays) ? (time() - ($CFG->reactforum_oldpostdays*24*60*60)) : 0;

    $sql = 'SELECT COUNT(p.id) '.
           'FROM {reactforum_posts} p '.
           'LEFT JOIN {reactforum_read} r ON r.postid = p.id AND r.userid = ? '.
           'WHERE p.discussion = ? '.
                'AND p.modified >= ? AND r.id is NULL';

    return $DB->count_records_sql($sql, array($userid, $discussionid, $cutoffdate));
}

/**
 * Converts a reactforum to use the Roles System
 *
 * @deprecated since Moodle 2.0 MDL-23479 - please do not use this function any more.
 */
function reactforum_convert_to_roles() {
    debugging('reactforum_convert_to_roles() is deprecated and will not be replaced.', DEBUG_DEVELOPER);
}

/**
 * Returns all records in the 'reactforum_read' table matching the passed keys, indexed
 * by userid.
 *
 * @global object
 * @param int $userid
 * @param int $postid
 * @param int $discussionid
 * @param int $reactforumid
 * @return array
 * @deprecated since Moodle 2.0 MDL-14113 - please do not use this function any more.
 */
function reactforum_tp_get_read_records($userid=-1, $postid=-1, $discussionid=-1, $reactforumid=-1) {
    debugging('reactforum_tp_get_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $DB;
    $select = '';
    $params = array();

    if ($userid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'userid = ?';
        $params[] = $userid;
    }
    if ($postid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'postid = ?';
        $params[] = $postid;
    }
    if ($discussionid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'discussionid = ?';
        $params[] = $discussionid;
    }
    if ($reactforumid > -1) {
        if ($select != '') $select .= ' AND ';
        $select .= 'reactforumid = ?';
        $params[] = $reactforumid;
    }

    return $DB->get_records_select('reactforum_read', $select, $params);
}

/**
 * Returns all read records for the provided user and discussion, indexed by postid.
 *
 * @global object
 * @param inti $userid
 * @param int $discussionid
 * @deprecated since Moodle 2.0 MDL-14113 - please do not use this function any more.
 */
function reactforum_tp_get_discussion_read_records($userid, $discussionid) {
    debugging('reactforum_tp_get_discussion_read_records() is deprecated and will not be replaced.', DEBUG_DEVELOPER);

    global $DB;
    $select = 'userid = ? AND discussionid = ?';
    $fields = 'postid, firstread, lastread';
    return $DB->get_records_select('reactforum_read', $select, array($userid, $discussionid), '', $fields);
}

// Deprecated in 2.3.

/**
 * This function gets run whenever user is enrolled into course
 *
 * @deprecated since Moodle 2.3 MDL-33166 - please do not use this function any more.
 * @param stdClass $cp
 * @return void
 */
function reactforum_user_enrolled($cp) {
    debugging('reactforum_user_enrolled() is deprecated. Please use reactforum_user_role_assigned instead.', DEBUG_DEVELOPER);
    global $DB;

    // NOTE: this has to be as fast as possible - we do not want to slow down enrolments!
    //       Originally there used to be 'mod/reactforum:initialsubscriptions' which was
    //       introduced because we did not have enrolment information in earlier versions...

    $sql = "SELECT f.id
              FROM {reactforum} f
         LEFT JOIN {reactforum_subscriptions} fs ON (fs.reactforum = f.id AND fs.userid = :userid)
             WHERE f.course = :courseid AND f.forcesubscribe = :initial AND fs.id IS NULL";
    $params = array('courseid'=>$cp->courseid, 'userid'=>$cp->userid, 'initial'=>REACTFORUM_INITIALSUBSCRIBE);

    $reactforums = $DB->get_records_sql($sql, $params);
    foreach ($reactforums as $reactforum) {
        \mod_reactforum\subscriptions::subscribe_user($cp->userid, $reactforum);
    }
}


// Deprecated in 2.4.

/**
 * Checks to see if a user can view a particular post.
 *
 * @deprecated since Moodle 2.4 use reactforum_user_can_see_post() instead
 *
 * @param object $post
 * @param object $course
 * @param object $cm
 * @param object $reactforum
 * @param object $discussion
 * @param object $user
 * @return boolean
 */
function reactforum_user_can_view_post($post, $course, $cm, $reactforum, $discussion, $user=null){
    debugging('reactforum_user_can_view_post() is deprecated. Please use reactforum_user_can_see_post() instead.', DEBUG_DEVELOPER);
    return reactforum_user_can_see_post($reactforum, $discussion, $post, $user, $cm);
}


// Deprecated in 2.6.

/**
 * REACTFORUM_TRACKING_ON - deprecated alias for REACTFORUM_TRACKING_FORCED.
 * @deprecated since 2.6
 */
define('REACTFORUM_TRACKING_ON', 2);

/**
 * @deprecated since Moodle 2.6
 * @see shorten_text()
 */
function reactforum_shorten_post($message) {
    throw new coding_exception('reactforum_shorten_post() can not be used any more. Please use shorten_text($message, $CFG->reactforum_shortpost) instead.');
}

// Deprecated in 2.8.

/**
 * @global object
 * @param int $userid
 * @param object $reactforum
 * @return bool
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::is_subscribed() instead
 */
function reactforum_is_subscribed($userid, $reactforum) {
    global $DB;
    debugging("reactforum_is_subscribed() has been deprecated, please use \\mod_reactforum\\subscriptions::is_subscribed() instead.",
            DEBUG_DEVELOPER);

    // Note: The new function does not take an integer form of reactforum.
    if (is_numeric($reactforum)) {
        $reactforum = $DB->get_record('reactforum', array('id' => $reactforum));
    }

    return mod_reactforum\subscriptions::is_subscribed($userid, $reactforum);
}

/**
 * Adds user to the subscriber list
 *
 * @param int $userid
 * @param int $reactforumid
 * @param context_module|null $context Module context, may be omitted if not known or if called for the current module set in page.
 * @param boolean $userrequest Whether the user requested this change themselves. This has an effect on whether
 * discussion subscriptions are removed too.
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::subscribe_user() instead
 */
function reactforum_subscribe($userid, $reactforumid, $context = null, $userrequest = false) {
    global $DB;
    debugging("reactforum_subscribe() has been deprecated, please use \\mod_reactforum\\subscriptions::subscribe_user() instead.",
            DEBUG_DEVELOPER);

    // Note: The new function does not take an integer form of reactforum.
    $reactforum = $DB->get_record('reactforum', array('id' => $reactforumid));
    \mod_reactforum\subscriptions::subscribe_user($userid, $reactforum, $context, $userrequest);
}

/**
 * Removes user from the subscriber list
 *
 * @param int $userid
 * @param int $reactforumid
 * @param context_module|null $context Module context, may be omitted if not known or if called for the current module set in page.
 * @param boolean $userrequest Whether the user requested this change themselves. This has an effect on whether
 * discussion subscriptions are removed too.
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::unsubscribe_user() instead
 */
function reactforum_unsubscribe($userid, $reactforumid, $context = null, $userrequest = false) {
    global $DB;
    debugging("reactforum_unsubscribe() has been deprecated, please use \\mod_reactforum\\subscriptions::unsubscribe_user() instead.",
            DEBUG_DEVELOPER);

    // Note: The new function does not take an integer form of reactforum.
    $reactforum = $DB->get_record('reactforum', array('id' => $reactforumid));
    \mod_reactforum\subscriptions::unsubscribe_user($userid, $reactforum, $context, $userrequest);
}

/**
 * Returns list of user objects that are subscribed to this reactforum.
 *
 * @param stdClass $course the course
 * @param stdClass $reactforum the reactforum
 * @param int $groupid group id, or 0 for all.
 * @param context_module $context the reactforum context, to save re-fetching it where possible.
 * @param string $fields requested user fields (with "u." table prefix)
 * @param boolean $considerdiscussions Whether to take discussion subscriptions and unsubscriptions into consideration.
 * @return array list of users.
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::fetch_subscribed_users() instead
  */
function reactforum_subscribed_users($course, $reactforum, $groupid = 0, $context = null, $fields = null) {
    debugging("reactforum_subscribed_users() has been deprecated, please use \\mod_reactforum\\subscriptions::fetch_subscribed_users() instead.",
            DEBUG_DEVELOPER);

    \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum, $groupid, $context, $fields);
}

/**
 * Determine whether the reactforum is force subscribed.
 *
 * @param object $reactforum
 * @return bool
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::is_forcesubscribed() instead
 */
function reactforum_is_forcesubscribed($reactforum) {
    debugging("reactforum_is_forcesubscribed() has been deprecated, please use \\mod_reactforum\\subscriptions::is_forcesubscribed() instead.",
            DEBUG_DEVELOPER);

    global $DB;
    if (!isset($reactforum->forcesubscribe)) {
       $reactforum = $DB->get_field('reactforum', 'forcesubscribe', array('id' => $reactforum));
    }

    return \mod_reactforum\subscriptions::is_forcesubscribed($reactforum);
}

/**
 * Set the subscription mode for a reactforum.
 *
 * @param int $reactforumid
 * @param mixed $value
 * @return bool
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::set_subscription_mode() instead
 */
function reactforum_forcesubscribe($reactforumid, $value = 1) {
    debugging("reactforum_forcesubscribe() has been deprecated, please use \\mod_reactforum\\subscriptions::set_subscription_mode() instead.",
            DEBUG_DEVELOPER);

    return \mod_reactforum\subscriptions::set_subscription_mode($reactforumid, $value);
}

/**
 * Get the current subscription mode for the reactforum.
 *
 * @param int|stdClass $reactforumid
 * @param mixed $value
 * @return bool
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::get_subscription_mode() instead
 */
function reactforum_get_forcesubscribed($reactforum) {
    debugging("reactforum_get_forcesubscribed() has been deprecated, please use \\mod_reactforum\\subscriptions::get_subscription_mode() instead.",
            DEBUG_DEVELOPER);

    global $DB;
    if (!isset($reactforum->forcesubscribe)) {
       $reactforum = $DB->get_field('reactforum', 'forcesubscribe', array('id' => $reactforum));
    }

    return \mod_reactforum\subscriptions::get_subscription_mode($reactforumid, $value);
}

/**
 * Get a list of reactforums in the specified course in which a user can change
 * their subscription preferences.
 *
 * @param stdClass $course The course from which to find subscribable reactforums.
 * @return array
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::is_subscribed in combination wtih
 * \mod_reactforum\subscriptions::fill_subscription_cache_for_course instead.
 */
function reactforum_get_subscribed_reactforums($course) {
    debugging("reactforum_get_subscribed_reactforums() has been deprecated, please see " .
              "\\mod_reactforum\\subscriptions::is_subscribed::() " .
              " and \\mod_reactforum\\subscriptions::fill_subscription_cache_for_course instead.",
              DEBUG_DEVELOPER);

    global $USER, $CFG, $DB;
    $sql = "SELECT f.id
              FROM {reactforum} f
                   LEFT JOIN {reactforum_subscriptions} fs ON (fs.reactforum = f.id AND fs.userid = ?)
             WHERE f.course = ?
                   AND f.forcesubscribe <> ".REACTFORUM_DISALLOWSUBSCRIBE."
                   AND (f.forcesubscribe = ".REACTFORUM_FORCESUBSCRIBE." OR fs.id IS NOT NULL)";
    if ($subscribed = $DB->get_records_sql($sql, array($USER->id, $course->id))) {
        foreach ($subscribed as $s) {
            $subscribed[$s->id] = $s->id;
        }
        return $subscribed;
    } else {
        return array();
    }
}

/**
 * Returns an array of reactforums that the current user is subscribed to and is allowed to unsubscribe from
 *
 * @return array An array of unsubscribable reactforums
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::get_unsubscribable_reactforums() instead
 */
function reactforum_get_optional_subscribed_reactforums() {
    debugging("reactforum_get_optional_subscribed_reactforums() has been deprecated, please use \\mod_reactforum\\subscriptions::get_unsubscribable_reactforums() instead.",
            DEBUG_DEVELOPER);

    return \mod_reactforum\subscriptions::get_unsubscribable_reactforums();
}

/**
 * Get the list of potential subscribers to a reactforum.
 *
 * @param object $reactforumcontext the reactforum context.
 * @param integer $groupid the id of a group, or 0 for all groups.
 * @param string $fields the list of fields to return for each user. As for get_users_by_capability.
 * @param string $sort sort order. As for get_users_by_capability.
 * @return array list of users.
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::get_potential_subscribers() instead
 */
function reactforum_get_potential_subscribers($reactforumcontext, $groupid, $fields, $sort = '') {
    debugging("reactforum_get_potential_subscribers() has been deprecated, please use \\mod_reactforum\\subscriptions::get_potential_subscribers() instead.",
            DEBUG_DEVELOPER);

    \mod_reactforum\subscriptions::get_potential_subscribers($reactforumcontext, $groupid, $fields, $sort);
}

/**
 * Builds and returns the body of the email notification in plain text.
 *
 * @uses CONTEXT_MODULE
 * @param object $course
 * @param object $cm
 * @param object $reactforum
 * @param object $discussion
 * @param object $post
 * @param object $userfrom
 * @param object $userto
 * @param boolean $bare
 * @param string $replyaddress The inbound address that a user can reply to the generated e-mail with. [Since 2.8].
 * @return string The email body in plain text format.
 * @deprecated since Moodle 3.0 use \mod_reactforum\output\reactforum_post_email instead
 */
function reactforum_make_mail_text($course, $cm, $reactforum, $discussion, $post, $userfrom, $userto, $bare = false, $replyaddress = null) {
    global $PAGE;
    $renderable = new \mod_reactforum\output\reactforum_post_email(
        $course,
        $cm,
        $reactforum,
        $discussion,
        $post,
        $userfrom,
        $userto,
        reactforum_user_can_post($reactforum, $discussion, $userto, $cm, $course)
        );

    $modcontext = context_module::instance($cm->id);
    $renderable->viewfullnames = has_capability('moodle/site:viewfullnames', $modcontext, $userto->id);

    if ($bare) {
        $renderer = $PAGE->get_renderer('mod_reactforum', 'emaildigestfull', 'textemail');
    } else {
        $renderer = $PAGE->get_renderer('mod_reactforum', 'email', 'textemail');
    }

    debugging("reactforum_make_mail_text() has been deprecated, please use the \mod_reactforum\output\reactforum_post_email renderable instead.",
            DEBUG_DEVELOPER);

    return $renderer->render($renderable);
}

/**
 * Builds and returns the body of the email notification in html format.
 *
 * @param object $course
 * @param object $cm
 * @param object $reactforum
 * @param object $discussion
 * @param object $post
 * @param object $userfrom
 * @param object $userto
 * @param string $replyaddress The inbound address that a user can reply to the generated e-mail with. [Since 2.8].
 * @return string The email text in HTML format
 * @deprecated since Moodle 3.0 use \mod_reactforum\output\reactforum_post_email instead
 */
function reactforum_make_mail_html($course, $cm, $reactforum, $discussion, $post, $userfrom, $userto, $replyaddress = null) {
    return reactforum_make_mail_post($course,
        $cm,
        $reactforum,
        $discussion,
        $post,
        $userfrom,
        $userto,
        reactforum_user_can_post($reactforum, $discussion, $userto, $cm, $course)
    );
}

/**
 * Given the data about a posting, builds up the HTML to display it and
 * returns the HTML in a string.  This is designed for sending via HTML email.
 *
 * @param object $course
 * @param object $cm
 * @param object $reactforum
 * @param object $discussion
 * @param object $post
 * @param object $userfrom
 * @param object $userto
 * @param bool $ownpost
 * @param bool $reply
 * @param bool $link
 * @param bool $rate
 * @param string $footer
 * @return string
 * @deprecated since Moodle 3.0 use \mod_reactforum\output\reactforum_post_email instead
 */
function reactforum_make_mail_post($course, $cm, $reactforum, $discussion, $post, $userfrom, $userto,
                              $ownpost=false, $reply=false, $link=false, $rate=false, $footer="") {
    global $PAGE;
    $renderable = new \mod_reactforum\output\reactforum_post_email(
        $course,
        $cm,
        $reactforum,
        $discussion,
        $post,
        $userfrom,
        $userto,
        $reply);

    $modcontext = context_module::instance($cm->id);
    $renderable->viewfullnames = has_capability('moodle/site:viewfullnames', $modcontext, $userto->id);

    // Assume that this is being used as a standard reactforum email.
    $renderer = $PAGE->get_renderer('mod_reactforum', 'email', 'htmlemail');

    debugging("reactforum_make_mail_post() has been deprecated, please use the \mod_reactforum\output\reactforum_post_email renderable instead.",
            DEBUG_DEVELOPER);

    return $renderer->render($renderable);
}
