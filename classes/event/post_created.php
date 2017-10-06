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
 * The mod_reactforum post created event.
 *
 * @package    mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_reactforum\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_reactforum post created event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - int discussionid: The discussion id the post is part of.
 *      - int reactforumid: The reactforum id the post is part of.
 *      - string reactforumtype: The type of reactforum the post is part of.
 * }
 *
 * @package    mod_reactforum
 * @since      Moodle 2.7
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post_created extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'reactforum_posts';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has created the post with id '$this->objectid' in the discussion with " .
            "id '{$this->other['discussionid']}' in the reactforum with course module id '$this->contextinstanceid'.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventpostcreated', 'mod_reactforum');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        if ($this->other['reactforumtype'] == 'single') {
            // Single discussion reactforums are an exception. We show
            // the reactforum itself since it only has one discussion
            // thread.
            $url = new \moodle_url('/mod/reactforum/view.php', array('f' => $this->other['reactforumid']));
        } else {
            $url = new \moodle_url('/mod/reactforum/discuss.php', array('d' => $this->other['discussionid']));
        }
        $url->set_anchor('p'.$this->objectid);
        return $url;
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        // The legacy log table expects a relative path to /mod/reactforum/.
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/reactforum/'));

        return array($this->courseid, 'reactforum', 'add post', $logurl, $this->other['reactforumid'], $this->contextinstanceid);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['discussionid'])) {
            throw new \coding_exception('The \'discussionid\' value must be set in other.');
        }

        if (!isset($this->other['reactforumid'])) {
            throw new \coding_exception('The \'reactforumid\' value must be set in other.');
        }

        if (!isset($this->other['reactforumtype'])) {
            throw new \coding_exception('The \'reactforumtype\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'reactforum_posts', 'restore' => 'reactforum_post');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['reactforumid'] = array('db' => 'reactforum', 'restore' => 'reactforum');
        $othermapped['discussionid'] = array('db' => 'reactforum_discussions', 'restore' => 'reactforum_discussion');

        return $othermapped;
    }
}
