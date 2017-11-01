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
 * @package    mod_reactforum
 * @subpackage backup-moodle2
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_reactforum_activity_task
 */

/**
 * Define the complete reactforum structure for backup, with file and id annotations
 */
class backup_reactforum_activity_structure_step extends backup_activity_structure_step
{

    protected function define_structure()
    {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated

        $reactforum = new backup_nested_element('reactforum', array('id'), array(
            'type', 'name', 'intro', 'introformat',
            'assessed', 'assesstimestart', 'assesstimefinish', 'scale',
            'maxbytes', 'maxattachments', 'forcesubscribe', 'trackingtype',
            'rsstype', 'rssarticles', 'timemodified', 'warnafter',
            'blockafter', 'blockperiod', 'completiondiscussions', 'completionreplies',
            'completionposts', 'displaywordcount', 'reactiontype', 'reactionallreplies'));

        $discussions = new backup_nested_element('discussions');

        $discussion = new backup_nested_element('discussion', array('id'), array(
            'name', 'firstpost', 'userid', 'groupid',
            'assessed', 'timemodified', 'usermodified', 'timestart',
            'timeend', 'pinned', 'reactiontype', 'reactionallreplies'));

        $posts = new backup_nested_element('posts');

        $post = new backup_nested_element('post', array('id'), array(
            'parent', 'userid', 'created', 'modified',
            'mailed', 'subject', 'message', 'messageformat',
            'messagetrust', 'attachment', 'totalscore', 'mailnow'));

        $ratings = new backup_nested_element('ratings');

        $rating = new backup_nested_element('rating', array('id'), array(
            'component', 'ratingarea', 'scaleid', 'value', 'userid', 'timecreated', 'timemodified'));

        $discussionsubs = new backup_nested_element('discussion_subs');

        $discussionsub = new backup_nested_element('discussion_sub', array('id'), array(
            'userid',
            'preference',
        ));

        $subscriptions = new backup_nested_element('subscriptions');

        $subscription = new backup_nested_element('subscription', array('id'), array(
            'userid'));

        $digests = new backup_nested_element('digests');

        $digest = new backup_nested_element('digest', array('id'), array(
            'userid', 'maildigest'));

        $readposts = new backup_nested_element('readposts');

        $read = new backup_nested_element('read', array('id'), array(
            'userid', 'discussionid', 'postid', 'firstread',
            'lastread'));

        $trackedprefs = new backup_nested_element('trackedprefs');

        $track = new backup_nested_element('track', array('id'), array(
            'userid'));

        $reactions = new backup_nested_element("reactions");
        $reaction = new backup_nested_element("reaction", array("id"), array('reactforum_id', "discussion_id", "reaction"));

        $userReactions = new backup_nested_element("user_reactions");
        $userReaction = new backup_nested_element("user_reaction", array("id"), array("user_id", "post_id", "reaction_id"));

        // Build the tree

        $reactforum->add_child($discussions);
        $discussions->add_child($discussion);

        $reactforum->add_child($subscriptions);
        $subscriptions->add_child($subscription);

        $reactforum->add_child($digests);
        $digests->add_child($digest);

        $reactforum->add_child($readposts);
        $readposts->add_child($read);

        $reactforum->add_child($trackedprefs);
        $trackedprefs->add_child($track);

        $discussion->add_child($posts);
        $posts->add_child($post);

        $post->add_child($ratings);
        $ratings->add_child($rating);

        $discussion->add_child($discussionsubs);
        $discussionsubs->add_child($discussionsub);

        $reactforum->add_child($reactions);
        $reactions->add_child($reaction);

        $reaction->add_child($userReactions);
        $userReactions->add_child($userReaction);

        // Define sources

        $reactforum->set_source_table('reactforum', array('id' => backup::VAR_ACTIVITYID));

        // All these source definitions only happen if we are including user info
        if ($userinfo)
        {
            $discussion->set_source_sql('
                SELECT *
                  FROM {reactforum_discussions}
                 WHERE reactforum = ?',
                array(backup::VAR_PARENTID));

            // Need posts ordered by id so parents are always before childs on restore
            $post->set_source_table('reactforum_posts', array('discussion' => backup::VAR_PARENTID), 'id ASC');
            $discussionsub->set_source_table('reactforum_discussion_subs', array('discussion' => backup::VAR_PARENTID));

            $subscription->set_source_table('reactforum_subscriptions', array('reactforum' => backup::VAR_PARENTID));
            $digest->set_source_table('reactforum_digests', array('reactforum' => backup::VAR_PARENTID));

            $read->set_source_table('reactforum_read', array('reactforumid' => backup::VAR_PARENTID));

            $track->set_source_table('reactforum_track_prefs', array('reactforumid' => backup::VAR_PARENTID));

            $rating->set_source_table('rating', array('contextid' => backup::VAR_CONTEXTID,
                'component' => backup_helper::is_sqlparam('mod_reactforum'),
                'ratingarea' => backup_helper::is_sqlparam('post'),
                'itemid' => backup::VAR_PARENTID));
            $rating->set_source_alias('rating', 'value');

            $reaction->set_source_table('reactforum_reactions', array('reactforum_id' => backup::VAR_PARENTID));
            $userReaction->set_source_table('reactforum_user_reactions', array('reaction_id' => backup::VAR_PARENTID));
        }

        // Define id annotations

        $reactforum->annotate_ids('scale', 'scale');

        $discussion->annotate_ids('group', 'groupid');

        $post->annotate_ids('user', 'userid');

        $discussionsub->annotate_ids('user', 'userid');

        $rating->annotate_ids('scale', 'scaleid');

        $rating->annotate_ids('user', 'userid');

        $subscription->annotate_ids('user', 'userid');

        $digest->annotate_ids('user', 'userid');

        $read->annotate_ids('user', 'userid');

        $track->annotate_ids('user', 'userid');

        $reaction->annotate_ids("reactforum_id", "reactforum_id");
        $reaction->annotate_ids("discussion_id", "discussion_id");
        $userReaction->annotate_ids("reaction_id", "reaction_id");

        // Define file annotations

        $reactforum->annotate_files('mod_reactforum', 'intro', null); // This file area hasn't itemid

        $post->annotate_files('mod_reactforum', 'post', 'id');
        $post->annotate_files('mod_reactforum', 'attachment', 'id');

        $reaction->annotate_files('mod_reactforum', 'reactions', 'id');

        // Return the root element (reactforum), wrapped into standard activity structure
        return $this->prepare_activity_structure($reactforum);
    }

}
