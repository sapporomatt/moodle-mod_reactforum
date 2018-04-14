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
 * Defines backup_reactforum_activity_task class
 *
 * @package   mod_reactforum
 * @category  backup
 * @copyright  2017 (C) VERSION2, INC.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/reactforum/backup/moodle2/backup_reactforum_stepslib.php');
require_once($CFG->dirroot . '/mod/reactforum/backup/moodle2/backup_reactforum_settingslib.php');

/**
 * Provides the steps to perform one complete backup of the ReactForum instance
 */
class backup_reactforum_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the reactforum.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_reactforum_activity_structure_step('reactforum structure', 'reactforum.xml'));
    }

    /**
     * Encodes URLs to the index.php, view.php and discuss.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of reactforums
        $search = "/(" . $base . "\/mod\/reactforum\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@REACTFORUMINDEX*$2@$', $content);

        // Link to reactforum view by moduleid
        $search = "/(" . $base . "\/mod\/reactforum\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@REACTFORUMVIEWBYID*$2@$', $content);

        // Link to reactforum view by reactforumid
        $search = "/(" . $base . "\/mod\/reactforum\/view.php\?f\=)([0-9]+)/";
        $content = preg_replace($search, '$@REACTFORUMVIEWBYF*$2@$', $content);

        // Link to reactforum discussion with parent syntax
        $search = "/(" . $base . "\/mod\/reactforum\/discuss.php\?d\=)([0-9]+)(?:\&amp;|\&)parent\=([0-9]+)/";
        $content = preg_replace($search, '$@REACTFORUMDISCUSSIONVIEWPARENT*$2*$3@$', $content);

        // Link to reactforum discussion with relative syntax
        $search = "/(" . $base . "\/mod\/reactforum\/discuss.php\?d\=)([0-9]+)\#([0-9]+)/";
        $content = preg_replace($search, '$@REACTFORUMDISCUSSIONVIEWINSIDE*$2*$3@$', $content);

        // Link to reactforum discussion by discussionid
        $search = "/(" . $base . "\/mod\/reactforum\/discuss.php\?d\=)([0-9]+)/";
        $content = preg_replace($search, '$@REACTFORUMDISCUSSIONVIEW*$2@$', $content);

        return $content;
    }
}
