# Contributing to Four Template Resolver

Thank you for your interest in contributing to Four Template Resolver! This document provides guidelines and information for contributors.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How to Contribute](#how-to-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)

## Code of Conduct

This project adheres to a code of conduct that promotes a respectful and inclusive environment. By participating, you agree to uphold these standards.

### Our Standards

- Use welcoming and inclusive language
- Be respectful of differing viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

## How to Contribute

### Types of Contributions

We welcome several types of contributions:

1. **Bug Reports** - Help us identify and fix issues
2. **Feature Requests** - Suggest new functionality
3. **Code Contributions** - Submit bug fixes or new features
4. **Documentation** - Improve guides, examples, and API docs
5. **Testing** - Add test cases and improve coverage
6. **Performance** - Optimize existing code

### Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Create a feature branch
4. Make your changes
5. Test thoroughly
6. Submit a pull request

## Development Setup

### Prerequisites

- PHP 8.4+
- Composer
- Git

### Installation

```bash
git clone https://github.com/YOUR_USERNAME/four-template-resolver.git
cd four-template-resolver
composer install
```

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run quality checks
composer quality
```

## Coding Standards

### PHP Standards

- **PHP Version**: Minimum PHP 8.4
- **Type Safety**: Use `declare(strict_types=1)` in all files
- **Code Style**: Follow PSR-12 coding standards
- **Static Analysis**: Code must pass PHPStan level 8

### Code Quality Tools

```bash
# Check code style
composer phpcs

# Fix code style issues
composer phpcbf

# Run static analysis
composer phpstan

# Run all quality checks
composer quality
```

### Architecture Guidelines

1. **Strict Typing**: All methods must have type declarations
2. **Interface Segregation**: Use interfaces for contracts
3. **Dependency Injection**: Avoid static dependencies
4. **Immutability**: Prefer immutable objects where possible
5. **Error Handling**: Use specific exception types

### Code Example

```php
<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Example;

use Four\TemplateResolver\Exception\ExampleException;

/**
 * Example class following project standards
 */
class ExampleClass
{
    public function __construct(
        private readonly string $requiredParam,
        private readonly bool $optionalParam = false
    ) {
    }

    public function processData(array $data): string
    {
        if (empty($data)) {
            throw new ExampleException('Data cannot be empty');
        }

        return $this->doProcessing($data);
    }

    private function doProcessing(array $data): string
    {
        // Implementation
        return 'processed';
    }
}
```

## Testing Guidelines

### Test Structure

- **Unit Tests**: Test individual components in isolation
- **Integration Tests**: Test component interactions
- **Mock Objects**: Use mocks for external dependencies
- **Test Coverage**: Aim for 95%+ code coverage

### Writing Tests

```php
<?php

declare(strict_types=1);

namespace Four\TemplateResolver\Tests\Unit;

use Four\TemplateResolver\Tests\TestCase;

class ExampleTest extends TestCase
{
    private ExampleClass $example;

    protected function setUp(): void
    {
        parent::setUp();
        $this->example = new ExampleClass('required');
    }

    public function testValidInput(): void
    {
        $result = $this->example->processData(['key' => 'value']);
        
        $this->assertEquals('processed', $result);
    }

    public function testInvalidInput(): void
    {
        $this->expectException(ExampleException::class);
        $this->example->processData([]);
    }
}
```

### Test Requirements

- All public methods must have tests
- Test both success and error cases
- Use descriptive test method names
- Include edge cases and boundary conditions
- Mock external dependencies

## Pull Request Process

### Before Submitting

1. **Code Quality**: All quality checks must pass
2. **Tests**: New code must have tests
3. **Documentation**: Update docs if needed
4. **Compatibility**: Ensure backward compatibility
5. **Performance**: Consider performance implications

### PR Checklist

- [ ] Code follows project standards
- [ ] Tests are included and pass
- [ ] Documentation is updated
- [ ] PHPStan level 8 passes
- [ ] Code coverage is maintained
- [ ] No breaking changes (unless major version)
- [ ] Examples work correctly

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Integration tests added/updated
- [ ] Manual testing performed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Tests pass
```

### Review Process

1. **Automated Checks**: GitHub Actions must pass
2. **Code Review**: Maintainer review required
3. **Testing**: Verify functionality works
4. **Documentation**: Check docs are accurate
5. **Merge**: Squash and merge when approved

## Issue Reporting

### Bug Reports

When reporting bugs, please include:

```markdown
## Bug Description
Clear description of the issue

## Steps to Reproduce
1. Step one
2. Step two
3. Step three

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- PHP Version: 8.4.x
- Library Version: 1.0.x
- OS: Linux/Windows/macOS

## Additional Context
Any other relevant information
```

### Feature Requests

For feature requests, include:

- **Problem**: What problem does this solve?
- **Solution**: Proposed solution
- **Alternatives**: Alternative solutions considered
- **Use Cases**: Specific use cases
- **Backward Compatibility**: Impact on existing code

## Documentation Guidelines

### Documentation Types

1. **API Documentation** - Method signatures and behavior
2. **Usage Guides** - How to use features
3. **Examples** - Practical code examples
4. **Architecture** - Design decisions and patterns

### Writing Standards

- Use clear, concise language
- Include code examples
- Test all examples
- Update with code changes
- Use consistent formatting

## Release Process

### Versioning

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

### Release Checklist

- [ ] All tests pass
- [ ] Documentation updated
- [ ] Examples verified
- [ ] CHANGELOG.md updated
- [ ] Version bumped
- [ ] Git tag created

## Community

### Getting Help

- **GitHub Issues**: For bugs and feature requests
- **GitHub Discussions**: For questions and discussions
- **Email**: info@4bytes.de for direct contact

### Recognition

Contributors will be recognized in:

- README.md contributors section
- CHANGELOG.md for significant contributions
- GitHub releases for major contributions

## License

By contributing to Four Template Resolver, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Four Template Resolver! 🎉