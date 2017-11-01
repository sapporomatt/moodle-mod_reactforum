@mod @mod_reactforum
Feature: A teacher can set one of 3 possible options for tracking read reactforum posts
  In order to ease the reactforum posts follow up
  As a user
  I need to distinct the unread posts from the read ones

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | trackreactforums |
      | student1 | Student | 1 | student1@example.com | 1 |
      | student2 | Student | 2 | student2@example.com | 0 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: Tracking reactforum posts off
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name | Test reactforum name |
      | ReactForum type | Standard reactforum for general use |
      | Description | Test reactforum description |
      | Read tracking | Off |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should not see "1 unread post"
    And I follow "Test reactforum name"
    And I should not see "Track unread posts"

  Scenario: Tracking reactforum posts optional with user tracking on
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name | Test reactforum name |
      | ReactForum type | Standard reactforum for general use |
      | Description | Test reactforum description |
      | Read tracking | Optional |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "1 unread post"
    And I follow "Test reactforum name"
    And I follow "Don't track unread posts"
    And I wait to be redirected
    And I am on "Course 1" course homepage
    And I should not see "1 unread post"
    And I follow "Test reactforum name"
    And I follow "Track unread posts"
    And I wait to be redirected
    And I click on "1" "link" in the "Admin User" "table_row"
    And I am on "Course 1" course homepage
    And I should not see "1 unread post"

  Scenario: Tracking reactforum posts optional with user tracking off
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name | Test reactforum name |
      | ReactForum type | Standard reactforum for general use |
      | Description | Test reactforum description |
      | Read tracking | Optional |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    When I log in as "student2"
    And I am on "Course 1" course homepage
    Then I should not see "1 unread post"
    And I follow "Test reactforum name"
    And I should not see "Track unread posts"

  Scenario: Tracking reactforum posts forced with user tracking on
    Given the following config values are set as admin:
      | reactforum_allowforcedreadtracking | 1 |
    And I am on "Course 1" course homepage
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name | Test reactforum name |
      | ReactForum type | Standard reactforum for general use |
      | Description | Test reactforum description |
      | Read tracking | Force |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "1 unread post"
    And I follow "1 unread post"
    And I should not see "Don't track unread posts"
    And I follow "Test post subject"
    And I am on "Course 1" course homepage
    And I should not see "1 unread post"

  Scenario: Tracking reactforum posts forced with user tracking off
    Given the following config values are set as admin:
      | reactforum_allowforcedreadtracking | 1 |
    And I am on "Course 1" course homepage
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name | Test reactforum name |
      | ReactForum type | Standard reactforum for general use |
      | Description | Test reactforum description |
      | Read tracking | Force |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And I log out
    When I log in as "student2"
    And I am on "Course 1" course homepage
    Then I should see "1 unread post"
    And I follow "1 unread post"
    And I should not see "Don't track unread posts"
    And I follow "Test post subject"
    And I am on "Course 1" course homepage
    And I should not see "1 unread post"

  Scenario: Tracking reactforum posts forced (with force disabled) with user tracking on
    Given the following config values are set as admin:
      | reactforum_allowforcedreadtracking | 1 |
    And I am on "Course 1" course homepage
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name | Test reactforum name |
      | ReactForum type | Standard reactforum for general use |
      | Description | Test reactforum description |
      | Read tracking | Force |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And the following config values are set as admin:
      | reactforum_allowforcedreadtracking | 0 |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "1 unread post"
    And I follow "Test reactforum name"
    And I follow "Don't track unread posts"
    And I wait to be redirected
    And I am on "Course 1" course homepage
    And I should not see "1 unread post"
    And I follow "Test reactforum name"
    And I follow "Track unread posts"
    And I wait to be redirected
    And I click on "1" "link" in the "Admin User" "table_row"
    And I am on "Course 1" course homepage
    And I should not see "1 unread post"

  Scenario: Tracking reactforum posts forced (with force disabled) with user tracking off
    Given the following config values are set as admin:
      | reactforum_allowforcedreadtracking | 1 |
    And I am on "Course 1" course homepage
    Given I add a "ReactForum" to section "1" and I fill the form with:
      | ReactForum name | Test reactforum name |
      | ReactForum type | Standard reactforum for general use |
      | Description | Test reactforum description |
      | Read tracking | Force |
    And I add a new discussion to "Test reactforum name" reactforum with:
      | Subject | Test post subject |
      | Message | Test post message |
    And the following config values are set as admin:
      | reactforum_allowforcedreadtracking | 0 |
    And I log out
    When I log in as "student2"
    And I am on "Course 1" course homepage
    Then I should not see "1 unread post"
    And I follow "Test reactforum name"
    And I should not see "Track unread posts"
