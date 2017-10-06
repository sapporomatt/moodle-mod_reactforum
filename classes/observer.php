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
 * Event observers used in reactforum.
 *
 * @package    mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_reactforum.
 */
class mod_reactforum_observer {

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB;

        // NOTE: this has to be as fast as possible.
        // Get user enrolment info from event.
        $cp = (object)$event->other['userenrolment'];
        if ($cp->lastenrol) {
            if (!$reactforums = $DB->get_records('reactforum', array('course' => $cp->courseid), '', 'id')) {
                return;
            }
            list($reactforumselect, $params) = $DB->get_in_or_equal(array_keys($reactforums), SQL_PARAMS_NAMED);
            $params['userid'] = $cp->userid;

            // Delete reactions data
            foreach ($reactforums as $reactforum)
            {
                $discussions = $DB->get_records('reactforum_discussions', array('reactforum' => $reactforum->id), '', 'id');
                foreach ($discussions as $discussion)
                {
                    $reactions = $DB->get_records('reactforum_reactions', array('discussion_id' => $discussion->id), '', 'id');

                    foreach ($reactions as $reaction)
                    {
                        $DB->delete_records("reactforum_user_reactions", array("reaction_id" => $reaction->id, "user_id" => $cp->userid));
                    }
                }
            }

            $DB->delete_records_select('reactforum_digests', 'userid = :userid AND reactforum '.$reactforumselect, $params);
            $DB->delete_records_select('reactforum_subscriptions', 'userid = :userid AND reactforum '.$reactforumselect, $params);
            $DB->delete_records_select('reactforum_track_prefs', 'userid = :userid AND reactforumid '.$reactforumselect, $params);
            $DB->delete_records_select('reactforum_read', 'userid = :userid AND reactforumid '.$reactforumselect, $params);
        }
    }

    /**
     * Observer for role_assigned event.
     *
     * @param \core\event\role_assigned $event
     * @return void
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        global $CFG, $DB;

        $context = context::instance_by_id($event->contextid, MUST_EXIST);

        // If contextlevel is course then only subscribe user. Role assignment
        // at course level means user is enroled in course and can subscribe to reactforum.
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        // ReactForum lib required for the constant used below.
        require_once($CFG->dirroot . '/mod/reactforum/lib.php');

        $userid = $event->relateduserid;
        $sql = "SELECT f.id, f.course as course, cm.id AS cmid, f.forcesubscribe
                  FROM {reactforum} f
                  JOIN {course_modules} cm ON (cm.instance = f.id)
                  JOIN {modules} m ON (m.id = cm.module)
             LEFT JOIN {reactforum_subscriptions} fs ON (fs.reactforum = f.id AND fs.userid = :userid)
                 WHERE f.course = :courseid
                   AND f.forcesubscribe = :initial
                   AND m.name = 'reactforum'
                   AND fs.id IS NULL";
        $params = array('courseid' => $context->instanceid, 'userid' => $userid, 'initial' => REACTFORUM_INITIALSUBSCRIBE);

        $reactforums = $DB->get_records_sql($sql, $params);
        foreach ($reactforums as $reactforum) {
            // If user doesn't have allowforcesubscribe capability then don't subscribe.
            $modcontext = context_module::instance($reactforum->cmid);
            if (has_capability('mod/reactforum:allowforcesubscribe', $modcontext, $userid)) {
                \mod_reactforum\subscriptions::subscribe_user($userid, $reactforum, $modcontext);
            }
        }
    }

    /**
     * Observer for \core\event\course_module_created event.
     *
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $CFG;

        if ($event->other['modulename'] === 'reactforum') {
            // Include the reactforum library to make use of the reactforum_instance_created function.
            require_once($CFG->dirroot . '/mod/reactforum/lib.php');

            $reactforum = $event->get_record_snapshot('reactforum', $event->other['instanceid']);
            reactforum_instance_created($event->get_context(), $reactforum);
        }
    }
}
