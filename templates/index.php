<?php if (!empty($posts)):?>
    <?php foreach ($posts as $post):?>
        <article>
            <h1><a href="<?=$post['url']?>"><?=htmlspecialchars($post['title'])?></a></h1>
            <?=excerpt($post['content'])?>
        </article>
    <?php endforeach ?>
    <nav>
        <?php if(isset($previous_url)):?><a style="float: left;" href="<?=$previous_url?>">&laquo; <?=$messages['previous_page']?></a><?php endif?>
        <?php if(isset($next_url)):?><a style="float: right;" href="<?=$next_url?>"><?=$messages['next_page']?> &raquo;</a><?php endif?>
    </nav>
<?php else: ?>
<p>This motherfucking static blog is fucking empty.</p>
<?php endif ?>
