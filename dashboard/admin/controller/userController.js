// Function Data User - Admin
$(document).ready(function () {
  console.log("Document ready - admin script loaded");

  console.log("Admin tab shown, loading data");
  loadAdminData();

  // Handle add admin button
  $(document).on("click", ".add-admin-btn", function () {
    showAdminForm();
  });

  // Handle edit admin button
  $(document).on("click", ".edit-admin-btn", function () {
    const adminId = $(this).data("id");
    showAdminForm(adminId);
  });

  // Handle view admin detail button
  $(document).on("click", ".view-admin-btn", function () {
    const adminId = $(this).data("id");
    showAdminDetail(adminId);
  });

  // Handle delete admin button
  $(document).on("click", ".delete-admin-btn", function () {
    const adminId = $(this).data("id");
    const adminName = $(this).data("name");
    deleteAdmin(adminId, adminName);
  });

  // Handle admin form submission
  $("#adminForm").on("submit", function (e) {
    e.preventDefault();
    saveAdmin();
  });

  // Handle password toggle
  $("#togglePassword").on("click", function () {
    const passwordField = $("#password");
    const passwordFieldType = passwordField.attr("type");
    const icon = $(this).find("i");

    if (passwordFieldType === "password") {
      passwordField.attr("type", "text");
      icon.removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
      passwordField.attr("type", "password");
      icon.removeClass("fa-eye-slash").addClass("fa-eye");
    }
  });

  // FIX: Handle profile upload area click - make sure this works
  $(document).on("click", ".upload-placeholder", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Upload placeholder clicked");
    $("#profile").trigger("click");
  });

  // FIX: Also handle click on the entire profile-upload-area
  $(document).on("click", ".profile-upload-area", function (e) {
    // Only trigger if clicking on the area itself, not on buttons inside it
    if (
      e.target === this ||
      $(e.target).hasClass("upload-placeholder") ||
      $(e.target).closest(".upload-placeholder").length > 0
    ) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Profile upload area clicked");
      $("#profile").trigger("click");
    }
  });

  // Handle profile image preview
  $("#profile").on("change", function () {
    const file = this.files[0];
    const preview = $("#profilePreview");
    const removeBtn = $("#removeProfileBtn");
    const uploadPlaceholder = $("#uploadPlaceholder");

    if (file) {
      console.log("File selected:", file.name, file.size, file.type);

      // Validate file type
      const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
      if (!validTypes.includes(file.type)) {
        showAlert(
          "Format file tidak valid! Gunakan JPG, PNG, atau GIF.",
          "warning"
        );
        this.value = "";
        return;
      }

      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        showAlert("Ukuran file terlalu besar! Maksimal 2MB.", "warning");
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
              }" class="img-thumbnail rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
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

  // Handle remove profile image
  $("#removeProfileBtn").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    console.log("Remove profile button clicked");
    $("#profile").val("");
    $("#profilePreview").hide();
    $("#uploadPlaceholder").show();
    $(this).hide();
    $("#removeExistingProfile").val("1"); // Flag to remove existing profile
    showAlert("Foto telah dihapus dari preview.", "info");
  });

  // Handle remove current profile in edit mode
  $(document).on("click", "#removeCurrentProfile", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $("#currentProfileDisplay").hide();
    $("#removeExistingProfile").val("1");
    showAlert("Foto profile akan dihapus saat menyimpan.", "info");
  });

  // Drag & Drop functionality for profile upload
  $(".profile-upload-area").on("dragover", function (e) {
    e.preventDefault();
    $(this).addClass("border-primary bg-light");
  });

  $(".profile-upload-area").on("dragleave", function (e) {
    e.preventDefault();
    $(this).removeClass("border-primary bg-light");
  });

  $(".profile-upload-area").on("drop", function (e) {
    e.preventDefault();
    $(this).removeClass("border-primary bg-light");

    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 0) {
      console.log("File dropped:", files[0].name);
      // Set the files to the input element
      const input = document.getElementById("profile");
      input.files = files;
      // Trigger change event
      $(input).trigger("change");
    }
  });

  // FUNGSI UNTUK MENAMPILKAN MODAL FORM Admin (TAMBAH/EDIT)
  function showAdminForm(adminId = null) {
    const modal = $("#adminFormModal");
    const form = $("#adminForm");
    const modalTitle = $("#adminFormModalLabel");
    const submitBtn = form.find('button[type="submit"]');

    // Reset form
    form[0].reset();
    $("#profilePreview").hide();
    $("#removeProfileBtn").hide();
    $("#uploadPlaceholder").show();
    $("#currentProfileDisplay").hide();
    $("#removeExistingProfile").val("0");

    if (adminId) {
      // Mode Edit
      modalTitle.html('<i class="fas fa-edit me-2"></i>Edit Admin');
      submitBtn.html('<i class="fas fa-save me-1"></i>Update Admin');

      // Show loading
      showLoading("Memuat data admin...");

      // Load admin data for editing
      $.ajax({
        type: "POST",
        url: "controller/userController.php",
        data: {
          request: "get_admin_by_id",
          admin_id: adminId,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          if (response.status === "success") {
            const admin = response.data;

            // Fill form with admin data
            $("#admin_id").val(admin.id_admin);
            $("#user_id").val(admin.id_user);
            $("#username").val(admin.username);
            $("#nama").val(admin.nama);

            // Show current profile image if exists
            if (admin.profile) {
              $("#currentProfileDisplay")
                .html(
                  `<div class="mb-4">
                  <label class="form-label fw-semibold">
                    <i class="fas fa-image text-primary me-1"></i>Foto Profile Saat Ini:
                  </label>
                  <div class="current-profile-container p-4 border rounded-4 bg-gradient" style="background: linear-gradient(145deg, #f8f9fa, #ffffff); border: 2px dashed #dee2e6 !important;">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <div class="position-relative">
                          <img src="../../../assets/uploads/profile/${admin.profile}" 
                               class="img-thumbnail rounded-circle shadow-sm" 
                               style="width: 100px; height: 100px; object-fit: cover;">
                          <div class="position-absolute top-0 start-100 translate-middle">
                            <span class="badge bg-success rounded-pill">
                              <i class="fas fa-check"></i>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <h6 class="mb-2 text-dark">${admin.nama}</h6>
                        <div class="d-flex align-items-center mb-2">
                          <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-user me-1"></i>Profile aktif
                          </span>
                          <small class="text-muted">Diupload sebelumnya</small>
                        </div>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="removeCurrentProfile">
                          <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                      </div>
                    </div>
                  </div>
                </div>`
                )
                .show();
            }

            // Make password optional for edit
            $("#password").removeAttr("required");
            $("#password").attr(
              "placeholder",
              "Kosongkan jika tidak ingin mengubah password"
            );

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
      modalTitle.html('<i class="fas fa-user-plus me-2"></i>Tambah Admin Baru');
      submitBtn.html('<i class="fas fa-save me-1"></i>Simpan Admin');

      // Clear hidden fields
      $("#admin_id").val("");
      $("#user_id").val("");

      // Make password required for add
      $("#password").attr("required", "required");
      $("#password").attr("placeholder", "Masukkan password");

      // Show modal immediately
      modal.modal("show");
    }
  }

  // Load Admin Data
  function loadAdminData() {
    const container = $(".admin-data-container");

    console.log("Loading admin data");

    // Show loading indicator
    container.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data Admin...</p>
      </div>
    `);

    // Fetch admin data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/userController.php",
      data: {
        request: "get_admin",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get admin response:", response);

        if (response.status === "success") {
          // Update container with admin data
          container.html(response.html);

          // Initialize DataTable if table exists
          if ($("#adminTable").length > 0) {
            $("#adminTable").DataTable({
              responsive: true,
              language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
              },
              order: [[1, "asc"]], // Sort by nama
              columnDefs: [
                { targets: [-1], orderable: false }, // Disable sorting on action column
              ],
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
        console.error("AJAX error when loading admin:", xhr, status, error);
        console.log("Response text:", xhr.responseText);

        // Show error message
        container.html(`
          <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memuat data Admin.
            <br><small>Error: ${error}</small>
          </div>
        `);
      },
    });
  }

  // Show Admin Detail
  function showAdminDetail(adminId) {
    const modal = $("#adminDetailModal");
    const content = $("#adminDetailContent");

    // Show loading
    content.html(`
      <div class="text-center py-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat detail Admin...</p>
      </div>
    `);

    modal.modal("show");

    // Load admin detail
    $.ajax({
      type: "POST",
      url: "controller/userController.php",
      data: {
        request: "get_admin_detail",
        admin_id: adminId,
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

  // Save Admin (Add/Update)
  function saveAdmin() {
    const form = $("#adminForm");
    const formData = new FormData(form[0]);
    const adminId = $("#admin_id").val();
    const password = $("#password").val();
    const confirmPassword = $("#confirm_password").val();
    const submitBtn = form.find('button[type="submit"]');

    // Validate required fields
    const requiredFields = ["username", "nama"];
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

    // Validate password if provided
    if ((password || confirmPassword) && password !== confirmPassword) {
      $("#password, #confirm_password").addClass("is-invalid");
      showAlert("Password dan konfirmasi password tidak cocok!", "warning");
      return;
    } else {
      $("#password, #confirm_password").removeClass("is-invalid");
    }

    // For add mode, password is required
    if (!adminId && !password) {
      $("#password").addClass("is-invalid");
      showAlert("Password wajib diisi untuk admin baru!", "warning");
      return;
    }

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
    formData.append("request", adminId ? "update_admin" : "add_admin");

    // Show loading
    showLoading(
      adminId ? "Mengupdate data admin..." : "Menyimpan data admin..."
    );

    $.ajax({
      type: "POST",
      url: "controller/userController.php",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            adminId
              ? '<i class="fas fa-save me-1"></i>Update Admin'
              : '<i class="fas fa-save me-1"></i>Simpan Admin'
          );

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#adminFormModal").modal("hide");
          loadAdminData(); // Reload data
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
            adminId
              ? '<i class="fas fa-save me-1"></i>Update Admin'
              : '<i class="fas fa-save me-1"></i>Simpan Admin'
          );
        showAlert("Terjadi kesalahan saat menyimpan data!", "error");
      },
    });
  }

  // Delete Admin
  function deleteAdmin(adminId, adminName) {
    Swal.fire({
      title: "Hapus Admin",
      html: `
      <div class="text-center">
        <div class="mb-3">
          <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
        </div>
        <p>Apakah Anda yakin ingin menghapus admin</p>
        <strong class="text-danger">"${adminName}"</strong>?
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
        showLoading("Menghapus admin...");

        $.ajax({
          type: "POST",
          url: "controller/userController.php",
          data: {
            request: "delete_admin",
            admin_id: adminId,
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
              loadAdminData(); // Reload data
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
    });
  }

  function showLoading(message = "Loading...") {
    Swal.fire({
      title: "Mohon Tunggu",
      text: message,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  }

  function hideLoading() {
    Swal.close();
  }
});
