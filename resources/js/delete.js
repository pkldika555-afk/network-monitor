window.openDeleteModal = function(id, name, url) {
    document.getElementById('delete-name').textContent = name;
    document.getElementById('delete-url').textContent  = url;
    document.getElementById('form-delete').action      = `/services/${id}`;
    document.getElementById('modal-delete').classList.remove('hidden');
}

window.closeDeleteModal = function() {
    document.getElementById('modal-delete').classList.add('hidden');
}

document.getElementById('modal-delete')?.addEventListener('click', e => {
    if (e.target.id === 'modal-delete') closeDeleteModal();
});