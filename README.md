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

You don't need a motherfucking configuration file to generate you blog.

But motherfucking static generator does not mean user-unfriendly.

The generator tries to load configuration from `mfconfig.php` at the root of your project. These configuration options are available:

- site_title: self-explanatory.
- output_dir: do you really need an explanation?
- posts_per_page: pretty obvious.
- lang: don't tell me you don't understand. Two letters format.

You also may store whatever option of your choice to customize your templates: configuration is injected in every template and accessible with `$config` (obviously).

You are encouraged to use an `extra` key rather than smearing your options across the file like peanut butter (who knows, a future version may use some new options). 

## Output

Guess where output is placed: `output/`. Yeah, mind-blowing.

### Server

Ever heard about PHP built-in webserver? Just motherfucking use it:

```
php -S localhost:8000 -t output
```
> Remember that PHP built-in webserver cannot handle motherfucking HTTP 404 by itself. Il you try a non existant URL, root `index.html` will be displayed

## Translations

Default messages in english and french are defined. You can add your own in the configuration file:

```
    'messages' => [
        'es' => [
            'previous' => 'Anterior',
            'next' => 'Siguiente',
            'previous_page' => 'Página anterior',
            'next_page' => 'Página siguiente',
        ]
    ],
```
They will be added to the default messages.

Messages are available in the templates via the `$messages` variable.

Set the language in your configuration file:
```
    'lang' => 'fr',
```

---

## Writing content

### Markdown

Obviously, content is written in `content/` directory. You may respect the directory structure :
```
content/yyyy/mm/dd/motherfucking-article.md
```
If you don't, I don't know what shit may happen.

No overkill front matter. Actually, no front matter.

### File Assets

Even though it's a motherfucking static generator, it respects your shitty cat pics.  
Create a directory with the same name than your markdown content.

You really need an example?
```
- content/2025/05/28/me-and-my-cat.md
- content/2025/05/28/me-and-my-cat/my-cat.jpg
```
Content assets will be placed in the same directory, so you don't have to care about the url: `![Who gives a shit about your cat, BTW?](my-cat.jpg)`

### Global assets

Moherfucking static generator allows you to manage your global asset. You're welcome.

Just put your shit (even `.css` files, if you're crazy) in a `content/assets/` directory, they will be copied in `output/assets/` (yeah, shocking). You're a big boy or a big girl, you'll know what URL to use.

### Templates

You're probably able to write some basic HTML. Anyway, this motherfucking static generator gives you 3 basic templates to start with. They are downloaded the first time you run the script if you have no `templates/` directory.

---

## Issues

No warranty. No tests. No regrets.

If it breaks, it's probably your fault.
