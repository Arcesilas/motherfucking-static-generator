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

    private array $messages;

    public function __construct(private readonly string|false $cwd) {
        false === $cwd and exit("getcwd() failed: WTF did you do with your current working directory?");
        $this->content_dir = "$this->cwd/content";
        $this->install();
        $this->parser = new Parsedown();
        $this->config = file_exists("$this->cwd/mfconfig.php") ? require $this->cwd . "/mfconfig.php" : [];
        $lang = $this->config['lang'] ?? 'en';
        $this->output_dir = "$this->cwd/" . ($this->config['output_dir'] ?? 'output');
        $this->messages = ($this->config['messages'][$lang] ?? []) + getMessages($lang);
    }

    private function install(): void {
        $parsedown = "$this->cwd/lib/Parsedown.php";

        if (!file_exists($parsedown)) {
            mkdirIfNotExists($this->cwd.'/lib');
            $this->download('https://github.com/erusev/parsedown/raw/refs/heads/master/Parsedown.php', $parsedown);
        }
        require $parsedown;

        if (!file_exists($this->content_dir)) {
            $hello_dir = $this->content_dir . '/' . date('Y/m/d');
            mkdir($hello_dir, 0750, true);
            file_put_contents("$hello_dir/hello-world.md", "# Hello world!\n\nThis is your mother fucking static website!");
        }

        if (!file_exists($this->templates_dir = $this->cwd . '/templates')) {
            mkdir($this->templates_dir, 0750, true);
            $base_url = 'Arcesilas/motherfucking-static-generator/refs/heads/main';
            $this->download("$base_url/templates/layout", 'templates/layout.php');
            $this->download("$base_url/templates/index", 'templates/index.php');
            $this->download("$base_url/templates/post", 'templates/post.php');
        }
    }

    private function download(string $url, string $to)
    {
        file_put_contents("$this->cwd/$to", file_get_contents("https://raw.githubusercontent.com/$url"));
    }

    public function build(): void {
        rrmdir($this->output_dir);

        // Build pages
        foreach ($this->getPagesFiles("$this->content_dir/pages") as $page) {
            $page = $this->enrichFileData($page);
            $this->buildFileWithAssets($page);
        }

        $this->copyGlobalAssets();

        // Build posts
        $posts_files = iterator_to_array($this->getSourceFiles("$this->content_dir/posts"));
        rsort($posts_files);
        $this->total_index_pages = (int) ceil(count($posts_files) / ($this->config["nb_per_page"] ?? 10));

        if (empty($posts_files)) {
            mkdir($this->output_dir);
            $rendered = $this->renderTemplate('layout');
            file_put_contents("$this->output_dir/index.html", $rendered);
            return;
        }

        $current_page = 1;
        $count = 0;
        $list = [];
        foreach ($this->getPostsFiles($posts_files) as $post) {
            $list[] = $post;
            $this->buildFileWithAssets($post);
            $is_last_page = !isset($post['next']);
            $posts_per_page = $this->config['posts_per_page'] ?? 10;
            if (++$count === $posts_per_page || $is_last_page) {
                $this->buildIndexPage($list, $current_page, $is_last_page);
                $count = 0;
                $list = [];
                $current_page++;
            }
        }
    }

    private function getPagesFiles($pages_dir): Generator {
        foreach ($this->getSourceFiles($pages_dir) as $file) {
            yield $this->enrichFileData($file);
        }
    }

    private function getPostsFiles(array $posts_files): Generator {
        $previous = null;
        foreach ($posts_files as $index => $file) {
            if (!isset($file['content'])) {
                $file = $this->enrichFileData($file);
            }

            $next = null;
            if (isset($posts_files[$index + 1])) {
                $next = $posts_files[$index + 1] = $this->enrichFileData($posts_files[$index + 1]);
            }

            $file_data = $file + [
                    'previous' => $previous,
                    'next' => $next
                ];
            $previous = $file;
            yield $file_data;
        }
    }

    private function getSourceFiles(string $content_dir): Generator {
        $iterator = new CallbackFilterIterator(getIterator($content_dir), fn ($path) => str_ends_with($path, '.md'));
        foreach ($iterator as $file) {
            yield [
                'url'      => '/' . substr($file, strlen($content_dir) + 1, -3) . '/',
                'pathname' => $file,
            ];
        }
    }

    private function buildFileWithAssets(array $file, $type = 'post'): void {
        $output_dir = $this->output_dir . $file['url'];
        $file['content'] = $this->parser->text($file['content']);
        $rendered = $this->renderTemplate($type, ['post' => $file]);
        $data = [
            'body' => $rendered,
        ];

        mkdir($output_dir, 0750, true);
        file_put_contents("$output_dir/index.html", $this->renderTemplate('layout', $data));

        // File assets
        if (is_dir($this->content_dir . $file['url'])) {
            $assets = glob($this->content_dir . $file['url'] . '*');
            foreach ($assets as $asset) {
                copy($asset, $output_dir . '/' . basename($asset));
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

    private function enrichFileData(array $file): array
    {
        $content = file_get_contents($file['pathname']);
        return $file + [
            'title' => $this->extractTitle($content),
            'content' => $content,
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

    private function renderTemplate(string $type, array $vars = []): string
    {
        $vars += ['config' => $this->config, 'messages' => $this->messages];
        $type = file_exists("$this->templates_dir/$type.php") ? $type : 'post';
        extract($vars);
        ob_start();
        include "$this->templates_dir/$type.php";
        return ob_get_clean();
    }
}

function getMessages(string $lang = 'en'): array {
    $messages = [
        'en' => [
            'previous' => 'Previous',
            'next' => 'Next',
            'previous_page' => 'Previous page',
            'next_page' => 'Next page',
        ],
        'fr' => [
            'previous' => 'Précédent',
            'next' => 'Suivant',
            'previous_page' => 'Page précédente',
            'next_page' => 'Page suivante',
        ],
    ];
    return $messages[$lang] ?? $messages['en'];
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

$generator = new MotherfuckingGenerator(getcwd());
$generator->build();

$motherfucking_time += hrtime(true);

echo "Built your motherfucking blog in " . $motherfucking_time/1e+9 . " seconds.\n";
