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
 * This file keeps track of upgrades to
 * the reactforum module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package   mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_reactforum_upgrade($oldversion)
{
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2017070600)
    {
        $selfReactedIDs = $DB->get_records_sql(
            "SELECT {reactforum_user_reactions}.`id` AS 'id'
                    FROM {reactforum_user_reactions}, `{reactforum_posts}`
                    WHERE {reactforum_user_reactions}.`post_id` = {reactforum_posts}.`id`
                      AND {reactforum_user_reactions}.`user_id` = {reactforum_posts}.`userid`"
        );

        foreach ($selfReactedIDs as $selfReactedID)
        {
            $DB->delete_records("reactforum_user_reactions", array("id" => $selfReactedID->id));
        }

        // ReactForum savepoint reached.
        upgrade_mod_savepoint(true, 2017070600, 'reactforum');
    }

    if($oldversion < 2017071216)
    {
        $table = new xmldb_table('reactforum_discussions');
        $field = new xmldb_field('reactiontype', XMLDB_TYPE_CHAR, '50', null, true, false, 'text', 'pinned');

        if (!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2017071216, 'reactforum');
    }

    if($oldversion < 2017072700)
    {
        $table = new xmldb_table('reactforum');
        $field = new xmldb_field('reactiontype', XMLDB_TYPE_CHAR, '50', null, true, false, 'text', 'displaywordcount');
        if (!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }

        //

        $table = new xmldb_table('reactforum_reactions');

        $field = new xmldb_field('reactforum_id', XMLDB_TYPE_INTEGER, '10', null, null, false, '0', 'id');
        if (!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('reactforum_id', XMLDB_KEY_FOREIGN, array('reactforum_id'), 'reactforum', 'id');
        if (!$dbman->find_key_name($table, $key))
        {
            $dbman->add_key($table, $key);
        }

        $index = new xmldb_index('discussion_idx', XMLDB_INDEX_NOTUNIQUE, array('discussion_id'));
        if(!$dbman->index_exists($table, $index))
        {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('reactforum_idx', XMLDB_INDEX_NOTUNIQUE, array('reactforum_id'));
        if(!$dbman->index_exists($table, $index))
        {
            $dbman->add_index($table, $index);
        }

        //

        $table = new xmldb_table('reactforum_user_reactions');

        $index = new xmldb_index('post_idx', XMLDB_INDEX_NOTUNIQUE, array('post_id'));
        if(!$dbman->index_exists($table, $index))
        {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('user_idx', XMLDB_INDEX_NOTUNIQUE, array('user_id'));
        if(!$dbman->index_exists($table, $index))
        {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('reaction_idxs', XMLDB_INDEX_NOTUNIQUE, array('reaction_id'));
        if(!$dbman->index_exists($table, $index))
        {
            $dbman->add_index($table, $index);
        }

        //

        upgrade_mod_savepoint(true, 2017072700,'reactforum');
    }

    if($oldversion < 2017102400)
    {
        $table = new xmldb_table('reactforum');
        $field = new xmldb_field('reactionallreplies', XMLDB_TYPE_INTEGER, '1', null, true, null, '0', 'reactiontype');
        if(!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('reactforum_discussions');
        $field = new xmldb_field('reactionallreplies', XMLDB_TYPE_INTEGER, '1', null, true, null, '0', 'reactiontype');
        if(!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2017102400, 'reactforum');
    }

    return true;
}
