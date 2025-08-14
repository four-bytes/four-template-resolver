<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Configuration;

/**
 * Maps country codes to languages for template resolution
 *
 * Follows the pattern from the original implementation:
 * DE/AT/CH -> German, others -> English
 */
class LanguageMapping
{
    /** @var array<string, string> */
    private array $countryToLanguage;

    /** @var string */
    private string $defaultLanguage;

    /**
     * @param array<string, string> $countryToLanguage Country code to language mapping
     * @param string $defaultLanguage Default language when country is not mapped
     */
    public function __construct(
        array $countryToLanguage = ['DEU' => 'german', 'AUT' => 'german', 'CHE' => 'german'],
        string $defaultLanguage = 'english'
    ) {
        $this->countryToLanguage = $countryToLanguage;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * Get language for country code
     */
    public function getLanguageForCountry(string $countryCode): string
    {
        return $this->countryToLanguage[$countryCode] ?? $this->defaultLanguage;
    }

    /**
     * Add country mapping
     */
    public function addCountryMapping(string $countryCode, string $language): self
    {
        $new = clone $this;
        $new->countryToLanguage[$countryCode] = $language;
        return $new;
    }

    /**
     * Set default language
     */
    public function withDefaultLanguage(string $language): self
    {
        $new = clone $this;
        $new->defaultLanguage = $language;
        return $new;
    }

    /**
     * Get all country mappings
     *
     * @return array<string, string>
     */
    public function getCountryMappings(): array
    {
        return $this->countryToLanguage;
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /**
     * Create mapping for European countries
     */
    public static function european(): self
    {
        return new self([
            'DEU' => 'german',
            'AUT' => 'german',
            'CHE' => 'german',
            'FRA' => 'french',
            'ITA' => 'italian',
            'ESP' => 'spanish',
            'NLD' => 'dutch',
            'POL' => 'polish',
            'BEL' => 'dutch', // Belgium defaults to Dutch, can be overridden
        ]);
    }

    /**
     * Create simple German/English mapping
     */
    public static function germanEnglish(): self
    {
        return new self();
    }
}
