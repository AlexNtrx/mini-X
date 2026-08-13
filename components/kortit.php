<article class="post">
    <div class="post-content">
        <div class="post-user">
            <strong>
                <?= htmlspecialchars($content['author']) ?>
            </strong>
            <span>
                <?= htmlspecialchars($content['created_at']) ?>
            </span>

        </div>
        <p>
            <?= htmlspecialchars($content['content']) ?>
        </p>

    </div>

</article>