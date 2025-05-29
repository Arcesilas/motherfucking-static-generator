# Writing content

## Markdown

Obviously, content is written in `content/` directory.

This motherfucking static website generator allows you to write both pages and posts. I guess you can make the difference between both.

You should respect the directory structure :
```
content/assets/banner.jpg
content/pages/whatever.md
content/posts/yyyy/mm/dd/motherfucking-article.md
```

Because of how directories are structured and output is built, you should not use the following names for your files:

- `assets.md` => would render to `assets/index.html`, and `assets` directory is already processed on its own
- `yyyy.md` where `yyyy` is a year => posts use this directory structure
- `index.md` => would obviously cause collision with the home page 

If you don't follow these rules, I don't know what shit may happen.

Content files are pure markdown. No overkill front matter. Actually, no front matter.

## Title

Since this motherfucking static generator does not use front matter, the title is extracted from posts and pages.

If you don't use `# Post title` on the first line in your post or page, it will have no title. Which you may want (weird).

## File Assets

Even though it's a motherfucking static generator, it respects your shitty cat pics.  
Create a directory with the same name than your markdown content.

You really need an example?
```
- content/2025/05/28/me-and-my-cat.md
- content/2025/05/28/me-and-my-cat/my-cat.jpg
```
Content assets will be placed in the same directory, so you don't have to care about the url: `![Who gives a shit about your cat, BTW?](my-cat.jpg)`

## Global assets

This motherfucking static generator allows you to manage your global asset. You're welcome.

Just put your shit (even `.css` files, if you're crazy) in a `content/assets/` directory, they will be copied in `output/assets/` (yeah, shocking). You're a big boy or a big girl, you'll know what URL to use.
