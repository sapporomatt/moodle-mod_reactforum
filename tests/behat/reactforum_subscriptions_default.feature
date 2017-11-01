@mod @mod_reactforum
Feature: A user can control their default discussion subscription settings
  In order to automatically subscribe to discussions
  As a user
  I can choose my default subscription preference

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                   | autosubscribe |
      | student1 | Student   | One      | student.one@example.com | 1             |
      | student2 | Student   | Two      | student.one@example.com | 0             |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: Creating a new discussion in an optional reactforum follows user preferences
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name        | Test reactforum name |
      | ReactForum type        | Standard reactforum for general use |
      | Description       | Test reactforum description |
      | Subscription mode | Optional subscription |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    When I press "Add a new discussion topic"
    Then "input[name=discussionsubscribe][checked=checked]" "css_element" should exist
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I press "Add a new discussion topic"
    And "input[name=discussionsubscribe]:not([checked=checked])" "css_element" should exist

  Scenario: Replying to an existing discussion in an optional reactforum follows user preferences
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name        | Test reactforum name |
      | ReactForum type        | Standard reactforum for general use |
      | Description       | Test reactforum description |
      | Subscription mode | Optional subscription |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I follow "Test post subject"
    When I follow "Reply"
    Then "input[name=discussionsubscribe][checked=checked]" "css_element" should exist
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I follow "Test post subject"
    And I follow "Reply"
    And "input[name=discussionsubscribe]:not([checked=checked])" "css_element" should exist

  Scenario: Creating a new discussion in an automatic reactforum follows reactforum subscription
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name        | Test reactforum name |
      | ReactForum type        | Standard reactforum for general use |
      | Description       | Test reactforum description |
      | Subscription mode | Auto subscription |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    When I press "Add a new discussion topic"
    Then "input[name=discussionsubscribe][checked=checked]" "css_element" should exist
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I press "Add a new discussion topic"
    And "input[name=discussionsubscribe][checked=checked]" "css_element" should exist

  Scenario: Replying to an existing discussion in an automatic reactforum follows reactforum subscription
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name        | Test reactforum name |
      | ReactForum type        | Standard reactforum for general use |
      | Description       | Test reactforum description |
      | Subscription mode | Optional subscription |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I follow "Test post subject"
    When I follow "Reply"
    Then "input[name=discussionsubscribe][checked=checked]" "css_element" should exist
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I follow "Test post subject"
    And I follow "Reply"
    And "input[name=discussionsubscribe]:not([checked=checked])" "css_element" should exist

  Scenario: Replying to an existing discussion in an automatic reactforum which has been unsubscribed from follows user preferences
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name        | Test reactforum name |
      | ReactForum type        | Standard reactforum for general use |
      | Description       | Test reactforum description |
      | Subscription mode | Auto subscription |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I click on "You are subscribed to this discussion. Click to unsubscribe." "link" in the "Test post subject" "table_row"
    And I should see "Student One will NOT be notified of new posts in 'Test post subject' of 'Test reactforum name'"
    And I follow "Test post subject"
    When I follow "Reply"
    And "input[name=discussionsubscribe][checked=checked]" "css_element" should exist
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test reactforum name"
    And I click on "You are subscribed to this discussion. Click to unsubscribe." "link" in the "Test post subject" "table_row"
    And I should see "Student Two will NOT be notified of new posts in 'Test post subject' of 'Test reactforum name'"
    And I follow "Test post subject"
    And I follow "Reply"
    And "input[name=discussionsubscribe]:not([checked=checked])" "css_element" should exist
