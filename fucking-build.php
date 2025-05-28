#!/usr/bin/env php
<?php

declare(strict_types=1);

$motherfucking_time = -hrtime(true);

class MotherfuckingGenerator {
    private readonly array $config;

    private readonly string $content_dir;

    private readonly string $output_dir;

    private readonly string $templates_dir;

    private readonly Parsedown $parser;

    private array $source_files = [];

    private int $total_index_pages;

    public function __construct(private readonly string|false $cwd) {
        false === $cwd and exit("getcwd() failed: WTF did you do with your current working directory?");
        $this->content_dir = "$this->cwd/content";
        $this->output_dir = "$this->cwd/output";
        $this->install();
        $this->parser = new Parsedown();
        $this->config = file_exists("$this->cwd/mfconfig.php") ? require $this->cwd . "/mfconfig.php" : [];
    }

    private function install(): void {
        $parsedown = "$this->cwd/lib/Parsedown.php";

        if (!file_exists($parsedown)) {
            mkdirIfNotExists($this->cwd.'/lib');
            file_put_contents(
                $parsedown,
                file_get_contents('https://github.com/erusev/parsedown/raw/refs/heads/master/Parsedown.php')
            );
        }
        require $parsedown;

        if (!file_exists($this->content_dir)) {
            $hello_dir = $this->content_dir . '/' . date('Y/m/d');
            mkdir($hello_dir, 0750, true);
            file_put_contents($hello_dir . '/hello-world.md', "# Hello world!\n\nThis is your mother fucking static website!");
        }

        if (!file_exists($this->templates_dir = $this->cwd . '/templates')) {
            mkdir($this->templates_dir, 0750, true);
            file_put_contents($this->templates_dir . '/layout.php', getTemplateLayout());
            file_put_contents($this->templates_dir . '/index.php', getTemplateIndex());
            file_put_contents($this->templates_dir . '/post.php', getTemplatePost());
        }
    }

    public function build(): void {
        rrmdir($this->output_dir);
        $this->source_files = $this->getSourceFiles();
        if (empty($this->source_files)) {
            mkdir($this->output_dir);
            $rendered = $this->renderTemplate('layout', ['config' => $this->config]);
            file_put_contents("$this->output_dir/index.html", $rendered);
            return;
        }

        $current_page = 1;
        $count = 0;
        $list = [];
        foreach ($this->getMardownFiles() as $post) {
            $list[] = $post;
            $this->buildPostWithAssets($post, $this->output_dir . $post['url']);
            $is_last_page = !isset($post['next']);
            $posts_per_page = $this->config['posts_per_page'] ?? 10;
            if (++$count === $posts_per_page || $is_last_page) {
                $this->buildIndexPage($list, $current_page, $is_last_page);
                $count = 0;
                $list = [];
                $current_page++;
            }
        }

        $this->copyGlobalAssets();
    }

    private function buildPostWithAssets(array $post, string $post_output_dir): void {
        $post['content'] = $this->parser->text($post['content']);
        $rendered = $this->renderTemplate('post', ['post' => $post]);
        $data = [
            'body' => $rendered,
            'config' => $this->config,
        ];

        mkdir($post_output_dir, 0750, true);
        file_put_contents("$post_output_dir/index.html", $this->renderTemplate('layout', $data));

        // File assets
        if (is_dir($this->content_dir . $post['url'])) {
            $assets = glob($this->content_dir . $post['url'] . '*');
            foreach ($assets as $asset) {
                copy($asset, $post_output_dir . '/' . basename($asset));
            }
        }
    }

    private function buildIndexPage(array $posts, int $page_num, $is_last_page): void {
        $rendered = $this->renderTemplate('index', [
            'posts' => $posts,
            'previous_url' => index_url($page_num - 1),
            'next_url' => $is_last_page ? null : index_url($page_num + 1),
        ]);
        $rendered = $this->renderTemplate('layout', [
            'body' => $rendered,
            'config' => $this->config,
        ]);
        $page_slug = index_url($page_num);
        $output_dir = $this->output_dir . '/' . $page_slug;

        mkdirIfNotExists($output_dir, 0750, true);
        file_put_contents("$output_dir/index.html", $rendered);
    }

    private function copyGlobalAssets(): void {
        $assets_dir = $this->content_dir . '/assets';
        if (is_dir($assets_dir)) {
            foreach (getIterator($assets_dir) as $asset) {
                $target_path = "$this->output_dir/assets/" . substr($asset, strlen($assets_dir) + 1);
                mkdirIfNotExists(dirname($target_path));
                copy($asset, $target_path);
            }
        }
    }

    private function getSourceFiles(): array {
        $source_files = [];
        $iterator = getIterator($this->content_dir);
        foreach ($iterator as $file) {
            if (!str_ends_with($file, '.md')) {
                continue;
            }
            $relative_path = substr($file, strlen($this->content_dir) + 1);
            $source_files[] = [
                'relative_path' => $relative_path,
                'pathname' => $file,
            ];
        }
        rsort($source_files);
        $this->total_index_pages = (int) ceil(count($source_files) / ($this->config["nb_per_page"] ?? 10));
        return $source_files;
    }

    private function getMardownFiles(): Generator {
        $previous = null;
        foreach ($this->source_files as $index => $file) {
            if (!isset($file['content'])) {
                $file = $this->enrichFileData($file);
            }

            $next = null;
            if (isset($this->source_files[$index + 1])) {
                $next = $source_files[$index + 1] = $this->enrichFileData($this->source_files[$index + 1]);
            }

            $file_data = $file + [
                'previous' => $previous,
                'next' => $next
            ];
            $previous = $file;
            yield $file_data;
        }
    }

    private function enrichFileData(array $file): array
    {
        $content = file_get_contents($file['pathname']);
        return $file + [
            'title' => $this->extractTitle($content),
            'content' => $content,
            'url' => '/' . substr_replace($file['relative_path'], '/', -3),
        ];
    }

    private function extractTitle(string &$markdown): string
    {
        if (preg_match('`^# (.+)\R+`', ltrim($markdown), $matches)) {
            $markdown = ltrim(substr($markdown, strlen($matches[0])));
            return $matches[1];
        }
        return '';
    }

    private function renderTemplate(string $path, array $vars): string
    {
        $path = "$this->templates_dir/$path.php";
        extract($vars);
        ob_start();
        include $path;
        return ob_get_clean();
    }
}

function getIterator(string $path): RecursiveIteratorIterator {
    return new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
}

function mkdirIfNotExists(string $dir): void
{
    if (!file_exists($dir)) {
        mkdir($dir, 0750, true);
    }
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') { continue; }
        $path = "$dir/$item";
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function excerpt(string $html, int $limit = 50, string $suffix = '…'): string
{
    $words = preg_split('`\s+`', strip_tags($html));
    $excerpt = implode(' ', array_slice($words, 0, $limit));
    return  $excerpt . (count($words) > $limit ? $suffix : '');
}

function index_url(int $page): ?string
{
    return match ($page) {
        0 => null,
        1 => '/',
        default => "/page-$page/"
    };
}

function getTemplateLayout(): string
{
    return <<<'layout'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=htmlspecialchars($config['site_title'] ?? '')?></title>
    <style>body{margin:40px auto;width: 100%;max-width:70ch;line-height:1.6;font-size:18px;color:#444;padding:0 10px}
    h1,h2,h3{line-height:1.2}</style>
</head>
<body>
    <?php if(isset($config['site_title'])): ?>
    <header>
        <h1><a href="/"><?=htmlspecialchars($config['site_title'])?></a></h1>
    </header>
    <?php endif; ?>
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

function getTemplateIndex(): string {
    return <<<'index'
<?php if (!empty($posts)):?>
    <?php foreach ($posts as $post):?>
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

function getTemplatePost(): string {
    return <<<'post'
<article>
    <header>
    <h1><?= $post['title']?></h1>
    </header>
    <?=$post['content']?>
</article>
<?php if(isset($post['previous'])):?><a href="<?=$post['previous']['url']?>">&laquo; Previous</a><?php endif?>
<?php if(isset($post['next'])):?><a href="<?=$post['next']['url']?>">Next &raquo;</a><?php endif?>
post;
}

$generator = new MotherfuckingGenerator(getcwd());
$generator->build();

$motherfucking_time += hrtime(true);

echo "Built your motherfucking blog in " . $motherfucking_time/1e+9 . " seconds.\n";
