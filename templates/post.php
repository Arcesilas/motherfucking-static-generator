<article>
    <header>
    <h1><?= $post['title']?></h1>
    </header>
    <?=$post['content']?>
</article>
<?php if(isset($post['previous'])):?><a href="<?=$post['previous']['url']?>">&laquo; <?=$messages['previous']?></a><?php endif?>
<?php if(isset($post['next'])):?><a href="<?=$post['next']['url']?>"><?=$messages['next']?> &raquo;</a><?php endif?>
