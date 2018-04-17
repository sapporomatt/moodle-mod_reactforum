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
 * @package    mod_reactforum
 * @subpackage backup-moodle2
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/reactforum/backup/moodle2/restore_reactforum_stepslib.php'); // Because it exists (must)

/**
 * reactforum restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_reactforum_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_reactforum_activity_structure_step('reactforum_structure', 'reactforum.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('reactforum', array('intro'), 'reactforum');
        $contents[] = new restore_decode_content('reactforum_posts', array('message'), 'reactforum_post');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        // List of reactforums in course
        $rules[] = new restore_decode_rule('REACTFORUMINDEX', '/mod/reactforum/index.php?id=$1', 'course');
        // ReactForum by cm->id and reactforum->id
        $rules[] = new restore_decode_rule('REACTFORUMVIEWBYID', '/mod/reactforum/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('REACTFORUMVIEWBYF', '/mod/reactforum/view.php?f=$1', 'reactforum');
        // Link to reactforum discussion
        $rules[] = new restore_decode_rule('REACTFORUMDISCUSSIONVIEW', '/mod/reactforum/discuss.php?d=$1', 'reactforum_discussion');
        // Link to discussion with parent and with anchor posts
        $rules[] = new restore_decode_rule('REACTFORUMDISCUSSIONVIEWPARENT', '/mod/reactforum/discuss.php?d=$1&parent=$2',
            array('reactforum_discussion', 'reactforum_post'));
        $rules[] = new restore_decode_rule('REACTFORUMDISCUSSIONVIEWINSIDE', '/mod/reactforum/discuss.php?d=$1#$2',
            array('reactforum_discussion', 'reactforum_post'));

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * reactforum logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('reactforum', 'add', 'view.php?id={course_module}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'update', 'view.php?id={course_module}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'view', 'view.php?id={course_module}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'view reactforum', 'view.php?id={course_module}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'mark read', 'view.php?f={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'start tracking', 'view.php?f={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'stop tracking', 'view.php?f={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'subscribe', 'view.php?f={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'unsubscribe', 'view.php?f={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'subscriber', 'subscribers.php?id={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'subscribers', 'subscribers.php?id={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'view subscribers', 'subscribers.php?id={reactforum}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'add discussion', 'discuss.php?d={reactforum_discussion}', '{reactforum_discussion}');
        $rules[] = new restore_log_rule('reactforum', 'view discussion', 'discuss.php?d={reactforum_discussion}', '{reactforum_discussion}');
        $rules[] = new restore_log_rule('reactforum', 'move discussion', 'discuss.php?d={reactforum_discussion}', '{reactforum_discussion}');
        $rules[] = new restore_log_rule('reactforum', 'delete discussi', 'view.php?id={course_module}', '{reactforum}',
            null, 'delete discussion');
        $rules[] = new restore_log_rule('reactforum', 'delete discussion', 'view.php?id={course_module}', '{reactforum}');
        $rules[] = new restore_log_rule('reactforum', 'add post', 'discuss.php?d={reactforum_discussion}&parent={reactforum_post}', '{reactforum_post}');
        $rules[] = new restore_log_rule('reactforum', 'update post', 'discuss.php?d={reactforum_discussion}#p{reactforum_post}&parent={reactforum_post}', '{reactforum_post}');
        $rules[] = new restore_log_rule('reactforum', 'update post', 'discuss.php?d={reactforum_discussion}&parent={reactforum_post}', '{reactforum_post}');
        $rules[] = new restore_log_rule('reactforum', 'prune post', 'discuss.php?d={reactforum_discussion}', '{reactforum_post}');
        $rules[] = new restore_log_rule('reactforum', 'delete post', 'discuss.php?d={reactforum_discussion}', '[post]');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('reactforum', 'view reactforums', 'index.php?id={course}', null);
        $rules[] = new restore_log_rule('reactforum', 'subscribeall', 'index.php?id={course}', '{course}');
        $rules[] = new restore_log_rule('reactforum', 'unsubscribeall', 'index.php?id={course}', '{course}');
        $rules[] = new restore_log_rule('reactforum', 'user report', 'user.php?course={course}&id={user}&mode=[mode]', '{user}');
        $rules[] = new restore_log_rule('reactforum', 'search', 'search.php?id={course}&search=[searchenc]', '[search]');

        return $rules;
    }
}
