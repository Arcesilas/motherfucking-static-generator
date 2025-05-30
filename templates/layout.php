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
        <?=$body?>
    </main>
    <footer>
        <p>This motherfucking website is powered by
            <a href="https://github.com/Arcesilas/motherfucking-static-generator">motherfucking static generator</a>
        </p>
    </footer>
</body>
</html>
