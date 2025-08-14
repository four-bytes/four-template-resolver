<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Unit;

use Four\TemplateResolver\Configuration\LanguageMapping;
use Four\TemplateResolver\LanguageDetector;
use Four\TemplateResolver\Tests\Fixtures\CustomerEntity;
use Four\TemplateResolver\Tests\TestCase;

class LanguageDetectorTest extends TestCase
{
    private LanguageDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new LanguageDetector();
    }

    public function testDetectFromEntityGerman(): void
    {
        $customer = new CustomerEntity('Hans', 'Müller', 'DEU');
        $language = $this->detector->detectFromEntity($customer);

        $this->assertEquals('german', $language);
    }

    public function testDetectFromEntityEnglish(): void
    {
        $customer = new CustomerEntity('John', 'Doe', 'USA');
        $language = $this->detector->detectFromEntity($customer);

        $this->assertEquals('english', $language);
    }

    public function testDetectFromEntityAustria(): void
    {
        $customer = new CustomerEntity('Franz', 'Schmidt', 'AUT');
        $language = $this->detector->detectFromEntity($customer);

        $this->assertEquals('german', $language);
    }

    public function testDetectFromEntitySwitzerland(): void
    {
        $customer = new CustomerEntity('Pierre', 'Dubois', 'CHE');
        $language = $this->detector->detectFromEntity($customer);

        $this->assertEquals('german', $language);
    }

    public function testDetectFromEntityDefaultsToEnglish(): void
    {
        $customer = new CustomerEntity('Jean', 'Martin', 'FRA');
        $language = $this->detector->detectFromEntity($customer);

        $this->assertEquals('english', $language);
    }

    public function testDetectFromEntityNoCountryMethod(): void
    {
        $entity = new class {
            public function getName(): string
            {
                return 'Test';
            }
        };

        $language = $this->detector->detectFromEntity($entity);
        $this->assertEquals('english', $language);
    }

    public function testDetectFromEntitiesUsesFirstMatch(): void
    {
        $entityWithoutCountry = new class {
            public function getName(): string
            {
                return 'Test';
            }
        };

        $germanCustomer = new CustomerEntity('Hans', 'Müller', 'DEU');
        $usaCustomer = new CustomerEntity('John', 'Doe', 'USA');

        $language = $this->detector->detectFromEntities([
            $entityWithoutCountry,
            $germanCustomer,
            $usaCustomer
        ]);

        $this->assertEquals('german', $language);
    }

    public function testDetectFromEntitiesDefaultsWhenNoMatch(): void
    {
        $entityWithoutCountry1 = new class {
            public function getName(): string
            {
                return 'Test1';
            }
        };

        $entityWithoutCountry2 = new class {
            public function getTitle(): string
            {
                return 'Test2';
            }
        };

        $language = $this->detector->detectFromEntities([
            $entityWithoutCountry1,
            $entityWithoutCountry2
        ]);

        $this->assertEquals('english', $language);
    }

    public function testCustomLanguageMapping(): void
    {
        $customMapping = new LanguageMapping(['FRA' => 'french'], 'italian');
        $detector = new LanguageDetector($customMapping);

        $frenchCustomer = new CustomerEntity('Jean', 'Martin', 'FRA');
        $unknownCustomer = new CustomerEntity('Mario', 'Rossi', 'ITA');

        $this->assertEquals('french', $detector->detectFromEntity($frenchCustomer));
        $this->assertEquals('italian', $detector->detectFromEntity($unknownCustomer));
    }

    public function testIsValidLanguage(): void
    {
        $this->assertTrue($this->detector->isValidLanguage('english'));
        $this->assertTrue($this->detector->isValidLanguage('german'));
        $this->assertTrue($this->detector->isValidLanguage('en'));
        $this->assertTrue($this->detector->isValidLanguage('de'));
        $this->assertTrue($this->detector->isValidLanguage('ENGLISH'));

        $this->assertFalse($this->detector->isValidLanguage('klingon'));
        $this->assertFalse($this->detector->isValidLanguage(''));
    }

    public function testNormalizeLanguage(): void
    {
        $this->assertEquals('english', $this->detector->normalizeLanguage('en'));
        $this->assertEquals('english', $this->detector->normalizeLanguage('eng'));
        $this->assertEquals('english', $this->detector->normalizeLanguage('ENGLISH'));

        $this->assertEquals('german', $this->detector->normalizeLanguage('de'));
        $this->assertEquals('german', $this->detector->normalizeLanguage('deu'));
        $this->assertEquals('german', $this->detector->normalizeLanguage('GERMAN'));

        $this->assertEquals('french', $this->detector->normalizeLanguage('fr'));
        $this->assertEquals('french', $this->detector->normalizeLanguage('fra'));

        // Unknown language should remain unchanged
        $this->assertEquals('klingon', $this->detector->normalizeLanguage('klingon'));
    }

    public function testWithMapping(): void
    {
        $customMapping = LanguageMapping::european();
        $detector = LanguageDetector::withMapping($customMapping);

        $this->assertSame($customMapping, $detector->getLanguageMapping());
    }

    public function testGetLanguageMapping(): void
    {
        $mapping = $this->detector->getLanguageMapping();
        $this->assertInstanceOf(LanguageMapping::class, $mapping);
    }
}
