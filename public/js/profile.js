// profile.js

document.addEventListener('DOMContentLoaded', function () {
    const changePasswordModal = document.getElementById('changePasswordModal');
    
    if (!changePasswordModal) return;

    // Khi modal mở, focus input đầu tiên
    changePasswordModal.addEventListener('show.bs.modal', function () {
        const firstInput = changePasswordModal.querySelector('input[name="current_password"]');
        if(firstInput) firstInput.focus();
    });

    // Khi modal đóng, reset form và xóa trạng thái lỗi
    changePasswordModal.addEventListener('hidden.bs.modal', function () {
        const form = changePasswordModal.querySelector('form');
        if(form) {
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }
    });
});
