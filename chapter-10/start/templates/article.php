<?php
$title = htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8');
$body = htmlspecialchars($article['body'], ENT_QUOTES, 'UTF-8');
?>
<article>
    <h1><?= $title ?></h1>
    <p><?= $body ?></p>
</article>
