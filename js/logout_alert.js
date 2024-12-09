function confirmLogout(event) {
    event.preventDefault(); // Prevent the default behavior (link redirect)

    Swal.fire({
        title: 'Are you sure you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
                "timeOut": "3000"
            };
            toastr.success('You have logged out successfully.');

            setTimeout(() => {
                window.location.href = 'logout.php'; // Redirect after Toastr message
            }, 1000); // Delay the redirection to show the Toastr message
        }
    });
}
