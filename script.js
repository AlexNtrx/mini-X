// näytää dropdown valikon
function toggleMenu(button) {
    const menu = button.nextElementSibling;
    menu.classList.toggle("show");
}
// näytää muokkausLomakkeen
function showEditForm(button) {
    const post = button.closest(".post");

    const postView = post.querySelector(".post-view");
    const editForm = post.querySelector(".edit-form");

    postView.style.display = "none";
    editForm.style.display = "block";
}
//  hamburger menu
   const hamburgerButton = document.getElementById("hamburgerButton");
        const sidebar = document.querySelector(".sidebar");
  
        function setSidebarOpen(isOpen) {
            sidebar.classList.toggle("open", isOpen);
           
        
        }
        hamburgerButton.addEventListener("click", function () {
            setSidebarOpen(!sidebar.classList.contains("open"));
        });
        hamburgerButton

    
    