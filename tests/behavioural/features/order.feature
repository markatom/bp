Feature: Orders' management
  Users can manage orders in order to process them.

  Scenario: Search order to process
    Given I am "Mark McDonald"
    When I follow "search"
    And I fill in "search-assignee" with "Mark McDonald"
    And I select "Ke zpracování" from "search-state"
    And I wait for "1" seconds
    Then I should see "Dopravní nehoda"
    And I should not see any of "Pracovní úraz, Úraz ve škole"

  Scenario: Add a new order
    Given I am "Mark McDonald"
    When I follow "add"
    And I fill in "name" with "Úraz v kanceláři"
    And I fill in "client" with "Adam"
    And I wait for "1" second
    And I click "Adam Němec" with xpath "//form//ul/li/a"
    And I fill in "assignee" with "John"
    And I wait for "1" second
    And I click "John Doe" with xpath "//form//ul/li/a"
    And I fill in "place" with "Kancelář"
    And I fill in "date" with "16. 4. 2015"
    And I fill in "description" with "Mandant upadl v kanceláři, pojišťovna odmítla vyplatit pojistné."
    And I fill in "caused-by" with "mandant"
    And I fill in "guilt" with "částečné"
    And I fill in "injury" with "Pohmožděné předloktí."
    And I press "save"
    Then I should see "Nová objednávka byla úspěšně vytvořena."
    And I should see "Úraz v kanceláři"
    And I should see "Adam Němec"
    And I should see "16. 4. 2015"
    And I should see "John Doe"
    When I follow "Detail úrazu v kanceláři" with xpath "//table/tbody/tr[4]/td[7]/div/a[1]"
    Then I should see "Úraz v kanceláři"
    And I should see "Adam Němec"
    And I should see "John Doe"
    And I should see "Ke zpracování"
    And I should see "Kancelář"
    And I should see "16. 4. 2015"
    And I should see "Mandant upadl v kanceláři, pojišťovna odmítla vyplatit pojistné."
    And I should see "mandant"
    And I should see "částečné"
    And I should see "Pohmožděné předloktí."

  Scenario: Edit a present order
    Given I am "Mark McDonald"
    When I follow "Upravit pracovní úraz" with xpath "//table/tbody/tr[1]/td[7]/div/a[4]"
    And I fill in "assignee" with "James"
    And I wait for "1" second
    And I click "James Smith" with xpath "//form//ul/li/a"
    And I click "Ke zpracování" with xpath "//*[text()='Ke zpracování']"
    And I press "save"
    When I should see "Změny byly úspěšně uloženy."
    Then I should see "James Smith"
    When I follow "Detail pracovího úrazu" with xpath "//table/tbody/tr[1]/td[7]/div/a[1]"
    Then I should see "James Smith"
    Then I should see "Ke zpracování"
