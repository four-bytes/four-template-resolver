<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Unit;

use Four\TemplateResolver\Configuration\LanguageMapping;
use Four\TemplateResolver\Configuration\TemplateConfiguration;
use Four\TemplateResolver\TemplateResolver;
use Four\TemplateResolver\TemplateResolverFactory;
use Four\TemplateResolver\Tests\TestCase;

class TemplateResolverFactoryTest extends TestCase
{
    public function testCreateWithDirectory(): void
    {
        $resolver = TemplateResolverFactory::createWithDirectory($this->getTestTemplateDirectory());

        $this->assertInstanceOf(TemplateResolver::class, $resolver);
        $this->assertEquals($this->getTestTemplateDirectory(), $resolver->getConfiguration()->templateDirectory);
    }

    public function testCreateWithConfiguration(): void
    {
        $config = TemplateConfiguration::withDirectory($this->getTestTemplateDirectory());
        $resolver = TemplateResolverFactory::createWithConfiguration($config);

        $this->assertInstanceOf(TemplateResolver::class, $resolver);
        $this->assertSame($config, $resolver->getConfiguration());
    }

    public function testCreateStrict(): void
    {
        $resolver = TemplateResolverFactory::createStrict($this->getTestTemplateDirectory());

        $this->assertInstanceOf(TemplateResolver::class, $resolver);
        $this->assertTrue($resolver->getConfiguration()->strictMode);
    }

    public function testCreateWithoutCaching(): void
    {
        $resolver = TemplateResolverFactory::createWithoutCaching($this->getTestTemplateDirectory());

        $this->assertInstanceOf(TemplateResolver::class, $resolver);
        $this->assertFalse($resolver->getConfiguration()->enableCaching);
    }

    public function testCreateEuropean(): void
    {
        $resolver = TemplateResolverFactory::createEuropean($this->getTestTemplateDirectory());

        $this->assertInstanceOf(TemplateResolver::class, $resolver);

        $languageMapping = $resolver->getConfiguration()->languageMapping;
        $this->assertEquals('german', $languageMapping->getLanguageForCountry('DEU'));
        $this->assertEquals('french', $languageMapping->getLanguageForCountry('FRA'));
        $this->assertEquals('italian', $languageMapping->getLanguageForCountry('ITA'));
    }

    public function testCreateWithLanguageMapping(): void
    {
        $customMapping = new LanguageMapping(['TEST' => 'test'], 'default');
        $resolver = TemplateResolverFactory::createWithLanguageMapping($this->getTestTemplateDirectory(), $customMapping);

        $this->assertInstanceOf(TemplateResolver::class, $resolver);
        $this->assertSame($customMapping, $resolver->getConfiguration()->languageMapping);
    }

    public function testCreateForMarketplaces(): void
    {
        $resolver = TemplateResolverFactory::createForMarketplaces($this->getTestTemplateDirectory());

        $this->assertInstanceOf(TemplateResolver::class, $resolver);

        $config = $resolver->getConfiguration();
        $this->assertTrue($config->enableCaching);
        $this->assertFalse($config->strictMode);
        $this->assertEquals('.txt', $config->templateExtension);

        // Should have German/English mapping
        $this->assertEquals('german', $config->languageMapping->getLanguageForCountry('DEU'));
        $this->assertEquals('english', $config->languageMapping->getLanguageForCountry('USA'));
    }

    public function testCreateFull(): void
    {
        $resolver = TemplateResolverFactory::createFull($this->getTestTemplateDirectory(), true);

        $this->assertInstanceOf(TemplateResolver::class, $resolver);

        $config = $resolver->getConfiguration();
        $this->assertTrue($config->enableCaching);
        $this->assertTrue($config->strictMode);

        // Should have European mapping
        $languageMapping = $config->languageMapping;
        $this->assertEquals('german', $languageMapping->getLanguageForCountry('DEU'));
        $this->assertEquals('french', $languageMapping->getLanguageForCountry('FRA'));
    }

    public function testCreateFullWithNonStrictMode(): void
    {
        $resolver = TemplateResolverFactory::createFull($this->getTestTemplateDirectory(), false);

        $this->assertInstanceOf(TemplateResolver::class, $resolver);
        $this->assertFalse($resolver->getConfiguration()->strictMode);
    }

    public function testCreateForDevelopment(): void
    {
        $resolver = TemplateResolverFactory::createForDevelopment($this->getTestTemplateDirectory());

        $this->assertInstanceOf(TemplateResolver::class, $resolver);

        $config = $resolver->getConfiguration();
        $this->assertFalse($config->enableCaching);
        $this->assertTrue($config->strictMode);
    }

    public function testCreateForProduction(): void
    {
        $resolver = TemplateResolverFactory::createForProduction($this->getTestTemplateDirectory());

        $this->assertInstanceOf(TemplateResolver::class, $resolver);

        $config = $resolver->getConfiguration();
        $this->assertTrue($config->enableCaching);
        $this->assertFalse($config->strictMode);

        // Should have European mapping
        $languageMapping = $config->languageMapping;
        $this->assertEquals('german', $languageMapping->getLanguageForCountry('DEU'));
        $this->assertEquals('french', $languageMapping->getLanguageForCountry('FRA'));
    }
}
