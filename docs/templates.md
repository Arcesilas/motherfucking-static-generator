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
- `(array) $config`: what do you think it is?
- `(array) $messages`: the translation messages (english and french)

Also, templates receive specific data:

### `layout.php`:
- `$body`: the rendered content of the `post.php` or `page.php`

### `post.php` and `page.php`:
- `(array) $post` , which contains (all strings) :
  - `title`: maybe it's the title
  - `content`: self-explanatory
  - `url`: the local url to the page/post (it's not a permalink)
  - `pathname`: for technical reasons, you should not need it
  - `next`: the next post (without next/previous)
  - `previous`: the previous post (without next/previous)

### `index.php`:
- `(array) $posts`: an array of all posts on the page
- `(?string) $previous_url`: url to the previous page (null on first page)
- `(?string) $next_url`: url to the next page (null on last page)

## Helpers

Currently, only one helper exists to use in your templates:

`excerpt(string $html, int $limit = 50, string $suffix = '…'): string`

Use it to no display the whole post on index page (used in default template).
