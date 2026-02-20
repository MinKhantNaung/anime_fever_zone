<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.16
- laravel/framework (LARAVEL) - v12
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `livewire-development` — Develops reactive Livewire 4 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI.
- `tailwindcss-development` — Styles applications using Tailwind CSS v3 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `vendor/bin/sail artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel file structure.
- This is perfectly fine and recommended by Laravel. Follow the existing structure from Laravel 10. We do not need to migrate to the new Laravel structure unless the user explicitly requests it.

## Laravel 10 Structure

- Middleware typically lives in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration happens in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule register in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces using only PHP — no JavaScript required.
- Instead of writing frontend code in JavaScript frameworks, you use Alpine.js to build the UI when client-side interactions are required.
- State lives on the server; the UI reflects it. Validate and authorize in actions (they're like HTTP requests).
- IMPORTANT: Activate `livewire-development` every time you're working with Livewire-related tasks.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test`, simply run `vendor/bin/sail bin pint` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `vendor/bin/sail artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `vendor/bin/sail artisan test --compact`.
- To run all tests in a file: `vendor/bin/sail artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `vendor/bin/sail artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== prism-php/prism rules ===

## Prism

- Prism is a Laravel package for integrating Large Language Models (LLMs) into applications with a fluent, expressive and eloquent API.
- Prism supports multiple AI providers: OpenAI, Anthropic, Ollama, Mistral, Groq, XAI, Gemini, VoyageAI, ElevenLabs, DeepSeek, and OpenRouter, Amazon Bedrock.
- Always use the `Prism\Prism\Facades\Prism` facade, class, or `prism()` helper function for all LLM interactions.
- Prism documentation follows the `llms.txt` format for its docs website and its hosted at `https://prismphp.com/**`
- **Before implementing any features using Prism, use the `web-search` tool to get the latest docs for that specific feature. The docs listing is available in <available-docs>**

### Basic Usage Patterns

- Use `Prism::text()` for text generation, `Prism::structured()` for structured output, `Prism::embeddings()` for embeddings, `Prism::image()` for image generation, and `Prism::audio()` for audio processing.
- Always chain the `using()` method to specify provider and model before generating responses.
- Use `asText()`, `asStructured()`, `asStream()`, `asEmbeddings()`, `asDataStreamResponse()`, `asEventStreamResponse()`, `asBroadcast()` etc. to finalize the request based on the desired response type.
- You can also use the fluent `prism()` helper function as an alternative to the Prism facade.

<available-docs>

## Getting Started

- [https://prismphp.com/getting-started/introduction.md] Use these docs for comprehensive introduction to Prism package, overview of architecture, list of all supported LLM providers (OpenAI, Anthropic, Gemini, etc.), and understanding the core philosophy behind Prism's unified API design
- [https://prismphp.com/getting-started/installation.md] Use these docs for step-by-step installation instructions via Composer, package registration, publishing config files, and initial setup requirements including PHP version compatibility
- [https://prismphp.com/getting-started/configuration.md] Use these docs for detailed configuration guide including environment variables, API keys setup for each provider, config file structure, default provider selection, and Laravel integration options

## Core Concepts

- [https://prismphp.com/core-concepts/text-generation.md] Use these docs for complete guide to text generation including basic usage patterns, the fluent API, provider/model selection, prompt engineering, max tokens configuration, temperature settings, and response handling
- [https://prismphp.com/core-concepts/streaming-output.md] Use these docs for streaming responses in real-time, handling chunked output, streaming event types, and streaming response types
- [https://prismphp.com/core-concepts/tools-function-calling.md] Use these docs for comprehensive tool/function calling functionality, defining tools with JSON schemas, registering handler functions, multi-step tool execution, error handling in tools, and provider-specific tool calling capabilities
- [https://prismphp.com/core-concepts/structured-output.md] Use these docs for generating structured JSON output, defining schemas and handling structured responses across different providers
- [https://prismphp.com/core-concepts/embeddings.md] Use these docs for creating vector embeddings from text and documents, choosing embedding models, and use cases like semantic search and similarity matching
- [https://prismphp.com/core-concepts/image-generation.md] Use these docs for generating images from text prompts, configuring image size and quality, working with different image models and handling image responses
- [https://prismphp.com/core-concepts/audio.md] Use these docs for audio processing including text-to-speech (TTS) synthesis, speech-to-text (STT) transcription, voice selection, audio format options, and handling audio files
- [https://prismphp.com/core-concepts/schemas.md] Use these docs for defining and working with schemas for structured output, JSON schema specifications
- [https://prismphp.com/core-concepts/prism-server.md] Use these docs for setting up and using Prism Server, Prism Server is a powerful feature that allows you to expose your Prism-powered AI models through a standardized API.
- [https://prismphp.com/core-concepts/testing.md] Use these docs for testing Prism integrations avoiding real API calls in tests, and assertion helpers

## Input Modalities

- [https://prismphp.com/input-modalities/images.md] Use these docs for passing images as input to LLMs, supporting multiple image formats
- [https://prismphp.com/input-modalities/documents.md] Use these docs for processing documents (PDFs, Word docs, etc.) as input, document parsing and text extraction
- [https://prismphp.com/input-modalities/audio.md] Use these docs for using audio files as input, audio transcription to text, supported audio formats
- [https://prismphp.com/input-modalities/video.md] Use these docs for working with video input for supporting providers.

## Providers

- [https://prismphp.com/providers/anthropic.md] Use these docs for Anthropic (Claude) provider and provider-specific parameters
- [https://prismphp.com/providers/deepseek.md] Use these docs for DeepSeek provider and provider-specific parameters
- [https://prismphp.com/providers/elevenlabs.md] Use these docs for ElevenLabs text-to-speech provider and provider-specific parameters
- [https://prismphp.com/providers/gemini.md] Use these docs for Google Gemini provider and provider-specific parameters
- [https://prismphp.com/providers/groq.md] Use these docs for Groq provider setup and provider-specific parameters
- [https://prismphp.com/providers/mistral.md] Use these docs for Mistral AI provider and provider-specific parameters
- [https://prismphp.com/providers/ollama.md] Use these docs for Ollama local LLM provider and provider-specific parameters
- [https://prismphp.com/providers/openai.md] Use these docs for OpenAI provider and provider-specific parameters
- [https://prismphp.com/providers/openrouter.md] Use these docs for OpenRouter provider and provider-specific parameters
- [https://prismphp.com/providers/voyageai.md] Use these docs for VoyageAI embeddings provider and provider-specific parameters
- [https://prismphp.com/providers/xai.md] Use these docs for XAI (Grok) provider and provider-specific parameters

## Advanced

- [https://prismphp.com/advanced/error-handling.md] Use these docs for error handling strategies,
- [https://prismphp.com/advanced/custom-providers.md] Use these docs for creating custom provider implementations, extending the Provider base class, implementing required methods (text, stream, structured, embeddings)
- [https://prismphp.com/advanced/rate-limits.md] Use these docs for managing API rate limits across providers.
- [https://prismphp.com/advanced/provider-interoperability.md] Use these docs for switching between providers seamlessly, writing provider-agnostic code
</available-docs>

#### Prism Relay (Model Context Protocol Integration) (https://github.com/prism-php/relay)

#### Prism Bedrock (AWS Bedrock Provider) (https://github.com/prism-php/bedrock)

</laravel-boost-guidelines>
