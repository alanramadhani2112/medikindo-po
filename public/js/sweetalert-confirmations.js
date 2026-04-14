/**
 * SweetAlert2 Confirmation Handlers
 * Medikindo PO System
 * 
 * Handles confirmation dialogs for CREATE, UPDATE, DELETE, and TOGGLE actions
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // DELETE CONFIRMATION
    // ============================================
    document.querySelectorAll('.delete-confirm').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const itemName = this.getAttribute('data-name') || 'data ini';
            
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Apakah Anda yakin ingin menghapus <strong>${itemName}</strong>?<br><span class="text-danger">Tindakan ini tidak dapat dibatalkan!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="ki-solid ki-trash fs-3 me-2"></i>Ya, Hapus!',
                cancelButtonText: '<i class="ki-solid ki-cross fs-3 me-2"></i>Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    form.submit();
                }
            });
        });
    });

    // ============================================
    // CREATE CONFIRMATION
    // ============================================
    document.querySelectorAll('.create-confirm').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const itemType = this.getAttribute('data-type') || 'data';
            
            Swal.fire({
                title: 'Konfirmasi Tambah Data',
                html: `Apakah Anda yakin ingin menambahkan ${itemType} ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#009ef7',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="ki-solid ki-check fs-3 me-2"></i>Ya, Simpan!',
                cancelButtonText: '<i class="ki-solid ki-cross fs-3 me-2"></i>Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    form.submit();
                }
            });
        });
    });

    // ============================================
    // UPDATE CONFIRMATION
    // ============================================
    document.querySelectorAll('.update-confirm').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const itemName = this.getAttribute('data-name') || 'data ini';
            
            Swal.fire({
                title: 'Konfirmasi Perubahan',
                html: `Apakah Anda yakin ingin menyimpan perubahan pada <strong>${itemName}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#009ef7',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="ki-solid ki-check fs-3 me-2"></i>Ya, Simpan!',
                cancelButtonText: '<i class="ki-solid ki-cross fs-3 me-2"></i>Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    form.submit();
                }
            });
        });
    });

    // ============================================
    // TOGGLE STATUS CONFIRMATION (Aktif/Nonaktif)
    // ============================================
    document.querySelectorAll('.toggle-status-confirm').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const itemName = this.getAttribute('data-name') || 'item ini';
            const currentStatus = this.getAttribute('data-status'); // 'active' or 'inactive'
            const action = currentStatus === 'active' ? 'menonaktifkan' : 'mengaktifkan';
            const actionTitle = currentStatus === 'active' ? 'Nonaktifkan' : 'Aktifkan';
            const icon = currentStatus === 'active' ? 'warning' : 'question';
            const confirmColor = currentStatus === 'active' ? '#ffc107' : '#50cd89';
            
            Swal.fire({
                title: `Konfirmasi ${actionTitle}`,
                html: `Apakah Anda yakin ingin ${action} <strong>${itemName}</strong>?`,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: `<i class="ki-solid ki-${currentStatus === 'active' ? 'cross-square' : 'check-circle'} fs-3 me-2"></i>Ya, ${actionTitle}!`,
                cancelButtonText: '<i class="ki-solid ki-cross fs-3 me-2"></i>Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: `btn btn-${currentStatus === 'active' ? 'warning' : 'success'}`,
                    cancelButton: 'btn btn-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    form.submit();
                }
            });
        });
    });

    // ============================================
    // SUCCESS MESSAGE (from session)
    // ============================================
    const successMessage = document.querySelector('[data-success-message]');
    if (successMessage) {
        const message = successMessage.getAttribute('data-success-message');
        Swal.fire({
            title: 'Berhasil!',
            text: message,
            icon: 'success',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-success'
            },
            buttonsStyling: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // ============================================
    // ERROR MESSAGE (from session)
    // ============================================
    const errorMessage = document.querySelector('[data-error-message]');
    if (errorMessage) {
        const message = errorMessage.getAttribute('data-error-message');
        Swal.fire({
            title: 'Gagal!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
    }

    // ============================================
    // SUBMIT CONFIRMATION (Generic)
    // ============================================
    document.querySelectorAll('.submit-confirm').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const title = this.getAttribute('data-title') || 'Konfirmasi';
            const message = this.getAttribute('data-message') || 'Apakah Anda yakin ingin melanjutkan?';
            const confirmText = this.getAttribute('data-confirm-text') || 'Ya, Lanjutkan!';
            
            Swal.fire({
                title: title,
                html: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#009ef7',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    form.submit();
                }
            });
        });
    });

});
