# AGENTS.md

This file provides guidance to Claude Code when working with this repository.

## What is four-template-resolver?

Modern PHP template resolver with entity-based processing, hierarchical fallback, and multi-language support.

## Build & Development Commands

| Command | Description |
|---------|-------------|
| `composer install` | Install PHP dependencies |
| `composer test` | Run PHPUnit test suite |
| `composer phpstan` | Run static analysis |
| `composer phpcs` | Run code style checks |
| `composer quality` | Run full quality gate |

## Architecture

- **`src/`** — Template resolver source code
- **`tests/`** — PHPUnit test suite
- **`composer.json`** — Dependencies and scripts

## Key Conventions

- **Namespace:** `Four\TemplateResolver\`
- PHP 8.4, PSR-12 code style
- UUID7 for all primary keys
- LF line endings, real umlauts (ä, ö, ü, ß)
- Conventional commits with issue reference: `feat: description (#42)`
- Branch naming: `feat/GH-{nr}-desc`, `fix/GH-{nr}-desc`

## Tech Stack

PHP 8.4, PHPUnit 11, PHPStan, PSR-12, PSR Cache

## Dependencies

- **Depends on:** PSR Cache implementation
- **Used by:** Four Bytes marketplace platform
