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
 * The module reactforums tests
 *
 * @package    mod_reactforum
 * @copyright  2013 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/reactforum/lib.php');

class mod_reactforum_subscriptions_testcase extends advanced_testcase {

    /**
     * Test setUp.
     */
    public function setUp() {
        // We must clear the subscription caches. This has to be done both before each test, and after in case of other
        // tests using these functions.
        \mod_reactforum\subscriptions::reset_reactforum_cache();
        \mod_reactforum\subscriptions::reset_discussion_cache();
    }

    /**
     * Test tearDown.
     */
    public function tearDown() {
        // We must clear the subscription caches. This has to be done both before each test, and after in case of other
        // tests using these functions.
        \mod_reactforum\subscriptions::reset_reactforum_cache();
        \mod_reactforum\subscriptions::reset_discussion_cache();
    }

    /**
     * Helper to create the required number of users in the specified
     * course.
     * Users are enrolled as students.
     *
     * @param stdClass $course The course object
     * @param integer $count The number of users to create
     * @return array The users created
     */
    protected function helper_create_users($course, $count) {
        $users = array();

        for ($i = 0; $i < $count; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Create a new discussion and post within the specified reactforum, as the
     * specified author.
     *
     * @param stdClass $reactforum The reactforum to post in
     * @param stdClass $author The author to post as
     * @param array An array containing the discussion object, and the post object
     */
    protected function helper_post_to_reactforum($reactforum, $author) {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_reactforum');

        // Create a discussion in the reactforum, and then add a post to that discussion.
        $record = new stdClass();
        $record->course = $reactforum->course;
        $record->userid = $author->id;
        $record->reactforum = $reactforum->id;
        $discussion = $generator->create_discussion($record);

        // Retrieve the post which was created by create_discussion.
        $post = $DB->get_record('reactforum_posts', array('discussion' => $discussion->id));

        return array($discussion, $post);
    }

    public function test_subscription_modes() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create a user enrolled in the course as a student.
        list($user) = $this->helper_create_users($course, 1);

        // Must be logged in as the current user.
        $this->setUser($user);

        \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_FORCESUBSCRIBE);
        $reactforum = $DB->get_record('reactforum', array('id' => $reactforum->id));
        $this->assertEquals(REACTFORUM_FORCESUBSCRIBE, \mod_reactforum\subscriptions::get_subscription_mode($reactforum));
        $this->assertTrue(\mod_reactforum\subscriptions::is_forcesubscribed($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribable($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::subscription_disabled($reactforum));

        \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_DISALLOWSUBSCRIBE);
        $reactforum = $DB->get_record('reactforum', array('id' => $reactforum->id));
        $this->assertEquals(REACTFORUM_DISALLOWSUBSCRIBE, \mod_reactforum\subscriptions::get_subscription_mode($reactforum));
        $this->assertTrue(\mod_reactforum\subscriptions::subscription_disabled($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribable($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::is_forcesubscribed($reactforum));

        \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_INITIALSUBSCRIBE);
        $reactforum = $DB->get_record('reactforum', array('id' => $reactforum->id));
        $this->assertEquals(REACTFORUM_INITIALSUBSCRIBE, \mod_reactforum\subscriptions::get_subscription_mode($reactforum));
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribable($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::subscription_disabled($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::is_forcesubscribed($reactforum));

        \mod_reactforum\subscriptions::set_subscription_mode($reactforum->id, REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $DB->get_record('reactforum', array('id' => $reactforum->id));
        $this->assertEquals(REACTFORUM_CHOOSESUBSCRIBE, \mod_reactforum\subscriptions::get_subscription_mode($reactforum));
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribable($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::subscription_disabled($reactforum));
        $this->assertFalse(\mod_reactforum\subscriptions::is_forcesubscribed($reactforum));
    }

    /**
     * Test fetching unsubscribable reactforums.
     */
    public function test_unsubscribable_reactforums() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        // Create a user enrolled in the course as a student.
        list($user) = $this->helper_create_users($course, 1);

        // Must be logged in as the current user.
        $this->setUser($user);

        // Without any subscriptions, there should be nothing returned.
        $result = \mod_reactforum\subscriptions::get_unsubscribable_reactforums();
        $this->assertEquals(0, count($result));

        // Create the reactforums.
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_FORCESUBSCRIBE);
        $forcereactforum = $this->getDataGenerator()->create_module('reactforum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_DISALLOWSUBSCRIBE);
        $disallowreactforum = $this->getDataGenerator()->create_module('reactforum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $choosereactforum = $this->getDataGenerator()->create_module('reactforum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $initialreactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // At present the user is only subscribed to the initial reactforum.
        $result = \mod_reactforum\subscriptions::get_unsubscribable_reactforums();
        $this->assertEquals(1, count($result));

        // Ensure that the user is enrolled in all of the reactforums except force subscribed.
        \mod_reactforum\subscriptions::subscribe_user($user->id, $disallowreactforum);
        \mod_reactforum\subscriptions::subscribe_user($user->id, $choosereactforum);

        $result = \mod_reactforum\subscriptions::get_unsubscribable_reactforums();
        $this->assertEquals(3, count($result));

        // Hide the reactforums.
        set_coursemodule_visible($forcereactforum->cmid, 0);
        set_coursemodule_visible($disallowreactforum->cmid, 0);
        set_coursemodule_visible($choosereactforum->cmid, 0);
        set_coursemodule_visible($initialreactforum->cmid, 0);
        $result = \mod_reactforum\subscriptions::get_unsubscribable_reactforums();
        $this->assertEquals(0, count($result));

        // Add the moodle/course:viewhiddenactivities capability to the student user.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $context = \context_course::instance($course->id);
        assign_capability('moodle/course:viewhiddenactivities', CAP_ALLOW, $roleids['student'], $context);
        $context->mark_dirty();

        // All of the unsubscribable reactforums should now be listed.
        $result = \mod_reactforum\subscriptions::get_unsubscribable_reactforums();
        $this->assertEquals(3, count($result));
    }

    /**
     * Test that toggling the reactforum-level subscription for a different user does not affect their discussion-level
     * subscriptions.
     */
    public function test_reactforum_subscribe_toggle_as_other() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create a user enrolled in the course as a student.
        list($author) = $this->helper_create_users($course, 1);

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);

        // Check that the user is currently not subscribed to the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // Check that the user is unsubscribed from the discussion too.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // Check that we have no records in either of the subscription tables.
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Subscribing to the reactforum should create a record in the subscriptions table, but not the reactforum discussion
        // subscriptions table.
        \mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum);
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Unsubscribing should remove the record from the reactforum subscriptions table, and not modify the reactforum
        // discussion subscriptions table.
        \mod_reactforum\subscriptions::unsubscribe_user($author->id, $reactforum);
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Enroling the user in the discussion should add one record to the reactforum discussion table without modifying the
        // form subscriptions.
        \mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Unsubscribing should remove the record from the reactforum subscriptions table, and not modify the reactforum
        // discussion subscriptions table.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Re-subscribe to the discussion so that we can check the effect of reactforum-level subscriptions.
        \mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Subscribing to the reactforum should have no effect on the reactforum discussion subscriptions table if the user did
        // not request the change themself.
        \mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum);
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Unsubscribing from the reactforum should have no effect on the reactforum discussion subscriptions table if the user
        // did not request the change themself.
        \mod_reactforum\subscriptions::unsubscribe_user($author->id, $reactforum);
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Subscribing to the reactforum should remove the per-discussion subscription preference if the user requested the
        // change themself.
        \mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum, null, true);
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Now unsubscribe from the current discussion whilst being subscribed to the reactforum as a whole.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Unsubscribing from the reactforum should remove the per-discussion subscription preference if the user requested the
        // change themself.
        \mod_reactforum\subscriptions::unsubscribe_user($author->id, $reactforum, null, true);
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Subscribe to the discussion.
        \mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Subscribe to the reactforum without removing the discussion preferences.
        \mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum);
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Unsubscribing from the discussion should result in a change.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

    }

    /**
     * Test that a user unsubscribed from a reactforum is not subscribed to it's discussions by default.
     */
    public function test_reactforum_discussion_subscription_reactforum_unsubscribed() {
        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create users enrolled in the course as students.
        list($author) = $this->helper_create_users($course, 1);

        // Check that the user is currently not subscribed to the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);

        // Check that the user is unsubscribed from the discussion too.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));
    }

    /**
     * Test that the act of subscribing to a reactforum subscribes the user to it's discussions by default.
     */
    public function test_reactforum_discussion_subscription_reactforum_subscribed() {
        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create users enrolled in the course as students.
        list($author) = $this->helper_create_users($course, 1);

        // Enrol the user in the reactforum.
        // If a subscription was added, we get the record ID.
        $this->assertInternalType('int', \mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum));

        // If we already have a subscription when subscribing the user, we get a boolean (true).
        $this->assertTrue(\mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum));

        // Check that the user is currently subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);

        // Check that the user is subscribed to the discussion too.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));
    }

    /**
     * Test that a user unsubscribed from a reactforum can be subscribed to a discussion.
     */
    public function test_reactforum_discussion_subscription_reactforum_unsubscribed_discussion_subscribed() {
        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create a user enrolled in the course as a student.
        list($author) = $this->helper_create_users($course, 1);

        // Check that the user is currently not subscribed to the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);

        // Attempting to unsubscribe from the discussion should not make a change.
        $this->assertFalse(\mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion));

        // Then subscribe them to the discussion.
        $this->assertTrue(\mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

        // Check that the user is still unsubscribed from the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // But subscribed to the discussion.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));
    }

    /**
     * Test that a user subscribed to a reactforum can be unsubscribed from a discussion.
     */
    public function test_reactforum_discussion_subscription_reactforum_subscribed_discussion_unsubscribed() {
        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create two users enrolled in the course as students.
        list($author) = $this->helper_create_users($course, 2);

        // Enrol the student in the reactforum.
        \mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum);

        // Check that the user is currently subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);

        // Then unsubscribe them from the discussion.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

        // Check that the user is still subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // But unsubscribed from the discussion.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));
    }

    /**
     * Test the effect of toggling the discussion subscription status when subscribed to the reactforum.
     */
    public function test_reactforum_discussion_toggle_reactforum_subscribed() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create two users enrolled in the course as students.
        list($author) = $this->helper_create_users($course, 2);

        // Enrol the student in the reactforum.
        \mod_reactforum\subscriptions::subscribe_user($author->id, $reactforum);

        // Check that the user is currently subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);

        // Check that the user is initially subscribed to that discussion.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // An attempt to subscribe again should result in a falsey return to indicate that no change was made.
        $this->assertFalse(\mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

        // And there should be no discussion subscriptions (and one reactforum subscription).
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // Then unsubscribe them from the discussion.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

        // Check that the user is still subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // An attempt to unsubscribe again should result in a falsey return to indicate that no change was made.
        $this->assertFalse(\mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion));

        // And there should be a discussion subscriptions (and one reactforum subscription).
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // But unsubscribed from the discussion.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be a record in the discussion subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // And one in the reactforum subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // Now subscribe the user again to the discussion.
        \mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);

        // Check that the user is still subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // And is subscribed to the discussion again.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be no record in the discussion subscription tracking table.
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // And one in the reactforum subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // And unsubscribe again.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

        // Check that the user is still subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // But unsubscribed from the discussion.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be a record in the discussion subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // And one in the reactforum subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // And subscribe the user again to the discussion.
        \mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);

        // Check that the user is still subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // And is subscribed to the discussion again.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be no record in the discussion subscription tracking table.
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // And one in the reactforum subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // And unsubscribe again.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

        // Check that the user is still subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // But unsubscribed from the discussion.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be a record in the discussion subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // And one in the reactforum subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // Now unsubscribe the user from the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::unsubscribe_user($author->id, $reactforum, null, true));

        // This removes both the reactforum_subscriptions, and the reactforum_discussion_subs records.
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $author->id,
            'reactforum'         => $reactforum->id,
        )));

        // And should have reset the discussion cache value.
        $result = \mod_reactforum\subscriptions::fetch_discussion_subscription($reactforum->id, $author->id);
        $this->assertInternalType('array', $result);
        $this->assertFalse(isset($result[$discussion->id]));
    }

    /**
     * Test the effect of toggling the discussion subscription status when unsubscribed from the reactforum.
     */
    public function test_reactforum_discussion_toggle_reactforum_unsubscribed() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create two users enrolled in the course as students.
        list($author) = $this->helper_create_users($course, 2);

        // Check that the user is currently unsubscribed to the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);

        // Check that the user is initially unsubscribed to that discussion.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // Then subscribe them to the discussion.
        $this->assertTrue(\mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

        // An attempt to subscribe again should result in a falsey return to indicate that no change was made.
        $this->assertFalse(\mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

        // Check that the user is still unsubscribed from the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // But subscribed to the discussion.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be a record in the discussion subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // Now unsubscribe the user again from the discussion.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

        // Check that the user is still unsubscribed from the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // And is unsubscribed from the discussion again.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be no record in the discussion subscription tracking table.
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // And subscribe the user again to the discussion.
        \mod_reactforum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);

        // Check that the user is still unsubscribed from the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // And is subscribed to the discussion again.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be a record in the discussion subscription tracking table.
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

        // And unsubscribe again.
        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

        // Check that the user is still unsubscribed from the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum));

        // But unsubscribed from the discussion.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($author->id, $reactforum, $discussion->id));

        // There should be no record in the discussion subscription tracking table.
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
    }

    /**
     * Test that the correct users are returned when fetching subscribed users from a reactforum where users can choose to
     * subscribe and unsubscribe.
     */
    public function test_fetch_subscribed_users_subscriptions() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum. where users are initially subscribed.
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some user enrolled in the course as a student.
        $usercount = 5;
        $users = $this->helper_create_users($course, $usercount);

        // All users should be subscribed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount, count($subscribers));

        // Subscribe the guest user too to the reactforum - they should never be returned by this function.
        $this->getDataGenerator()->enrol_user($CFG->siteguest, $course->id);
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount, count($subscribers));

        // Unsubscribe 2 users.
        $unsubscribedcount = 2;
        for ($i = 0; $i < $unsubscribedcount; $i++) {
            \mod_reactforum\subscriptions::unsubscribe_user($users[$i]->id, $reactforum);
        }

        // The subscription count should now take into account those users who have been unsubscribed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));
    }

    /**
     * Test that the correct users are returned hwen fetching subscribed users from a reactforum where users are forcibly
     * subscribed.
     */
    public function test_fetch_subscribed_users_forced() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum. where users are initially subscribed.
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_FORCESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some user enrolled in the course as a student.
        $usercount = 5;
        $users = $this->helper_create_users($course, $usercount);

        // All users should be subscribed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount, count($subscribers));
    }

    /**
     * Test that unusual combinations of discussion subscriptions do not affect the subscribed user list.
     */
    public function test_fetch_subscribed_users_discussion_subscriptions() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum. where users are initially subscribed.
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some user enrolled in the course as a student.
        $usercount = 5;
        $users = $this->helper_create_users($course, $usercount);

        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $users[0]);

        // All users should be subscribed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount, count($subscribers));
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum, 0, null, null, true);
        $this->assertEquals($usercount, count($subscribers));

        \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($users[0]->id, $discussion);

        // All users should be subscribed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount, count($subscribers));

        // All users should be subscribed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum, 0, null, null, true);
        $this->assertEquals($usercount, count($subscribers));

        // Manually insert an extra subscription for one of the users.
        $record = new stdClass();
        $record->userid = $users[2]->id;
        $record->reactforum = $reactforum->id;
        $record->discussion = $discussion->id;
        $record->preference = time();
        $DB->insert_record('reactforum_discussion_subs', $record);

        // The discussion count should not have changed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount, count($subscribers));
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum, 0, null, null, true);
        $this->assertEquals($usercount, count($subscribers));

        // Unsubscribe 2 users.
        $unsubscribedcount = 2;
        for ($i = 0; $i < $unsubscribedcount; $i++) {
            \mod_reactforum\subscriptions::unsubscribe_user($users[$i]->id, $reactforum);
        }

        // The subscription count should now take into account those users who have been unsubscribed.
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum, 0, null, null, true);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));

        // Now subscribe one of those users back to the discussion.
        $subscribeddiscussionusers = 1;
        for ($i = 0; $i < $subscribeddiscussionusers; $i++) {
            \mod_reactforum\subscriptions::subscribe_user_to_discussion($users[$i]->id, $discussion);
        }
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));
        $subscribers = \mod_reactforum\subscriptions::fetch_subscribed_users($reactforum, 0, null, null, true);
        $this->assertEquals($usercount - $unsubscribedcount + $subscribeddiscussionusers, count($subscribers));
    }

    /**
     * Test whether a user is force-subscribed to a reactforum.
     */
    public function test_force_subscribed_to_reactforum() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_FORCESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create a user enrolled in the course as a student.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $roleids['student']);

        // Check that the user is currently subscribed to the reactforum.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum));

        // Remove the allowforcesubscribe capability from the user.
        $cm = get_coursemodule_from_instance('reactforum', $reactforum->id);
        $context = \context_module::instance($cm->id);
        assign_capability('mod/reactforum:allowforcesubscribe', CAP_PROHIBIT, $roleids['student'], $context);
        $context->mark_dirty();
        $this->assertFalse(has_capability('mod/reactforum:allowforcesubscribe', $context, $user->id));

        // Check that the user is no longer subscribed to the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum));
    }

    /**
     * Test that the subscription cache can be pre-filled.
     */
    public function test_subscription_cache_prefill() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some users.
        $users = $this->helper_create_users($course, 20);

        // Reset the subscription cache.
        \mod_reactforum\subscriptions::reset_reactforum_cache();

        // Filling the subscription cache should only use a single query.
        $startcount = $DB->perf_get_reads();
        $this->assertNull(\mod_reactforum\subscriptions::fill_subscription_cache($reactforum->id));
        $postfillcount = $DB->perf_get_reads();
        $this->assertEquals(1, $postfillcount - $startcount);

        // Now fetch some subscriptions from that reactforum - these should use
        // the cache and not perform additional queries.
        foreach ($users as $user) {
            $this->assertTrue(\mod_reactforum\subscriptions::fetch_subscription_cache($reactforum->id, $user->id));
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(0, $finalcount - $postfillcount);
    }

    /**
     * Test that the subscription cache can filled user-at-a-time.
     */
    public function test_subscription_cache_fill() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some users.
        $users = $this->helper_create_users($course, 20);

        // Reset the subscription cache.
        \mod_reactforum\subscriptions::reset_reactforum_cache();

        // Filling the subscription cache should only use a single query.
        $startcount = $DB->perf_get_reads();

        // Fetch some subscriptions from that reactforum - these should not use the cache and will perform additional queries.
        foreach ($users as $user) {
            $this->assertTrue(\mod_reactforum\subscriptions::fetch_subscription_cache($reactforum->id, $user->id));
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(20, $finalcount - $startcount);
    }

    /**
     * Test that the discussion subscription cache can filled course-at-a-time.
     */
    public function test_discussion_subscription_cache_fill_for_course() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        // Create the reactforums.
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_DISALLOWSUBSCRIBE);
        $disallowreactforum = $this->getDataGenerator()->create_module('reactforum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $choosereactforum = $this->getDataGenerator()->create_module('reactforum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $initialreactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some users and keep a reference to the first user.
        $users = $this->helper_create_users($course, 20);
        $user = reset($users);

        // Reset the subscription caches.
        \mod_reactforum\subscriptions::reset_reactforum_cache();

        $startcount = $DB->perf_get_reads();
        $result = \mod_reactforum\subscriptions::fill_subscription_cache_for_course($course->id, $user->id);
        $this->assertNull($result);
        $postfillcount = $DB->perf_get_reads();
        $this->assertEquals(1, $postfillcount - $startcount);
        $this->assertFalse(\mod_reactforum\subscriptions::fetch_subscription_cache($disallowreactforum->id, $user->id));
        $this->assertFalse(\mod_reactforum\subscriptions::fetch_subscription_cache($choosereactforum->id, $user->id));
        $this->assertTrue(\mod_reactforum\subscriptions::fetch_subscription_cache($initialreactforum->id, $user->id));
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(0, $finalcount - $postfillcount);

        // Test for all users.
        foreach ($users as $user) {
            $result = \mod_reactforum\subscriptions::fill_subscription_cache_for_course($course->id, $user->id);
            $this->assertFalse(\mod_reactforum\subscriptions::fetch_subscription_cache($disallowreactforum->id, $user->id));
            $this->assertFalse(\mod_reactforum\subscriptions::fetch_subscription_cache($choosereactforum->id, $user->id));
            $this->assertTrue(\mod_reactforum\subscriptions::fetch_subscription_cache($initialreactforum->id, $user->id));
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(count($users), $finalcount - $postfillcount);
    }

    /**
     * Test that the discussion subscription cache can be forcibly updated for a user.
     */
    public function test_discussion_subscription_cache_prefill() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some users.
        $users = $this->helper_create_users($course, 20);

        // Post some discussions to the reactforum.
        $discussions = array();
        $author = $users[0];
        for ($i = 0; $i < 20; $i++) {
            list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);
            $discussions[] = $discussion;
        }

        // Unsubscribe half the users from the half the discussions.
        $reactforumcount = 0;
        $usercount = 0;
        foreach ($discussions as $data) {
            if ($reactforumcount % 2) {
                continue;
            }
            foreach ($users as $user) {
                if ($usercount % 2) {
                    continue;
                }
                \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);
                $usercount++;
            }
            $reactforumcount++;
        }

        // Reset the subscription caches.
        \mod_reactforum\subscriptions::reset_reactforum_cache();
        \mod_reactforum\subscriptions::reset_discussion_cache();

        // Filling the discussion subscription cache should only use a single query.
        $startcount = $DB->perf_get_reads();
        $this->assertNull(\mod_reactforum\subscriptions::fill_discussion_subscription_cache($reactforum->id));
        $postfillcount = $DB->perf_get_reads();
        $this->assertEquals(1, $postfillcount - $startcount);

        // Now fetch some subscriptions from that reactforum - these should use
        // the cache and not perform additional queries.
        foreach ($users as $user) {
            $result = \mod_reactforum\subscriptions::fetch_discussion_subscription($reactforum->id, $user->id);
            $this->assertInternalType('array', $result);
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(0, $finalcount - $postfillcount);
    }

    /**
     * Test that the discussion subscription cache can filled user-at-a-time.
     */
    public function test_discussion_subscription_cache_fill() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create some users.
        $users = $this->helper_create_users($course, 20);

        // Post some discussions to the reactforum.
        $discussions = array();
        $author = $users[0];
        for ($i = 0; $i < 20; $i++) {
            list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $author);
            $discussions[] = $discussion;
        }

        // Unsubscribe half the users from the half the discussions.
        $reactforumcount = 0;
        $usercount = 0;
        foreach ($discussions as $data) {
            if ($reactforumcount % 2) {
                continue;
            }
            foreach ($users as $user) {
                if ($usercount % 2) {
                    continue;
                }
                \mod_reactforum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);
                $usercount++;
            }
            $reactforumcount++;
        }

        // Reset the subscription caches.
        \mod_reactforum\subscriptions::reset_reactforum_cache();
        \mod_reactforum\subscriptions::reset_discussion_cache();

        $startcount = $DB->perf_get_reads();

        // Now fetch some subscriptions from that reactforum - these should use
        // the cache and not perform additional queries.
        foreach ($users as $user) {
            $result = \mod_reactforum\subscriptions::fetch_discussion_subscription($reactforum->id, $user->id);
            $this->assertInternalType('array', $result);
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(20, $finalcount - $startcount);
    }

    /**
     * Test that after toggling the reactforum subscription as another user,
     * the discussion subscription functionality works as expected.
     */
    public function test_reactforum_subscribe_toggle_as_other_repeat_subscriptions() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create a user enrolled in the course as a student.
        list($user) = $this->helper_create_users($course, 1);

        // Post a discussion to the reactforum.
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $user);

        // Confirm that the user is currently not subscribed to the reactforum.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum));

        // Confirm that the user is unsubscribed from the discussion too.
        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum, $discussion->id));

        // Confirm that we have no records in either of the subscription tables.
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $user->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));

        // Subscribing to the reactforum should create a record in the subscriptions table, but not the reactforum discussion
        // subscriptions table.
        \mod_reactforum\subscriptions::subscribe_user($user->id, $reactforum);
        $this->assertEquals(1, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $user->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(0, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));

        // Now unsubscribe from the discussion. This should return true.
        $this->assertTrue(\mod_reactforum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion));

        // Attempting to unsubscribe again should return false because no change was made.
        $this->assertFalse(\mod_reactforum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion));

        // Subscribing to the discussion again should return truthfully as the subscription preference was removed.
        $this->assertTrue(\mod_reactforum\subscriptions::subscribe_user_to_discussion($user->id, $discussion));

        // Attempting to subscribe again should return false because no change was made.
        $this->assertFalse(\mod_reactforum\subscriptions::subscribe_user_to_discussion($user->id, $discussion));

        // Now unsubscribe from the discussion. This should return true once more.
        $this->assertTrue(\mod_reactforum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion));

        // And unsubscribing from the reactforum but not as a request from the user should maintain their preference.
        \mod_reactforum\subscriptions::unsubscribe_user($user->id, $reactforum);

        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $user->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));

        // Subscribing to the discussion should return truthfully because a change was made.
        $this->assertTrue(\mod_reactforum\subscriptions::subscribe_user_to_discussion($user->id, $discussion));
        $this->assertEquals(0, $DB->count_records('reactforum_subscriptions', array(
            'userid'        => $user->id,
            'reactforum'         => $reactforum->id,
        )));
        $this->assertEquals(1, $DB->count_records('reactforum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));
    }

    /**
     * Test that providing a context_module instance to is_subscribed does not result in additional lookups to retrieve
     * the context_module.
     */
    public function test_is_subscribed_cm() {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => REACTFORUM_FORCESUBSCRIBE);
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        // Create a user enrolled in the course as a student.
        list($user) = $this->helper_create_users($course, 1);

        // Retrieve the $cm now.
        $cm = get_fast_modinfo($reactforum->course)->instances['reactforum'][$reactforum->id];

        // Reset get_fast_modinfo.
        get_fast_modinfo(0, 0, true);

        // Call is_subscribed without passing the $cmid - this should result in a lookup and filling of some of the
        // caches. This provides us with consistent data to start from.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum));
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum));

        // Make a note of the number of DB calls.
        $basecount = $DB->perf_get_reads();

        // Call is_subscribed - it should give return the correct result (False), and result in no additional queries.
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum, null, $cm));

        // The capability check does require some queries, so we don't test it directly.
        // We don't assert here because this is dependant upon linked code which could change at any time.
        $suppliedcmcount = $DB->perf_get_reads() - $basecount;

        // Call is_subscribed without passing the $cmid now - this should result in a lookup.
        get_fast_modinfo(0, 0, true);
        $basecount = $DB->perf_get_reads();
        $this->assertTrue(\mod_reactforum\subscriptions::is_subscribed($user->id, $reactforum));
        $calculatedcmcount = $DB->perf_get_reads() - $basecount;

        // There should be more queries than when we performed the same check a moment ago.
        $this->assertGreaterThan($suppliedcmcount, $calculatedcmcount);
    }

    public function is_subscribable_reactforums() {
        return [
            [
                'forcesubscribe' => REACTFORUM_DISALLOWSUBSCRIBE,
            ],
            [
                'forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE,
            ],
            [
                'forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE,
            ],
            [
                'forcesubscribe' => REACTFORUM_FORCESUBSCRIBE,
            ],
        ];
    }

    public function is_subscribable_provider() {
        $data = [];
        foreach ($this->is_subscribable_reactforums() as $reactforum) {
            $data[] = [$reactforum];
        }

        return $data;
    }

    /**
     * @dataProvider is_subscribable_provider
     */
    public function test_is_subscribable_logged_out($options) {
        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();
        $options['course'] = $course->id;
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribable($reactforum));
    }

    /**
     * @dataProvider is_subscribable_provider
     */
    public function test_is_subscribable_is_guest($options) {
        global $DB;
        $this->resetAfterTest(true);

        $guest = $DB->get_record('user', array('username'=>'guest'));
        $this->setUser($guest);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();
        $options['course'] = $course->id;
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        $this->assertFalse(\mod_reactforum\subscriptions::is_subscribable($reactforum));
    }

    public function is_subscribable_loggedin_provider() {
        return [
            [
                ['forcesubscribe' => REACTFORUM_DISALLOWSUBSCRIBE],
                false,
            ],
            [
                ['forcesubscribe' => REACTFORUM_CHOOSESUBSCRIBE],
                true,
            ],
            [
                ['forcesubscribe' => REACTFORUM_INITIALSUBSCRIBE],
                true,
            ],
            [
                ['forcesubscribe' => REACTFORUM_FORCESUBSCRIBE],
                false,
            ],
        ];
    }

    /**
     * @dataProvider is_subscribable_loggedin_provider
     */
    public function test_is_subscribable_loggedin($options, $expect) {
        $this->resetAfterTest(true);

        // Create a course, with a reactforum.
        $course = $this->getDataGenerator()->create_course();
        $options['course'] = $course->id;
        $reactforum = $this->getDataGenerator()->create_module('reactforum', $options);

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->setUser($user);

        $this->assertEquals($expect, \mod_reactforum\subscriptions::is_subscribable($reactforum));
    }
}
