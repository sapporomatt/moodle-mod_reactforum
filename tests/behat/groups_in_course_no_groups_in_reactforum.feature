@mod @mod_reactforum
Feature: ReactForums in 'No groups' mode allow posting to All participants for all users
  In order to post to a reactforum in 'No groups' mode, which is in course which has groups
  As any user
  I need to post

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group A | C1 | G1 |
      | Group B | C1 | G2 |
    And the following "group members" exist:
      | user | group |
      | teacher1 | G1 |
      | teacher1 | G2 |
      | student1 | G1 |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course | idnumber     | groupmode |
      | reactforum      | Standard reactforum name    | Standard reactforum description    | C1     | nogroups     | 0         |

  Scenario: Teacher can post
    Given I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Standard reactforum name"
    And I should not see "Group A"
    And I should not see "Group B"
    When I click on "Add a new discussion topic" "button"
    Then I should not see "Post a copy to all groups"
    And I should not see "Group" in the "form" "css_element"
    And I set the following fields to these values:
      | Subject | Teacher 1 -> ReactForum  |
      | Message | Teacher 1 -> ReactForum  |
    And I press "Post to reactforum"
    And I wait to be redirected
    And I should see "Teacher 1 -> ReactForum"

  Scenario: Student can post
    Given I log in as "student1"
    And I follow "Course 1"
    And I follow "Standard reactforum name"
    And I should not see "Group A"
    And I should not see "Group B"
    When I click on "Add a new discussion topic" "button"
    Then I should not see "Post a copy to all groups"
    And I should not see "Group" in the "form" "css_element"
    And I set the following fields to these values:
      | Subject | Student 1 -> ReactForum  |
      | Message | Student 1 -> ReactForum  |
    And I press "Post to reactforum"
    And I wait to be redirected
    And I should see "Student 1 -> ReactForum"
