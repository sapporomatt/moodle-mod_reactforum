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
 * User react to a topic
 *
 * @package    mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once($CFG->dirroot . '/mod/reactforum/lib.php');

$postID = required_param('post', PARAM_INT);
$reactionID = required_param('reaction', PARAM_INT);

if (!$post = $DB->get_record('reactforum_posts', array('id' => $postID)))
{
    throw new moodle_exception('error', 'mod_reactforum');
}

$discussion = $DB->get_record('reactforum_discussions', array('id' => $post->discussion), '*', MUST_EXIST);
$reactforum = $DB->get_record('reactforum', array('id' => $discussion->reactforum), '*', MUST_EXIST);
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

if(reactforum_user_can_see_post($reactforum, $discussion, $post, null, $cm) == false)
{
    throw new moodle_exception('error', 'mod_reactforum');
}

if($post->userid == $USER->id)
{
    throw new moodle_exception('error', 'mod_reactforum');
}

$userReactionObj = $DB->get_record(
    "reactforum_user_reactions",
    array(
        "post_id" => $postID,
        "user_id" => $USER->id
    ));

if($userReactionObj == false)
{   // New record
    $userReactionObj = new stdClass();
    $userReactionObj->post_id = $postID;
    $userReactionObj->user_id = $USER->id;
    $userReactionObj->reaction_id = $reactionID;

    $DB->insert_record("reactforum_user_reactions", $userReactionObj);
}
else if($userReactionObj->reaction_id == $reactionID)
{   // Remove reaction
    $DB->delete_records("reactforum_user_reactions",
        array(
            "post_id" => $postID,
            "reaction_id" => $reactionID,
            "user_id" => $USER->id
        ));
}
else
{   // Update record
    $userReactionObj->reaction_id = $reactionID;

    $DB->update_record("reactforum_user_reactions", $userReactionObj);
}


// Get final number

$newObjArr = reactforum_get_reactions_from_discussion($discussion);

$arrayResult = array();

$postisreacted = ($DB->count_records('reactforum_user_reactions', array('post_id' => $post->id, 'user_id' => $USER->id)) > 0);
foreach ($newObjArr as $obj)
{
    $countObj = $DB->get_record("reactforum_user_reactions",
        array(
            "post_id" => $postID,
            "reaction_id" => $obj->id
        ), "COUNT(*) AS 'count'");

    $userCountObj = $DB->get_record("reactforum_user_reactions",
        array(
            "post_id" => $postID,
            "reaction_id" => $obj->id,
            "user_id" => $USER->id
        ), "COUNT(*) AS 'count'");

    $item = array(
        "post_id" => $postID,
        "reaction_id" => $obj->id,
        "count" => $countObj->count,
        "reacted" => ($userCountObj->count == 1)
    );

    if ($reactforum->delayedcounter && $post->userid != $USER->id && !$postisreacted) {
        $item['count'] = '';
    }

    array_push($arrayResult, $item);
}

echo json_encode(array("status" => true, "data" => $arrayResult));