Feature: Managing workflows
  In order to check workflows crud
  As an Administrator
  I want to be able to manage workflow entity

  Scenario: Workflow creation
    Given I login as administrator
    Then I go to System/ Workflows
    And I press "Create Workflow"
    And I fill form with:
      | Name            | Test workflow          |
      | Related Entity  | Business Customer      |
    Then I press "Add step"
    And I fill form with:
      | label           | Step1  |
    And I press "Apply"
    Then I press "Add transition"
    And I fill form with:
      | label           | Trans1  |
      | step_from       | (Start) |
      | step_to         | Step1   |
    And I press "Apply"
    Then I press "Add step"
    And I fill form with:
      | label           | Step2  |
      | order           | 1      |
      | is_final        | true   |
    And I press "Apply"
    Then I press "Add transition"
    And I fill form with:
      | label           | Trans1  |
      | step_from       | Step1   |
      | step_to         | Step2   |
    And I press "Apply"
    When I save and close form
    And I go to System/ Workflows
    Then I should see Test workflow in grid with following data:
      | Name            | Test workflow          |
      | Related Entity  | Business Customer      |
      | Active          | No                     |
      | System          | No                     |
      | Priority        | 0                      |

  Scenario: Workflow activation from grid
    Given I click Activate Test workflow in grid
    And I press "Activate"
    Then I should see "Workflow activated" flash message
    And I should see Test workflow in grid with following data:
      | Related Entity  | Business Customer      |
      | Active          | Yes                    |
      | System          | No                     |
      | Priority        | 0                      |

  Scenario: Workflow deactivation from entity view
    Given I sort grid by Related Entity
    And I click Deactivate Test workflow in grid
    When I press "Yes, Deactivate"
    And I go to System/ Workflows
    Then I should see Test workflow in grid with following data:
      | Related Entity  | Business Customer      |
      | Active          | No                     |
      | System          | No                     |
      | Priority        | 0                      |


  Scenario: Workflow edit
    Given I click Edit Test workflow in grid
    And I fill form with:
      | Name            | Glorious workflow  |
      | Related Entity  | Business Unit      |
    When I save and close form
    Then I should see "Could not save workflow. Please add at least one step and one transition." flash message
    Then I press "Add step"
    And I fill form with:
      | label           | Step1  |
      | order           | 1      |
      | is_final        | true   |
    And I press "Apply"
    Then I press "Add transition"
    And I fill form with:
      | label           | Trans1  |
      | step_from       | (Start) |
      | step_to         | Step1   |
    And I press "Apply"
    When I save and close form
    And I go to System/ Workflows
    Then I should see Glorious workflow in grid with following data:
      | Name            | Glorious workflow  |
      | Related Entity  | Business Unit      |
