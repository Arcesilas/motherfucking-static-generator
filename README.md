# Motherfucking static site generator

**Build a motherfucking static blog in (milli)seconds.**  

> *Inspired by [motherfucking website](https://motherfuckingwebsite.com) and [better motherfucking website](http://bettermotherfuckingwebsite.com/)*

## License

This generator is released under [WTF Public License](https://www.wtfpl.net/): I don't give a shit what you do with it.

---

## Features

- One motherfucking PHP file
- One dependency (Parsedown)
- No content? Empty site.
- Zero-conf. No front matter.
- No options, no flags, no CLI arguments.  
  Just run the damn script to build your shit in milliseconds.

and

- translations
- customizable templates
- more to come

---

## Installation

You need a recent version of PHP: it's a motherfucking static generator, but it's modern.

### Via git clone

```bash
git clone https://github.com/Arcesilas/motherfucking-static-generator.git
cd motherfucking-static-generator
php fucking-build.php
```

First time run:
- it downloads Parsedown.php so you don't have to give a shit about it
- it creates a `content/` directory with a motherfucking first blog post
- it downloads default `templates/` if directory is not present
- it builds your motherfucking blog

### Via wget

You're probably lazy. And you're right. it's motherfucking efficient.

```
php <(wget -qO- https://raw.githubusercontent.com/Arcesilas/motherfucking-static-generator/main/fucking-build.php)
```
You're done.

### Via Composer

You don't need this shit to build a motherfucking website.

## CLI options

What?

## Configuration

See [configuration](docs/configuration.md) documentation.

## Writing content

See [content](docs/writing-content.md) documentation.

## Output

By default, output is placed in `output/` directory. Yeah, I had to think hard for this one.

You can also configure it.

## Preview

Ever heard about PHP built-in webserver? Just motherfucking use it:

```
php -S localhost:8000 -t output
```
> Remember that PHP built-in webserver cannot handle motherfucking HTTP 404 by itself. Il you try a non existant URL, root `index.html` will be displayed

## More docs

- [templates](docs/templates.md)
- [translation](docs/translation.md)

## Issues

No warranty. No tests. No regrets.

If it breaks, it's probably your fault.
