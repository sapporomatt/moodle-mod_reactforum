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
 * Render reaction image
 *
 * @package    mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once($CFG->dirroot . '/mod/reactforum/lib.php');

$reactionid = required_param('id', PARAM_INT);

$reaction = $DB->get_record('reactforum_reactions', array('id' => $reactionid), '*', MUST_EXIST);

if($reaction->reactforum_id == 0)
{
    $discussion = $DB->get_record('reactforum_discussions', array('id' => $reaction->discussion_id), '*', MUST_EXIST);
    $reactforum = $DB->get_record('reactforum', array('id' => $discussion->reactforum), '*', MUST_EXIST);
    $level = 'discussion';
    $reactiontype = $discussion->reactiontype;
}
else
{
    $reactforum = $DB->get_record('reactforum', array('id' => $reaction->reactforum_id), '*', MUST_EXIST);
    $level = 'reactforum';
    $reactiontype = $reactforum->reactiontype;
}

$course = $DB->get_record('course', array('id' => $reactforum->course), '*', MUST_EXIST);

$cm = get_coursemodule_from_instance('reactforum', $reactforum->id, $course->id, false, MUST_EXIST);
$context = context_module::instance($cm->id);

require_sesskey();
require_login($course, false, $cm);
require_course_login($course, true, $cm);
require_capability('mod/reactforum:viewdiscussion', $context, NULL, true, 'noviewdiscussionspermission', 'reactforum');

$return = new stdClass();

if (is_guest($context, $USER))
{
    // is_guest should be used here as this also checks whether the user is a guest in the current course.
    // Guests and visitors cannot subscribe - only enrolled users.
    throw new moodle_exception('error', 'mod_reactforum');
}

if($reactiontype != 'image')
{
    throw new moodle_exception('error', 'mod_reactforum');
}

$fs = get_file_storage();
$files = $fs->get_area_files(reactforum_get_context($reactforum->id)->id, 'mod_reactforum', 'reactions', $reactionid);

if(count($files) == 0)
{
    exit;
}

foreach ($files as $file) if($file->is_valid_image())
{
    header("Content-type: " . $file->get_mimetype());
    header("filename=" . $file->get_filename());
//    header("Content-Disposition: attachment; filename=" . $file->get_filename());

    echo $file->get_content();
    exit;
}