<!-- Reusable SweetAlert2 Utility Helpers Component -->
<script>
    /**
     * Reusable SweetAlert2 Utility Helpers
     * Centralized consistency for confirmation dialogs, loading states, alerts, and toast messages.
     */
    
    // 1. Toast Notification Helper
    window.SwalToast = function(icon, title) {
        if (typeof Swal === 'undefined') {
            alert(title);
            return;
        }
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: icon, // 'success', 'error', 'warning', 'info'
            title: title
        });
    };

    // Override or alias showToast to ensure global compatibility
    window.showToast = window.SwalToast;

    // 2. Loading State Helper
    window.SwalLoading = function(title = 'Memproses...', text = 'Mohon tunggu sebentar.') {
        if (typeof Swal === 'undefined') return;
        
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };

    // 3. Close SweetAlert Helper
    window.SwalClose = function() {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    };

    // 4. Alert Success Helper
    window.SwalSuccess = function(title = 'Sukses!', text = 'Aksi berhasil dilakukan.') {
        if (typeof Swal === 'undefined') {
            alert(text);
            return;
        }
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'success',
            confirmButtonText: 'Selesai',
            customClass: {
                confirmButton: 'btn btn-primary px-4 py-2 rounded-3 fw-bold text-sm'
            },
            buttonsStyling: false
        });
    };

    // 5. Alert Error Helper
    window.SwalError = function(title = 'Gagal!', text = 'Terjadi kesalahan sistem.') {
        if (typeof Swal === 'undefined') {
            alert(text);
            return;
        }
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'error',
            confirmButtonText: 'Tutup',
            customClass: {
                confirmButton: 'btn btn-danger px-4 py-2 rounded-3 fw-bold text-sm'
            },
            buttonsStyling: false
        });
    };

    // 6. Alert Warning / Destructive Confirmation Helper (Red Confirm Button)
    window.SwalConfirm = function(title, text, confirmButtonText, callback, cancelButtonText = 'Batal') {
        if (typeof Swal === 'undefined') {
            if (confirm(text || title)) {
                callback();
            }
            return;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            cancelButtonText: cancelButtonText,
            customClass: {
                confirmButton: 'btn btn-danger px-4 py-2 me-2 rounded-3 fw-bold text-sm',
                cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-bold text-sm'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    };

    // 7. Non-Destructive Confirmation Helper (Primary Blue/Dark Confirm Button)
    window.SwalConfirmPrimary = function(title, text, confirmButtonText, callback, cancelButtonText = 'Batal') {
        if (typeof Swal === 'undefined') {
            if (confirm(text || title)) {
                callback();
            }
            return;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            cancelButtonText: cancelButtonText,
            customClass: {
                confirmButton: 'btn btn-primary px-4 py-2 me-2 rounded-3 fw-bold text-sm',
                cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 fw-bold text-sm'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    };

    // 8. Single Button Warning Dialog Helper
    window.SwalAlertWarning = function(title, text, confirmButtonText = 'Mengerti') {
        if (typeof Swal === 'undefined') {
            alert(text || title);
            return;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            confirmButtonText: confirmButtonText,
            customClass: {
                confirmButton: 'btn btn-primary px-4 py-2 rounded-3 fw-bold text-sm'
            },
            buttonsStyling: false
        });
    };
</script>
