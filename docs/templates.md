# Templates

No template engine is used, we don't need these shits to build a motherfucking static website. Templates are pure PHP.

## The files

If you cloned the repository, you already have:
- `layout.php`
- `index.php`
- `post.php`

If you downloaded only the script, it will download the default template files if no `templates` dir is found at the root of your project.

You can create an optional `page.php` file to display your pages. `post.php` will be used by default.

## Variables

Templates are very customizable. Each template always receive the following variables:
- `$config`: what do you think it is?
- `$messages`: the translation messages (english and french)

`post.php` and `page.php` templates also receive:
- `$post`

## Helpers

Currently, only one helper exists to use in your templates:

`excerpt(string $html, int $limit = 50, string $suffix = '…'): string`

Use it to no display the whole post on index page (used in default template).
