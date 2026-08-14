<article
    class="post"
    id="post-<?= $content["id"] ?>">

    <div class="post-content">

        <!-- Normal post -->
        <div class="post-view">

            <div class="post-top">

                <div class="post-user">

                    <strong class="post-author">
                        <?= htmlspecialchars($content["author"], ENT_QUOTES, "UTF-8") ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($content["created_at"], ENT_QUOTES, "UTF-8") ?>
                    </span>

                </div>

                <div class="post-menu">

                    <button
                        type="button"
                        class="menu-button"
                        onclick="toggleMenu(this)">
                        ...
                    </button>

                    <div class="menu-dropdown">

                        <button
                            type="button"
                            onclick="showEditForm(this)">
                            Muokkaa
                        </button>

                        <button
                            type="submit"
                            name="delete_post">
                            Poistaa
                        </button>

                    </div>

                </div>

            </div>

            <p class="post-text">
                <?= htmlspecialchars($content["content"], ENT_QUOTES, "UTF-8") ?>
            </p>

        </div>


        <!-- Edit form -->
        <form
            class="edit-form"
            method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= $content["id"] ?>">

            <input
                class="post-author"
                type="text"
                name="author"
                value="<?= htmlspecialchars($content["author"], ENT_QUOTES, "UTF-8") ?>"
                required>

            <textarea
                class="post-text"
                name="content"
                required><?= htmlspecialchars($content["content"], ENT_QUOTES, "UTF-8") ?></textarea>

            <button
                type="submit"
                name="update_post">
                Tallenna
            </button>

        </form>

    </div>

</article>