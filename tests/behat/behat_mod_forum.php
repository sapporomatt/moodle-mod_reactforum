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
 * Steps definitions related with the reactforum activity.
 *
 * @package    mod_reactforum
 * @category   test
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;
/**
 * ReactForum-related steps definitions.
 *
 * @package    mod_reactforum
 * @category   test
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_reactforum extends behat_base {

    /**
     * Adds a topic to the reactforum specified by it's name. Useful for the Announcements and blog-style reactforums.
     *
     * @Given /^I add a new topic to "(?P<reactforum_name_string>(?:[^"]|\\")*)" reactforum with:$/
     * @param string $reactforumname
     * @param TableNode $table
     */
    public function i_add_a_new_topic_to_reactforum_with($reactforumname, TableNode $table) {
        $this->add_new_discussion($reactforumname, $table, get_string('addanewtopic', 'reactforum'));
    }

    /**
     * Adds a discussion to the reactforum specified by it's name with the provided table data (usually Subject and Message). The step begins from the reactforum's course page.
     *
     * @Given /^I add a new discussion to "(?P<reactforum_name_string>(?:[^"]|\\")*)" reactforum with:$/
     * @param string $reactforumname
     * @param TableNode $table
     */
    public function i_add_a_reactforum_discussion_to_reactforum_with($reactforumname, TableNode $table) {
        $this->add_new_discussion($reactforumname, $table, get_string('addanewdiscussion', 'reactforum'));
    }

    /**
     * Adds a reply to the specified post of the specified reactforum. The step begins from the reactforum's page or from the reactforum's course page.
     *
     * @Given /^I reply "(?P<post_subject_string>(?:[^"]|\\")*)" post from "(?P<reactforum_name_string>(?:[^"]|\\")*)" reactforum with:$/
     * @param string $postname The subject of the post
     * @param string $reactforumname The reactforum name
     * @param TableNode $table
     */
    public function i_reply_post_from_reactforum_with($postsubject, $reactforumname, TableNode $table) {

        // Navigate to reactforum.
        $this->execute('behat_general::click_link', $this->escape($reactforumname));
        $this->execute('behat_general::click_link', $this->escape($postsubject));
        $this->execute('behat_general::click_link', get_string('reply', 'reactforum'));

        // Fill form and post.
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', $table);

        $this->execute('behat_forms::press_button', get_string('posttoreactforum', 'reactforum'));
        $this->execute('behat_general::i_wait_to_be_redirected');
    }

    /**
     * Returns the steps list to add a new discussion to a reactforum.
     *
     * Abstracts add a new topic and add a new discussion, as depending
     * on the reactforum type the button string changes.
     *
     * @param string $reactforumname
     * @param TableNode $table
     * @param string $buttonstr
     */
    protected function add_new_discussion($reactforumname, TableNode $table, $buttonstr) {

        // Navigate to reactforum.
        $this->execute('behat_general::click_link', $this->escape($reactforumname));
        $this->execute('behat_forms::press_button', $buttonstr);

        // Fill form and post.
        $this->execute('behat_forms::i_set_the_following_fields_to_these_values', $table);
        $this->execute('behat_forms::press_button', get_string('posttoreactforum', 'reactforum'));
        $this->execute('behat_general::i_wait_to_be_redirected');
    }

}
