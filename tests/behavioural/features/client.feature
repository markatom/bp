Feature: Clients' management
  In order to record orders, the information system must record their clients so the user must be able to manage them.

  Scenario: Search client
    Given I am "John Doe"
    When I follow "clients"
    Then I should see "Karel Kos, Petr Zapalač, Ondřej Hubený, Kryštof Čalfa, Zdeněk Velínský, Adam Němec, Jiří Hojek, Tomáš Jonáš" in that order
    When I follow "fullName"
    Then I should see "Adam Němec, Jiří Hojek, Karel Kos, Kryštof Čalfa, Ondřej Hubený, Petr Zapalač, Tomáš Jonáš, Zdeněk Velínský" in that order
    When I follow "fullName"
    Then I should see "Zdeněk Velínský, Tomáš Jonáš, Petr Zapalač, Ondřej Hubený, Kryštof Čalfa, Karel Kos, Jiří Hojek, Adam Němec" in that order
    When I follow "search"
    And I fill in "search-fullName" with "k"
    And I wait for "1" seconds
    Then I should see "Zdeněk Velínský, Kryštof Čalfa, Karel Kos, Jiří Hojek" in that order
    And I should not see any of "Tomáš Jonáš, Ondřej Hubený, Adam Němec, Petr Zalač"
    When I rewrite value of "search-fullName" with "tomáš"
    And I wait for "1" seconds
    Then I should see "Tomáš Jonáš"
    And I should not see any of "Ondřej Hubený, Adam Němec, Petr Zalač, Jiří Hojek, Karel Kos, Kryštof Čalfa, Zdeněk Velínský"
    When I follow "search"
    And I wait for "1" seconds
    Then I should see "Zdeněk Velínský, Tomáš Jonáš, Petr Zapalač, Ondřej Hubený, Kryštof Čalfa, Karel Kos, Jiří Hojek, Adam Němec" in that order

  Scenario: Add new client
    Given I am "John Doe"
    When I follow "clients"
    And I follow "add"
    And I fill in "fullName" with "Jan Novák"
    And I fill in "dateOfBirth" with "1. 1. 1980"
    And I fill in "email" with "jan@novak.cz"
    And I fill in "telephone" with "123456789"
    And I fill in "street" with "Radniční 12"
    And I fill in "city" with "Kladno"
    And I fill in "zip" with "12345"
    And I press "Uložit"
    Then I should see "Nový klient byl úspěšně vytvořen."
    And I should see "Jan Novák"
    And I should see "1. 1. 1980"
    And I should see "jan@novak.cz"
    And I should see "+420 123 456 789"
    When I follow "Detail Jan Novák" with xpath "//table/tbody/tr[9]/td[5]/div/a[1]"
    Then I should see "Jan Novák"
    And I should see "1. 1. 1980"
    And I should see "jan@novak.cz"
    And I should see "+420 123 456 789"
    And I should see "Radniční 12"
    And I should see "Kladno"
    And I should see "123 45"
    And I should see "Česká republika"

  Scenario: Edit present client
    Given I am "John Doe"
    When I follow "clients"
    And I follow "Upravit Ondřej Hubený" with xpath "//table/tbody/tr[3]/td[5]/div/a[2]"
    And I rewrite value of "fullName" with "Pavel Hubený"
    And I press "Uložit"
    Then I should see "Změny byly úspěšně uloženy."
    And I should see "Pavel Hubený"
    When I follow "Detail Pavel Hubený" with xpath "//table/tbody/tr[3]/td[5]/div/a[1]"
    Then I should see "Pavel Hubený"