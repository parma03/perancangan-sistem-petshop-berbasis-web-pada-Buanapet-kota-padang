// Function Data Transaksi Cart
$(document).ready(function () {
  console.log("Document ready - transaksi cart script loaded");

  // Load transaksi cart data on page load
  loadTransaksiCartData();

  // Handle view transaksi cart detail button
  $(document).on("click", ".view-transaksi-cart-btn", function () {
    const transaksiId = $(this).data("id");
    showTransaksiCartDetail(transaksiId);
  });

  // Handle confirm transaksi cart button
  $(document).on("click", ".confirm-transaksi-cart-btn", function () {
    const transaksiId = $(this).data("id");
    const barangName = $(this).data("barang");
    const customerName = $(this).data("customer");
    const totalHarga = $(this).data("total");
    confirmTransaksiCart(transaksiId, barangName, customerName, totalHarga);
  });

  // Load Transaksi Cart Data
  function loadTransaksiCartData() {
    const container = $(".transaksi-data-container");

    console.log("Loading transaksi cart data");

    // Show loading indicator
    container.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data histori transaksi barang...</p>
      </div>
    `);

    // Fetch transaksi cart data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/historiCartController.php",
      data: {
        request: "get_transaksi_cart",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get transaksi cart response:", response);

        if (response.status === "success") {
          // Update container with transaksi data
          container.html(response.html);

          // Initialize DataTable if table exists
          if ($("#transaksiCartTable").length > 0) {
            $("#transaksiCartTable").DataTable({
              responsive: true,
              language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
              },
              order: [[6, "desc"]], // Sort by tanggal transaksi desc
              columnDefs: [
                { targets: [-1], orderable: false }, // Disable sorting on action column
                { targets: [0], orderable: false }, // Disable sorting on # column
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
          "AJAX error when loading histori transaksi cart:",
          xhr,
          status,
          error
        );
        console.log("Response text:", xhr.responseText);

        // Show error message
        container.html(`
          <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data histori transaksi barang.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  // Show Transaksi Cart Detail
  function showTransaksiCartDetail(transaksiId) {
    const modal = $("#transaksiDetailModal");
    const content = $("#transaksiDetailContent");

    // Show loading
    content.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat detail histori transaksi barang...</p>
      </div>
    `);

    modal.modal("show");

    // Load transaksi cart detail
    $.ajax({
      type: "POST",
      url: "controller/historiCartController.php",
      data: {
        request: "get_transaksi_cart_detail",
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
  window.historiCartController = {
    loadData: loadTransaksiCartData,
    showDetail: showTransaksiCartDetail,
    confirm: confirmTransaksiCart,
  };
});
