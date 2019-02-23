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
 * Tests for the reactforum implementation of the RSS component.
 *
 * @package    mod_reactforum
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/helper.php');
require_once("{$CFG->dirroot}/mod/reactforum/rsslib.php");

/**
 * Tests for the reactforum implementation of the RSS component.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_reactforum_rsslib_testcase extends advanced_testcase {
    // Include the mod_reactforum test helpers.
    // This includes functions to create reactforums, users, discussions, and posts.
    use helper;

    /**
     * Ensure that deleted posts are not included.
     */
    public function test_reactforum_rss_feed_discussions_sql_respect_deleted() {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $reactforum = $this->getDataGenerator()->create_module('reactforum', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('reactforum', $reactforum->id);

        list($user, $otheruser) = $this->helper_create_users($course, 2);

        // Post twice.
        $this->helper_post_to_reactforum($reactforum, $otheruser);
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $otheruser);

        list($sql, $params) = reactforum_rss_feed_discussions_sql($reactforum, $cm);
        $discussions = $DB->get_records_sql($sql, $params);
        $this->assertCount(2, $discussions);

        $post->deleted = 1;
        $DB->update_record('reactforum_posts', $post);

        list($sql, $params) = reactforum_rss_feed_discussions_sql($reactforum, $cm);
        $discussions = $DB->get_records_sql($sql, $params);
        $this->assertCount(1, $discussions);
    }


    /**
     * Ensure that deleted posts are not included.
     */
    public function test_reactforum_rss_feed_posts_sql_respect_deleted() {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $reactforum = $this->getDataGenerator()->create_module('reactforum', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('reactforum', $reactforum->id);

        list($user, $otheruser) = $this->helper_create_users($course, 2);

        // Post twice.
        $this->helper_post_to_reactforum($reactforum, $otheruser);
        list($discussion, $post) = $this->helper_post_to_reactforum($reactforum, $otheruser);

        list($sql, $params) = reactforum_rss_feed_posts_sql($reactforum, $cm);
        $posts = $DB->get_records_sql($sql, $params);
        $this->assertCount(2, $posts);

        $post->deleted = 1;
        $DB->update_record('reactforum_posts', $post);

        list($sql, $params) = reactforum_rss_feed_posts_sql($reactforum, $cm);
        $posts = $DB->get_records_sql($sql, $params);
        $this->assertCount(1, $posts);
    }
}
