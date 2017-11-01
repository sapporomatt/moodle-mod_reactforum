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
 * A type of reactforum.
 *
 * @package    mod_reactforum
 * @copyright  2014 Andrew Robert Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');

/**
 * User selector control for removing subscribed users
 * @package   mod_reactforum
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_reactforum_existing_subscriber_selector extends mod_reactforum_subscriber_selector_base {

    /**
     * Finds all subscribed users
     *
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['reactforumid'] = $this->reactforumid;

        // only active enrolled or everybody on the frontpage
        list($esql, $eparams) = get_enrolled_sql($this->context, '', $this->currentgroup, true);
        $fields = $this->required_fields_sql('u');
        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $params = array_merge($params, $eparams, $sortparams);

        $subscribers = $DB->get_records_sql("SELECT $fields
                                               FROM {user} u
                                               JOIN ($esql) je ON je.id = u.id
                                               JOIN {reactforum_subscriptions} s ON s.userid = u.id
                                              WHERE $wherecondition AND s.reactforum = :reactforumid
                                           ORDER BY $sort", $params);

        $cm = get_coursemodule_from_instance('reactforum', $this->reactforumid);
        $modinfo = get_fast_modinfo($cm->course);
        $info = new \core_availability\info_module($modinfo->get_cm($cm->id));
        $subscribers = $info->filter_user_list($subscribers);

        return array(get_string("existingsubscribers", 'reactforum') => $subscribers);
    }

}
