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
 * @copyright 2014 Andrew Robert Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Deprecated a very long time ago.

/**
 * @deprecated since Moodle 1.1 - please do not use this function any more.
 */
function reactforum_count_unrated_posts() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}


// Since Moodle 1.5.

/**
 * @deprecated since Moodle 1.5 - please do not use this function any more.
 */
function reactforum_tp_count_discussion_read_records() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 1.5 - please do not use this function any more.
 */
function reactforum_get_user_discussions() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}


// Since Moodle 1.6.

/**
 * @deprecated since Moodle 1.6 - please do not use this function any more.
 */
function reactforum_tp_count_reactforum_posts() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 1.6 - please do not use this function any more.
 */
function reactforum_tp_count_reactforum_read_records() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}


// Since Moodle 1.7.

/**
 * @deprecated since Moodle 1.7 - please do not use this function any more.
 */
function reactforum_get_open_modes() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}


// Since Moodle 1.9.

/**
 * @deprecated since Moodle 1.9 MDL-13303 - please do not use this function any more.
 */
function reactforum_get_child_posts() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 1.9 MDL-13303 - please do not use this function any more.
 */
function reactforum_get_discussion_posts() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}


// Since Moodle 2.0.

/**
 * @deprecated since Moodle 2.0 MDL-21657 - please do not use this function any more.
 */
function reactforum_get_ratings() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 2.0 MDL-14632 - please do not use this function any more.
 */
function reactforum_get_tracking_link() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 2.0 MDL-14113 - please do not use this function any more.
 */
function reactforum_tp_count_discussion_unread_posts() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 2.0 MDL-23479 - please do not use this function any more.
 */
function reactforum_convert_to_roles() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 2.0 MDL-14113 - please do not use this function any more.
 */
function reactforum_tp_get_read_records() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 2.0 MDL-14113 - please do not use this function any more.
 */
function reactforum_tp_get_discussion_read_records() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

// Deprecated in 2.3.

/**
 * @deprecated since Moodle 2.3 MDL-33166 - please do not use this function any more.
 */
function reactforum_user_enrolled() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}


// Deprecated in 2.4.

/**
 * @deprecated since Moodle 2.4 use reactforum_user_can_see_post() instead
 */
function reactforum_user_can_view_post() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
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
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. '
        . 'Please use shorten_text($message, $CFG->reactforum_shortpost) instead.');
}

// Deprecated in 2.8.

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::is_subscribed() instead
 */
function reactforum_is_subscribed() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more.');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::subscribe_user() instead
 */
function reactforum_subscribe() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::subscribe_user() instead');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::unsubscribe_user() instead
 */
function reactforum_unsubscribe() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::unsubscribe_user() instead');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::fetch_subscribed_users() instead
  */
function reactforum_subscribed_users() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::fetch_subscribed_users() instead');
}

/**
 * Determine whether the reactforum is force subscribed.
 *
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::is_forcesubscribed() instead
 */
function reactforum_is_forcesubscribed($reactforum) {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::is_forcesubscribed() instead');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::set_subscription_mode() instead
 */
function reactforum_forcesubscribe($reactforumid, $value = 1) {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::set_subscription_mode() instead');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::get_subscription_mode() instead
 */
function reactforum_get_forcesubscribed($reactforum) {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::set_subscription_mode() instead');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::is_subscribed in combination wtih
 * \mod_reactforum\subscriptions::fill_subscription_cache_for_course instead.
 */
function reactforum_get_subscribed_reactforums() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::is_subscribed(), and '
        . \mod_reactforum\subscriptions::class . '::fill_subscription_cache_for_course() instead');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::get_unsubscribable_reactforums() instead
 */
function reactforum_get_optional_subscribed_reactforums() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::get_unsubscribable_reactforums() instead');
}

/**
 * @deprecated since Moodle 2.8 use \mod_reactforum\subscriptions::get_potential_subscribers() instead
 */
function reactforum_get_potential_subscribers() {
    throw new coding_exception(__FUNCTION__ . '() can not be used any more. Please use '
        . \mod_reactforum\subscriptions::class . '::get_potential_subscribers() instead');
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
