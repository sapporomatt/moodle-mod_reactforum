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
 * ReactForum subscription manager.
 *
 * @package    mod_reactforum
 * @copyright  2014 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_reactforum;

defined('MOODLE_INTERNAL') || die();

/**
 * ReactForum subscription manager.
 *
 * @copyright  2014 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subscriptions {

    /**
     * The status value for an unsubscribed discussion.
     *
     * @var int
     */
    const REACTFORUM_DISCUSSION_UNSUBSCRIBED = -1;

    /**
     * The subscription cache for reactforums.
     *
     * The first level key is the user ID
     * The second level is the reactforum ID
     * The Value then is bool for subscribed of not.
     *
     * @var array[] An array of arrays.
     */
    protected static $reactforumcache = array();

    /**
     * The list of reactforums which have been wholly retrieved for the reactforum subscription cache.
     *
     * This allows for prior caching of an entire reactforum to reduce the
     * number of DB queries in a subscription check loop.
     *
     * @var bool[]
     */
    protected static $fetchedreactforums = array();

    /**
     * The subscription cache for reactforum discussions.
     *
     * The first level key is the user ID
     * The second level is the reactforum ID
     * The third level key is the discussion ID
     * The value is then the users preference (int)
     *
     * @var array[]
     */
    protected static $reactforumdiscussioncache = array();

    /**
     * The list of reactforums which have been wholly retrieved for the reactforum discussion subscription cache.
     *
     * This allows for prior caching of an entire reactforum to reduce the
     * number of DB queries in a subscription check loop.
     *
     * @var bool[]
     */
    protected static $discussionfetchedreactforums = array();

    /**
     * Whether a user is subscribed to this reactforum, or a discussion within
     * the reactforum.
     *
     * If a discussion is specified, then report whether the user is
     * subscribed to posts to this particular discussion, taking into
     * account the reactforum preference.
     *
     * If it is not specified then only the reactforum preference is considered.
     *
     * @param int $userid The user ID
     * @param \stdClass $reactforum The record of the reactforum to test
     * @param int $discussionid The ID of the discussion to check
     * @param $cm The coursemodule record. If not supplied, this will be calculated using get_fast_modinfo instead.
     * @return boolean
     */
    public static function is_subscribed($userid, $reactforum, $discussionid = null, $cm = null) {
        // If reactforum is force subscribed and has allowforcesubscribe, then user is subscribed.
        if (self::is_forcesubscribed($reactforum)) {
            if (!$cm) {
                $cm = get_fast_modinfo($reactforum->course)->instances['reactforum'][$reactforum->id];
            }
            if (has_capability('mod/reactforum:allowforcesubscribe', \context_module::instance($cm->id), $userid)) {
                return true;
            }
        }

        if ($discussionid === null) {
            return self::is_subscribed_to_reactforum($userid, $reactforum);
        }

        $subscriptions = self::fetch_discussion_subscription($reactforum->id, $userid);

        // Check whether there is a record for this discussion subscription.
        if (isset($subscriptions[$discussionid])) {
            return ($subscriptions[$discussionid] != self::REACTFORUM_DISCUSSION_UNSUBSCRIBED);
        }

        return self::is_subscribed_to_reactforum($userid, $reactforum);
    }

    /**
     * Whether a user is subscribed to this reactforum.
     *
     * @param int $userid The user ID
     * @param \stdClass $reactforum The record of the reactforum to test
     * @return boolean
     */
    protected static function is_subscribed_to_reactforum($userid, $reactforum) {
        return self::fetch_subscription_cache($reactforum->id, $userid);
    }

    /**
     * Helper to determine whether a reactforum has it's subscription mode set
     * to forced subscription.
     *
     * @param \stdClass $reactforum The record of the reactforum to test
     * @return bool
     */
    public static function is_forcesubscribed($reactforum) {
        return ($reactforum->forcesubscribe == REACTFORUM_FORCESUBSCRIBE);
    }

    /**
     * Helper to determine whether a reactforum has it's subscription mode set to disabled.
     *
     * @param \stdClass $reactforum The record of the reactforum to test
     * @return bool
     */
    public static function subscription_disabled($reactforum) {
        return ($reactforum->forcesubscribe == REACTFORUM_DISALLOWSUBSCRIBE);
    }

    /**
     * Helper to determine whether the specified reactforum can be subscribed to.
     *
     * @param \stdClass $reactforum The record of the reactforum to test
     * @return bool
     */
    public static function is_subscribable($reactforum) {
        return (isloggedin() && !isguestuser() &&
                !\mod_reactforum\subscriptions::is_forcesubscribed($reactforum) &&
                !\mod_reactforum\subscriptions::subscription_disabled($reactforum));
    }

    /**
     * Set the reactforum subscription mode.
     *
     * By default when called without options, this is set to REACTFORUM_FORCESUBSCRIBE.
     *
     * @param \stdClass $reactforum The record of the reactforum to set
     * @param int $status The new subscription state
     * @return bool
     */
    public static function set_subscription_mode($reactforumid, $status = 1) {
        global $DB;
        return $DB->set_field("reactforum", "forcesubscribe", $status, array("id" => $reactforumid));
    }

    /**
     * Returns the current subscription mode for the reactforum.
     *
     * @param \stdClass $reactforum The record of the reactforum to set
     * @return int The reactforum subscription mode
     */
    public static function get_subscription_mode($reactforum) {
        return $reactforum->forcesubscribe;
    }

    /**
     * Returns an array of reactforums that the current user is subscribed to and is allowed to unsubscribe from
     *
     * @return array An array of unsubscribable reactforums
     */
    public static function get_unsubscribable_reactforums() {
        global $USER, $DB;

        // Get courses that $USER is enrolled in and can see.
        $courses = enrol_get_my_courses();
        if (empty($courses)) {
            return array();
        }

        $courseids = array();
        foreach($courses as $course) {
            $courseids[] = $course->id;
        }
        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'c');

        // Get all reactforums from the user's courses that they are subscribed to and which are not set to forced.
        // It is possible for users to be subscribed to a reactforum in subscription disallowed mode so they must be listed
        // here so that that can be unsubscribed from.
        $sql = "SELECT f.id, cm.id as cm, cm.visible, f.course
                FROM {reactforum} f
                JOIN {course_modules} cm ON cm.instance = f.id
                JOIN {modules} m ON m.name = :modulename AND m.id = cm.module
                LEFT JOIN {reactforum_subscriptions} fs ON (fs.reactforum = f.id AND fs.userid = :userid)
                WHERE f.forcesubscribe <> :forcesubscribe
                AND fs.id IS NOT NULL
                AND cm.course
                $coursesql";
        $params = array_merge($courseparams, array(
            'modulename'=>'reactforum',
            'userid' => $USER->id,
            'forcesubscribe' => REACTFORUM_FORCESUBSCRIBE,
        ));
        $reactforums = $DB->get_recordset_sql($sql, $params);

        $unsubscribablereactforums = array();
        foreach($reactforums as $reactforum) {
            if (empty($reactforum->visible)) {
                // The reactforum is hidden - check if the user can view the reactforum.
                $context = \context_module::instance($reactforum->cm);
                if (!has_capability('moodle/course:viewhiddenactivities', $context)) {
                    // The user can't see the hidden reactforum to cannot unsubscribe.
                    continue;
                }
            }

            $unsubscribablereactforums[] = $reactforum;
        }
        $reactforums->close();

        return $unsubscribablereactforums;
    }

    /**
     * Get the list of potential subscribers to a reactforum.
     *
     * @param context_module $context the reactforum context.
     * @param integer $groupid the id of a group, or 0 for all groups.
     * @param string $fields the list of fields to return for each user. As for get_users_by_capability.
     * @param string $sort sort order. As for get_users_by_capability.
     * @return array list of users.
     */
    public static function get_potential_subscribers($context, $groupid, $fields, $sort = '') {
        global $DB;

        // Only active enrolled users or everybody on the frontpage.
        list($esql, $params) = get_enrolled_sql($context, 'mod/reactforum:allowforcesubscribe', $groupid, true);
        if (!$sort) {
            list($sort, $sortparams) = users_order_by_sql('u');
            $params = array_merge($params, $sortparams);
        }

        $sql = "SELECT $fields
                FROM {user} u
                JOIN ($esql) je ON je.id = u.id
            ORDER BY $sort";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Fetch the reactforum subscription data for the specified userid and reactforum.
     *
     * @param int $reactforumid The reactforum to retrieve a cache for
     * @param int $userid The user ID
     * @return boolean
     */
    public static function fetch_subscription_cache($reactforumid, $userid) {
        if (isset(self::$reactforumcache[$userid]) && isset(self::$reactforumcache[$userid][$reactforumid])) {
            return self::$reactforumcache[$userid][$reactforumid];
        }
        self::fill_subscription_cache($reactforumid, $userid);

        if (!isset(self::$reactforumcache[$userid]) || !isset(self::$reactforumcache[$userid][$reactforumid])) {
            return false;
        }

        return self::$reactforumcache[$userid][$reactforumid];
    }

    /**
     * Fill the reactforum subscription data for the specified userid and reactforum.
     *
     * If the userid is not specified, then all subscription data for that reactforum is fetched in a single query and used
     * for subsequent lookups without requiring further database queries.
     *
     * @param int $reactforumid The reactforum to retrieve a cache for
     * @param int $userid The user ID
     * @return void
     */
    public static function fill_subscription_cache($reactforumid, $userid = null) {
        global $DB;

        if (!isset(self::$fetchedreactforums[$reactforumid])) {
            // This reactforum has not been fetched as a whole.
            if (isset($userid)) {
                if (!isset(self::$reactforumcache[$userid])) {
                    self::$reactforumcache[$userid] = array();
                }

                if (!isset(self::$reactforumcache[$userid][$reactforumid])) {
                    if ($DB->record_exists('reactforum_subscriptions', array(
                        'userid' => $userid,
                        'reactforum' => $reactforumid,
                    ))) {
                        self::$reactforumcache[$userid][$reactforumid] = true;
                    } else {
                        self::$reactforumcache[$userid][$reactforumid] = false;
                    }
                }
            } else {
                $subscriptions = $DB->get_recordset('reactforum_subscriptions', array(
                    'reactforum' => $reactforumid,
                ), '', 'id, userid');
                foreach ($subscriptions as $id => $data) {
                    if (!isset(self::$reactforumcache[$data->userid])) {
                        self::$reactforumcache[$data->userid] = array();
                    }
                    self::$reactforumcache[$data->userid][$reactforumid] = true;
                }
                self::$fetchedreactforums[$reactforumid] = true;
                $subscriptions->close();
            }
        }
    }

    /**
     * Fill the reactforum subscription data for all reactforums that the specified userid can subscribe to in the specified course.
     *
     * @param int $courseid The course to retrieve a cache for
     * @param int $userid The user ID
     * @return void
     */
    public static function fill_subscription_cache_for_course($courseid, $userid) {
        global $DB;

        if (!isset(self::$reactforumcache[$userid])) {
            self::$reactforumcache[$userid] = array();
        }

        $sql = "SELECT
                    f.id AS reactforumid,
                    s.id AS subscriptionid
                FROM {reactforum} f
                LEFT JOIN {reactforum_subscriptions} s ON (s.reactforum = f.id AND s.userid = :userid)
                WHERE f.course = :course
                AND f.forcesubscribe <> :subscriptionforced";

        $subscriptions = $DB->get_recordset_sql($sql, array(
            'course' => $courseid,
            'userid' => $userid,
            'subscriptionforced' => REACTFORUM_FORCESUBSCRIBE,
        ));

        foreach ($subscriptions as $id => $data) {
            self::$reactforumcache[$userid][$id] = !empty($data->subscriptionid);
        }
        $subscriptions->close();
    }

    /**
     * Returns a list of user objects who are subscribed to this reactforum.
     *
     * @param stdClass $reactforum The reactforum record.
     * @param int $groupid The group id if restricting subscriptions to a group of users, or 0 for all.
     * @param context_module $context the reactforum context, to save re-fetching it where possible.
     * @param string $fields requested user fields (with "u." table prefix).
     * @param boolean $includediscussionsubscriptions Whether to take discussion subscriptions and unsubscriptions into consideration.
     * @return array list of users.
     */
    public static function fetch_subscribed_users($reactforum, $groupid = 0, $context = null, $fields = null,
            $includediscussionsubscriptions = false) {
        global $CFG, $DB;

        if (empty($fields)) {
            $allnames = get_all_user_name_fields(true, 'u');
            $fields ="u.id,
                      u.username,
                      $allnames,
                      u.maildisplay,
                      u.mailformat,
                      u.maildigest,
                      u.imagealt,
                      u.email,
                      u.emailstop,
                      u.city,
                      u.country,
                      u.lastaccess,
                      u.lastlogin,
                      u.picture,
                      u.timezone,
                      u.theme,
                      u.lang,
                      u.trackforums,
                      u.mnethostid";
        }
                /// JLH u.trackreactforums above changed to u.trackforums
        
        // Retrieve the reactforum context if it wasn't specified.
        $context = reactforum_get_context($reactforum->id, $context);

        if (self::is_forcesubscribed($reactforum)) {
            $results = \mod_reactforum\subscriptions::get_potential_subscribers($context, $groupid, $fields, "u.email ASC");

        } else {
            // Only active enrolled users or everybody on the frontpage.
            list($esql, $params) = get_enrolled_sql($context, '', $groupid, true);
            $params['reactforumid'] = $reactforum->id;

            if ($includediscussionsubscriptions) {
                $params['sreactforumid'] = $reactforum->id;
                $params['dsreactforumid'] = $reactforum->id;
                $params['unsubscribed'] = self::REACTFORUM_DISCUSSION_UNSUBSCRIBED;

                $sql = "SELECT $fields
                        FROM (
                            SELECT userid FROM {reactforum_subscriptions} s
                            WHERE
                                s.reactforum = :sreactforumid
                                UNION
                            SELECT userid FROM {reactforum_discussion_subs} ds
                            WHERE
                                ds.reactforum = :dsreactforumid AND ds.preference <> :unsubscribed
                        ) subscriptions
                        JOIN {user} u ON u.id = subscriptions.userid
                        JOIN ($esql) je ON je.id = u.id
                        ORDER BY u.email ASC";

            } else {
                $sql = "SELECT $fields
                        FROM {user} u
                        JOIN ($esql) je ON je.id = u.id
                        JOIN {reactforum_subscriptions} s ON s.userid = u.id
                        WHERE
                          s.reactforum = :reactforumid
                        ORDER BY u.email ASC";
            }
            $results = $DB->get_records_sql($sql, $params);
        }

        // Guest user should never be subscribed to a reactforum.
        unset($results[$CFG->siteguest]);

        // Apply the activity module availability resetrictions.
        $cm = get_coursemodule_from_instance('reactforum', $reactforum->id, $reactforum->course);
        $modinfo = get_fast_modinfo($reactforum->course);
        $info = new \core_availability\info_module($modinfo->get_cm($cm->id));
        $results = $info->filter_user_list($results);

        return $results;
    }

    /**
     * Retrieve the discussion subscription data for the specified userid and reactforum.
     *
     * This is returned as an array of discussions for that reactforum which contain the preference in a stdClass.
     *
     * @param int $reactforumid The reactforum to retrieve a cache for
     * @param int $userid The user ID
     * @return array of stdClass objects with one per discussion in the reactforum.
     */
    public static function fetch_discussion_subscription($reactforumid, $userid = null) {
        self::fill_discussion_subscription_cache($reactforumid, $userid);

        if (!isset(self::$reactforumdiscussioncache[$userid]) || !isset(self::$reactforumdiscussioncache[$userid][$reactforumid])) {
            return array();
        }

        return self::$reactforumdiscussioncache[$userid][$reactforumid];
    }

    /**
     * Fill the discussion subscription data for the specified userid and reactforum.
     *
     * If the userid is not specified, then all discussion subscription data for that reactforum is fetched in a single query
     * and used for subsequent lookups without requiring further database queries.
     *
     * @param int $reactforumid The reactforum to retrieve a cache for
     * @param int $userid The user ID
     * @return void
     */
    public static function fill_discussion_subscription_cache($reactforumid, $userid = null) {
        global $DB;

        if (!isset(self::$discussionfetchedreactforums[$reactforumid])) {
            // This reactforum hasn't been fetched as a whole yet.
            if (isset($userid)) {
                if (!isset(self::$reactforumdiscussioncache[$userid])) {
                    self::$reactforumdiscussioncache[$userid] = array();
                }

                if (!isset(self::$reactforumdiscussioncache[$userid][$reactforumid])) {
                    $subscriptions = $DB->get_recordset('reactforum_discussion_subs', array(
                        'userid' => $userid,
                        'reactforum' => $reactforumid,
                    ), null, 'id, discussion, preference');
                    foreach ($subscriptions as $id => $data) {
                        self::add_to_discussion_cache($reactforumid, $userid, $data->discussion, $data->preference);
                    }
                    $subscriptions->close();
                }
            } else {
                $subscriptions = $DB->get_recordset('reactforum_discussion_subs', array(
                    'reactforum' => $reactforumid,
                ), null, 'id, userid, discussion, preference');
                foreach ($subscriptions as $id => $data) {
                    self::add_to_discussion_cache($reactforumid, $data->userid, $data->discussion, $data->preference);
                }
                self::$discussionfetchedreactforums[$reactforumid] = true;
                $subscriptions->close();
            }
        }
    }

    /**
     * Add the specified discussion and user preference to the discussion
     * subscription cache.
     *
     * @param int $reactforumid The ID of the reactforum that this preference belongs to
     * @param int $userid The ID of the user that this preference belongs to
     * @param int $discussion The ID of the discussion that this preference relates to
     * @param int $preference The preference to store
     */
    protected static function add_to_discussion_cache($reactforumid, $userid, $discussion, $preference) {
        if (!isset(self::$reactforumdiscussioncache[$userid])) {
            self::$reactforumdiscussioncache[$userid] = array();
        }

        if (!isset(self::$reactforumdiscussioncache[$userid][$reactforumid])) {
            self::$reactforumdiscussioncache[$userid][$reactforumid] = array();
        }

        self::$reactforumdiscussioncache[$userid][$reactforumid][$discussion] = $preference;
    }

    /**
     * Reset the discussion cache.
     *
     * This cache is used to reduce the number of database queries when
     * checking reactforum discussion subscription states.
     */
    public static function reset_discussion_cache() {
        self::$reactforumdiscussioncache = array();
        self::$discussionfetchedreactforums = array();
    }

    /**
     * Reset the reactforum cache.
     *
     * This cache is used to reduce the number of database queries when
     * checking reactforum subscription states.
     */
    public static function reset_reactforum_cache() {
        self::$reactforumcache = array();
        self::$fetchedreactforums = array();
    }

    /**
     * Adds user to the subscriber list.
     *
     * @param int $userid The ID of the user to subscribe
     * @param \stdClass $reactforum The reactforum record for this reactforum.
     * @param \context_module|null $context Module context, may be omitted if not known or if called for the current
     *      module set in page.
     * @param boolean $userrequest Whether the user requested this change themselves. This has an effect on whether
     *     discussion subscriptions are removed too.
     * @return bool|int Returns true if the user is already subscribed, or the reactforum_subscriptions ID if the user was
     *     successfully subscribed.
     */
    public static function subscribe_user($userid, $reactforum, $context = null, $userrequest = false) {
        global $DB;

        if (self::is_subscribed($userid, $reactforum)) {
            return true;
        }

        $sub = new \stdClass();
        $sub->userid  = $userid;
        $sub->reactforum = $reactforum->id;

        $result = $DB->insert_record("reactforum_subscriptions", $sub);

        if ($userrequest) {
            $discussionsubscriptions = $DB->get_recordset('reactforum_discussion_subs', array('userid' => $userid, 'reactforum' => $reactforum->id));
            $DB->delete_records_select('reactforum_discussion_subs',
                    'userid = :userid AND reactforum = :reactforumid AND preference <> :preference', array(
                        'userid' => $userid,
                        'reactforumid' => $reactforum->id,
                        'preference' => self::REACTFORUM_DISCUSSION_UNSUBSCRIBED,
                    ));

            // Reset the subscription caches for this reactforum.
            // We know that the there were previously entries and there aren't any more.
            if (isset(self::$reactforumdiscussioncache[$userid]) && isset(self::$reactforumdiscussioncache[$userid][$reactforum->id])) {
                foreach (self::$reactforumdiscussioncache[$userid][$reactforum->id] as $discussionid => $preference) {
                    if ($preference != self::REACTFORUM_DISCUSSION_UNSUBSCRIBED) {
                        unset(self::$reactforumdiscussioncache[$userid][$reactforum->id][$discussionid]);
                    }
                }
            }
        }

        // Reset the cache for this reactforum.
        self::$reactforumcache[$userid][$reactforum->id] = true;

        $context = reactforum_get_context($reactforum->id, $context);
        $params = array(
            'context' => $context,
            'objectid' => $result,
            'relateduserid' => $userid,
            'other' => array('reactforumid' => $reactforum->id),

        );
        $event  = event\subscription_created::create($params);
        if ($userrequest && $discussionsubscriptions) {
            foreach ($discussionsubscriptions as $subscription) {
                $event->add_record_snapshot('reactforum_discussion_subs', $subscription);
            }
            $discussionsubscriptions->close();
        }
        $event->trigger();

        return $result;
    }

    /**
     * Removes user from the subscriber list
     *
     * @param int $userid The ID of the user to unsubscribe
     * @param \stdClass $reactforum The reactforum record for this reactforum.
     * @param \context_module|null $context Module context, may be omitted if not known or if called for the current
     *     module set in page.
     * @param boolean $userrequest Whether the user requested this change themselves. This has an effect on whether
     *     discussion subscriptions are removed too.
     * @return boolean Always returns true.
     */
    public static function unsubscribe_user($userid, $reactforum, $context = null, $userrequest = false) {
        global $DB;

        $sqlparams = array(
            'userid' => $userid,
            'reactforum' => $reactforum->id,
        );
        $DB->delete_records('reactforum_digests', $sqlparams);

        if ($reactforumsubscription = $DB->get_record('reactforum_subscriptions', $sqlparams)) {
            $DB->delete_records('reactforum_subscriptions', array('id' => $reactforumsubscription->id));

            if ($userrequest) {
                $discussionsubscriptions = $DB->get_recordset('reactforum_discussion_subs', $sqlparams);
                $DB->delete_records('reactforum_discussion_subs',
                        array('userid' => $userid, 'reactforum' => $reactforum->id, 'preference' => self::REACTFORUM_DISCUSSION_UNSUBSCRIBED));

                // We know that the there were previously entries and there aren't any more.
                if (isset(self::$reactforumdiscussioncache[$userid]) && isset(self::$reactforumdiscussioncache[$userid][$reactforum->id])) {
                    self::$reactforumdiscussioncache[$userid][$reactforum->id] = array();
                }
            }

            // Reset the cache for this reactforum.
            self::$reactforumcache[$userid][$reactforum->id] = false;

            $context = reactforum_get_context($reactforum->id, $context);
            $params = array(
                'context' => $context,
                'objectid' => $reactforumsubscription->id,
                'relateduserid' => $userid,
                'other' => array('reactforumid' => $reactforum->id),

            );
            $event = event\subscription_deleted::create($params);
            $event->add_record_snapshot('reactforum_subscriptions', $reactforumsubscription);
            if ($userrequest && $discussionsubscriptions) {
                foreach ($discussionsubscriptions as $subscription) {
                    $event->add_record_snapshot('reactforum_discussion_subs', $subscription);
                }
                $discussionsubscriptions->close();
            }
            $event->trigger();
        }

        return true;
    }

    /**
     * Subscribes the user to the specified discussion.
     *
     * @param int $userid The userid of the user being subscribed
     * @param \stdClass $discussion The discussion to subscribe to
     * @param \context_module|null $context Module context, may be omitted if not known or if called for the current
     *     module set in page.
     * @return boolean Whether a change was made
     */
    public static function subscribe_user_to_discussion($userid, $discussion, $context = null) {
        global $DB;

        // First check whether the user is subscribed to the discussion already.
        $subscription = $DB->get_record('reactforum_discussion_subs', array('userid' => $userid, 'discussion' => $discussion->id));
        if ($subscription) {
            if ($subscription->preference != self::REACTFORUM_DISCUSSION_UNSUBSCRIBED) {
                // The user is already subscribed to the discussion. Ignore.
                return false;
            }
        }
        // No discussion-level subscription. Check for a reactforum level subscription.
        if ($DB->record_exists('reactforum_subscriptions', array('userid' => $userid, 'reactforum' => $discussion->reactforum))) {
            if ($subscription && $subscription->preference == self::REACTFORUM_DISCUSSION_UNSUBSCRIBED) {
                // The user is subscribed to the reactforum, but unsubscribed from the discussion, delete the discussion preference.
                $DB->delete_records('reactforum_discussion_subs', array('id' => $subscription->id));
                unset(self::$reactforumdiscussioncache[$userid][$discussion->reactforum][$discussion->id]);
            } else {
                // The user is already subscribed to the reactforum. Ignore.
                return false;
            }
        } else {
            if ($subscription) {
                $subscription->preference = time();
                $DB->update_record('reactforum_discussion_subs', $subscription);
            } else {
                $subscription = new \stdClass();
                $subscription->userid  = $userid;
                $subscription->reactforum = $discussion->reactforum;
                $subscription->discussion = $discussion->id;
                $subscription->preference = time();

                $subscription->id = $DB->insert_record('reactforum_discussion_subs', $subscription);
                self::$reactforumdiscussioncache[$userid][$discussion->reactforum][$discussion->id] = $subscription->preference;
            }
        }

        $context = reactforum_get_context($discussion->reactforum, $context);
        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'relateduserid' => $userid,
            'other' => array(
                'reactforumid' => $discussion->reactforum,
                'discussion' => $discussion->id,
            ),

        );
        $event  = event\discussion_subscription_created::create($params);
        $event->trigger();

        return true;
    }
    /**
     * Unsubscribes the user from the specified discussion.
     *
     * @param int $userid The userid of the user being unsubscribed
     * @param \stdClass $discussion The discussion to unsubscribe from
     * @param \context_module|null $context Module context, may be omitted if not known or if called for the current
     *     module set in page.
     * @return boolean Whether a change was made
     */
    public static function unsubscribe_user_from_discussion($userid, $discussion, $context = null) {
        global $DB;

        // First check whether the user's subscription preference for this discussion.
        $subscription = $DB->get_record('reactforum_discussion_subs', array('userid' => $userid, 'discussion' => $discussion->id));
        if ($subscription) {
            if ($subscription->preference == self::REACTFORUM_DISCUSSION_UNSUBSCRIBED) {
                // The user is already unsubscribed from the discussion. Ignore.
                return false;
            }
        }
        // No discussion-level preference. Check for a reactforum level subscription.
        if (!$DB->record_exists('reactforum_subscriptions', array('userid' => $userid, 'reactforum' => $discussion->reactforum))) {
            if ($subscription && $subscription->preference != self::REACTFORUM_DISCUSSION_UNSUBSCRIBED) {
                // The user is not subscribed to the reactforum, but subscribed from the discussion, delete the discussion subscription.
                $DB->delete_records('reactforum_discussion_subs', array('id' => $subscription->id));
                unset(self::$reactforumdiscussioncache[$userid][$discussion->reactforum][$discussion->id]);
            } else {
                // The user is not subscribed from the reactforum. Ignore.
                return false;
            }
        } else {
            if ($subscription) {
                $subscription->preference = self::REACTFORUM_DISCUSSION_UNSUBSCRIBED;
                $DB->update_record('reactforum_discussion_subs', $subscription);
            } else {
                $subscription = new \stdClass();
                $subscription->userid  = $userid;
                $subscription->reactforum = $discussion->reactforum;
                $subscription->discussion = $discussion->id;
                $subscription->preference = self::REACTFORUM_DISCUSSION_UNSUBSCRIBED;

                $subscription->id = $DB->insert_record('reactforum_discussion_subs', $subscription);
            }
            self::$reactforumdiscussioncache[$userid][$discussion->reactforum][$discussion->id] = $subscription->preference;
        }

        $context = reactforum_get_context($discussion->reactforum, $context);
        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'relateduserid' => $userid,
            'other' => array(
                'reactforumid' => $discussion->reactforum,
                'discussion' => $discussion->id,
            ),

        );
        $event  = event\discussion_subscription_deleted::create($params);
        $event->trigger();

        return true;
    }

}
