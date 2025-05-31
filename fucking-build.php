#!/usr/bin/env php
<?php

declare(strict_types=1);

$motherfucking_time = -hrtime(true);

class MotherfuckingGenerator {
    private array $config;

    private readonly string $content_dir;

    private readonly string $output_dir;

    private readonly string $templates_dir;

    private readonly Parsedown $parser;

    private readonly array $source_files;

    private int $total_index_pages;

    private array $messages;

    private array $plugins = [];

    private array $hooks = [];

    public function __construct(private readonly string|false $cwd) {
        false === $cwd and exit("getcwd() failed: WTF did you do with your current working directory?");
        $this->content_dir = "$this->cwd/content";
        $this->install();
        $this->config = file_exists("$this->cwd/mfconfig.php") ? require "$this->cwd/mfconfig.php" : [];
        $this->loadPlugins($this->config['plugins'] ?? []);
        $this->hook('setGenerator', $this);
        $this->config += $this->hook('registerConfig', $this->config) ?? [];
        $this->hook('setContentDir', $this->content_dir);
        $this->parser = new Parsedown();
        $this->hook('setParser', $this->parser);
        $lang = $this->config['lang'] ?? 'en';
        $this->hook('setOutputDir', $this->output_dir = "$this->cwd/" . ($this->config['output_dir'] ?? 'output'));
        $this->messages = ($this->config['messages'][$lang] ?? []) + getMessages($lang);
    }

    private function install(): void {
        $parsedown = "lib/Parsedown.php";

        if (!file_exists($parsedown)) {
            mkdirIfNotExists($this->cwd.'/lib');
            $this->download('erusev/parsedown/refs/heads/master/Parsedown.php', "lib/Parsedown.php");
        }
        require "$this->cwd/$parsedown";

        if (!file_exists($this->content_dir)) {
            $hello_dir = "$this->content_dir/" . date('Y/m/d');
            mkdir($hello_dir, 0750, true);
            file_put_contents("$hello_dir/hello-world.md", "# Hello world!\n\nThis is your mother fucking static website!");
        }

        if (!file_exists($this->templates_dir = "$this->cwd/templates")) {
            mkdir($this->templates_dir, 0750, true);
            $base_url = 'Arcesilas/motherfucking-static-generator/refs/heads/main';
            $this->download("$base_url/templates/layout", 'templates/layout.php');
            $this->download("$base_url/templates/index", 'templates/index.php');
            $this->download("$base_url/templates/post", 'templates/post.php');
        }
        $this->hook('setTemplatesDir', $this->templates_dir);
    }

    private function download(string $url, string $to) {
        file_put_contents("$this->cwd/$to", file_get_contents("https://raw.githubusercontent.com/$url"));
    }

    public function build(): void {
        $this->hook('beforeBuild');
        rrmdir($this->output_dir);

        // Build pages
        foreach ($this->getPagesFiles("$this->content_dir/pages") as $page) {
//            $page = $this->enrichFileData($page, 'page');
            $this->buildFileWithAssets($page);
        }

        $this->copyGlobalAssets();

        // Build posts
        $posts_files = iterator_to_array($this->getSourceFiles("$this->content_dir/posts"));
        rsort($posts_files);
        $this->total_index_pages = (int) ceil(count($posts_files) / ($this->config["posts_per_page"] ?? 10));

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
                $this->buildIndexPage($list, $current_page, $this->total_index_pages);
                $count = 0;
                $list = [];
                $current_page++;
            }
        }
        $this->hook('afterBuild');
    }

    private function getPagesFiles($pages_dir): Generator {
        foreach ($this->getSourceFiles($pages_dir) as $file) {
            yield $this->enrichFileData($file, 'page');
        }
    }

    private function getPostsFiles(array $posts_files): Generator {
        $previous = null;
        foreach ($posts_files as $index => &$file) {
            if (!isset($file['content'])) {
                $posts_files[$index] = $this->enrichFileData($file, 'post');
            }

            $next = null;
            if (isset($posts_files[$index + 1])) {
                $next = $posts_files[$index + 1] = $this->enrichFileData($posts_files[$index + 1], 'post');
            }

            $file_data = $posts_files[$index] + [
                    'previous' => $previous,
                    'next' => $next
                ];
            $previous = $posts_files[$index];
            yield $file_data;
        }
    }

    private function getSourceFiles(string $content_dir): Generator {
        $iterator = new CallbackFilterIterator(getIterator($content_dir), fn ($path) => str_ends_with($path, '.md'));
        foreach ($iterator as $file) {
            $url = '/' . substr($file, strlen($content_dir) + 1, -3) . '/';
            yield [
                'url'      => $this->hook('alterUrl', $url),
                'pathname' => $file,
            ];
        }
    }

    private function buildFileWithAssets(array $file, $type = 'post'): void {
        $output_dir = $this->output_dir . $file['url'];
        $file['content'] = $this->hook('preParseContent', $file['content']);
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
                $this->hook('handleFileAsset', $asset);
                copy($asset, "$output_dir/" . basename($asset));
            }
        }
    }

    public function buildIndexPage(
        array $posts,
        int $page_num,
        int $total_pages,
        string $base_url = '',
        string $template = 'index',
        string $title = null
    ): void {
        $rendered = $this->renderTemplate($template, [
            'title' => $title,
            'posts' => $posts,
            'previous_url' => index_url($page_num - 1, $base_url),
            'next_url' => ($page_num === $total_pages) ? null : index_url($page_num + 1, $base_url),
            'total_index_pages' => $total_pages,
            'current_page' => $page_num,
            'pagination' => $this->getPagination($page_num, $total_pages),
        ]);
        $rendered = $this->renderTemplate('layout', [
            'body' => $rendered,
        ]);
        $page_slug = index_url($page_num, $base_url);
        $output_dir = "$this->output_dir/$page_slug";

        mkdirIfNotExists($output_dir, 0750, true);
        file_put_contents("$output_dir/index.html", $rendered);
    }

    private function copyGlobalAssets(): void {
        $assets_dir = "$this->content_dir/assets";
        if (is_dir($assets_dir)) {
            foreach (getIterator($assets_dir) as $asset) {
                $this->hook('handleGlobalAsset', $asset);
                $target_path = "$this->output_dir/assets/" . substr($asset, strlen($assets_dir) + 1);
                mkdirIfNotExists(dirname($target_path));
                copy($asset, $target_path);
            }
        }
    }

    private function enrichFileData(array $file, string $type): array {
        $file['raw_content'] = $content = file_get_contents($file['pathname']);
        return $this->hook('enrichFileData', array_merge($file, [
            'title' => $this->extractTitle($content),
            'content' => $content,
            'type' => $type,
        ]));
    }

    private function extractTitle(string &$markdown): string {
        $markdown = $this->stripComments($markdown);
        if (preg_match('`^# (.+)\R+`', ltrim($markdown), $matches)) {
            $markdown = ltrim(substr($markdown, strlen($matches[0])));
            return $matches[1];
        }
        return '';
    }

    public function stripComments(string $content): string {
        return preg_replace('`^\[//]: # (.*)$`m', '', $content);
    }

    private function renderTemplate(string $template, array $vars = []): string {
        $vars += ['config' => $this->config, 'messages' => $this->messages];
        $template = file_exists("$this->templates_dir/$template.php") ? $template : 'post';
        $vars += $this->hook('renderTemplate', compact('template', 'vars'));
        extract($vars);
        ob_start();
        include "$this->templates_dir/$template.php";
        return ob_get_clean();
    }

    private function getPagination(int $current, int $total): array     {
        $around = $this->config['pages_around'] ?? 2;
        $pages = array_merge([1, 2, 3, $total - 2, $total -1, $total], range($current - $around, $current + $around));
        $pages = array_filter(array_unique($pages), fn ($page) => $page > 0 && $page <= $total);
        sort($pages);
        foreach ($pages as $page) {
            if ($page > ($prev ?? 0) + 1) {
                $result[] = null;
            }
            $result[] = $prev = $page;
        }
        return $result;
    }

    private function loadPlugins(array $plugins): void {
        foreach ($plugins as $plugin) {
            $plugin_file = "$this->cwd/plugins/$plugin/$plugin.php";
            if (!file_exists($plugin_file)) { continue; }

            require_once "$this->cwd/plugins/$plugin/$plugin.php";
            $instance = $this->plugins[$plugin] = new $plugin();
            foreach ($instance->getHooks() as $hook) {
                $this->hooks[$hook][] = $instance;
            }
        }
    }

    private function hook(string $hook, mixed $value = null): mixed {
        foreach ($this->hooks[$hook] ?? [] as $plugin) {
            $value = $plugin->$hook($value);
        }
        return $value;
    }
}

function getMessages(string $lang = 'en'): array {
    $messages = [
        'en' => [
            'previous' => 'Previous',
            'next' => 'Next',
            'previous_page' => 'Previous page',
            'next_page' => 'Next page',
            'aria-pagination' => 'Page navigation',
        ],
        'fr' => [
            'previous' => 'Précédent',
            'next' => 'Suivant',
            'previous_page' => 'Page précédente',
            'next_page' => 'Page suivante',
            'aria-pagination' => 'Navigation par page',
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

function mkdirIfNotExists(string $dir): void {
    if (!file_exists($dir)) {
        mkdir($dir, 0750, true);
    }
}

function rrmdir(string $dir): void {
    if (!is_dir($dir)) { return; }
    $dirs = array_diff(scandir($dir), ['.', '..']);
    foreach ($dirs as $item) {
        $path = "$dir/$item";
        is_dir($path) ? rrmdir($path) : unlink($path);
    }
    rmdir($dir);
}

function excerpt(string $html, int $limit = 50, string $suffix = '…'): string {
    $words = preg_split('`\s+`', strip_tags($html));
    $excerpt = implode(' ', array_slice($words, 0, $limit));
    return  $excerpt . (count($words) > $limit ? $suffix : '');
}

function index_url(int $page, string $base_url = ''): ?string {
    return match ($page) {
        0 => null,
        1 => $base_url ? "/$base_url/" : "/",
        default => $base_url ? "/$base_url/page-$page/" : "/page-$page/",
    };
}

$generator = new MotherfuckingGenerator(getcwd());
$generator->build();

$motherfucking_time += hrtime(true);

echo "Built your motherfucking blog in " . $motherfucking_time/1e+9 . " seconds.\n";
