<?php 
// This base class represents a person with basic attributes such as ID, first name, last name, and email. It serves as a parent class for employees.
class Person { 
    protected ?int $id;
    protected $firstName;
    protected $lastName;
    protected $email;

    public function __construct(int $id, string $firstName, string $lastName, string $email) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    // Function to get the ID of the person
    public function getId(): int {
        return $this->id;
    }

    // Function to get the full name of the person
    public function getFullName(): string {
        return $this->firstName ." ". $this->lastName;
    }
    
    // Function to get the email of the person
    public function getEmail(): string {
        return $this->email;
    }
}