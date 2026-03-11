window.openEditUser = function(btn) {
    const d = btn.dataset;
    document.getElementById("form-edit-user").action = `/users/${d.id}`;
    document.getElementById("eu-name").value = d.name;
    document.getElementById("eu-nrp").value = d.nrp || "";
    document.getElementById("eu-email").value = d.email;
    document.getElementById("eu-role").value = d.role;
    document.getElementById("eu-password").value = "";
    document.getElementById("modal-edit-user").classList.remove("hidden");
}

window.openDeleteUser = function(btn) {
    const d = btn.dataset;
    document.getElementById('du-name').textContent     = d.name;
    document.getElementById('form-delete-user').action = `/users/${d.id}`;
    document.getElementById('modal-delete-user').classList.remove('hidden');
}

window.closeDeleteUser = function() {
    document.getElementById('modal-delete-user').classList.add('hidden');
}

document.getElementById('modal-delete-user')?.addEventListener('click', e => {
    if (e.target.id === 'modal-delete-user') closeDeleteUser();
});

["modal-add-user", "modal-edit-user", "modal-delete-user"].forEach((id) => {
    document.getElementById(id).addEventListener("click", (e) => {
        if (e.target.id === id) e.target.classList.add("hidden");
    });
});
