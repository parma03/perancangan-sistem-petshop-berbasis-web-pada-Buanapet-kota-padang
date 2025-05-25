$(document).ready(function () {
  console.log("Document ready - services script loaded");

  console.log("Services tab shown, loading data");
  loadServicesData();

  // Handle add services button
  $(document).on("click", ".add-services-btn", function () {
    showServicesForm();
  });

  // Handle edit services button
  $(document).on("click", ".edit-services-btn", function () {
    const servicesId = $(this).data("id");
    showServicesForm(servicesId);
  });

  // Handle view services detail button
  $(document).on("click", ".view-services-btn", function () {
    const servicesId = $(this).data("id");
    showServicesDetail(servicesId);
  });

  // Handle delete services button
  $(document).on("click", ".delete-services-btn", function () {
    const servicesId = $(this).data("id");
    const servicesName = $(this).data("name");
    deleteServices(servicesId, servicesName);
  });

  // Handle manage schedule button
  $(document).on("click", ".manage-schedule-btn", function () {
    const serviceId = $(this).data("id");
    const serviceName = $(this).data("name");
    showScheduleManagement(serviceId, serviceName);
  });

  // Handle services form submission
  $("#servicesForm").on("submit", function (e) {
    e.preventDefault();
    saveServices();
  });

  // Handle schedule form submission
  $("#scheduleForm").on("submit", function (e) {
    e.preventDefault();
    saveSchedule();
  });

  // Handle add schedule button
  $(document).on("click", ".add-schedule-btn", function () {
    const serviceId = $(this).data("service-id");
    showScheduleForm(serviceId);
  });

  // Handle edit schedule button
  $(document).on("click", ".edit-schedule-btn", function () {
    const scheduleId = $(this).data("id");
    const serviceId = $(this).data("service-id");
    showScheduleForm(serviceId, scheduleId);
  });

  // Handle delete schedule button
  $(document).on("click", ".delete-schedule-btn", function () {
    const scheduleId = $(this).data("id");
    const day = $(this).data("day");
    deleteSchedule(scheduleId, day);
  });

  // Format currency input
  $("#harga_services").on("input", function () {
    let value = $(this).val().replace(/\D/g, "");
    $(this).val(value);
  });

  // FUNGSI UNTUK MENAMPILKAN MODAL FORM SERVICES (TAMBAH/EDIT)
  function showServicesForm(servicesId = null) {
    const modal = $("#servicesFormModal");
    const form = $("#servicesForm");
    const modalTitle = $("#servicesFormModalLabel");
    const submitBtn = form.find('button[type="submit"]');

    // Reset form
    form[0].reset();

    if (servicesId) {
      // Mode Edit
      modalTitle.html('<i class="fas fa-edit me-2"></i>Edit Service');
      submitBtn.html('<i class="fas fa-save me-1"></i>Update Service');

      // Show loading
      showLoading("Memuat data service...");

      // Load services data for editing
      $.ajax({
        type: "POST",
        url: "controller/servicesController.php",
        data: {
          request: "get_services_by_id",
          services_id: servicesId,
        },
        dataType: "json",
        success: function (response) {
          hideLoading();

          if (response.status === "success") {
            const service = response.data;

            // Fill form with service data
            $("#services_id").val(service.id_service);
            $("#nama_services").val(service.nama_service);
            $("#harga_services").val(service.harga_service);
            $("#deskripsi_services").val(service.deskripsi_service);

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
      modalTitle.html('<i class="fas fa-plus me-2"></i>Tambah Service Baru');
      submitBtn.html('<i class="fas fa-save me-1"></i>Simpan Service');
      $("#services_id").val("");
      modal.modal("show");
    }
  }

  // Load Services Data
  function loadServicesData() {
    const container = $(".services-data-container");

    console.log("Loading services data");

    // Show loading indicator
    container.html(`
      <div class="text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="text-muted">Memuat data services...</h5>
        <p class="text-muted mb-0">Silakan tunggu sebentar</p>
      </div>
    `);

    // Fetch services data via AJAX
    $.ajax({
      type: "POST",
      url: "controller/servicesController.php",
      data: {
        request: "get_services",
      },
      dataType: "json",
      success: function (response) {
        console.log("Get services response:", response);

        if (response.status === "success") {
          // Update container with services data
          container.html(response.html);

          // Initialize DataTable if table exists
          if ($("#servicesTable").length > 0) {
            $("#servicesTable").DataTable({
              responsive: true,
              language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
              },
              order: [[1, "asc"]], // Sort by nama_service
              columnDefs: [
                { targets: [-1], orderable: false }, // Disable sorting on action column
                { targets: [0], width: "5%" },
                { targets: [1], width: "25%" },
                { targets: [2], width: "15%" },
                { targets: [3], width: "20%" },
                { targets: [4], width: "10%" },
                { targets: [5], width: "15%" },
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
                    response.message || "Tidak dapat memuat data services."
                  }</p>
                  <small class="text-muted">Silakan refresh halaman atau hubungi administrator</small>
                </div>
              </div>
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error when loading services:", xhr, status, error);
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
                <p class="mb-2">Tidak dapat memuat data services dari server.</p>
                <small class="text-muted">Error: ${error}</small>
                <div class="mt-2">
                  <button class="btn btn-sm btn-outline-danger" onclick="loadServicesData()">
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

  // Show Services Detail
  function showServicesDetail(servicesId) {
    const modal = $("#servicesDetailModal");
    const content = $("#servicesDetailContent");

    // Show loading
    content.html(`
      <div class="text-center py-4">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h6 class="text-muted">Memuat detail service...</h6>
      </div>
    `);

    modal.modal("show");

    // Load services detail
    $.ajax({
      type: "POST",
      url: "controller/servicesController.php",
      data: {
        request: "get_services_detail",
        services_id: servicesId,
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

  // Save Services (Add/Update)
  function saveServices() {
    const form = $("#servicesForm");
    const servicesId = $("#services_id").val();
    const submitBtn = form.find('button[type="submit"]');

    // Get form data
    const formData = {
      request: servicesId ? "update_services" : "add_services",
      nama_services: $("#nama_services").val().trim(),
      harga_services: $("#harga_services").val().trim(),
      deskripsi_services: $("#deskripsi_services").val().trim(),
    };

    if (servicesId) {
      formData.services_id = servicesId;
    }

    // Validate required fields
    if (!formData.nama_services || !formData.harga_services) {
      showAlert("Nama service dan harga harus diisi!", "warning");
      return;
    }

    if (
      isNaN(formData.harga_services) ||
      parseInt(formData.harga_services) <= 0
    ) {
      showAlert("Harga service harus berupa angka yang valid!", "warning");
      return;
    }

    // Disable submit button
    submitBtn
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');

    // Show loading
    showLoading(
      servicesId ? "Mengupdate data service..." : "Menyimpan data service..."
    );

    $.ajax({
      type: "POST",
      url: "controller/servicesController.php",
      data: formData,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            servicesId
              ? '<i class="fas fa-save me-1"></i>Update Service'
              : '<i class="fas fa-save me-1"></i>Simpan Service'
          );

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#servicesFormModal").modal("hide");
          loadServicesData(); // Reload data
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
            servicesId
              ? '<i class="fas fa-save me-1"></i>Update Service'
              : '<i class="fas fa-save me-1"></i>Simpan Service'
          );
        showAlert("Terjadi kesalahan saat menyimpan data!", "error");
      },
    });
  }

  // Delete Services
  function deleteServices(servicesId, servicesName) {
    Swal.fire({
      title: "Hapus Service",
      html: `
        <div class="text-center">
          <div class="mb-3">
            <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
          </div>
          <p>Apakah Anda yakin ingin menghapus service</p>
          <strong class="text-danger">"${servicesName}"</strong>?
          <div class="alert alert-warning mt-3 border-0 rounded-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <small>Data service dan semua jadwalnya akan dihapus!</small>
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
        showLoading("Menghapus service...");

        $.ajax({
          type: "POST",
          url: "controller/servicesController.php",
          data: {
            request: "delete_services",
            services_id: servicesId,
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
              loadServicesData(); // Reload data
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

  // Show Schedule Management Modal
  function showScheduleManagement(serviceId, serviceName) {
    const modal = $("#scheduleManagementModal");
    const content = $("#scheduleManagementContent");
    const modalTitle = $("#scheduleManagementModalLabel");

    modalTitle.html(
      `<i class="fas fa-calendar me-2"></i>Kelola Jadwal - ${serviceName}`
    );

    // Show loading
    content.html(`
      <div class="text-center py-4">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h6 class="text-muted">Memuat jadwal service...</h6>
      </div>
    `);

    modal.modal("show");

    // Load schedules
    loadServiceSchedules(serviceId, serviceName);
  }

  // Load Service Schedules
  function loadServiceSchedules(serviceId, serviceName) {
    $.ajax({
      type: "POST",
      url: "controller/servicesController.php",
      data: {
        request: "get_service_schedules",
        service_id: serviceId,
      },
      dataType: "json",
      success: function (response) {
        const content = $("#scheduleManagementContent");

        if (response.status === "success") {
          const schedules = response.data;
          let html = `
            <div class="mb-4">
              <button type="button" class="btn btn-primary add-schedule-btn" data-service-id="${serviceId}">
                <i class="fas fa-plus me-1"></i>Tambah Jadwal
              </button>
            </div>
          `;

          if (schedules.length > 0) {
            html += `
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>Hari</th>
                      <th>Jam Mulai</th>
                      <th>Jam Selesai</th>
                      <th>Durasi</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
            `;

            schedules.forEach((schedule) => {
              const start = new Date(`2000-01-01 ${schedule.duty_start}`);
              const end = new Date(`2000-01-01 ${schedule.duty_end}`);
              const diff = (end - start) / (1000 * 60 * 60); // hours

              html += `
                <tr>
                  <td><strong>${schedule.hari}</strong></td>
                  <td>${schedule.duty_start}</td>
                  <td>${schedule.duty_end}</td>
                  <td>${diff} jam</td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-warning edit-schedule-btn" 
                              data-id="${schedule.id_jadwal}" 
                              data-service-id="${serviceId}"
                              title="Edit">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button type="button" class="btn btn-danger delete-schedule-btn" 
                              data-id="${schedule.id_jadwal}" 
                              data-day="${schedule.hari}"
                              title="Hapus">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              `;
            });

            html += `
                  </tbody>
                </table>
              </div>
            `;
          } else {
            html += `
              <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Belum Ada Jadwal</h6>
                <p class="text-muted">Service ini belum memiliki jadwal operasional</p>
              </div>
            `;
          }

          content.html(html);
        } else {
          content.html(`
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-circle me-2"></i>
              ${response.message}
            </div>
          `);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", xhr, status, error);
        $("#scheduleManagementContent").html(`
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Terjadi kesalahan saat memuat jadwal
          </div>
        `);
      },
    });
  }

  // Show Schedule Form
  function showScheduleForm(serviceId, scheduleId = null) {
    const modal = $("#scheduleFormModal");
    const form = $("#scheduleForm");
    const modalTitle = $("#scheduleFormModalLabel");
    const submitBtn = form.find('button[type="submit"]');

    // Reset form
    form[0].reset();
    $("#service_id").val(serviceId);

    if (scheduleId) {
      // Mode Edit - Load schedule data
      modalTitle.html('<i class="fas fa-edit me-2"></i>Edit Jadwal');
      submitBtn.html('<i class="fas fa-save me-1"></i>Update Jadwal');
      $("#schedule_id").val(scheduleId);

      // You would need to implement get schedule by id in PHP controller
      // For now, just show the modal
      modal.modal("show");
    } else {
      // Mode Add
      modalTitle.html('<i class="fas fa-plus me-2"></i>Tambah Jadwal');
      submitBtn.html('<i class="fas fa-save me-1"></i>Simpan Jadwal');
      $("#schedule_id").val("");
      modal.modal("show");
    }
  }

  // Save Schedule
  function saveSchedule() {
    const form = $("#scheduleForm");
    const scheduleId = $("#schedule_id").val();
    const submitBtn = form.find('button[type="submit"]');

    const formData = {
      request: scheduleId ? "update_schedule" : "add_schedule",
      service_id: $("#service_id").val(),
      hari: $("#hari").val(),
      duty_start: $("#duty_start").val(),
      duty_end: $("#duty_end").val(),
    };

    if (scheduleId) {
      formData.schedule_id = scheduleId;
    }

    // Validation
    if (!formData.hari || !formData.duty_start || !formData.duty_end) {
      showAlert("Semua field harus diisi!", "warning");
      return;
    }

    if (formData.duty_start >= formData.duty_end) {
      showAlert("Waktu mulai harus lebih awal dari waktu selesai!", "warning");
      return;
    }

    // Disable submit button
    submitBtn
      .prop("disabled", true)
      .html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');

    showLoading(scheduleId ? "Mengupdate jadwal..." : "Menyimpan jadwal...");

    $.ajax({
      type: "POST",
      url: "controller/servicesController.php",
      data: formData,
      dataType: "json",
      success: function (response) {
        hideLoading();
        submitBtn
          .prop("disabled", false)
          .html(
            scheduleId
              ? '<i class="fas fa-save me-1"></i>Update Jadwal'
              : '<i class="fas fa-save me-1"></i>Simpan Jadwal'
          );

        if (response.status === "success") {
          showAlert(response.message, "success");
          $("#scheduleFormModal").modal("hide");
          // Reload schedules in the management modal
          loadServiceSchedules(formData.service_id, "");
          // Also reload main services data to update schedule count
          loadServicesData();
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
            scheduleId
              ? '<i class="fas fa-save me-1"></i>Update Jadwal'
              : '<i class="fas fa-save me-1"></i>Simpan Jadwal'
          );
        showAlert("Terjadi kesalahan saat menyimpan jadwal!", "error");
      },
    });
  }

  // Delete Schedule
  function deleteSchedule(scheduleId, day) {
    Swal.fire({
      title: "Hapus Jadwal",
      html: `
        <div class="text-center">
          <div class="mb-3">
            <i class="fas fa-calendar-times fa-3x text-danger mb-3"></i>
          </div>
          <p>Apakah Anda yakin ingin menghapus jadwal</p>
          <strong class="text-danger">"${day}"</strong>?
        </div>
      `,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: '<i class="fas fa-trash me-1"></i>Ya, Hapus!',
      cancelButtonText: '<i class="fas fa-times me-1"></i>Batal',
    }).then((result) => {
      if (result.isConfirmed) {
        showLoading("Menghapus jadwal...");

        $.ajax({
          type: "POST",
          url: "controller/servicesController.php",
          data: {
            request: "delete_schedule",
            schedule_id: scheduleId,
          },
          dataType: "json",
          success: function (response) {
            hideLoading();

            if (response.status === "success") {
              showAlert(response.message, "success");
              // Reload current schedule view
              const serviceId = $("#service_id").val();
              if (serviceId) {
                loadServiceSchedules(serviceId, "");
              }
              // Also reload main services data to update schedule count
              loadServicesData();
            } else {
              showAlert(response.message, "error");
            }
          },
          error: function (xhr, status, error) {
            console.error("AJAX error:", xhr, status, error);
            hideLoading();
            showAlert("Terjadi kesalahan saat menghapus jadwal!", "error");
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
  window.loadServicesData = loadServicesData;
  window.loadServiceSchedules = loadServiceSchedules;
});
