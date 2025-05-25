$(document).ready(function () {
  console.log("Document ready - barang script loaded");

  console.log("Barang tab shown, loading data");
  loadBarangData();

  // Handle add barang button
  $(document).on("click", ".add-barang-btn", function () {
    showBarangForm();
  });

  // Handle edit barang button
  $(document).on("click", ".edit-barang-btn", function () {
    const barangId = $(this).data("id");
    showBarangForm(barangId);
  });

  // Handle view barang detail button
  $(document).on("click", ".view-barang-btn", function () {
    const barangId = $(this).data("id");
    showBarangDetail(barangId);
  });

  // Handle delete barang button
  $(document).on("click", ".delete-barang-btn", function () {
    const barangId = $(this).data("id");
    const barangName = $(this).data("name");
    deleteBarang(barangId, barangName);
  });

  // Handle barang form submission
  $("#barangForm").on("submit", function (e) {
    e.preventDefault();
    saveBarang();
  });

  // FIX: Handle photo upload area click - make sure this works
  $(document).on("click", ".upload-placeholder", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Upload placeholder clicked");
    $("#foto_barang").trigger("click");
  });

  // FIX: Also handle click on the entire photo-upload-area
  $(document).on("click", ".photo-upload-area", function (e) {
    // Only trigger if clicking on the area itself, not on buttons inside it
    if (
      e.target === this ||
      $(e.target).hasClass("upload-placeholder") ||
      $(e.target).closest(".upload-placeholder").length > 0
    ) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Photo upload area clicked");
      $("#foto_barang").trigger("click");
    }
  });

  // Handle photo image preview
  $("#foto_barang").on("change", function (e) {
    console.log("File input changed");
    const file = this.files[0];
    const preview = $("#photoPreview");
    const removeBtn = $("#removePhotoBtn");
    const uploadPlaceholder = $("#uploadPlaceholder");

    if (file) {
      console.log("File selected:", file.name, file.size, file.type);

      // Validate file type
      const validTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/gif",
        "image/webp",
      ];
      if (!validTypes.includes(file.type)) {
        showAlert(
          "Format file tidak valid! Gunakan JPG, PNG, GIF, atau WEBP.",
          "warning"
        );
        this.value = "";
        return;
      }

      // Validate file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        showAlert("Ukuran file terlalu besar! Maksimal 5MB.", "warning");
        this.value = "";
        return;
      }

      // Show preview
      const reader = new FileReader();
      reader.onload = function (e) {
        console.log("File loaded for preview");
        uploadPlaceholder.hide();
        preview
          .html(
            `<div class="text-center">
              <div class="position-relative d-inline-block">
                <img src="${
                  e.target.result
                }" class="img-thumbnail rounded-3 shadow-sm" style="width: 200px; height: 200px; object-fit: cover;">
                <div class="position-absolute top-0 start-100 translate-middle">
                  <span class="badge bg-success rounded-pill">
                    <i class="fas fa-check"></i>
                  </span>
                </div>
              </div>
              <p class="mt-3 text-success mb-0">
                <i class="fas fa-check-circle me-1"></i>Foto siap diupload
              </p>
              <small class="text-muted">${(file.size / 1024 / 1024).toFixed(
                2
              )} MB</small>
            </div>`
          )
          .show();
        removeBtn.show();
      };
      reader.readAsDataURL(file);
    } else {
      console.log("No file selected");
      preview.hide();
      removeBtn.hide();
      uploadPlaceholder.show();
    }
  });

  // Handle remove photo image
  $("#removePhotoBtn").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Remove photo button clicked");
    $("#foto_barang").val("");
    $("#photoPreview").hide();
    $("#uploadPlaceholder").show();
    $(this).hide();
    $("#removeExistingPhoto").val("1");
    showAlert("Foto telah dihapus dari preview.", "info");
  });

  // Handle remove current photo in edit mode
  $(document).on("click", "#removeCurrentPhoto", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $("#currentPhotoDisplay").hide();
    $("#removeExistingPhoto").val("1");
    showAlert("Foto barang akan dihapus saat menyimpan.", "info");
  });

  // Drag & Drop functionality
  $(".photo-upload-area").on("dragover", function (e) {
    e.preventDefault();
    $(this).addClass("border-primary bg-light");
  });

  $(".photo-upload-area").on("dragleave", function (e) {
    e.preventDefault();
    $(this).removeClass("border-primary bg-light");
  });

  $(".photo-upload-area").on("drop", function (e) {
    e.preventDefault();
    $(this).removeClass("border-primary bg-light");

    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 0) {
      console.log("File dropped:", files[0].name);
      // Set the files to the input element
      const input = document.getElementById("foto_barang");
      input.files = files;
      // Trigger change event
      $(input).trigger("change");
    }
  });

  // Format currency input
  $("#harga_barang").on("input", function () {
    let value = $(this).val().replace(/\D/g, "");
    $(this).val(value);
  });

  // FUNGSI UNTUK MENAMPILKAN MODAL FORM BARANG (TAMBAH/EDIT)
  function showBarangForm(barangId = null) {
    const modal = $("#barangFormModal");
    const form = $("#barangForm");
    const modalTitle = $("#barangFormModalLabel");
    const submitBtn = form.find('button[type="submit"]');

    // Reset form
    form[0].reset();
    $("#photoPreview").hide();
    $("#removePhotoBtn").hide();
    $("#uploadPlaceholder").show();
    $("#currentPhotoDisplay").hide();
    $("#removeExistingPhoto").val("0");

    if (barangId) {
      // Mode Edit
      modalTitle.html('<i class="fas fa-edit me-2"></i>Edit Barang');
      submitBtn.html('<i class="fas fa-save me-1"></i>Update Barang');

      // Show loading
      showLoading("Memuat data barang...");

      // Load barang data for editing
      $.ajax({
        type: "POST",
        url: "controller/barangController.php",
        data: {
          request: "get_barang_by_id",
          barang_id: barangId,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          if (response.status === "success") {
            const barang = response.data;

            // Fill form with barang data
            $("#barang_id").val(barang.id_barang);
            $("#nama_barang").val(barang.nama_barang);
            $("#tipe_barang").val(barang.tipe_barang);
            $("#harga_barang").val(barang.harga_barang);
            $("#deskripsi_barang").val(barang.deskripsi_barang);

            // Show current photo if exists
            if (barang.foto_barang) {
              $("#currentPhotoDisplay")
                .html(
                  `<div class="mb-4">
                    <label class="form-label fw-semibold">
                      <i class="fas fa-image text-primary me-1"></i>Foto Barang Saat Ini:
                    </label>
                    <div class="current-photo-container p-4 border rounded-4 bg-gradient" style="background: linear-gradient(145deg, #f8f9fa, #ffffff); border: 2px dashed #dee2e6 !important;">
                      <div class="row align-items-center">
                        <div class="col-auto">
                          <div class="position-relative">
                            <img src="../../../assets/uploads/barang/${barang.foto_barang}" 
                                 class="img-thumbnail rounded-3 shadow-sm" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                            <div class="position-absolute top-0 start-100 translate-middle">
                              <span class="badge bg-success rounded-pill">
                                <i class="fas fa-check"></i>
                              </span>
                            </div>
                          </div>
                        </div>
                        <div class="col">
                          <h6 class="mb-2 text-dark">${barang.nama_barang}</h6>
                          <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-light text-dark me-2">
                              <i class="fas fa-image me-1"></i>Foto aktif
                            </span>
                            <small class="text-muted">Diupload sebelumnya</small>
                          </div>
                        </div>
                        <div class="col-auto">
                          <button type="button" class="btn btn-outline-danger btn-sm" id="removeCurrentPhoto">
                            <i class="fas fa-trash me-1"></i>Hapus
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>`
                )
                .show();
            }

            // Show modal
            modal.modal("show");
          } else {
            showAlert(response.message, "error");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", xhr, status, error);
          hideLoading();
          showAlert("Terjadi kesalahan saat memuat data!", "error");
        },
      });
    } else {
      // Mode Add
      modalTitle.html('<i class="fas fa-plus me-2"></i>Tambah Barang Baru');
      submitBtn.html('<i class="fas fa-save me-1"></i>Simpan Barang');
      $("#barang_id").val("");
      modal.modal("show");
    }
  }

  // Load Barang Data
  function loadBarangData() {
    const container = $(".barang-data-container");

    console.log("Loading barang data");

    // Show loading indicator
    container.html(`
      <div class="text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="text-muted">Memuat data barang...</h5>
        <p class="text-muted mb-0">Silakan tunggu sebentar</p>
      </div>
    `);

    // Fetch barang data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/barangController.php",
      data: {
        request: "get_barang",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get barang response:", response);

        if (response.status === "success") {
          // Update container with barang data
          container.html(response.html);

          // Initialize DataTable if table exists
          if ($("#barangTable").length > 0) {
            $("#barangTable").DataTable({
              responsive: true,
              language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
              },
              order: [[1, "asc"]], // Sort by nama_barang
              columnDefs: [
                { targets: [-1], orderable: false }, // Disable sorting on action column
                { targets: [0], width: "5%" },
                { targets: [1], width: "30%" },
                { targets: [2], width: "20%" },
                { targets: [3], width: "15%" },
                { targets: [4], width: "10%" },
                { targets: [5], width: "20%" },
              ],
              pageLength: 10,
              dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
              drawCallback: function () {
                // Add custom styling after table draw
                $(
                  ".dataTables_wrapper .dataTables_paginate .paginate_button.current"
                ).addClass("btn-primary");
              },
            });
          }
        } else {
          // Show error message
          container.html(`
            <div class="alert alert-danger border-0 shadow-sm rounded-4" role="alert">
              <div class="d-flex align-items-center">
                <div class="alert-icon me-3">
                  <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="alert-heading mb-2">Terjadi Kesalahan</h6>
                  <p class="mb-0">${
                    response.message || "Tidak dapat memuat data barang."
                  }</p>
                  <small class="text-muted">Silakan refresh halaman atau hubungi administrator</small>
                </div>
              </div>
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error when loading barang:", xhr, status, error);
        console.log("Response text:", xhr.responseText);

        // Show error message
        container.html(`
          <div class="alert alert-danger border-0 shadow-sm rounded-4" role="alert">
            <div class="d-flex align-items-center">
              <div class="alert-icon me-3">
                <i class="fas fa-wifi fa-2x text-danger"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="alert-heading mb-2">Koneksi Bermasalah</h6>
                <p class="mb-2">Tidak dapat memuat data barang dari server.</p>
                <small class="text-muted">Error: ${error}</small>
                <div class="mt-2">
                  <button class="btn btn-sm btn-outline-danger" onclick="loadBarangData()">
                    <i class="fas fa-redo me-1"></i>Coba Lagi
                  </button>
                </div>
              </div>
            </div>
          </div>
        `);
      },
    });
  }

  // Show Barang Detail
  function showBarangDetail(barangId) {
    const modal = $("#barangDetailModal");
    const content = $("#barangDetailContent");

    // Show loading
    content.html(`
      <div class="text-center py-4">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h6 class="text-muted">Memuat detail barang...</h6>
      </div>
    `);

    modal.modal("show");

    // Load barang detail
    $.ajax({
      type: "POST",
      url: "controller/barangController.php",
      data: {
        request: "get_barang_detail",
        barang_id: barangId,
      },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          content.html(response.html);
        } else {
          content.html(`
            <div class="alert alert-danger rounded-4" role="alert">
              <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
                <div>
                  <strong>Error!</strong> ${response.message}
                </div>
              </div>
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        content.html(`
          <div class="alert alert-danger rounded-4" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
              <div>
                <strong>Koneksi Error!</strong><br>
                Terjadi kesalahan saat memuat detail.
                <br><small class="text-muted">Error: ${error}</small>
              </div>
            </div>
          </div>
        `);
      },
    });
  }

  // Save Barang (Add/Update)
  function saveBarang() {
    const form = $("#barangForm");
    const formData = new FormData(form[0]);
    const barangId = $("#barang_id").val();
    const submitBtn = form.find('button[type="submit"]');

    // Validate required fields
    const requiredFields = ["nama_barang", "tipe_barang", "harga_barang"];
    let isValid = true;
    let firstInvalidField = null;

    requiredFields.forEach((fieldName) => {
      const field = $(`#${fieldName}`);
      const value = field.val().trim();

      if (!value) {
        field.addClass("is-invalid");
        if (!firstInvalidField) {
          firstInvalidField = field;
        }
        isValid = false;
      } else {
        field.removeClass("is-invalid");
      }
    });

    if (!isValid) {
      showAlert("Mohon lengkapi semua field yang wajib diisi!", "warning");
      if (firstInvalidField) {
        firstInvalidField.focus();
      }
      return;
    }

    // Disable submit button
    submitBtn
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');

    // Add request type
    formData.append("request", barangId ? "update_barang" : "add_barang");

    // Show loading
    showLoading(
      barangId ? "Mengupdate data barang..." : "Menyimpan data barang..."
    );

    $.ajax({
      type: "POST",
      url: "controller/barangController.php",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            barangId
              ? '<i class="fas fa-save me-1"></i>Update Barang'
              : '<i class="fas fa-save me-1"></i>Simpan Barang'
          );

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#barangFormModal").modal("hide");
          loadBarangData(); // Reload data
        } else {
          showAlert(response.message, "error");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            barangId
              ? '<i class="fas fa-save me-1"></i>Update Barang'
              : '<i class="fas fa-save me-1"></i>Simpan Barang'
          );
        showAlert("Terjadi kesalahan saat menyimpan data!", "error");
      },
    });
  }

  // Delete Barang
  function deleteBarang(barangId, barangName) {
    Swal.fire({
      title: "Hapus Barang",
      html: `
        <div class="text-center">
          <div class="mb-3">
            <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
          </div>
          <p>Apakah Anda yakin ingin menghapus barang</p>
          <strong class="text-danger">"${barangName}"</strong>?
          <div class="alert alert-warning mt-3 border-0 rounded-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <small>Data yang dihapus tidak dapat dikembalikan!</small>
          </div>
        </div>
      `,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: '<i class="fas fa-trash me-1"></i>Ya, Hapus!',
      cancelButtonText: '<i class="fas fa-times me-1"></i>Batal',
      customClass: {
        popup: "rounded-4 shadow-lg",
        confirmButton: "btn-lg",
        cancelButton: "btn-lg",
      },
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading
        showLoading("Menghapus barang...");

        $.ajax({
          type: "POST",
          url: "controller/barangController.php",
          data: {
            request: "delete_barang",
            barang_id: barangId,
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
              });
              loadBarangData(); // Reload data
            } else {
              showAlert(response.message, "error");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", xhr, status, error);
            hideLoading();
            showAlert("Terjadi kesalahan saat menghapus data!", "error");
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

  // Make functions globally accessible
  window.loadBarangData = loadBarangData;
});
