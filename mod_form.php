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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_reactforum_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $DB, $PAGE;

        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('reactforumname', 'reactforum'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('reactforumintro', 'reactforum'));

        $reactforumtypes = reactforum_get_reactforum_types();
        core_collator::asort($reactforumtypes, core_collator::SORT_STRING);
        $mform->addElement('select', 'type', get_string('reactforumtype', 'reactforum'), $reactforumtypes);
        $mform->addHelpButton('type', 'reactforumtype', 'reactforum');
        $mform->setDefault('type', 'general');

        $radioarray = array();
        array_push($radioarray, $mform->createElement('radio', 'reactiontype', '', get_string('reactionstype_text', 'reactforum'), 'text'));
        array_push($radioarray, $mform->createElement('radio', 'reactiontype', '', get_string('reactionstype_image', 'reactforum'), 'image'));
        array_push($radioarray, $mform->createElement('radio', 'reactiontype', '', get_string('reactionstype_discussion', 'reactforum'), 'discussion'));
        array_push($radioarray, $mform->createElement('radio', 'reactiontype', '', get_string('reactionstype_none', 'reactforum'), 'none'));
        $mform->addGroup($radioarray, 'reactiontype', get_string('reactionstype', 'reactforum'), array('<br>'), false);

        $mform->addGroup(null, 'reactions', get_string('reactions', 'reactforum'), array('<br>'), false);

        $mform->addElement('filepicker', 'reactionimage', '', null, array('maxbytes' => 0, 'accepted_types' => array('image')));

        $mform->addElement('checkbox', 'reactionallreplies', get_string('reactions_allreplies', 'reactforum'));
        $mform->addHelpButton('reactionallreplies', 'reactions_allreplies', 'reactforum');

        if(isset($_GET['update']))
        {
            $cmid = $_GET['update'];
            $cm = get_coursemodule_from_id('reactforum', $cmid);
            $rid = $cm->instance;

            $reactforum = $DB->get_record('reactforum', array('id' => $rid));
            $reactions_values = array();
            $reactions = $DB->get_records("reactforum_reactions", array("reactforum_id" => $rid));

            foreach($reactions as $reactionObj)
            {
                array_push($reactions_values, array("id" => $reactionObj->id, "value" => $reactionObj->reaction));
            }

            $reactions_js = json_encode(array(
                "type" => $reactforum->reactiontype,
                "reactions" => $reactions_values,
                'level' => 'reactforum'
            ));

            $PAGE->requires->js_init_code("reactions_oldvalues = {$reactions_js};", false);
        }

        reactforum_form_call_js($PAGE);

        // Attachments and word count.
        $mform->addElement('header', 'attachmentswordcounthdr', get_string('attachmentswordcount', 'reactforum'));

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes, 0, $CFG->reactforum_maxbytes);
        $choices[1] = get_string('uploadnotallowed');
        $mform->addElement('select', 'maxbytes', get_string('maxattachmentsize', 'reactforum'), $choices);
        $mform->addHelpButton('maxbytes', 'maxattachmentsize', 'reactforum');
        $mform->setDefault('maxbytes', $CFG->reactforum_maxbytes);

        $choices = array(
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100
        );
        $mform->addElement('select', 'maxattachments', get_string('maxattachments', 'reactforum'), $choices);
        $mform->addHelpButton('maxattachments', 'maxattachments', 'reactforum');
        $mform->setDefault('maxattachments', $CFG->reactforum_maxattachments);

        $mform->addElement('selectyesno', 'displaywordcount', get_string('displaywordcount', 'reactforum'));
        $mform->addHelpButton('displaywordcount', 'displaywordcount', 'reactforum');
        $mform->setDefault('displaywordcount', 0);

        // Subscription and tracking.
        $mform->addElement('header', 'subscriptionandtrackinghdr', get_string('subscriptionandtracking', 'reactforum'));

        $options = array();
        $options[REACTFORUM_CHOOSESUBSCRIBE] = get_string('subscriptionoptional', 'reactforum');
        $options[REACTFORUM_FORCESUBSCRIBE] = get_string('subscriptionforced', 'reactforum');
        $options[REACTFORUM_INITIALSUBSCRIBE] = get_string('subscriptionauto', 'reactforum');
        $options[REACTFORUM_DISALLOWSUBSCRIBE] = get_string('subscriptiondisabled','reactforum');
        $mform->addElement('select', 'forcesubscribe', get_string('subscriptionmode', 'reactforum'), $options);
        $mform->addHelpButton('forcesubscribe', 'subscriptionmode', 'reactforum');

        $options = array();
        $options[REACTFORUM_TRACKING_OPTIONAL] = get_string('trackingoptional', 'reactforum');
        $options[REACTFORUM_TRACKING_OFF] = get_string('trackingoff', 'reactforum');
        if ($CFG->reactforum_allowforcedreadtracking) {
            $options[REACTFORUM_TRACKING_FORCED] = get_string('trackingon', 'reactforum');
        }
        $mform->addElement('select', 'trackingtype', get_string('trackingtype', 'reactforum'), $options);
        $mform->addHelpButton('trackingtype', 'trackingtype', 'reactforum');
        $default = $CFG->reactforum_trackingtype;
        if ((!$CFG->reactforum_allowforcedreadtracking) && ($default == REACTFORUM_TRACKING_FORCED)) {
            $default = REACTFORUM_TRACKING_OPTIONAL;
        }
        $mform->setDefault('trackingtype', $default);

        if ($CFG->enablerssfeeds && isset($CFG->reactforum_enablerssfeeds) && $CFG->reactforum_enablerssfeeds) {
//-------------------------------------------------------------------------------
            $mform->addElement('header', 'rssheader', get_string('rss'));
            $choices = array();
            $choices[0] = get_string('none');
            $choices[1] = get_string('discussions', 'reactforum');
            $choices[2] = get_string('posts', 'reactforum');
            $mform->addElement('select', 'rsstype', get_string('rsstype'), $choices);
            $mform->addHelpButton('rsstype', 'rsstype', 'reactforum');
            if (isset($CFG->reactforum_rsstype)) {
                $mform->setDefault('rsstype', $CFG->reactforum_rsstype);
            }

            $choices = array();
            $choices[0] = '0';
            $choices[1] = '1';
            $choices[2] = '2';
            $choices[3] = '3';
            $choices[4] = '4';
            $choices[5] = '5';
            $choices[10] = '10';
            $choices[15] = '15';
            $choices[20] = '20';
            $choices[25] = '25';
            $choices[30] = '30';
            $choices[40] = '40';
            $choices[50] = '50';
            $mform->addElement('select', 'rssarticles', get_string('rssarticles'), $choices);
            $mform->addHelpButton('rssarticles', 'rssarticles', 'reactforum');
            $mform->disabledIf('rssarticles', 'rsstype', 'eq', '0');
            if (isset($CFG->reactforum_rssarticles)) {
                $mform->setDefault('rssarticles', $CFG->reactforum_rssarticles);
            }
        }

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'blockafterheader', get_string('blockafter', 'reactforum'));
        $options = array();
        $options[0] = get_string('blockperioddisabled','reactforum');
        $options[60*60*24]   = '1 '.get_string('day');
        $options[60*60*24*2] = '2 '.get_string('days');
        $options[60*60*24*3] = '3 '.get_string('days');
        $options[60*60*24*4] = '4 '.get_string('days');
        $options[60*60*24*5] = '5 '.get_string('days');
        $options[60*60*24*6] = '6 '.get_string('days');
        $options[60*60*24*7] = '1 '.get_string('week');
        $mform->addElement('select', 'blockperiod', get_string('blockperiod', 'reactforum'), $options);
        $mform->addHelpButton('blockperiod', 'blockperiod', 'reactforum');

        $mform->addElement('text', 'blockafter', get_string('blockafter', 'reactforum'));
        $mform->setType('blockafter', PARAM_INT);
        $mform->setDefault('blockafter', '0');
        $mform->addRule('blockafter', null, 'numeric', null, 'client');
        $mform->addHelpButton('blockafter', 'blockafter', 'reactforum');
        $mform->disabledIf('blockafter', 'blockperiod', 'eq', 0);

        $mform->addElement('text', 'warnafter', get_string('warnafter', 'reactforum'));
        $mform->setType('warnafter', PARAM_INT);
        $mform->setDefault('warnafter', '0');
        $mform->addRule('warnafter', null, 'numeric', null, 'client');
        $mform->addHelpButton('warnafter', 'warnafter', 'reactforum');
        $mform->disabledIf('warnafter', 'blockperiod', 'eq', 0);

        $coursecontext = context_course::instance($COURSE->id);
        plagiarism_get_form_elements_module($mform, $coursecontext, 'mod_reactforum');

//-------------------------------------------------------------------------------

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons();
    }

    function definition_after_data() {
        parent::definition_after_data();
        $mform     =& $this->_form;
        $type      =& $mform->getElement('type');
        $typevalue = $mform->getElementValue('type');

        //we don't want to have these appear as possible selections in the form but
        //we want the form to display them if they are set.
        if ($typevalue[0]=='news') {
            $type->addOption(get_string('namenews', 'reactforum'), 'news');
            $mform->addHelpButton('type', 'namenews', 'reactforum');
            $type->freeze();
            $type->setPersistantFreeze(true);
        }
        if ($typevalue[0]=='social') {
            $type->addOption(get_string('namesocial', 'reactforum'), 'social');
            $type->freeze();
            $type->setPersistantFreeze(true);
        }

    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completiondiscussionsenabled']=
            !empty($default_values['completiondiscussions']) ? 1 : 0;
        if (empty($default_values['completiondiscussions'])) {
            $default_values['completiondiscussions']=1;
        }
        $default_values['completionrepliesenabled']=
            !empty($default_values['completionreplies']) ? 1 : 0;
        if (empty($default_values['completionreplies'])) {
            $default_values['completionreplies']=1;
        }
        $default_values['completionpostsenabled']=
            !empty($default_values['completionposts']) ? 1 : 0;
        if (empty($default_values['completionposts'])) {
            $default_values['completionposts']=1;
        }
    }

      function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionpostsenabled', '', get_string('completionposts','reactforum'));
        $group[] =& $mform->createElement('text', 'completionposts', '', array('size'=>3));
        $mform->setType('completionposts',PARAM_INT);
        $mform->addGroup($group, 'completionpostsgroup', get_string('completionpostsgroup','reactforum'), array(' '), false);
        $mform->disabledIf('completionposts','completionpostsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completiondiscussionsenabled', '', get_string('completiondiscussions','reactforum'));
        $group[] =& $mform->createElement('text', 'completiondiscussions', '', array('size'=>3));
        $mform->setType('completiondiscussions',PARAM_INT);
        $mform->addGroup($group, 'completiondiscussionsgroup', get_string('completiondiscussionsgroup','reactforum'), array(' '), false);
        $mform->disabledIf('completiondiscussions','completiondiscussionsenabled','notchecked');

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionrepliesenabled', '', get_string('completionreplies','reactforum'));
        $group[] =& $mform->createElement('text', 'completionreplies', '', array('size'=>3));
        $mform->setType('completionreplies',PARAM_INT);
        $mform->addGroup($group, 'completionrepliesgroup', get_string('completionrepliesgroup','reactforum'), array(' '), false);
        $mform->disabledIf('completionreplies','completionrepliesenabled','notchecked');

        return array('completiondiscussionsgroup','completionrepliesgroup','completionpostsgroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completiondiscussionsenabled']) && $data['completiondiscussions']!=0) ||
            (!empty($data['completionrepliesenabled']) && $data['completionreplies']!=0) ||
            (!empty($data['completionpostsenabled']) && $data['completionposts']!=0);
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiondiscussionsenabled) || !$autocompletion) {
                $data->completiondiscussions = 0;
            }
            if (empty($data->completionrepliesenabled) || !$autocompletion) {
                $data->completionreplies = 0;
            }
            if (empty($data->completionpostsenabled) || !$autocompletion) {
                $data->completionposts = 0;
            }
        }
        return $data;
    }
}

