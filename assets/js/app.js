class IPManagement {
  constructor() {
    this.currentBranch = null;
    this.currentPage = 1;
    this.recordsPerPage = 10;
    this.deviceTypes = [];
    this.subnets = [];
    this.searchQuery = "";
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

    // Search functionality
    document.getElementById("search-input").addEventListener("input", (e) => {
      this.searchQuery = e.target.value.trim();
      this.currentPage = 1;
      this.loadIPs();
    });

    // Clear search
    document.getElementById("clear-search").addEventListener("click", () => {
      document.getElementById("search-input").value = "";
      this.searchQuery = "";
      this.currentPage = 1;
      this.loadIPs();
    });

    // Records per page
    document
      .getElementById("records-per-page")
      .addEventListener("change", (e) => {
        this.recordsPerPage = parseInt(e.target.value);
        this.currentPage = 1;
        this.loadIPs();
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
    this.currentPage = 1;
    this.searchQuery = "";

    // Reset search
    document.getElementById("search-input").value = "";

    document.getElementById("selected-branch-name").textContent = branchName;
    document.getElementById("ip-content").style.display = "block";
    document.getElementById("no-branch-selected").style.display = "none";

    this.loadIPs();
  }

  async loadIPs() {
    this.showLoading(true);

    try {
      const data = await this.fetchIPs(
        this.currentBranch,
        this.currentPage,
        this.searchQuery
      );
      this.renderIPTable(data.ips);
      this.renderPagination(data.pagination);
      this.updateResultsInfo(data.pagination);
    } catch (error) {
      console.error("Error loading IPs:", error);
      this.showToast("Failed to load IP addresses", "error");
    } finally {
      this.showLoading(false);
    }
  }

  async fetchIPs(branchId, page, search = "") {
    let url = `api/ips.php?branch_id=${branchId}&page=${page}&per_page=${this.recordsPerPage}`;

    if (search) {
      url += `&search=${encodeURIComponent(search)}`;
    }

    const response = await fetch(url);
    if (!response.ok) throw new Error("Failed to fetch IPs");
    return await response.json();
  }

  updateResultsInfo(pagination) {
    const info = document.getElementById("results-info");
    const start =
      (pagination.current_page - 1) * pagination.records_per_page + 1;
    const end = Math.min(
      pagination.current_page * pagination.records_per_page,
      pagination.total_records
    );

    if (pagination.total_records > 0) {
      info.textContent = `Showing ${start}-${end} of ${pagination.total_records} results`;
    } else {
      info.textContent = "";
    }
  }

  renderIPTable(ips) {
    const tbody = document.getElementById("ip-table-body");
    tbody.innerHTML = "";

    if (ips.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
            ${
              this.searchQuery
                ? "No IP addresses match your search criteria"
                : "No IP addresses found for this branch"
            }
          </td>
        </tr>
      `;
      return;
    }

    ips.forEach((ip) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td><strong>${ip.ip_address}</strong></td>
        <td>${ip.device_name}</td>
        <td><span class="badge bg-primary">${ip.device_type}</span></td>
        <td>${ip.subnet_mask}</td>
        <td>${
          ip.description || '<em class="text-muted">No description</em>'
        }</td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-warning btn-sm" onclick="ipManager.showEditModal(${JSON.stringify(
              ip
            ).replace(/"/g, "&quot;")})">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-danger btn-sm" onclick="ipManager.deleteIP(${
              ip.id
            })">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

  renderPagination(pagination) {
    const container = document.getElementById("pagination-container");
    container.innerHTML = "";

    if (pagination.total_pages <= 1) return;

    // First button
    const firstLi = document.createElement("li");
    firstLi.className = `page-item ${
      pagination.current_page === 1 ? "disabled" : ""
    }`;
    firstLi.innerHTML = `
      <a class="page-link" href="#" data-page="1">
        <i class="fas fa-angle-double-left"></i> First
      </a>
    `;
    container.appendChild(firstLi);

    // Previous button
    const prevLi = document.createElement("li");
    prevLi.className = `page-item ${
      pagination.current_page === 1 ? "disabled" : ""
    }`;
    prevLi.innerHTML = `
      <a class="page-link" href="#" data-page="${
        pagination.current_page - 1
      }">
        <i class="fas fa-chevron-left"></i> Previous
      </a>
    `;
    container.appendChild(prevLi);

    // Page numbers (show max 5 pages around current)
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(
      pagination.total_pages,
      pagination.current_page + 2
    );

    if (startPage > 1) {
      const li = document.createElement("li");
      li.className = "page-item disabled";
      li.innerHTML = '<span class="page-link">...</span>';
      container.appendChild(li);
    }

    for (let i = startPage; i <= endPage; i++) {
      const li = document.createElement("li");
      li.className = `page-item ${
        i === pagination.current_page ? "active" : ""
      }`;
      li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
      container.appendChild(li);
    }

    if (endPage < pagination.total_pages) {
      const li = document.createElement("li");
      li.className = "page-item disabled";
      li.innerHTML = '<span class="page-link">...</span>';
      container.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement("li");
    nextLi.className = `page-item ${
      pagination.current_page === pagination.total_pages ? "disabled" : ""
    }`;
    nextLi.innerHTML = `
      <a class="page-link" href="#" data-page="${
        pagination.current_page + 1
      }">
        Next <i class="fas fa-chevron-right"></i>
      </a>
    `;
    container.appendChild(nextLi);

    // Last button
    const lastLi = document.createElement("li");
    lastLi.className = `page-item ${
      pagination.current_page === pagination.total_pages ? "disabled" : ""
    }`;
    lastLi.innerHTML = `
      <a class="page-link" href="#" data-page="${pagination.total_pages}">
        Last <i class="fas fa-angle-double-right"></i>
      </a>
    `;
    container.appendChild(lastLi);

    // Add click events
    container.addEventListener("click", (e) => {
      e.preventDefault();
      const link = e.target.closest("a[data-page]");
      if (link && !link.closest(".disabled")) {
        this.currentPage = parseInt(link.dataset.page);
        this.loadIPs();
      }
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
        this.loadIPs();
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
        this.loadIPs();
        this.loadBranches();
      } else {
        this.showToast(result.error || "Delete failed", "error");
      }
    } catch (error) {
      console.error("Error deleting IP:", error);
      this.showToast("Failed to delete IP address", "error");
    }
  }

  showLoading(show) {
    document.getElementById("loading-spinner").style.display = show
      ? "block"
      : "none";
  }

  showToast(message, type = "success") {
    const toastHtml = `
      <div class="toast ${type}" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
          <i class="fas ${
            type === "success" ? "fa-check-circle" : "fa-exclamation-circle"
          } me-2"></i>
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