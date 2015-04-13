Feature: Sign in/out
  In order to use the information system, user must be signed in.

  Scenario: Unknown email address
    Given I am on the welcome screen
    When I fill in "email" with "foo@bar.com"
    And I fill in "password" with "12345"
    And I press "signIn"
    Then I should see "Uživatel se zadaným e-mailem v systému neexistuje."

  Scenario: Wrong password
    Given I am on the welcome screen
    When I fill in "email" with "mark.mcdonald@example.net"
    And I fill in "password" with "ipsum"
    And I press "signIn"
    Then I should see "Zadáno chybné heslo k uživatelskému účtu."

  Scenario: Successful sing in
    Given I am on the welcome screen
    When I fill in "email" with "mark.mcdonald@example.net"
    And I fill in "password" with "lorem"
    And I press "signIn"
    Then I should see "Dashboard"

  Scenario: Sign out
    When I follow "signOut"
    Then I should see "Odhlášení proběhlo úspěšně."
