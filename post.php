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
 * Edit and save a new post to a discussion
 *
 * @package   mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/completionlib.php');

$reply = optional_param('reply', 0, PARAM_INT);
$reactforum = optional_param('reactforum', 0, PARAM_INT);
$edit = optional_param('edit', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$prune = optional_param('prune', 0, PARAM_INT);
$name = optional_param('name', '', PARAM_CLEAN);
$confirm = optional_param('confirm', 0, PARAM_INT);
$groupid = optional_param('groupid', null, PARAM_INT);

$PAGE->set_url('/mod/reactforum/post.php', array(
    'reply' => $reply,
    'reactforum' => $reactforum,
    'edit' => $edit,
    'delete' => $delete,
    'prune' => $prune,
    'name' => $name,
    'confirm' => $confirm,
    'groupid' => $groupid,
));
//these page_params will be passed as hidden variables later in the form.
$page_params = array('reply' => $reply, 'reactforum' => $reactforum, 'edit' => $edit);

$sitecontext = context_system::instance();

if (!isloggedin() or isguestuser())
{

    if (!isloggedin() and !get_local_referer())
    {
        // No referer+not logged in - probably coming in via email  See MDL-9052
        require_login();
    }

    if (!empty($reactforum))
    {      // User is starting a new discussion in a reactforum
        if (!$reactforum = $DB->get_record('reactforum', array('id' => $reactforum)))
        {
            print_error('invalidreactforumid', 'reactforum');
        }
    }
    else if (!empty($reply))
    {      // User is writing a new reply
        if (!$parent = reactforum_get_post_full($reply))
        {
            print_error('invalidparentpostid', 'reactforum');
        }
        if (!$discussion = $DB->get_record('reactforum_discussions', array('id' => $parent->discussion)))
        {
            print_error('notpartofdiscussion', 'reactforum');
        }
        if (!$reactforum = $DB->get_record('reactforum', array('id' => $discussion->reactforum)))
        {
            print_error('invalidreactforumid');
        }
    }
    if (!$course = $DB->get_record('course', array('id' => $reactforum->course)))
    {
        print_error('invalidcourseid');
    }

    if (!$cm = get_coursemodule_from_instance('reactforum', $reactforum->id, $course->id))
    { // For the logs
        print_error('invalidcoursemodule');
    }
    else
    {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $reactforum);
    $PAGE->set_context($modcontext);
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);
    $referer = get_local_referer(false);

    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('noguestpost', 'reactforum') . '<br /><br />' . get_string('liketologin'), get_login_url(), $referer);
    echo $OUTPUT->footer();
    exit;
}

require_login(0, false);   // Script is useless unless they're logged in

if (!empty($reactforum))
{      // User is starting a new discussion in a reactforum

    if (!$reactforum = $DB->get_record("reactforum", array("id" => $reactforum)))
    {
        print_error('invalidreactforumid', 'reactforum');
    }
    if (!$course = $DB->get_record("course", array("id" => $reactforum->course)))
    {
        print_error('invalidcourseid');
    }
    if (!$cm = get_coursemodule_from_instance("reactforum", $reactforum->id, $course->id))
    {
        print_error("invalidcoursemodule");
    }

    if($reactforum->reactiontype == 'discussion')
    {
        reactforum_form_call_js($PAGE);
    }

    // Retrieve the contexts.
    $modcontext = context_module::instance($cm->id);
    $coursecontext = context_course::instance($course->id);

    if (!reactforum_user_can_post_discussion($reactforum, $groupid, -1, $cm))
    {
        if (!isguestuser())
        {
            if (!is_enrolled($coursecontext))
            {
                if (enrol_selfenrol_available($course->id))
                {
                    $SESSION->wantsurl = qualified_me();
                    $SESSION->enrolcancel = get_local_referer(false);
                    redirect(new moodle_url('/enrol/index.php', array('id' => $course->id,
                        'returnurl' => '/mod/reactforum/view.php?f=' . $reactforum->id)),
                        get_string('youneedtoenrol'));
                }
            }
        }
        print_error('nopostreactforum', 'reactforum');
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $modcontext))
    {
        print_error("activityiscurrentlyhidden");
    }

    $SESSION->fromurl = get_local_referer(false);

    // Load up the $post variable.

    $post = new stdClass();
    $post->course = $course->id;
    $post->reactforum = $reactforum->id;
    $post->discussion = 0;           // ie discussion # not defined yet
    $post->parent = 0;
    $post->subject = '';
    $post->userid = $USER->id;
    $post->message = '';
    $post->messageformat = editors_get_preferred_format();
    $post->messagetrust = 0;
    $post->reactiontype = 'none';

    if (isset($groupid))
    {
        $post->groupid = $groupid;
    }
    else
    {
        $post->groupid = groups_get_activity_group($cm);
    }

    // Unsetting this will allow the correct return URL to be calculated later.
    unset($SESSION->fromdiscussion);

}
else if (!empty($reply))
{      // User is writing a new reply

    if (!$parent = reactforum_get_post_full($reply))
    {
        print_error('invalidparentpostid', 'reactforum');
    }
    if (!$discussion = $DB->get_record("reactforum_discussions", array("id" => $parent->discussion)))
    {
        print_error('notpartofdiscussion', 'reactforum');
    }
    if (!$reactforum = $DB->get_record("reactforum", array("id" => $discussion->reactforum)))
    {
        print_error('invalidreactforumid', 'reactforum');
    }
    if (!$course = $DB->get_record("course", array("id" => $discussion->course)))
    {
        print_error('invalidcourseid');
    }
    if (!$cm = get_coursemodule_from_instance("reactforum", $reactforum->id, $course->id))
    {
        print_error('invalidcoursemodule');
    }

    // Ensure lang, theme, etc. is set up properly. MDL-6926
    $PAGE->set_cm($cm, $course, $reactforum);

    // Retrieve the contexts.
    $modcontext = context_module::instance($cm->id);
    $coursecontext = context_course::instance($course->id);

    if (!reactforum_user_can_post($reactforum, $discussion, $USER, $cm, $course, $modcontext))
    {
        if (!isguestuser())
        {
            if (!is_enrolled($coursecontext))
            {  // User is a guest here!
                $SESSION->wantsurl = qualified_me();
                $SESSION->enrolcancel = get_local_referer(false);
                redirect(new moodle_url('/enrol/index.php', array('id' => $course->id,
                    'returnurl' => '/mod/reactforum/view.php?f=' . $reactforum->id)),
                    get_string('youneedtoenrol'));
            }
        }
        print_error('nopostreactforum', 'reactforum');
    }

    // Make sure user can post here
    if (isset($cm->groupmode) && empty($course->groupmodeforce))
    {
        $groupmode = $cm->groupmode;
    }
    else
    {
        $groupmode = $course->groupmode;
    }
    if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $modcontext))
    {
        if ($discussion->groupid == -1)
        {
            print_error('nopostreactforum', 'reactforum');
        }
        else
        {
            if (!groups_is_member($discussion->groupid))
            {
                print_error('nopostreactforum', 'reactforum');
            }
        }
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $modcontext))
    {
        print_error("activityiscurrentlyhidden");
    }

    // Load up the $post variable.

    $post = new stdClass();
    $post->course = $course->id;
    $post->reactforum = $reactforum->id;
    $post->discussion = $parent->discussion;
    $post->parent = $parent->id;
    $post->subject = $parent->subject;
    $post->userid = $USER->id;
    $post->message = '';

    $post->groupid = ($discussion->groupid == -1) ? 0 : $discussion->groupid;

    $strre = get_string('re', 'reactforum');
    if (!(substr($post->subject, 0, strlen($strre)) == $strre))
    {
        $post->subject = $strre . ' ' . $post->subject;
    }

    // Unsetting this will allow the correct return URL to be calculated later.
    unset($SESSION->fromdiscussion);

}
else if (!empty($edit))
{  // User is editing their own post

    if (!$post = reactforum_get_post_full($edit))
    {
        print_error('invalidpostid', 'reactforum');
    }
    if ($post->parent)
    {
        if (!$parent = reactforum_get_post_full($post->parent))
        {
            print_error('invalidparentpostid', 'reactforum');
        }
    }

    if (!$discussion = $DB->get_record("reactforum_discussions", array("id" => $post->discussion)))
    {
        print_error('notpartofdiscussion', 'reactforum');
    }
    if (!$reactforum = $DB->get_record("reactforum", array("id" => $discussion->reactforum)))
    {
        print_error('invalidreactforumid', 'reactforum');
    }
    if (!$course = $DB->get_record("course", array("id" => $discussion->course)))
    {
        print_error('invalidcourseid');
    }
    if (!$cm = get_coursemodule_from_instance("reactforum", $reactforum->id, $course->id))
    {
        print_error('invalidcoursemodule');
    }
    else
    {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $reactforum);

    if (!($reactforum->type == 'news' && !$post->parent && $discussion->timestart > time()))
    {
        if (((time() - $post->created) > $CFG->maxeditingtime) and
            !has_capability('mod/reactforum:editanypost', $modcontext)
        )
        {
            print_error('maxtimehaspassed', 'reactforum', '', format_time($CFG->maxeditingtime));
        }
    }
    if (($post->userid <> $USER->id) and
        !has_capability('mod/reactforum:editanypost', $modcontext)
    )
    {
        print_error('cannoteditposts', 'reactforum');
    }


    // Load up the $post variable.
    $post->edit = $edit;
    $post->course = $course->id;
    $post->reactforum = $reactforum->id;
    $post->groupid = ($discussion->groupid == -1) ? 0 : $discussion->groupid;

    $post = trusttext_pre_edit($post, 'message', $modcontext);

    // LOAD REACTIONS
    if($post->parent == 0 && $reactforum->reactiontype == 'discussion')
    {
        $post->reactiontype = $discussion->reactiontype;

        $reactions_values = array();
        $reactions = $DB->get_records("reactforum_reactions", array("discussion_id" => $discussion->id));

        foreach($reactions as $reactionObj)
        {
            array_push($reactions_values, array("id" => $reactionObj->id, "value" => $reactionObj->reaction));
        }

        $reactions_js = json_encode(array(
            "type" => $discussion->reactiontype,
            "reactions" => $reactions_values,
            'level' => 'discussion'
        ));

        $PAGE->requires->js_init_code("reactions_oldvalues = {$reactions_js};", false);

        reactforum_form_call_js($PAGE);
    }

    // Unsetting this will allow the correct return URL to be calculated later.
    unset($SESSION->fromdiscussion);

}
else if (!empty($delete))
{  // User is deleting a post

    if (!$post = reactforum_get_post_full($delete))
    {
        print_error('invalidpostid', 'reactforum');
    }
    if (!$discussion = $DB->get_record("reactforum_discussions", array("id" => $post->discussion)))
    {
        print_error('notpartofdiscussion', 'reactforum');
    }
    if (!$reactforum = $DB->get_record("reactforum", array("id" => $discussion->reactforum)))
    {
        print_error('invalidreactforumid', 'reactforum');
    }
    if (!$cm = get_coursemodule_from_instance("reactforum", $reactforum->id, $reactforum->course))
    {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $reactforum->course)))
    {
        print_error('invalidcourseid');
    }

    require_login($course, false, $cm);
    $modcontext = context_module::instance($cm->id);

    if (!(($post->userid == $USER->id && has_capability('mod/reactforum:deleteownpost', $modcontext))
        || has_capability('mod/reactforum:deleteanypost', $modcontext))
    )
    {
        print_error('cannotdeletepost', 'reactforum');
    }


    $replycount = reactforum_count_replies($post);

    if (!empty($confirm) && confirm_sesskey())
    {    // User has confirmed the delete
        //check user capability to delete post.
        $timepassed = time() - $post->created;
        if (($timepassed > $CFG->maxeditingtime) && !has_capability('mod/reactforum:deleteanypost', $modcontext))
        {
            print_error("cannotdeletepost", "reactforum",
                reactforum_go_back_to(new moodle_url("/mod/reactforum/discuss.php", array('d' => $post->discussion))));
        }

        if ($post->totalscore)
        {
            notice(get_string('couldnotdeleteratings', 'rating'),
                reactforum_go_back_to(new moodle_url("/mod/reactforum/discuss.php", array('d' => $post->discussion))));

        }
        else if ($replycount && !has_capability('mod/reactforum:deleteanypost', $modcontext))
        {
            print_error("couldnotdeletereplies", "reactforum",
                reactforum_go_back_to(new moodle_url("/mod/reactforum/discuss.php", array('d' => $post->discussion))));

        }
        else
        {
            if (!$post->parent)
            {  // post is a discussion topic as well, so delete discussion
                if ($reactforum->type == 'single')
                {
                    notice("Sorry, but you are not allowed to delete that discussion!",
                        reactforum_go_back_to(new moodle_url("/mod/reactforum/discuss.php", array('d' => $post->discussion))));
                }

                // DELETE REACTIONS
                $reactions = $DB->get_records("reactforum_reactions", array("discussion_id" => $discussion->id));
                foreach($reactions as $reactionObj)
                {
                    reactforum_remove_reaction($reactionObj->id);
                }

                reactforum_delete_discussion($discussion, false, $course, $cm, $reactforum);

                $params = array(
                    'objectid' => $discussion->id,
                    'context' => $modcontext,
                    'other' => array(
                        'reactforumid' => $reactforum->id,
                    )
                );

                $event = \mod_reactforum\event\discussion_deleted::create($params);
                $event->add_record_snapshot('reactforum_discussions', $discussion);
                $event->trigger();

                redirect("view.php?f=$discussion->reactforum");

            }
            else if (reactforum_delete_post($post, has_capability('mod/reactforum:deleteanypost', $modcontext),
                $course, $cm, $reactforum))
            {
                $DB->delete_records("reactforum_user_reactions", array("post_id" => $post->id));

                if ($reactforum->type == 'single')
                {
                    // Single discussion reactforums are an exception. We show
                    // the reactforum itself since it only has one discussion
                    // thread.
                    $discussionurl = new moodle_url("/mod/reactforum/view.php", array('f' => $reactforum->id));
                }
                else
                {
                    $discussionurl = new moodle_url("/mod/reactforum/discuss.php", array('d' => $discussion->id));
                }

                redirect(reactforum_go_back_to($discussionurl));
            }
            else
            {
                print_error('errorwhiledelete', 'reactforum');
            }
        }


    }
    else
    { // User just asked to delete something

        reactforum_set_return();
        $PAGE->navbar->add(get_string('delete', 'reactforum'));
        $PAGE->set_title($course->shortname);
        $PAGE->set_heading($course->fullname);

        if ($replycount)
        {
            if (!has_capability('mod/reactforum:deleteanypost', $modcontext))
            {
                print_error("couldnotdeletereplies", "reactforum",
                    reactforum_go_back_to(new moodle_url('/mod/reactforum/discuss.php', array('d' => $post->discussion), 'p' . $post->id)));
            }
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($reactforum->name), 2);
            echo $OUTPUT->confirm(get_string("deletesureplural", "reactforum", $replycount + 1),
                "post.php?delete=$delete&confirm=$delete",
                $CFG->wwwroot . '/mod/reactforum/discuss.php?d=' . $post->discussion . '#p' . $post->id);

            reactforum_print_post($post, $discussion, $reactforum, $cm, $course, false, false, false);

            if (empty($post->edit))
            {
                $reactforumtracked = reactforum_tp_is_tracked($reactforum);
                $posts = reactforum_get_all_discussion_posts($discussion->id, "created ASC", $reactforumtracked);
                reactforum_print_posts_nested($course, $cm, $reactforum, $discussion, $post, false, false, $reactforumtracked, $posts);
            }
        }
        else
        {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($reactforum->name), 2);
            echo $OUTPUT->confirm(get_string("deletesure", "reactforum", $replycount),
                "post.php?delete=$delete&confirm=$delete",
                $CFG->wwwroot . '/mod/reactforum/discuss.php?d=' . $post->discussion . '#p' . $post->id);
            reactforum_print_post($post, $discussion, $reactforum, $cm, $course, false, false, false);
        }

    }
    echo $OUTPUT->footer();
    die;


}
else if (!empty($prune))
{  // Pruning

    if (!$post = reactforum_get_post_full($prune))
    {
        print_error('invalidpostid', 'reactforum');
    }
    if (!$discussion = $DB->get_record("reactforum_discussions", array("id" => $post->discussion)))
    {
        print_error('notpartofdiscussion', 'reactforum');
    }
    if (!$reactforum = $DB->get_record("reactforum", array("id" => $discussion->reactforum)))
    {
        print_error('invalidreactforumid', 'reactforum');
    }
    if ($reactforum->type == 'single')
    {
        print_error('cannotsplit', 'reactforum');
    }
    if (!$post->parent)
    {
        print_error('alreadyfirstpost', 'reactforum');
    }
    if (!$cm = get_coursemodule_from_instance("reactforum", $reactforum->id, $reactforum->course))
    { // For the logs
        print_error('invalidcoursemodule');
    }
    else
    {
        $modcontext = context_module::instance($cm->id);
    }
    if (!has_capability('mod/reactforum:splitdiscussions', $modcontext))
    {
        print_error('cannotsplit', 'reactforum');
    }

    $PAGE->set_cm($cm);
    $PAGE->set_context($modcontext);

    $prunemform = new mod_reactforum_prune_form(null, array('prune' => $prune, 'confirm' => $prune));


    if ($prunemform->is_cancelled())
    {
        redirect(reactforum_go_back_to(new moodle_url("/mod/reactforum/discuss.php", array('d' => $post->discussion))));
    }
    else if ($fromform = $prunemform->get_data())
    {
        // User submits the data.
        $newdiscussion = new stdClass();
        $newdiscussion->course = $discussion->course;
        $newdiscussion->reactforum = $discussion->reactforum;
        $newdiscussion->name = $name;
        $newdiscussion->firstpost = $post->id;
        $newdiscussion->userid = $discussion->userid;
        $newdiscussion->groupid = $discussion->groupid;
        $newdiscussion->assessed = $discussion->assessed;
        $newdiscussion->usermodified = $post->userid;
        $newdiscussion->timestart = $discussion->timestart;
        $newdiscussion->timeend = $discussion->timeend;

        $newid = $DB->insert_record('reactforum_discussions', $newdiscussion);

        $newpost = new stdClass();
        $newpost->id = $post->id;
        $newpost->parent = 0;
        $newpost->subject = $name;

        $DB->update_record("reactforum_posts", $newpost);

        reactforum_change_discussionid($post->id, $newid);

        // Update last post in each discussion.
        reactforum_discussion_update_last_post($discussion->id);
        reactforum_discussion_update_last_post($newid);

        // Fire events to reflect the split..
        $params = array(
            'context' => $modcontext,
            'objectid' => $discussion->id,
            'other' => array(
                'reactforumid' => $reactforum->id,
            )
        );
        $event = \mod_reactforum\event\discussion_updated::create($params);
        $event->trigger();

        $params = array(
            'context' => $modcontext,
            'objectid' => $newid,
            'other' => array(
                'reactforumid' => $reactforum->id,
            )
        );
        $event = \mod_reactforum\event\discussion_created::create($params);
        $event->trigger();

        $params = array(
            'context' => $modcontext,
            'objectid' => $post->id,
            'other' => array(
                'discussionid' => $newid,
                'reactforumid' => $reactforum->id,
                'reactforumtype' => $reactforum->type,
            )
        );
        $event = \mod_reactforum\event\post_updated::create($params);
        $event->add_record_snapshot('reactforum_discussions', $discussion);
        $event->trigger();

        redirect(reactforum_go_back_to(new moodle_url("/mod/reactforum/discuss.php", array('d' => $newid))));

    }
    else
    {
        // Display the prune form.
        $course = $DB->get_record('course', array('id' => $reactforum->course));
        $PAGE->navbar->add(format_string($post->subject, true), new moodle_url('/mod/reactforum/discuss.php', array('d' => $discussion->id)));
        $PAGE->navbar->add(get_string("prune", "reactforum"));
        $PAGE->set_title(format_string($discussion->name) . ": " . format_string($post->subject));
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($reactforum->name), 2);
        echo $OUTPUT->heading(get_string('pruneheading', 'reactforum'), 3);

        $prunemform->display();

        reactforum_print_post($post, $discussion, $reactforum, $cm, $course, false, false, false);
    }

    echo $OUTPUT->footer();
    die;
}
else
{
    print_error('unknowaction');

}

if (!isset($coursecontext))
{
    // Has not yet been set by post.php.
    $coursecontext = context_course::instance($reactforum->course);
}


// from now on user must be logged on properly

if (!$cm = get_coursemodule_from_instance('reactforum', $reactforum->id, $course->id))
{ // For the logs
    print_error('invalidcoursemodule');
}
$modcontext = context_module::instance($cm->id);
require_login($course, false, $cm);

if (isguestuser())
{
    // just in case
    print_error('noguest');
}

if (!isset($reactforum->maxattachments))
{  // TODO - delete this once we add a field to the reactforum table
    $reactforum->maxattachments = 3;
}

$thresholdwarning = reactforum_check_throttling($reactforum, $cm);
$mform_post = new mod_reactforum_post_form('post.php', array('course' => $course,
    'cm' => $cm,
    'coursecontext' => $coursecontext,
    'modcontext' => $modcontext,
    'reactforum' => $reactforum,
    'post' => $post,
    'subscribe' => \mod_reactforum\subscriptions::is_subscribed($USER->id, $reactforum,
        null, $cm),
    'thresholdwarning' => $thresholdwarning,
    'edit' => $edit), 'post', '', array('id' => 'mformreactforum'));

$draftitemid = file_get_submitted_draft_itemid('attachments');
file_prepare_draft_area($draftitemid, $modcontext->id, 'mod_reactforum', 'attachment', empty($post->id) ? null : $post->id, mod_reactforum_post_form::attachment_options($reactforum));

//load data into form NOW!

if ($USER->id != $post->userid)
{   // Not the original author, so add a message to the end
    $data = new stdClass();
    $data->date = userdate($post->modified);
    if ($post->messageformat == FORMAT_HTML)
    {
        $data->name = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $USER->id . '&course=' . $post->course . '">' .
            fullname($USER) . '</a>';
        $post->message .= '<p><span class="edited">(' . get_string('editedby', 'reactforum', $data) . ')</span></p>';
    }
    else
    {
        $data->name = fullname($USER);
        $post->message .= "\n\n(" . get_string('editedby', 'reactforum', $data) . ')';
    }
    unset($data);
}

$formheading = '';
if (!empty($parent))
{
    $heading = get_string("yourreply", "reactforum");
    $formheading = get_string('reply', 'reactforum');
}
else
{
    if ($reactforum->type == 'qanda')
    {
        $heading = get_string('yournewquestion', 'reactforum');
    }
    else
    {
        $heading = get_string('yournewtopic', 'reactforum');
    }
}

$postid = empty($post->id) ? null : $post->id;
$draftid_editor = file_get_submitted_draft_itemid('message');
$currenttext = file_prepare_draft_area($draftid_editor, $modcontext->id, 'mod_reactforum', 'post', $postid, mod_reactforum_post_form::editor_options($modcontext, $postid), $post->message);

$manageactivities = has_capability('moodle/course:manageactivities', $coursecontext);
if (\mod_reactforum\subscriptions::subscription_disabled($reactforum) && !$manageactivities)
{
    // User does not have permission to subscribe to this discussion at all.
    $discussionsubscribe = false;
}
else if (\mod_reactforum\subscriptions::is_forcesubscribed($reactforum))
{
    // User does not have permission to unsubscribe from this discussion at all.
    $discussionsubscribe = true;
}
else
{
    if (isset($discussion) && \mod_reactforum\subscriptions::is_subscribed($USER->id, $reactforum, $discussion->id, $cm))
    {
        // User is subscribed to the discussion - continue the subscription.
        $discussionsubscribe = true;
    }
    else if (!isset($discussion) && \mod_reactforum\subscriptions::is_subscribed($USER->id, $reactforum, null, $cm))
    {
        // Starting a new discussion, and the user is subscribed to the reactforum - subscribe to the discussion.
        $discussionsubscribe = true;
    }
    else
    {
        // User is not subscribed to either reactforum or discussion. Follow user preference.
        $discussionsubscribe = $USER->autosubscribe;
    }
}

$mform_post->set_data(array('attachments' => $draftitemid,
        'general' => $heading,
        'subject' => $post->subject,
        'message' => array(
            'text' => $currenttext,
            'format' => empty($post->messageformat) ? editors_get_preferred_format() : $post->messageformat,
            'itemid' => $draftid_editor
        ),
        'discussionsubscribe' => $discussionsubscribe,
        'mailnow' => !empty($post->mailnow),
        'userid' => $post->userid,
        'parent' => $post->parent,
        'discussion' => $post->discussion,
        'course' => $course->id,
        'reactiontype' => $post->reactiontype,
    ) + $page_params +
    (isset($post->format) ? array(
        'format' => $post->format) :
        array()) +

    (isset($discussion->timestart) ? array(
        'timestart' => $discussion->timestart) :
        array()) +

    (isset($discussion->timeend) ? array(
        'timeend' => $discussion->timeend) :
        array()) +

    (isset($discussion->pinned) ? array(
        'pinned' => $discussion->pinned) :
        array()) +

    (isset($post->groupid) ? array(
        'groupid' => $post->groupid) :
        array()) +

    (isset($discussion->id) ?
        array('discussion' => $discussion->id) :
        array()));

if ($mform_post->is_cancelled())
{
    if (!isset($discussion->id) || $reactforum->type === 'qanda')
    {
        // Q and A reactforums don't have a discussion page, so treat them like a new thread..
        redirect(new moodle_url('/mod/reactforum/view.php', array('f' => $reactforum->id)));
    }
    else
    {
        redirect(new moodle_url('/mod/reactforum/discuss.php', array('d' => $discussion->id)));
    }
}
else if ($fromform = $mform_post->get_data())
{
    $fs = get_file_storage();

    if (empty($SESSION->fromurl))
    {
        $errordestination = "$CFG->wwwroot/mod/reactforum/view.php?f=$reactforum->id";
    }
    else
    {
        $errordestination = $SESSION->fromurl;
    }

    $fromform->itemid = $fromform->message['itemid'];
    $fromform->messageformat = $fromform->message['format'];
    $fromform->message = $fromform->message['text'];
    // WARNING: the $fromform->message array has been overwritten, do not use it anymore!
    $fromform->messagetrust = trusttext_trusted($modcontext);

    if ($fromform->edit)
    {           // Updating a post
        unset($fromform->groupid);
        $fromform->id = $fromform->edit;
        $message = '';

        //fix for bug #4314
        if (!$realpost = $DB->get_record('reactforum_posts', array('id' => $fromform->id)))
        {
            $realpost = new stdClass();
            $realpost->userid = -1;
        }


        // if user has edit any post capability
        // or has either startnewdiscussion or reply capability and is editting own post
        // then he can proceed
        // MDL-7066
        if (!(($realpost->userid == $USER->id && (has_capability('mod/reactforum:replypost', $modcontext)
                    || has_capability('mod/reactforum:startdiscussion', $modcontext))) ||
            has_capability('mod/reactforum:editanypost', $modcontext))
        )
        {
            print_error('cannotupdatepost', 'reactforum');
        }

        // If the user has access to all groups and they are changing the group, then update the post.
        if (isset($fromform->groupinfo) && has_capability('mod/reactforum:movediscussions', $modcontext))
        {
            if (empty($fromform->groupinfo))
            {
                $fromform->groupinfo = -1;
            }

            if (!reactforum_user_can_post_discussion($reactforum, $fromform->groupinfo, null, $cm, $modcontext))
            {
                print_error('cannotupdatepost', 'reactforum');
            }

            $DB->set_field('reactforum_discussions', 'groupid', $fromform->groupinfo, array('firstpost' => $fromform->id));
        }
        // When editing first post/discussion.
        if (!$fromform->parent)
        {
            if (has_capability('mod/reactforum:pindiscussions', $modcontext))
            {
                // Can change pinned if we have capability.
                $fromform->pinned = !empty($fromform->pinned) ? REACTFORUM_DISCUSSION_PINNED : REACTFORUM_DISCUSSION_UNPINNED;
            }
            else
            {
                // We don't have the capability to change so keep to previous value.
                unset($fromform->pinned);
            }
        }
        $updatepost = $fromform; //realpost
        $updatepost->reactforum = $reactforum->id;
        if (!reactforum_update_post($updatepost, $mform_post))
        {
            print_error("couldnotupdate", "reactforum", $errordestination);
        }

        // MDL-11818
        if (($reactforum->type == 'single') && ($updatepost->parent == '0'))
        { // updating first post of single discussion type -> updating reactforum intro
            $reactforum->intro = $updatepost->message;
            $reactforum->timemodified = time();
            $DB->update_record("reactforum", $reactforum);
        }

        if ($realpost->userid == $USER->id)
        {
            $message .= get_string("postupdated", "reactforum");
        }
        else
        {
            $realuser = $DB->get_record('user', array('id' => $realpost->userid));
            $message .= get_string("editedpostupdated", "reactforum", fullname($realuser));
        }

        $subscribemessage = reactforum_post_subscription($fromform, $reactforum, $discussion);
        if ($reactforum->type == 'single')
        {
            // Single discussion reactforums are an exception. We show
            // the reactforum itself since it only has one discussion
            // thread.
            $discussionurl = new moodle_url("/mod/reactforum/view.php", array('f' => $reactforum->id));
        }
        else
        {
            $discussionurl = new moodle_url("/mod/reactforum/discuss.php", array('d' => $discussion->id), 'p' . $fromform->id);
        }

        $params = array(
            'context' => $modcontext,
            'objectid' => $fromform->id,
            'other' => array(
                'discussionid' => $discussion->id,
                'reactforumid' => $reactforum->id,
                'reactforumtype' => $reactforum->type,
            )
        );

        if ($realpost->userid !== $USER->id)
        {
            $params['relateduserid'] = $realpost->userid;
        }



        $event = \mod_reactforum\event\post_updated::create($params);
        $event->add_record_snapshot('reactforum_discussions', $discussion);
        $event->trigger();

        // EDITING REACTIONS: START
        if($fromform->parent == 0 && $reactforum->reactiontype == 'discussion')
        {
            if($fromform->reactiontype != $discussion->reactiontype || $fromform->reactiontype == 'none')
            {
                $reactions = $DB->get_records('reactforum_reactions', array('discussion_id' => $discussion->id));
                foreach ($reactions as $reaction)
                {
                    reactforum_remove_reaction($reaction->id);
                }

                $editdiscussion = new stdClass();
                $editdiscussion->id = $discussion->id;
                $editdiscussion->reactiontype = $fromform->reactiontype;
                $DB->update_record('reactforum_discussions', $editdiscussion);
            }

            if($fromform->reactiontype == 'text' && isset($_POST['reactions']))
            {
                if(isset($_POST['reactions']['edit']))
                {
                    foreach($_POST['reactions']['edit'] as $reactionid => $reaction)
                    {
                        if(trim($reaction) == '')
                        {
                            continue;
                        }

                        $reactionobj = new stdClass();
                        $reactionobj->id = $reactionid;
                        $reactionobj->reaction = $reaction;
                        $DB->update_record('reactforum_reactions', $reactionobj);
                    }
                }

                if(isset($_POST['reactions']['delete']))
                {
                    foreach($_POST['reactions']['delete'] as $reactionid)
                    {
                        reactforum_remove_reaction($reactionid);
                    }
                }

                if(isset($_POST['reactions']['new']))
                {
                    $newreactions = array();
                    foreach ($_POST['reactions']['new'] as $reaction)
                    {
                        if(trim($reaction) == '')
                        {
                            continue;
                        }

                        $reactionobj = new stdClass();
                        $reactionobj->discussion_id = $discussion->id;
                        $reactionobj->reaction = $reaction;

                        array_push($newreactions, $reactionobj);
                    }

                    $DB->insert_records('reactforum_reactions', $newreactions);
                }
            }
            else if($fromform->reactiontype == 'image' && isset($_POST['reactions']))
            {
                foreach ($_POST['reactions']['edit'] as $reactionid => $tempfileid)
                {
                    if($tempfileid > 0)
                    {
                        if (!reactforum_save_temp($fs, $modcontext->id, $fs->get_file_by_id($tempfileid), $reactionid))
                        {
                            print_error("error", "reactforum", $errordestination);
                        }
                    }
                }

                if (isset($_POST['reactions']['delete']))
                {
                    foreach ($_POST['reactions']['delete'] as $reactionid)
                    {
                        reactforum_remove_reaction($reactionid);
                    }
                }

                foreach ($_POST['reactions']['new'] as $tempfileid)
                {
                    $reactionobj = new stdClass();
                    $reactionobj->discussion_id = $discussion->id;
                    $reactionobj->reaction = '';

                    $newreactionid = $DB->insert_record('reactforum_reactions', $reactionobj);

                    if(!reactforum_save_temp($fs, $modcontext->id, $fs->get_file_by_id($tempfileid), $newreactionid))
                    {
                        print_error("error", "reactforum", $errordestination);
                    }
                }
            }
        }
        // EDITTING REACTIONS: END

        redirect(
            reactforum_go_back_to($discussionurl),
            $message . $subscribemessage,
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );

    }
    else if ($fromform->discussion)
    { // Adding a new post to an existing discussion
        // Before we add this we must check that the user will not exceed the blocking threshold.
        reactforum_check_blocking_threshold($thresholdwarning);

        unset($fromform->groupid);
        $message = '';
        $addpost = $fromform;
        $addpost->reactforum = $reactforum->id;
        if ($fromform->id = reactforum_add_new_post($addpost, $mform_post))
        {
            $subscribemessage = reactforum_post_subscription($fromform, $reactforum, $discussion);

            if (!empty($fromform->mailnow))
            {
                $message .= get_string("postmailnow", "reactforum");
            }
            else
            {
                $message .= '<p>' . get_string("postaddedsuccess", "reactforum") . '</p>';
                $message .= '<p>' . get_string("postaddedtimeleft", "reactforum", format_time($CFG->maxeditingtime)) . '</p>';
            }

            if ($reactforum->type == 'single')
            {
                // Single discussion reactforums are an exception. We show
                // the reactforum itself since it only has one discussion
                // thread.
                $discussionurl = new moodle_url("/mod/reactforum/view.php", array('f' => $reactforum->id), 'p' . $fromform->id);
            }
            else
            {
                $discussionurl = new moodle_url("/mod/reactforum/discuss.php", array('d' => $discussion->id), 'p' . $fromform->id);
            }

            $params = array(
                'context' => $modcontext,
                'objectid' => $fromform->id,
                'other' => array(
                    'discussionid' => $discussion->id,
                    'reactforumid' => $reactforum->id,
                    'reactforumtype' => $reactforum->type,
                )
            );
            $event = \mod_reactforum\event\post_created::create($params);
            $event->add_record_snapshot('reactforum_posts', $fromform);
            $event->add_record_snapshot('reactforum_discussions', $discussion);
            $event->trigger();

            // Update completion state
            $completion = new completion_info($course);
            if ($completion->is_enabled($cm) &&
                ($reactforum->completionreplies || $reactforum->completionposts)
            )
            {
                $completion->update_state($cm, COMPLETION_COMPLETE);
            }

            redirect(
                reactforum_go_back_to($discussionurl),
                $message . $subscribemessage,
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );

        }
        else
        {
            print_error("couldnotadd", "reactforum", $errordestination);
        }
        exit;

    }
    else
    { // Adding a new discussion.
        // The location to redirect to after successfully posting.
        $redirectto = new moodle_url('view.php', array('f' => $fromform->reactforum));

        $fromform->mailnow = empty($fromform->mailnow) ? 0 : 1;

        $discussion = $fromform;
        $discussion->name = $fromform->subject;

        $newstopic = false;
        if ($reactforum->type == 'news' && !$fromform->parent)
        {
            $newstopic = true;
        }
        $discussion->timestart = $fromform->timestart;
        $discussion->timeend = $fromform->timeend;

        if (has_capability('mod/reactforum:pindiscussions', $modcontext) && !empty($fromform->pinned))
        {
            $discussion->pinned = REACTFORUM_DISCUSSION_PINNED;
        }
        else
        {
            $discussion->pinned = REACTFORUM_DISCUSSION_UNPINNED;
        }

        $allowedgroups = array();
        $groupstopostto = array();

        // If we are posting a copy to all groups the user has access to.
        if (isset($fromform->posttomygroups))
        {
            // Post to each of my groups.
            require_capability('mod/reactforum:canposttomygroups', $modcontext);

            // Fetch all of this user's groups.
            // Note: all groups are returned when in visible groups mode so we must manually filter.
            $allowedgroups = groups_get_activity_allowed_groups($cm);
            foreach ($allowedgroups as $groupid => $group)
            {
                if (reactforum_user_can_post_discussion($reactforum, $groupid, -1, $cm, $modcontext))
                {
                    $groupstopostto[] = $groupid;
                }
            }
        }
        else if (isset($fromform->groupinfo))
        {
            // Use the value provided in the dropdown group selection.
            $groupstopostto[] = $fromform->groupinfo;
            $redirectto->param('group', $fromform->groupinfo);
        }
        else if (isset($fromform->groupid) && !empty($fromform->groupid))
        {
            // Use the value provided in the hidden form element instead.
            $groupstopostto[] = $fromform->groupid;
            $redirectto->param('group', $fromform->groupid);
        }
        else
        {
            // Use the value for all participants instead.
            $groupstopostto[] = -1;
        }

        // Before we post this we must check that the user will not exceed the blocking threshold.
        reactforum_check_blocking_threshold($thresholdwarning);

        foreach ($groupstopostto as $group)
        {
            if (!reactforum_user_can_post_discussion($reactforum, $group, -1, $cm, $modcontext))
            {
                print_error('cannotcreatediscussion', 'reactforum');
            }

            $discussion->groupid = $group;
            $message = '';
            if ($discussion->id = reactforum_add_discussion($discussion, $mform_post))
            {
                // REACTIONS_ADD: START
                if($reactforum->reactiontype == 'discussion')
                {
                    if ($fromform->reactiontype == 'text')
                    {
                        if (isset($_POST['reactions']['new']))
                        {
                            $reactionobjects = array();

                            foreach ($_POST['reactions']['new'] as $reaction)
                            {
                                if (trim($reaction) == '')
                                {
                                    continue;
                                }

                                $reactionobj = new stdClass();
                                $reactionobj->discussion_id = $discussion->id;
                                $reactionobj->reaction = $reaction;

                                array_push($reactionobjects, $reactionobj);
                            }

                            $DB->insert_records('reactforum_reactions', $reactionobjects);
                        }
                    }
                    else if ($fromform->reactiontype == 'image')
                    {
                        foreach ($tempfiles['new'] as $tempfile)
                        {
                            $reactionobj = new stdClass();
                            $reactionobj->discussion_id = $discussion->id;
                            $reactionobj->reaction = '';

                            $newreactionid = $DB->insert_record('reactforum_reactions', $reactionobj);

                            if (!reactforum_save_temp($fs, $modcontext->id, $tempfile, $newreactionid))
                            {
                                print_error("error", "reactforum", $errordestination);
                            }
                        }
                    }
                }
                // REACTIONS_ADD: END

                $params = array(
                    'context' => $modcontext,
                    'objectid' => $discussion->id,
                    'other' => array(
                        'reactforumid' => $reactforum->id,
                    )
                );
                $event = \mod_reactforum\event\discussion_created::create($params);
                $event->add_record_snapshot('reactforum_discussions', $discussion);
                $event->trigger();

                if ($fromform->mailnow)
                {
                    $message .= get_string("postmailnow", "reactforum");
                }
                else
                {
                    $message .= '<p>' . get_string("postaddedsuccess", "reactforum") . '</p>';
                    $message .= '<p>' . get_string("postaddedtimeleft", "reactforum", format_time($CFG->maxeditingtime)) . '</p>';
                }

                $subscribemessage = reactforum_post_subscription($fromform, $reactforum, $discussion);
            }
            else
            {
                print_error("couldnotadd", "reactforum", $errordestination);
            }
        }

        // Update completion status.
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) &&
            ($reactforum->completiondiscussions || $reactforum->completionposts)
        )
        {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

        // Redirect back to the discussion.
        redirect(
            reactforum_go_back_to($redirectto->out()),
            $message . $subscribemessage,
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    // Clear temp files
    reactforum_clear_temp($fs);
}


// To get here they need to edit a post, and the $post
// variable will be loaded with all the particulars,
// so bring up the form.

// $course, $reactforum are defined.  $discussion is for edit and reply only.

if ($post->discussion)
{
    if (!$toppost = $DB->get_record("reactforum_posts", array("discussion" => $post->discussion, "parent" => 0)))
    {
        print_error('cannotfindparentpost', 'reactforum', '', $post->id);
    }
}
else
{
    $toppost = new stdClass();
    $toppost->subject = ($reactforum->type == "news") ? get_string("addanewtopic", "reactforum") :
        get_string("addanewdiscussion", "reactforum");
}

if (empty($post->edit))
{
    $post->edit = '';
}

if (empty($discussion->name))
{
    if (empty($discussion))
    {
        $discussion = new stdClass();
    }
    $discussion->name = $reactforum->name;
}
if ($reactforum->type == 'single')
{
    // There is only one discussion thread for this reactforum type. We should
    // not show the discussion name (same as reactforum name in this case) in
    // the breadcrumbs.
    $strdiscussionname = '';
}
else
{
    // Show the discussion name in the breadcrumbs.
    $strdiscussionname = format_string($discussion->name) . ':';
}

$forcefocus = empty($reply) ? NULL : 'message';

if (!empty($discussion->id))
{
    $PAGE->navbar->add(format_string($toppost->subject, true), "discuss.php?d=$discussion->id");
}

if ($post->parent)
{
    $PAGE->navbar->add(get_string('reply', 'reactforum'));
}

if ($edit)
{
    $PAGE->navbar->add(get_string('edit', 'reactforum'));
}

$PAGE->set_title("$course->shortname: $strdiscussionname " . format_string($toppost->subject));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($reactforum->name), 2);

// checkup
if (!empty($parent) && !reactforum_user_can_see_post($reactforum, $discussion, $post, null, $cm))
{
    print_error('cannotreply', 'reactforum');
}
if (empty($parent) && empty($edit) && !reactforum_user_can_post_discussion($reactforum, $groupid, -1, $cm, $modcontext))
{
    print_error('cannotcreatediscussion', 'reactforum');
}

if ($reactforum->type == 'qanda'
    && !has_capability('mod/reactforum:viewqandawithoutposting', $modcontext)
    && !empty($discussion->id)
    && !reactforum_user_has_posted($reactforum->id, $discussion->id, $USER->id)
)
{
    echo $OUTPUT->notification(get_string('qandanotify', 'reactforum'));
}

// If there is a warning message and we are not editing a post we need to handle the warning.
if (!empty($thresholdwarning) && !$edit)
{
    // Here we want to throw an exception if they are no longer allowed to post.
    reactforum_check_blocking_threshold($thresholdwarning);
}

if (!empty($parent))
{
    if (!$discussion = $DB->get_record('reactforum_discussions', array('id' => $parent->discussion)))
    {
        print_error('notpartofdiscussion', 'reactforum');
    }

    reactforum_print_post($parent, $discussion, $reactforum, $cm, $course, false, false, false);
    if (empty($post->edit))
    {
        if ($reactforum->type != 'qanda' || reactforum_user_can_see_discussion($reactforum, $discussion, $modcontext))
        {
            $reactforumtracked = reactforum_tp_is_tracked($reactforum);
            $posts = reactforum_get_all_discussion_posts($discussion->id, "created ASC", $reactforumtracked);
            reactforum_print_posts_threaded($course, $cm, $reactforum, $discussion, $parent, 0, false, $reactforumtracked, $posts);
        }
    }
}
else
{
    if (!empty($reactforum->intro))
    {
        echo $OUTPUT->box(format_module_intro('reactforum', $reactforum, $cm->id), 'generalbox', 'intro');

        if (!empty($CFG->enableplagiarism))
        {
            require_once($CFG->libdir . '/plagiarismlib.php');
            echo plagiarism_print_disclosure($cm->id);
        }
    }
}

if (!empty($formheading))
{
    echo $OUTPUT->heading($formheading, 2, array('class' => 'accesshide'));
}
$mform_post->display();

echo $OUTPUT->footer();
