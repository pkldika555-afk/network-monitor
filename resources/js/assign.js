let assignId = null;

window.openAssign = function(id, current) {
    assignId = id;
    document.getElementById('assign-input').value = current || '';
    document.getElementById('modal-assign').classList.remove('hidden');
}

window.saveAssign = async function() {
    const val  = document.getElementById('assign-input').value.trim();
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const res  = await fetch(`/services/${assignId}/assign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ assigned_to: val }),
        });
        const data = await res.json();

        const el = document.getElementById('assign-' + assignId);
        if (el) el.textContent = data.assigned_to || '— unassigned';

        document.getElementById('modal-assign').classList.add('hidden');

        // Pakai toast dari monitor.js
        if (typeof toast === 'function') {
            toast(val ? `Assigned to "${val}"` : 'Unassigned.', 'ok');
        }
    } catch(e) {
        if (typeof toast === 'function') toast('Assign failed.', 'err');
    }
}

// Tutup modal klik luar
document.getElementById('modal-assign')?.addEventListener('click', e => {
    if (e.target.id === 'modal-assign') e.target.classList.add('hidden');
});