<?php if (!empty($posts)):?>
    <?php foreach ($posts as $post):?>
        <article>
            <h1><a href="<?=$post['url']?>"><?=htmlspecialchars($post['title'])?></a></h1>
            <?=excerpt($post['content'])?>
        </article>
    <?php endforeach ?>
    <nav id="pagination" aria-label="<?=$messages['aria-pagination']?> }}">
        <ul>
        <?php if(isset($previous_url)): ?>
            <li><a href="<?= $previous_url?>">&laquo; <?= $messages['previous_page']?></a></li>
        <?php endif?>
        <?php foreach($pagination as $p): ?>
            <?php if(null === $p):?>
                <li>. . .</li>
            <?php elseif($p === $current_page):?>
                <li class="current"><?=$p?></li>
            <?php else:?>
                <li><a href="<?= index_url($p) ?>"><?=$p?></a></li>
            <?php endif?>
        <?php endforeach?>
        <?php if(isset($next_url)):?>
            <li><a href="<?=$next_url?>"><?=$messages['next_page']?> &raquo;</a></li>
        <?php endif?>
        </ul>
    </nav>
<?php else: ?>
<p>This motherfucking static blog is fucking empty.</p>
<?php endif ?>
