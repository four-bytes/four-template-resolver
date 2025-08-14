<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Fixtures;

/**
 * Sample customer entity for testing language detection
 */
class CustomerEntity
{
    public function __construct(
        private string $firstname = 'John',
        private string $lastname = 'Doe',
        private string $country = 'USA',
        private string $email = 'john.doe@example.com'
    ) {
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
