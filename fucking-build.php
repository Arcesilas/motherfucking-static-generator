#!/usr/bin/env php
<?php

declare(strict_types=1);

$motherfucking_time = -hrtime(true);

function checkDep($cwd): void
{
    $parsedownPath = "$cwd/lib/Parsedown.php";

    if (!file_exists($parsedownPath)) {
        mkdirIfNotExists(dirname($parsedownPath));
        writeIfNotExists(
            $parsedownPath,
            file_get_contents('https://github.com/erusev/parsedown/raw/refs/heads/master/Parsedown.php')
        );
    }
    require $parsedownPath;
}

function checkContent(string $content_dir): void
{
    if (! file_exists($content_dir)) {
        mkdir($content_dir, 0750);
        $hello_path = "$content_dir/" . date('Y/m/d') . '/hello-world.md';
        mkdirIfNotExists(dirname($hello_path));
        writeIfNotExists($hello_path, "# Hello world!\n\nThis is your mother fucking static website!");
    }
}

function checkTemplates(string $templates_dir): void
{
    if (! file_exists($templates_dir)) {
        mkdir($templates_dir, 0750);
        file_put_contents($templates_dir . '/layout.php', getTemplateLayout());
        file_put_contents($templates_dir . '/index.php', getTemplateIndex());
        file_put_contents($templates_dir . '/post.php', getTemplatePost());
    }
}

function mkdirIfNotExists(string $dir): void
{
    if (!file_exists($dir)) {
        mkdir($dir, 0750, true);
    }
}

function writeIfNotExists(string $file, string $content): void
{
    if (! file_exists($file)) {
        file_put_contents($file, $content);
    }
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            rrmdir($path); // récursivité
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function getMarkdownFiles(string $base_path): Generator
{
    $iterator_flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_path, $iterator_flags),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $source_files = [];
    foreach ($iterator as $file) {
        if (!str_ends_with($file, '.md')) {
            continue;
        }
        $relative_path = substr($file, strlen($base_path) + 1);
        $source_files[] = [
            'relative_path' => $relative_path,
            'pathname' => $file,
        ];
    }
    rsort($source_files);

    $previous = null;
    foreach ($source_files as $index => $file) {
        if (!isset($file['content'])) {
            $file = complete_file_data($file);
        }

        if (isset($source_files[$index + 1])) {
            $next = $source_files[$index + 1] = complete_file_data($source_files[$index + 1]);
        } else {
            $next = null;
        }

        $file_data = $file + [
            'previous' => $previous,
            'next' => $next
        ];
        $previous = $file;
        yield $file_data;
    }
}

function excerpt(string $html, int $limit = 50, string $suffix = '…'): string
{
    $words = preg_split('`\s+`', strip_tags($html));
    $excerpt = implode(' ', array_slice($words, 0, $limit));
    return  $excerpt . (count($words) > $limit ? $suffix : '');
}

function complete_file_data(array $file): array
{
    $content = file_get_contents($file['pathname']);
    return $file + [
        'title' => extractTitle($content),
        'content' => $content,
        'url' => '/' . substr_replace($file['relative_path'], '/', -3),
    ];
}

function extractTitle(string &$markdown): string
{
    if (preg_match('`^# (.+)\R+`', ltrim($markdown), $matches)) {
        $markdown = ltrim(substr($markdown, strlen($matches[0])));
        return $matches[1];
    }
    return '';
}

function checkEnv(string|false $cwd): void
{
    if (false === $cwd) {
        echo "getcwd() fucking failed. What's wrong with you?";
        exit;
    }

    if ($_SERVER['argc'] > 1) {
        echo "You don't need fucking arguments. This script does only one shit. Deal with it.\n";
        exit;
    }
}

function checkConfig(string $cwd, $default_config): void
{
    if (!file_exists($cwd . '/mfconfig.php')) {
        file_put_contents($cwd . '/mfconfig.php', $default_config);
    }
}

function render_template(string $path, array $vars): string
{
    extract($vars);
    ob_start();
    include $path;
    return ob_get_clean();
}

function index_url(int $page): ?string
{
    return match ($page) {
        0 => null,
        1 => '/',
        default => "/page-$page/"
    };
}

$default_config = <<<'config'
<?php

return [
    'site_title' => 'This is a motherfucking static blog',
];

config;

function getTemplateLayout(): string
{
    return <<<'layout'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=htmlspecialchars($config['site_title'])?></title>
    <style>body{margin:40px auto;width: 100%;max-width:70ch;line-height:1.6;font-size:18px;color:#444;padding:0 10px}
    h1,h2,h3{line-height:1.2}</style>
</head>
<body>
    <header>
        <h1><a href="/"><?=htmlspecialchars($config['site_title'])?></a></h1>
    </header>
    <main>
        <?= isset($body) ? $body : "<p>This motherfucking static blog is fucking empty.</p>"?>
    </main>
    <footer>
        <p>This motherfucking static website is powered by 
            <a href="https://github.com/Arcesilas/motherfucking-static-generator">motherfucking static generator</a>
        </p>
    </footer>
</body>
</html>
layout;
}

function getTemplateIndex(): string
{
    return <<<'index'
<?php if (!empty($list)):?>
    <?php foreach ($list as $post):?>
        <article>
            <h1><a href="<?=$post['url']?>"><?=htmlspecialchars($post['title'])?></a></h1>
            <?=excerpt($post['content'])?>
        </article>
    <?php endforeach ?>
    <nav>
        <?php if(isset($previous_url)):?><a href="<?=$previous_url?>">&laquo; Previous</a><?php endif?>
        <?php if(isset($next_url)):?><a href="<?=$next_url?>">Next &raquo;</a><?php endif?>
    </nav>
<?php else: ?>
<p>This motherfucking static blog is fucking empty.</p>
<?php endif ?>
index;
}

function getTemplatePost(): string
{
    return <<<'post'
<article>
    <?=$post['content']?>
</article>
<?php if(isset($post['previous'])):?><a href="<?=$post['previous']['url']?>">&laquo; Previous</a><?php endif?>
<?php if(isset($post['next'])):?><a href="<?=$post['next']['url']?>">Next &raquo;</a><?php endif?>
post;
}

$cwd = getcwd();
$content_dir = "$cwd/content";
$output_dir = "$cwd/output";

checkEnv($cwd);
checkDep($cwd);
checkContent($content_dir);

checkConfig($cwd, $default_config);
checkTemplates($cwd . '/templates');

$parser = new Parsedown();
$config = require $cwd . '/mfconfig.php';

rrmdir($output_dir);

$page = 1;
$list = [];
$index = 0;
$empty = true;

foreach (getMarkdownFiles($content_dir) as $post) {
    $empty === true and $empty = false;
    $post['content'] = $parser->text($post['content']);
    $post_rendered = render_template("$cwd/templates/post.php", ['post' => $post]);
    $data = [
        'body' => $post_rendered,
        'config' => $config,
    ];
    $list[] = $post;

    $page_rendered = render_template("$cwd/templates/layout.php", $data);
    $post_output_dir = "$output_dir/$post[url]";
    mkdir($post_output_dir, 0750, true);
    file_put_contents("$post_output_dir/index.html", $page_rendered);

    // File assets
    if (is_dir($content_dir . $post['url'])) {
        $assets = glob($content_dir . $post['url'] . '*');
        foreach ($assets as $asset) {
            copy($asset, $post_output_dir . '/' . basename($asset));
        }
    }

    $this_is_the_end = !isset($post['next']);
    if (++$index === 5 || $this_is_the_end) {
        $index_rendered = render_template("$cwd/templates/index.php", [
            'list' => $list,
            'previous_url' => index_url($page - 1),
            'next_url' => $this_is_the_end ? null : index_url($page + 1),
        ]);
        $data = [
            'body' => $index_rendered,
            'config' => $config,
        ];
        $page_rendered = render_template("$cwd/templates/layout.php", $data);
        $page_slug = index_url($page);
        $index_output_dir = "{$output_dir}{$page_slug}";

        mkdirIfNotExists($index_output_dir, 0750, true);
        file_put_contents("$index_output_dir/index.html", $page_rendered);

        $index = 0;
        $list = [];
        $page++;
    }
}

if ($empty) {
    mkdirIfNotExists($output_dir);
    $rendered = render_template("$cwd/templates/layout.php", compact('config'));
    file_put_contents("$output_dir/index.html", $rendered);
}

$assets_dir = $content_dir . '/assets';
if (is_dir($assets_dir)) {
    $target_dir = $output_dir . '/assets';
    $iterator_flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME;
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($assets_dir, $iterator_flags),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($iterator as $asset) {
        $relative_path = substr($asset, strlen($assets_dir) + 1);
        $target_path = "$target_dir/$relative_path";
        mkdirIfNotExists(dirname($target_path));
        copy($asset, $target_path);
    }
}

$motherfucking_time += hrtime(true);

echo "Built your motherfucking blog in " . $motherfucking_time/1e+9 . " seconds.\n";
