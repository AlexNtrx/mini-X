<article class="post">
    <div class="post-content">
        <div class="post-user">
            <strong>
            <?= htmlspecialchars($content["author"], ENT_QUOTES, "UTF-8") ?>
            </strong>
            <span>
                <?= htmlspecialchars($content["created_at"], ENT_QUOTES, "UTF-8") ?>
            </span>

        </div>
        <p>
            <?= htmlspecialchars($content["content"], ENT_QUOTES, "UTF-8") ?>
        </p>

    </div>

</article>