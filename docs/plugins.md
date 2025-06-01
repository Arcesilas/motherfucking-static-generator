# Plugins

This motherfucking static generator has a motherfucking plugin system. Yeah. 2 methods, 2 properties and 8 lines of code. No useless shit.

**Convention > Configuration**:
- Plugins name must be in kebab-case, with `php-plugin` prefix: `php-plugin-foo-bar-baz`. this must be the name of the directory in the `plugins/` folder
- The plugin file must be in PascalCase, with `.php` suffix: `FooBarBaz.php`
- The plugin class must be the same as the file (without prefix, and without a namespace): `FooBarBaz`
- The plugin class must have a public method `getHooks(): array`

Respect the conventions, or it breaks. As it should.

To disable a plugin, just rename the directory, for instance: `_php-plugin-foo-bar-baz` will not match and therefore will not be loaded.

## Registration

**Simple is better**: when registered, the `getHooks()` method is called, to know what hooks the plugin responds to. No magic. No guessing.

## Hooks

Hooks can be:
- a *notification*: the méthod returns `void` (return a value if you want, it won’t change a damn thing).
- a transformation: the return value replaces (prefix `=`) or appends (prefix `+=`) the original value.

### Available hooks

- `setContentDir(string $content_dir)`
- `setOutputDir(string $output_dir): void`
- `setTemplatesDir(string $templates_dir): void`
- `setGenerator(MotherfuckingGenerator $generator): void`
- `setParser(Parsedown $parsedown): void`
- `handleFileAsset(string $asset): void`
- `handleGlobalAsset(string $asset): void`
- `beforeBuild(): void`
- `afterBuild(): void`
- `= alterUrl(string $original_url): string $new_url`
- `+= registerConfig(array $configuration): array $config`
- `+= enrichFileData(array $file): array $file`
- `+= renderTemplate(['template' => $template, 'vars' => $vars]): array $additional_vars`

About the variables:
- `$content_dir`, `$output_dir`, `$templates_dir`: you don't need a schema, do you?
- `$generator`: the MotherfuckingGenerator instance
- `$parsedown`: the Parsedown instance.
- `$configuration`: the global configuration from `./mfconfig.php`
- `$file` is an array as described in the [templates docs](templates.md)
- `$original_url`: URL of the file when built (ie. `2025/05/31/foo-article/`). It can be used a an identifier for the file, since 2 files should not have the same (it you follow the rules when writing content)
- `$asset`: is the relative path of the asset
- `$template`: name of the template (typically `post` or `page`)
- `$vars`: the variables passed to the template (see [templates docs](templates.md))
