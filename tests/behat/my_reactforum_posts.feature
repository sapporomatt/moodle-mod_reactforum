@mod @mod_reactforum
Feature: A user can view their posts and discussions
  In order to ensure a user can view their posts and discussions
  As a student
  I need to view my post and discussions

  Scenario: View the student's posts and discussions
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity   | name                   | intro       | course | idnumber     | groupmode |
      | reactforum      | Test reactforum name        | Test reactforum  | C1     | reactforum        | 0         |
    And I log in as "student1"
    And I follow "Course 1"
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | ReactForum discussion 1 |
      | Message | How awesome is this reactforum discussion? |
    And I reply "ReactForum discussion 1" post from "Test reactforum name" reactforum with:
      | Message | Actually, I've seen better. |
    When I follow "Profile" in the user menu
    And I follow "ReactForum posts"
    Then I should see "How awesome is this reactforum discussion?"
    And I should see "Actually, I've seen better."
    And I follow "Profile" in the user menu
    And I follow "ReactForum discussions"
    And I should see "How awesome is this reactforum discussion?"
    And I should not see "Actually, I've seen better."
