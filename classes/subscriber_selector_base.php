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
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');

/**
 * Abstract class used by reactforum subscriber selection controls
 * @package   mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class mod_reactforum_subscriber_selector_base extends user_selector_base {

    /**
     * The id of the reactforum this selector is being used for
     * @var int
     */
    protected $reactforumid = null;
    /**
     * The context of the reactforum this selector is being used for
     * @var object
     */
    protected $context = null;
    /**
     * The id of the current group
     * @var int
     */
    protected $currentgroup = null;

    /**
     * Constructor method
     * @param string $name
     * @param array $options
     */
    public function __construct($name, $options) {
        $options['accesscontext'] = $options['context'];
        parent::__construct($name, $options);
        if (isset($options['context'])) {
            $this->context = $options['context'];
        }
        if (isset($options['currentgroup'])) {
            $this->currentgroup = $options['currentgroup'];
        }
        if (isset($options['reactforumid'])) {
            $this->reactforumid = $options['reactforumid'];
        }
    }

    /**
     * Returns an array of options to seralise and store for searches
     *
     * @return array
     */
    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] =  substr(__FILE__, strlen($CFG->dirroot.'/'));
        $options['context'] = $this->context;
        $options['currentgroup'] = $this->currentgroup;
        $options['reactforumid'] = $this->reactforumid;
        return $options;
    }

}
