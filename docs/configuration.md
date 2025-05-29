# Configuration

This motherfucking static website generator is zero-conf. You only need the main script `fucking-build.php` to get started.

However, *motherfucking static generator* does not mean user-unfriendly and it's not really costly to load an array from a PHP file. 

The configuration file is `./mfconfig.php` (yeah, it's a motherfucking configuration file). It should return a simple array `'key' => 'value'`, not a big deal to create.

Default available options are:
- `lang` (default: `en`)
- `output_dir` (default: `output`)
- `posts_per_page` (default: `10`)
- `site_title` (default: undefined, you may not need it if you write it in your templates)

You are free to add as many options as you need: configuration is injected in every template, so you can use it everywhere.
