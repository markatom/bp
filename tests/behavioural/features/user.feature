Feature: User management
  Admin can edit users' information. Employee can edit only his profile.

  Scenario: Admin edits user
    Given I am "John Doe"
    When I follow "user-management"
    And I follow "Upravit James Smith" with xpath "//table/tbody/tr[2]/td[4]/div/a[1]"
    And I rewrite value of "full-name" with "Thomas Smith"
    And I press "save"
    Then I should see "Změny byly úspěšně uloženy."
    And I should see "Thomas Smith"

  Scenario: Employee cannot edit user
    Given I am "James Smith"
    Then I should not see "Správa uživatelů"

  Scenario: User edits his own profile
    Given I am "James Smith"
    When I follow "profile"
    And I rewrite value of "full-name" with "Thomas Smith"
    And I press "save"
    Then I should see "Změny byly úspěšně uloženy."
    And I should see "Thomas Smith"
