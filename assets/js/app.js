class IPManagement {
  constructor() {
    this.currentBranch = null;
    this.deviceTypes = [];
    this.subnets = [];
    this.dataTable = null;
    this.init();
  }

  init() {
    this.loadBranches();
    this.loadDeviceTypes();
    this.loadSubnets();
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Add IP button
    document.getElementById("add-ip-btn").addEventListener("click", () => {
      this.showAddModal();
    });

    // Modal form submission
    document.getElementById("ip-form").addEventListener("submit", (e) => {
      e.preventDefault();
      this.handleFormSubmit();
    });
  }

  async loadBranches() {
    try {
      const branches = await this.fetchBranches();
      this.renderBranches(branches);
    } catch (error) {
      console.error("Error loading branches:", error);
      this.showToast("Failed to load branches", "error");
    }
  }

  async loadDeviceTypes() {
    try {
      const response = await fetch("api/device_types.php");
      if (!response.ok) throw new Error("Failed to fetch device types");
      this.deviceTypes = await response.json();
    } catch (error) {
      console.error("Error loading device types:", error);
      this.showToast("Failed to load device types", "error");
    }
  }

  async loadSubnets() {
    try {
      const response = await fetch("api/subnets.php");
      if (!response.ok) throw new Error("Failed to fetch subnets");
      this.subnets = await response.json();
    } catch (error) {
      console.error("Error loading subnets:", error);
      this.showToast("Failed to load subnets", "error");
    }
  }

  async fetchBranches() {
    const response = await fetch("api/branches.php");
    if (!response.ok) throw new Error("Failed to fetch branches");
    return await response.json();
  }

  renderBranches(branches) {
    const container = document.getElementById("branch-buttons");
    container.innerHTML = "";

    branches.forEach((branch) => {
      const branchCard = document.createElement("div");
      branchCard.className = "branch-card";
      branchCard.innerHTML = `
        <button class="btn btn-branch" data-branch-id="${branch.id}" data-branch-name="${branch.name}">
          <i class="fas fa-building branch-icon"></i>
          ${branch.name}
          <span class="ip-count">${branch.ip_count} IPs</span>
        </button>
      `;

      branchCard.addEventListener("click", () =>
        this.selectBranch(branch.id, branch.name, branchCard)
      );
      container.appendChild(branchCard);
    });
  }

  selectBranch(branchId, branchName, cardElement) {
    document
      .querySelectorAll(".branch-card")
      .forEach((card) => card.classList.remove("active"));
    cardElement.classList.add("active");

    this.currentBranch = branchId;

    document.getElementById("selected-branch-name").textContent = branchName;
    document.getElementById("ip-content").style.display = "block";
    document.getElementById("no-branch-selected").style.display = "none";

    this.initializeDataTable();
  }

  initializeDataTable() {
    // Destroy existing DataTable if it exists
    if (this.dataTable) {
      this.dataTable.destroy();
    }

    // Initialize DataTable with AJAX
    this.dataTable = $('#ip-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: 'api/ips_datatable.php',
        type: 'POST',
        data: (d) => {
          d.branch_id = this.currentBranch;
          return d;
        },
        error: (xhr, error, thrown) => {
          console.error('DataTables error:', error, thrown);
          this.showToast('Failed to load IP addresses', 'error');
        }
      },
      columns: [
        { 
          data: 'ip_address',
          render: (data) => `<strong>${data}</strong>`
        },
        { data: 'device_name' },
        { 
          data: 'device_type',
          render: (data) => `<span class="badge bg-primary">${data}</span>`
        },
        { data: 'subnet_mask' },
        { 
          data: 'description',
          render: (data) => data || '<em class="text-muted">No description</em>'
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: (data, type, row) => {
            const ipJson = JSON.stringify(row).replace(/"/g, '&quot;');
            return `
              <div class="action-buttons">
                <button class="btn btn-warning btn-sm" onclick="ipManager.showEditModal(${ipJson})">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="ipManager.deleteIP(${row.id})">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            `;
          }
        }
      ],
      order: [[0, 'asc']], // Sort by IP address by default
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      language: {
        emptyTable: "No IP addresses found for this branch",
        zeroRecords: "No matching IP addresses found",
        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
      },
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      responsive: true,
      autoWidth: false
    });
  }

  showAddModal() {
    if (!this.currentBranch) {
      this.showToast("Please select a branch first", "error");
      return;
    }

    document.getElementById("modal-title").textContent = "Add New IP Address";
    document.getElementById("ip-form").reset();
    document.getElementById("ip-id").value = "";

    this.populateDeviceTypeSelect();
    this.populateSubnetSelect();

    const modal = new bootstrap.Modal(document.getElementById("ip-modal"));
    modal.show();
  }

  showEditModal(ip) {
    document.getElementById("modal-title").textContent = "Edit IP Address";
    document.getElementById("ip-id").value = ip.id;
    document.getElementById("ip-address").value = ip.ip_address;
    document.getElementById("device-name").value = ip.device_name;
    document.getElementById("description").value = ip.description || "";

    this.populateDeviceTypeSelect(ip.device_type_id);
    this.populateSubnetSelect(ip.subnet_id);

    const modal = new bootstrap.Modal(document.getElementById("ip-modal"));
    modal.show();
  }

  populateDeviceTypeSelect(selectedId = null) {
    const select = document.getElementById("device-type");
    select.innerHTML = '<option value="">Select Device Type</option>';

    this.deviceTypes.forEach((type) => {
      const option = document.createElement("option");
      option.value = type.id;
      option.textContent = type.name;
      if (selectedId && type.id == selectedId) {
        option.selected = true;
      }
      select.appendChild(option);
    });
  }

  populateSubnetSelect(selectedId = null) {
    const select = document.getElementById("subnet");
    select.innerHTML = '<option value="">Select Subnet</option>';

    this.subnets.forEach((subnet) => {
      const option = document.createElement("option");
      option.value = subnet.id;
      option.textContent = `/${subnet.prefix} (${subnet.subnet_mask})`;
      if (selectedId && subnet.id == selectedId) {
        option.selected = true;
      }
      select.appendChild(option);
    });
  }

  async handleFormSubmit() {
    const form = document.getElementById("ip-form");
    const formData = new FormData(form);

    const ipData = {
      ip_address: formData.get("ip_address"),
      device_name: formData.get("device_name"),
      device_type_id: formData.get("device_type_id"),
      subnet_id: formData.get("subnet_id"),
      description: formData.get("description"),
      branch_id: this.currentBranch,
    };

    const ipId = document.getElementById("ip-id").value;
    const isEdit = ipId !== "";

    try {
      let response;
      if (isEdit) {
        ipData.id = ipId;
        response = await fetch("api/ips.php", {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(ipData),
        });
      } else {
        response = await fetch("api/ips.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(ipData),
        });
      }

      const result = await response.json();

      if (response.ok) {
        this.showToast(result.message, "success");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("ip-modal")
        );
        modal.hide();
        
        // Reload DataTable
        if (this.dataTable) {
          this.dataTable.ajax.reload();
        }
        
        // Refresh branch IP counts
        this.loadBranches();
      } else {
        this.showToast(result.error || "Operation failed", "error");
      }
    } catch (error) {
      console.error("Error saving IP:", error);
      this.showToast("Failed to save IP address", "error");
    }
  }

  async deleteIP(ipId) {
    if (!confirm("Are you sure you want to delete this IP address?")) {
      return;
    }

    try {
      const response = await fetch(`api/ips.php?id=${ipId}`, {
        method: "DELETE",
      });

      const result = await response.json();

      if (response.ok) {
        this.showToast(result.message, "success");
        
        // Reload DataTable
        if (this.dataTable) {
          this.dataTable.ajax.reload();
        }
        
        // Refresh branch IP counts
        this.loadBranches();
      } else {
        this.showToast(result.error || "Delete failed", "error");
      }
    } catch (error) {
      console.error("Error deleting IP:", error);
      this.showToast("Failed to delete IP address", "error");
    }
  }

  showToast(message, type = "success") {
    const toastHtml = `
      <div class="toast ${type}" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
          <i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"} me-2"></i>
          ${message}
        </div>
      </div>
    `;

    let container = document.querySelector(".toast-container");
    if (!container) {
      container = document.createElement("div");
      container.className = "toast-container position-fixed top-0 end-0 p-3";
      document.body.appendChild(container);
    }

    container.innerHTML = toastHtml;
    const toastElement = container.querySelector(".toast");
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    toastElement.addEventListener("hidden.bs.toast", () => {
      toastElement.remove();
    });
  }
}

// Initialize the application when DOM is loaded
let ipManager;
document.addEventListener("DOMContentLoaded", () => {
  ipManager = new IPManagement();
});