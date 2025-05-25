// pimpinan/controller/pimpinanController.js

// Fungsi untuk konfirmasi logout dengan SweetAlert
function confirmLogout() {
  Swal.fire({
    title: "Konfirmasi Logout",
    text: "Apakah Anda yakin ingin keluar dari sistem?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, Logout!",
    cancelButtonText: "Batal",
    reverseButtons: true,
    showLoaderOnConfirm: true,
    preConfirm: () => {
      return logout();
    },
    allowOutsideClick: () => !Swal.isLoading(),
  });
}

// Fungsi untuk melakukan logout
function logout() {
  return fetch(window.location.href, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: "action=logout",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        // Tampilkan success message
        Swal.fire({
          title: "Berhasil!",
          text: data.message || "Logout berhasil!",
          icon: "success",
          timer: 2000,
          showConfirmButton: false,
          allowOutsideClick: false,
          allowEscapeKey: false,
        }).then(() => {
          // Redirect setelah SweetAlert ditutup
          if (data.data && data.data.redirect) {
            window.location.href = data.data.redirect;
          } else {
            window.location.href = "../../index.php";
          }
        });
      } else {
        // Tampilkan error message
        Swal.fire({
          title: "Gagal!",
          text: data.message || "Logout gagal!",
          icon: data.type || "error",
          confirmButtonText: "OK",
        });
      }
    })
    .catch((error) => {
      console.error("Error:", error);

      // Tampilkan error dengan opsi untuk tetap redirect
      Swal.fire({
        title: "Terjadi Kesalahan!",
        text: "Koneksi bermasalah, tetapi Anda akan tetap di-logout untuk keamanan.",
        icon: "warning",
        confirmButtonText: "OK",
        allowOutsideClick: false,
      }).then(() => {
        // Fallback: redirect langsung jika AJAX gagal
        window.location.href = "../../index.php";
      });
    });
}

// Fungsi helper untuk menampilkan notifikasi umum dengan SweetAlert
function showAlert(message, type = "info", title = null) {
  const config = {
    text: message,
    icon: type,
    confirmButtonText: "OK",
  };

  // Set title berdasarkan type jika tidak ada title khusus
  if (title) {
    config.title = title;
  } else {
    switch (type) {
      case "success":
        config.title = "Berhasil!";
        break;
      case "error":
        config.title = "Gagal!";
        break;
      case "warning":
        config.title = "Peringatan!";
        break;
      case "info":
        config.title = "Informasi";
        break;
      default:
        config.title = "Pemberitahuan";
    }
  }

  Swal.fire(config);
}

// Fungsi untuk konfirmasi aksi umum
function confirmAction(
  message,
  confirmText = "Ya",
  cancelText = "Batal",
  type = "question"
) {
  return Swal.fire({
    title: "Konfirmasi",
    text: message,
    icon: type,
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    reverseButtons: true,
  });
}

// Fungsi untuk menampilkan loading
function showLoading(message = "Memproses...") {
  Swal.fire({
    title: message,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });
}

// Fungsi untuk menutup loading
function hideLoading() {
  Swal.close();
}

// Fungsi untuk toast notification (notifikasi kecil di pojok)
function showToast(message, type = "success", position = "top-end") {
  const Toast = Swal.mixin({
    toast: true,
    position: position,
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
  });

  Toast.fire({
    icon: type,
    title: message,
  });
}

// Event listener untuk tombol logout
document.addEventListener("DOMContentLoaded", function () {
  // Cari semua tombol/link logout dan attach event listener
  const logoutButtons = document.querySelectorAll(
    '[data-action="logout"], .logout-btn, #logout-btn'
  );

  logoutButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      confirmLogout();
    });
  });
});

// Fungsi untuk handle response dari server dengan SweetAlert
function handleServerResponse(
  response,
  successCallback = null,
  errorCallback = null
) {
  if (response.success) {
    const alertType = response.type || "success";

    if (successCallback && typeof successCallback === "function") {
      successCallback(response);
    } else {
      showAlert(response.message, alertType);
    }

    // Handle redirect jika ada
    if (response.data && response.data.redirect) {
      setTimeout(() => {
        window.location.href = response.data.redirect;
      }, 2000);
    }
  } else {
    const alertType = response.type || "error";

    if (errorCallback && typeof errorCallback === "function") {
      errorCallback(response);
    } else {
      showAlert(response.message, alertType);
    }
  }
}

// Export functions untuk dapat digunakan di file lain
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    confirmLogout,
    logout,
    showAlert,
    confirmAction,
    showLoading,
    hideLoading,
    showToast,
    handleServerResponse,
  };
}
