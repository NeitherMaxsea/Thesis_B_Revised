import Swal from 'sweetalert2';
const appToast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timerProgressBar: true,
    customClass: {
        popup: 'app-swal-toast',
        title: 'app-swal-toast__title',
        htmlContainer: 'app-swal-toast__message',
    },
});

const showSweetToast = ({
    title,
    text = '',
    icon = 'info',
    loading = false,
    timer = 3400,
} = {}) => {
    const toastTitle = title || (loading ? 'Loading' : 'Notice');

    if (loading) {
        return appToast.fire({
            title: toastTitle,
            text,
            timer: undefined,
            timerProgressBar: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
    }

    return appToast.fire({
        title: toastTitle,
        text,
        icon,
        timer,
    });
};

const showSweetModal = ({
    title = 'Notice',
    text = '',
    icon = 'info',
    confirmButtonText = 'I understand',
} = {}) => Swal.fire({
    title,
    text,
    icon,
    confirmButtonText,
    buttonsStyling: false,
    customClass: {
        popup: 'app-swal-modal',
        title: 'app-swal-modal__title',
        htmlContainer: 'app-swal-modal__message',
        confirmButton: 'app-swal-modal__confirm',
    },
});


export { showSweetToast, showSweetModal };
