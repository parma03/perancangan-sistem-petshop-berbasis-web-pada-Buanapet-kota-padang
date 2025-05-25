// Function Data Transaksi Services
$(document).ready(function () {
  console.log("Document ready - transaksi services script loaded");

  // Load transaksi services data on page load
  loadTransaksiServicesData();

  // Handle view transaksi detail button
  $(document).on("click", ".view-transaksi-btn", function () {
    const transaksiId = $(this).data("id");
    showTransaksiDetail(transaksiId);
  });

  // Handle confirm transaksi button
  $(document).on("click", ".confirm-transaksi-btn", function () {
    const transaksiId = $(this).data("id");
    const serviceName = $(this).data("service");
    const customerName = $(this).data("customer");
    confirmTransaksi(transaksiId, serviceName, customerName);
  });

  // Load Transaksi Services Data
  function loadTransaksiServicesData() {
    const container = $(".transaksi-data-container");

    console.log("Loading transaksi services data");

    // Show loading indicator
    container.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data transaksi...</p>
      </div>
    `);

    // Fetch transaksi services data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/transaksiServicesController.php",
      data: {
        request: "get_transaksi_services",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get transaksi services response:", response);

        if (response.status === "success") {
          // Update container with transaksi data
          container.html(response.html);

          // Initialize DataTable if table exists
          if ($("#transaksiServicesTable").length > 0) {
            $("#transaksiServicesTable").DataTable({
              responsive: true,
              language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
              },
              order: [[5, "desc"]], // Sort by tanggal transaksi desc
              columnDefs: [
                { targets: [-1], orderable: false }, // Disable sorting on action column
              ],
              pageLength: 10,
              lengthMenu: [5, 10, 25, 50],
              dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            });
          }
        } else {
          // Show error message
          container.html(`
            <div class="alert alert-danger" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i>${
                response.message || "Terjadi kesalahan saat memuat data."
              }
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error(
          "AJAX error when loading transaksi services:",
          xhr,
          status,
          error
        );
        console.log("Response text:", xhr.responseText);

        // Show error message
        container.html(`
          <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data transaksi.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  // Show Transaksi Detail
  function showTransaksiDetail(transaksiId) {
    const modal = $("#transaksiDetailModal");
    const content = $("#transaksiDetailContent");

    // Show loading
    content.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat detail transaksi...</p>
      </div>
    `);

    modal.modal("show");

    // Load transaksi detail
    $.ajax({
      type: "POST",
      url: "controller/transaksiServicesController.php",
      data: {
        request: "get_transaksi_detail",
        transaksi_id: transaksiId,
      },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          content.html(response.html);
        } else {
          content.html(`
            <div class="alert alert-danger" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i>${response.message}
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        content.html(`
          <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat detail.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  // Confirm Transaksi
  function confirmTransaksi(transaksiId, serviceName, customerName) {
    Swal.fire({
      title: "Konfirmasi Transaksi",
      html: `
        <div class="text-center">
          <div class="mb-3">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
          </div>
          <p>Apakah Anda yakin ingin mengkonfirmasi transaksi ini?</p>
          <div class="bg-light p-3 rounded mb-3">
            <strong>Service:</strong> ${serviceName}<br>
            <strong>Pelanggan:</strong> ${customerName}
          </div>
          <div class="alert alert-info border-0 rounded-3">
            <i class="fas fa-info-circle me-2"></i>
            <small>Status akan berubah dari <strong>Pending</strong> menjadi <strong>Completed</strong></small>
          </div>
        </div>
      `,
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      confirmButtonText: '<i class="fas fa-check me-1"></i>Ya, Konfirmasi!',
      cancelButtonText: '<i class="fas fa-times me-1"></i>Batal',
      customClass: {
        popup: "rounded-4 shadow-lg",
        confirmButton: "btn-lg",
        cancelButton: "btn-lg",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading
        showLoading("Mengkonfirmasi transaksi...");

        $.ajax({
          type: "POST",
          url: "controller/transaksiServicesController.php",
          data: {
            request: "confirm_transaksi",
            transaksi_id: transaksiId,
          },
          dataType: "json",
          success: function (response) {
            hideLoading();

            if (response.status === "success") {
              Swal.fire({
                title: "Berhasil!",
                text: response.message,
                icon: "success",
                confirmButtonText: "OK",
                customClass: {
                  popup: "rounded-4 shadow-lg",
                },
              }).then(() => {
                // Reload data to reflect changes
                loadTransaksiServicesData();
              });
            } else {
              showAlert(response.message, "error");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", xhr, status, error);
            hideLoading();
            showAlert(
              "Terjadi kesalahan saat mengkonfirmasi transaksi!",
              "error"
            );
          },
        });
      }
    });
  }

  // FUNGSI HELPER UNTUK ALERT DAN LOADING
  function showAlert(message, type = "info") {
    let icon = "info";
    let title = "Informasi";

    switch (type) {
      case "success":
        icon = "success";
        title = "Berhasil";
        break;
      case "error":
        icon = "error";
        title = "Error";
        break;
      case "warning":
        icon = "warning";
        title = "Peringatan";
        break;
    }

    Swal.fire({
      title: title,
      text: message,
      icon: icon,
      confirmButtonText: "OK",
      customClass: {
        popup: "rounded-4 shadow-lg",
      },
    });
  }

  function showLoading(message = "Loading...") {
    Swal.fire({
      title: "Mohon Tunggu",
      text: message,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      customClass: {
        popup: "rounded-4 shadow-lg",
      },
      didOpen: () => {
        Swal.showLoading();
      },
    });
  }

  function hideLoading() {
    Swal.close();
  }

  // Export functions for external use
  window.transaksiServicesController = {
    loadData: loadTransaksiServicesData,
    showDetail: showTransaksiDetail,
    confirm: confirmTransaksi,
  };
});
