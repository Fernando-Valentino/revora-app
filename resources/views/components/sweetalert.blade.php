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
            confirmButtonColor: '#005BAA',
            confirmButtonText: 'Selesai'
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
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Tutup'
        });
    };

    // 6. Alert Warning / Confirmation Helper
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
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#4B5563',
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
</script>
