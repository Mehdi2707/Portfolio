document.addEventListener("DOMContentLoaded", function() {
    const addProjectBtn = document.querySelector(".add-project");
    const formProject = document.querySelector(".form-project");

    addProjectBtn.addEventListener("click", function() {
        formProject.classList.remove("d-none");
    });
});
