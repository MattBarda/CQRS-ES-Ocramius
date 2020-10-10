Feature: Check in and check out

  Scenario: Check-in
    Given a building was registered
    When the user checks into the building
    Then the user should have been checked into the building

  Scenario: Double check-in raises anomalies
    Given a building was registered
    And a user checked into the building
    When the user checks into the building
    Then a check in anomaly should have been detected