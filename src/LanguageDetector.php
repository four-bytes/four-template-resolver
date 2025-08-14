<?php

declare(strict_types=1);

namespace Four\TemplateResolver;

use Four\TemplateResolver\Configuration\LanguageMapping;

/**
 * Detects language from entity properties or context
 *
 * Supports automatic language detection from customer country codes
 * and manual language specification.
 */
class LanguageDetector
{
    public function __construct(
        private readonly LanguageMapping $languageMapping = new LanguageMapping()
    ) {
    }

    /**
     * Detect language from entity
     *
     * Looks for country-related properties in the entity and maps them to languages.
     *
     * @param object $entity Entity to analyze
     * @return string Detected language
     */
    public function detectFromEntity(object $entity): string
    {
        // Try various country-related method names
        $countryMethods = ['getCountry', 'getCountryCode', 'getBillingCountry', 'getShippingCountry'];

        foreach ($countryMethods as $method) {
            if (method_exists($entity, $method)) {
                $country = $entity->$method();
                if (is_string($country) && !empty($country)) {
                    return $this->languageMapping->getLanguageForCountry($country);
                }
            }
        }

        // Try properties directly (for entities with public properties)
        $countryProperties = ['country', 'countryCode', 'billing_country', 'shipping_country'];

        foreach ($countryProperties as $property) {
            if (property_exists($entity, $property)) {
                /** @var mixed $country */
                $country = $entity->$property;
                if (is_string($country) && !empty($country)) {
                    return $this->languageMapping->getLanguageForCountry($country);
                }
            }
        }

        return $this->languageMapping->getDefaultLanguage();
    }

    /**
     * Detect language from multiple entities
     *
     * Uses the first entity that provides a country code.
     *
     * @param object[] $entities Entities to analyze
     * @return string Detected language
     */
    public function detectFromEntities(array $entities): string
    {
        foreach ($entities as $entity) {
            if (!is_object($entity)) {
                continue;
            }

            $language = $this->detectFromEntity($entity);

            // If we got something other than default, use it
            if ($language !== $this->languageMapping->getDefaultLanguage()) {
                return $language;
            }
        }

        return $this->languageMapping->getDefaultLanguage();
    }

    /**
     * Validate language code
     */
    public function isValidLanguage(string $language): bool
    {
        // Common language codes validation
        $validLanguages = [
            'english', 'german', 'french', 'spanish', 'italian', 'dutch', 'polish',
            'en', 'de', 'fr', 'es', 'it', 'nl', 'pl',
            'eng', 'deu', 'fra', 'esp', 'ita', 'nld', 'pol'
        ];

        return in_array(strtolower($language), $validLanguages, true);
    }

    /**
     * Normalize language code to consistent format
     */
    public function normalizeLanguage(string $language): string
    {
        $normalized = strtolower($language);

        // Map various formats to consistent names
        $languageMap = [
            'en' => 'english',
            'eng' => 'english',
            'de' => 'german',
            'deu' => 'german',
            'fr' => 'french',
            'fra' => 'french',
            'es' => 'spanish',
            'esp' => 'spanish',
            'it' => 'italian',
            'ita' => 'italian',
            'nl' => 'dutch',
            'nld' => 'dutch',
            'pl' => 'polish',
            'pol' => 'polish'
        ];

        return $languageMap[$normalized] ?? $normalized;
    }

    /**
     * Get language mapping instance
     */
    public function getLanguageMapping(): LanguageMapping
    {
        return $this->languageMapping;
    }

    /**
     * Create detector with custom mapping
     */
    public static function withMapping(LanguageMapping $mapping): self
    {
        return new self($mapping);
    }
}
