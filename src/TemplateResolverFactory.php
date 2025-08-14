<?php

declare(strict_types=1);

namespace Four\TemplateResolver;

use Four\TemplateResolver\Configuration\LanguageMapping;
use Four\TemplateResolver\Configuration\TemplateConfiguration;

/**
 * Factory for creating TemplateResolver instances
 *
 * Provides convenient methods for creating resolver instances with common configurations.
 */
class TemplateResolverFactory
{
    /**
     * Create resolver with template directory
     */
    public static function createWithDirectory(string $templateDirectory): TemplateResolverInterface
    {
        $configuration = TemplateConfiguration::withDirectory($templateDirectory);
        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver with configuration
     */
    public static function createWithConfiguration(TemplateConfiguration $configuration): TemplateResolverInterface
    {
        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver with strict mode enabled
     *
     * Throws exceptions when templates are not found instead of returning empty strings.
     */
    public static function createStrict(string $templateDirectory): TemplateResolverInterface
    {
        $configuration = TemplateConfiguration::withStrictMode($templateDirectory);
        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver without caching
     *
     * Disables all caching for development or memory-constrained environments.
     */
    public static function createWithoutCaching(string $templateDirectory): TemplateResolverInterface
    {
        $configuration = TemplateConfiguration::withoutCaching($templateDirectory);
        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver with European language mapping
     *
     * Includes mappings for major European languages.
     */
    public static function createEuropean(string $templateDirectory): TemplateResolverInterface
    {
        $languageMapping = LanguageMapping::european();
        $configuration = TemplateConfiguration::withLanguageMapping($templateDirectory, $languageMapping);
        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver with custom language mapping
     */
    public static function createWithLanguageMapping(string $templateDirectory, LanguageMapping $languageMapping): TemplateResolverInterface
    {
        $configuration = TemplateConfiguration::withLanguageMapping($templateDirectory, $languageMapping);
        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver for marketplace templates
     *
     * Optimized configuration for e-commerce marketplace integrations.
     */
    public static function createForMarketplaces(string $templateDirectory): TemplateResolverInterface
    {
        $languageMapping = LanguageMapping::germanEnglish();
        $configuration = new TemplateConfiguration(
            templateDirectory: $templateDirectory,
            enableCaching: true,
            templateExtension: '.txt',
            strictMode: false,
            languageMapping: $languageMapping
        );

        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver with all features enabled
     *
     * Includes European language support, caching, and comprehensive error handling.
     */
    public static function createFull(string $templateDirectory, bool $strictMode = false): TemplateResolverInterface
    {
        $languageMapping = LanguageMapping::european();
        $configuration = new TemplateConfiguration(
            templateDirectory: $templateDirectory,
            enableCaching: true,
            templateExtension: '.txt',
            strictMode: $strictMode,
            languageMapping: $languageMapping
        );

        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver for development
     *
     * Disables caching and enables strict mode for better error reporting during development.
     */
    public static function createForDevelopment(string $templateDirectory): TemplateResolverInterface
    {
        $configuration = new TemplateConfiguration(
            templateDirectory: $templateDirectory,
            enableCaching: false,
            templateExtension: '.txt',
            strictMode: true,
            languageMapping: new LanguageMapping()
        );

        return new TemplateResolver($configuration);
    }

    /**
     * Create resolver for production
     *
     * Optimized for production with caching enabled and lenient error handling.
     */
    public static function createForProduction(string $templateDirectory): TemplateResolverInterface
    {
        $languageMapping = LanguageMapping::european();
        $configuration = new TemplateConfiguration(
            templateDirectory: $templateDirectory,
            enableCaching: true,
            templateExtension: '.txt',
            strictMode: false,
            languageMapping: $languageMapping
        );

        return new TemplateResolver($configuration);
    }
}
