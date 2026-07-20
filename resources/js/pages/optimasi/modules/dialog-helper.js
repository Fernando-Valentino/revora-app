export function swalConfirm(title, text, confirmButtonText, onConfirm) {
    if (window.SwalConfirm) {
        window.SwalConfirm(title, text, confirmButtonText, onConfirm);
    } else if (window.Swal) {
        window.Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText
        }).then((result) => {
            if (result.isConfirmed && onConfirm) onConfirm();
        });
    }
}

export function swalConfirmPrimary(title, text, confirmButtonText, onConfirm) {
    if (window.SwalConfirmPrimary) {
        window.SwalConfirmPrimary(title, text, confirmButtonText, onConfirm);
    } else {
        swalConfirm(title, text, confirmButtonText, onConfirm);
    }
}

export function swalLoading(title, text) {
    if (window.SwalLoading) {
        window.SwalLoading(title, text);
    } else if (window.Swal) {
        window.Swal.fire({
            title,
            text,
            allowOutsideClick: false,
            didOpen: () => {
                window.Swal.showLoading();
            }
        });
    }
}

export function swalError(title, text) {
    if (window.SwalError) {
        window.SwalError(title, text);
    } else if (window.Swal) {
        window.Swal.fire({ title, text, icon: 'error' });
    }
}

export function swalAlertWarning(title, text) {
    if (window.SwalAlertWarning) {
        window.SwalAlertWarning(title, text);
    } else if (window.Swal) {
        window.Swal.fire({ title, text, icon: 'warning' });
    }
}
