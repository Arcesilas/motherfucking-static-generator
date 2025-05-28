# Motherfucking static site generator

**Build a motherfucking static blog in (milli)seconds.**  

> *Inspired by [motherfucking website](https://motherfuckingwebsite.com) and [better motherfucking website](http://bettermotherfuckingwebsite.com/)*

## License

This generator is released under [WTF Public License](https://www.wtfpl.net/): I don't give a shit what you do with it.

---

## Features

- One fucking PHP file
- One dependency (Parsedown)
- No content? Fucking empty site.
- (Almost) no configuration. No front matter.
- No options, no flags, no CLI arguments.  
  Just run the damn script to build your shit in milliseconds.

---

## Installation

You need a recent version of PHP: its a motherfucking static generator, but it's modern.

### Via git clone

```bash
git clone https://github.com/Arcesilas/motherfucking-static-generator.git
cd motherfucking-static-generator
php fucking-build.php
```

First time run:
- it downloads Parsedown.php to you don't have to give a shit about it
- it creates a `content/` directory with a fucking first blog post
- it creates an `mfconfig.php` configuration file (Yeah. Configuration file. With one fucking option)
- it creates the default motherfucking templates
- it builds your motherfucking website

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

## Writing content

### Markdown

Obviously, content is written in `content/` directory. You may respect the directory structure :
```
content/yyyy/mm/dd/motherfucking-article.md
```
If you don't, I don't know what shit may happen.

No overkill front matter. Actually, no front matter.

### Assets

Even though it's a motherfucking static generator, it respects your shitty cat pics.  
Create a directory with the same name than your markdown content.

You really need an example?
```
- content/2025/05/28/me-and-my-cat.md
- content/2025/05/28/me-and-my-cat/my-cat.jpg
```
Content assets will be placed in the same directory, so you don't have to care about the url: `![Who gives a shit about your cat, BTW?](my-cat.jpg)`

## Issues

No warranty. No tests. No regrets.

If it breaks, it's probably your fault.
