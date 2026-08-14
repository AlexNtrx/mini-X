
function toggleMenu(button) {
    const menu = button.nextElementSibling;
    menu.classList.toggle("show");
}
function showEditForm(button) {
    const post = button.closest(".post");

    const postView = post.querySelector(".post-view");
    const editForm = post.querySelector(".edit-form");

    postView.style.display = "none";
    editForm.style.display = "block";
}