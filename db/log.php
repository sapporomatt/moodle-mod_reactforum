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
 * Definition of log events
 *
 * @package    mod_reactforum
 * @category   log
 * @copyright  2010 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB; // TODO: this is a hack, we should really do something with the SQL in SQL tables.

$logs = array(
    array('module' => 'reactforum', 'action' => 'add', 'mtable' => 'reactforum', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'update', 'mtable' => 'reactforum', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'add discussion', 'mtable' => 'reactforum_discussions', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'add post', 'mtable' => 'reactforum_posts', 'field' => 'subject'),
    array('module' => 'reactforum', 'action' => 'update post', 'mtable' => 'reactforum_posts', 'field' => 'subject'),
    array('module' => 'reactforum', 'action' => 'user report', 'mtable' => 'user',
          'field'  => $DB->sql_concat('firstname', "' '", 'lastname')),
    array('module' => 'reactforum', 'action' => 'move discussion', 'mtable' => 'reactforum_discussions', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'view subscribers', 'mtable' => 'reactforum', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'view discussion', 'mtable' => 'reactforum_discussions', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'view reactforum', 'mtable' => 'reactforum', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'subscribe', 'mtable' => 'reactforum', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'unsubscribe', 'mtable' => 'reactforum', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'pin discussion', 'mtable' => 'reactforum_discussions', 'field' => 'name'),
    array('module' => 'reactforum', 'action' => 'unpin discussion', 'mtable' => 'reactforum_discussions', 'field' => 'name'),
);