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
 * @package   mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/reactforum/lib.php');
require_once($CFG->libdir . '/rsslib.php');

$id = optional_param('id', 0, PARAM_INT);                   // Course id
$subscribe = optional_param('subscribe', null, PARAM_INT);  // Subscribe/Unsubscribe all reactforums

$url = new moodle_url('/mod/reactforum/index.php', array('id'=>$id));
if ($subscribe !== null) {
    require_sesskey();
    $url->param('subscribe', $subscribe);
}
$PAGE->set_url($url);

if ($id) {
    if (! $course = $DB->get_record('course', array('id' => $id))) {
        print_error('invalidcourseid');
    }
} else {
    $course = get_site();
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');
$coursecontext = context_course::instance($course->id);


unset($SESSION->fromdiscussion);

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_reactforum\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strreactforums       = get_string('reactforums', 'reactforum');
$strreactforum        = get_string('reactforum', 'reactforum');
$strdescription  = get_string('description');
$strdiscussions  = get_string('discussions', 'reactforum');
$strsubscribed   = get_string('subscribed', 'reactforum');
$strunreadposts  = get_string('unreadposts', 'reactforum');
$strtracking     = get_string('tracking', 'reactforum');
$strmarkallread  = get_string('markallread', 'reactforum');
$strtrackreactforum   = get_string('trackreactforum', 'reactforum');
$strnotrackreactforum = get_string('notrackreactforum', 'reactforum');
$strsubscribe    = get_string('subscribe', 'reactforum');
$strunsubscribe  = get_string('unsubscribe', 'reactforum');
$stryes          = get_string('yes');
$strno           = get_string('no');
$strrss          = get_string('rss');
$stremaildigest  = get_string('emaildigest');

$searchform = reactforum_search_form($course);

// Start of the table for General ReactForums

$generaltable = new html_table();
$generaltable->head  = array ($strreactforum, $strdescription, $strdiscussions);
$generaltable->align = array ('left', 'left', 'center');

if ($usetracking = reactforum_tp_can_track_reactforums()) {
    $untracked = reactforum_tp_get_untracked_reactforums($USER->id, $course->id);

    $generaltable->head[] = $strunreadposts;
    $generaltable->align[] = 'center';

    $generaltable->head[] = $strtracking;
    $generaltable->align[] = 'center';
}

// Fill the subscription cache for this course and user combination.
\mod_reactforum\subscriptions::fill_subscription_cache_for_course($course->id, $USER->id);

$can_subscribe = is_enrolled($coursecontext);
if ($can_subscribe) {
    $generaltable->head[] = $strsubscribed;
    $generaltable->align[] = 'center';

    $generaltable->head[] = $stremaildigest . ' ' . $OUTPUT->help_icon('emaildigesttype', 'mod_reactforum');
    $generaltable->align[] = 'center';

    // Retrieve the list of reactforum digest options for later.
    $digestoptions = reactforum_get_user_digest_options();
    $digestoptions_selector = new single_select(new moodle_url('/mod/reactforum/maildigest.php',
        array(
            'backtoindex' => 1,
        )),
        'maildigest',
        $digestoptions,
        null,
        '');
    $digestoptions_selector->method = 'post';
}

if ($show_rss = (($can_subscribe || $course->id == SITEID) &&
                 isset($CFG->enablerssfeeds) && isset($CFG->reactforum_enablerssfeeds) &&
                 $CFG->enablerssfeeds && $CFG->reactforum_enablerssfeeds)) {
    $generaltable->head[] = $strrss;
    $generaltable->align[] = 'center';
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();

// Parse and organise all the reactforums.  Most reactforums are course modules but
// some special ones are not.  These get placed in the general reactforums
// category with the reactforums in section 0.

$reactforums = $DB->get_records_sql("
    SELECT f.*,
           d.maildigest
      FROM {reactforum} f
 LEFT JOIN {reactforum_digests} d ON d.reactforum = f.id AND d.userid = ?
     WHERE f.course = ?
    ", array($USER->id, $course->id));

$generalreactforums  = array();
$learningreactforums = array();
$modinfo = get_fast_modinfo($course);

foreach ($modinfo->get_instances_of('reactforum') as $reactforumid=>$cm) {
    if (!$cm->uservisible or !isset($reactforums[$reactforumid])) {
        continue;
    }

    $reactforum = $reactforums[$reactforumid];

    if (!$context = context_module::instance($cm->id, IGNORE_MISSING)) {
        continue;   // Shouldn't happen
    }

    if (!has_capability('mod/reactforum:viewdiscussion', $context)) {
        continue;
    }

    // fill two type array - order in modinfo is the same as in course
    if ($reactforum->type == 'news' or $reactforum->type == 'social') {
        $generalreactforums[$reactforum->id] = $reactforum;

    } else if ($course->id == SITEID or empty($cm->sectionnum)) {
        $generalreactforums[$reactforum->id] = $reactforum;

    } else {
        $learningreactforums[$reactforum->id] = $reactforum;
    }
}

// Do course wide subscribe/unsubscribe if requested
if (!is_null($subscribe)) {
    if (isguestuser() or !$can_subscribe) {
        // There should not be any links leading to this place, just redirect.
        redirect(
                new moodle_url('/mod/reactforum/index.php', array('id' => $id)),
                get_string('subscribeenrolledonly', 'reactforum'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
    }
    // Can proceed now, the user is not guest and is enrolled
    foreach ($modinfo->get_instances_of('reactforum') as $reactforumid=>$cm) {
        $reactforum = $reactforums[$reactforumid];
        $modcontext = context_module::instance($cm->id);
        $cansub = false;

        if (has_capability('mod/reactforum:viewdiscussion', $modcontext)) {
            $cansub = true;
        }
        if ($cansub && $cm->visible == 0 &&
            !has_capability('mod/reactforum:managesubscriptions', $modcontext))
        {
            $cansub = false;
        }
        if (!\mod_reactforum\subscriptions::is_forcesubscribed($reactforum)) {
            $subscribed = \mod_reactforum\subscriptions::is_subscribed($USER->id, $reactforum, null, $cm);
            $canmanageactivities = has_capability('moodle/course:manageactivities', $coursecontext, $USER->id);
            if (($canmanageactivities || \mod_reactforum\subscriptions::is_subscribable($reactforum)) && $subscribe && !$subscribed && $cansub) {
                \mod_reactforum\subscriptions::subscribe_user($USER->id, $reactforum, $modcontext, true);
            } else if (!$subscribe && $subscribed) {
                \mod_reactforum\subscriptions::unsubscribe_user($USER->id, $reactforum, $modcontext, true);
            }
        }
    }
    $returnto = reactforum_go_back_to(new moodle_url('/mod/reactforum/index.php', array('id' => $course->id)));
    $shortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
    if ($subscribe) {
        redirect(
                $returnto,
                get_string('nowallsubscribed', 'reactforum', $shortname),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
    } else {
        redirect(
                $returnto,
                get_string('nowallunsubscribed', 'reactforum', $shortname),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
    }
}

/// First, let's process the general reactforums and build up a display

if ($generalreactforums) {
    foreach ($generalreactforums as $reactforum) {
        $cm      = $modinfo->instances['reactforum'][$reactforum->id];
        $context = context_module::instance($cm->id);

        $count = reactforum_count_discussions($reactforum, $cm, $course);

        if ($usetracking) {
            if ($reactforum->trackingtype == REACTFORUM_TRACKING_OFF) {
                $unreadlink  = '-';
                $trackedlink = '-';

            } else {
                if (isset($untracked[$reactforum->id])) {
                        $unreadlink  = '-';
                } else if ($unread = reactforum_tp_count_reactforum_unread_posts($cm, $course)) {
                        $unreadlink = '<span class="unread"><a href="view.php?f='.$reactforum->id.'">'.$unread.'</a>';
                    $unreadlink .= '<a title="'.$strmarkallread.'" href="markposts.php?f='.
                                   $reactforum->id.'&amp;mark=read&amp;sesskey=' . sesskey() . '"><img src="'.$OUTPUT->pix_url('t/markasread') . '" alt="'.$strmarkallread.'" class="iconsmall" /></a></span>';
                } else {
                    $unreadlink = '<span class="read">0</span>';
                }

                if (($reactforum->trackingtype == REACTFORUM_TRACKING_FORCED) && ($CFG->reactforum_allowforcedreadtracking)) {
                    $trackedlink = $stryes;
                } else if ($reactforum->trackingtype === REACTFORUM_TRACKING_OFF || ($USER->trackreactforums == 0)) {
                    $trackedlink = '-';
                } else {
                    $aurl = new moodle_url('/mod/reactforum/settracking.php', array(
                            'id' => $reactforum->id,
                            'sesskey' => sesskey(),
                        ));
                    if (!isset($untracked[$reactforum->id])) {
                        $trackedlink = $OUTPUT->single_button($aurl, $stryes, 'post', array('title'=>$strnotrackreactforum));
                    } else {
                        $trackedlink = $OUTPUT->single_button($aurl, $strno, 'post', array('title'=>$strtrackreactforum));
                    }
                }
            }
        }

        $reactforum->intro = shorten_text(format_module_intro('reactforum', $reactforum, $cm->id), $CFG->reactforum_shortpost);
        $reactforumname = format_string($reactforum->name, true);

        if ($cm->visible) {
            $style = '';
        } else {
            $style = 'class="dimmed"';
        }
        $reactforumlink = "<a href=\"view.php?f=$reactforum->id\" $style>".format_string($reactforum->name,true)."</a>";
        $discussionlink = "<a href=\"view.php?f=$reactforum->id\" $style>".$count."</a>";

        $row = array ($reactforumlink, $reactforum->intro, $discussionlink);
        if ($usetracking) {
            $row[] = $unreadlink;
            $row[] = $trackedlink;    // Tracking.
        }

        if ($can_subscribe) {
            $row[] = reactforum_get_subscribe_link($reactforum, $context, array('subscribed' => $stryes,
                    'unsubscribed' => $strno, 'forcesubscribed' => $stryes,
                    'cantsubscribe' => '-'), false, false, true);

            $digestoptions_selector->url->param('id', $reactforum->id);
            if ($reactforum->maildigest === null) {
                $digestoptions_selector->selected = -1;
            } else {
                $digestoptions_selector->selected = $reactforum->maildigest;
            }
            $row[] = $OUTPUT->render($digestoptions_selector);
        }

        //If this reactforum has RSS activated, calculate it
        if ($show_rss) {
            if ($reactforum->rsstype and $reactforum->rssarticles) {
                //Calculate the tooltip text
                if ($reactforum->rsstype == 1) {
                    $tooltiptext = get_string('rsssubscriberssdiscussions', 'reactforum');
                } else {
                    $tooltiptext = get_string('rsssubscriberssposts', 'reactforum');
                }

                if (!isloggedin() && $course->id == SITEID) {
                    $userid = guest_user()->id;
                } else {
                    $userid = $USER->id;
                }
                //Get html code for RSS link
                $row[] = rss_get_link($context->id, $userid, 'mod_reactforum', $reactforum->id, $tooltiptext);
            } else {
                $row[] = '&nbsp;';
            }
        }

        $generaltable->data[] = $row;
    }
}


// Start of the table for Learning ReactForums
$learningtable = new html_table();
$learningtable->head  = array ($strreactforum, $strdescription, $strdiscussions);
$learningtable->align = array ('left', 'left', 'center');

if ($usetracking) {
    $learningtable->head[] = $strunreadposts;
    $learningtable->align[] = 'center';

    $learningtable->head[] = $strtracking;
    $learningtable->align[] = 'center';
}

if ($can_subscribe) {
    $learningtable->head[] = $strsubscribed;
    $learningtable->align[] = 'center';

    $learningtable->head[] = $stremaildigest . ' ' . $OUTPUT->help_icon('emaildigesttype', 'mod_reactforum');
    $learningtable->align[] = 'center';
}

if ($show_rss = (($can_subscribe || $course->id == SITEID) &&
                 isset($CFG->enablerssfeeds) && isset($CFG->reactforum_enablerssfeeds) &&
                 $CFG->enablerssfeeds && $CFG->reactforum_enablerssfeeds)) {
    $learningtable->head[] = $strrss;
    $learningtable->align[] = 'center';
}

/// Now let's process the learning reactforums

if ($course->id != SITEID) {    // Only real courses have learning reactforums
    // 'format_.'$course->format only applicable when not SITEID (format_site is not a format)
    $strsectionname  = get_string('sectionname', 'format_'.$course->format);
    // Add extra field for section number, at the front
    array_unshift($learningtable->head, $strsectionname);
    array_unshift($learningtable->align, 'center');


    if ($learningreactforums) {
        $currentsection = '';
            foreach ($learningreactforums as $reactforum) {
            $cm      = $modinfo->instances['reactforum'][$reactforum->id];
            $context = context_module::instance($cm->id);

            $count = reactforum_count_discussions($reactforum, $cm, $course);

            if ($usetracking) {
                if ($reactforum->trackingtype == REACTFORUM_TRACKING_OFF) {
                    $unreadlink  = '-';
                    $trackedlink = '-';

                } else {
                    if (isset($untracked[$reactforum->id])) {
                        $unreadlink  = '-';
                    } else if ($unread = reactforum_tp_count_reactforum_unread_posts($cm, $course)) {
                        $unreadlink = '<span class="unread"><a href="view.php?f='.$reactforum->id.'">'.$unread.'</a>';
                        $unreadlink .= '<a title="'.$strmarkallread.'" href="markposts.php?f='.
                                       $reactforum->id.'&amp;mark=read&sesskey=' . sesskey() . '"><img src="'.$OUTPUT->pix_url('t/markasread') . '" alt="'.$strmarkallread.'" class="iconsmall" /></a></span>';
                    } else {
                        $unreadlink = '<span class="read">0</span>';
                    }

                    if (($reactforum->trackingtype == REACTFORUM_TRACKING_FORCED) && ($CFG->reactforum_allowforcedreadtracking)) {
                        $trackedlink = $stryes;
                    } else if ($reactforum->trackingtype === REACTFORUM_TRACKING_OFF || ($USER->trackreactforums == 0)) {
                        $trackedlink = '-';
                    } else {
                        $aurl = new moodle_url('/mod/reactforum/settracking.php', array('id'=>$reactforum->id));
                        if (!isset($untracked[$reactforum->id])) {
                            $trackedlink = $OUTPUT->single_button($aurl, $stryes, 'post', array('title'=>$strnotrackreactforum));
                        } else {
                            $trackedlink = $OUTPUT->single_button($aurl, $strno, 'post', array('title'=>$strtrackreactforum));
                        }
                    }
                }
            }

            $reactforum->intro = shorten_text(format_module_intro('reactforum', $reactforum, $cm->id), $CFG->reactforum_shortpost);

            if ($cm->sectionnum != $currentsection) {
                $printsection = get_section_name($course, $cm->sectionnum);
                if ($currentsection) {
                    $learningtable->data[] = 'hr';
                }
                $currentsection = $cm->sectionnum;
            } else {
                $printsection = '';
            }

            $reactforumname = format_string($reactforum->name,true);

            if ($cm->visible) {
                $style = '';
            } else {
                $style = 'class="dimmed"';
            }
            $reactforumlink = "<a href=\"view.php?f=$reactforum->id\" $style>".format_string($reactforum->name,true)."</a>";
            $discussionlink = "<a href=\"view.php?f=$reactforum->id\" $style>".$count."</a>";

            $row = array ($printsection, $reactforumlink, $reactforum->intro, $discussionlink);
            if ($usetracking) {
                $row[] = $unreadlink;
                $row[] = $trackedlink;    // Tracking.
            }

            if ($can_subscribe) {
                $row[] = reactforum_get_subscribe_link($reactforum, $context, array('subscribed' => $stryes,
                    'unsubscribed' => $strno, 'forcesubscribed' => $stryes,
                    'cantsubscribe' => '-'), false, false, true);

                $digestoptions_selector->url->param('id', $reactforum->id);
                if ($reactforum->maildigest === null) {
                    $digestoptions_selector->selected = -1;
                } else {
                    $digestoptions_selector->selected = $reactforum->maildigest;
                }
                $row[] = $OUTPUT->render($digestoptions_selector);
            }

            //If this reactforum has RSS activated, calculate it
            if ($show_rss) {
                if ($reactforum->rsstype and $reactforum->rssarticles) {
                    //Calculate the tolltip text
                    if ($reactforum->rsstype == 1) {
                        $tooltiptext = get_string('rsssubscriberssdiscussions', 'reactforum');
                    } else {
                        $tooltiptext = get_string('rsssubscriberssposts', 'reactforum');
                    }
                    //Get html code for RSS link
                    $row[] = rss_get_link($context->id, $USER->id, 'mod_reactforum', $reactforum->id, $tooltiptext);
                } else {
                    $row[] = '&nbsp;';
                }
            }

            $learningtable->data[] = $row;
        }
    }
}


/// Output the page
$PAGE->navbar->add($strreactforums);
$PAGE->set_title("$course->shortname: $strreactforums");
$PAGE->set_heading($course->fullname);
$PAGE->set_button($searchform);
echo $OUTPUT->header();

// Show the subscribe all options only to non-guest, enrolled users
if (!isguestuser() && isloggedin() && $can_subscribe) {
    echo $OUTPUT->box_start('subscription');
    echo html_writer::tag('div',
        html_writer::link(new moodle_url('/mod/reactforum/index.php', array('id'=>$course->id, 'subscribe'=>1, 'sesskey'=>sesskey())),
            get_string('allsubscribe', 'reactforum')),
        array('class'=>'helplink'));
    echo html_writer::tag('div',
        html_writer::link(new moodle_url('/mod/reactforum/index.php', array('id'=>$course->id, 'subscribe'=>0, 'sesskey'=>sesskey())),
            get_string('allunsubscribe', 'reactforum')),
        array('class'=>'helplink'));
    echo $OUTPUT->box_end();
    echo $OUTPUT->box('&nbsp;', 'clearer');
}

if ($generalreactforums) {
    echo $OUTPUT->heading(get_string('generalreactforums', 'reactforum'), 2);
    echo html_writer::table($generaltable);
}

if ($learningreactforums) {
    echo $OUTPUT->heading(get_string('learningreactforums', 'reactforum'), 2);
    echo html_writer::table($learningtable);
}

echo $OUTPUT->footer();

