class IPManagement {
  constructor() {
    this.currentBranch = null;
    this.currentBranchName = null;
    this.currentNetwork = null;
    this.currentSubnetId = null;
    this.deviceTypes = [];
    this.subnets = [];
    this.dataTable = null;
    this.THEME_KEY = 'ip-management-theme';
    this.init();
  }

  init() {
    this.initTheme();
    this.loadBranches();
    this.loadDeviceTypes();
    this.loadSubnets();
    this.setupEventListeners();
  }

  initTheme() {
    const savedTheme = this.getTheme();
    this.setTheme(savedTheme);
  }

  getTheme() {
    try {
      const savedTheme = localStorage.getItem(this.THEME_KEY);
      if (savedTheme) {
        return savedTheme;
      }
    } catch (e) {
      console.warn('localStorage not available:', e);
    }
    
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    return prefersDark ? 'dark' : 'light';
  }

  setTheme(theme) {
    try {
      localStorage.setItem(this.THEME_KEY, theme);
    } catch (e) {
      console.warn('localStorage not available:', e);
    }
    
    document.documentElement.setAttribute('data-theme', theme);
    const icon = document.querySelector('.theme-toggle i');
    if (icon) {
      icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
  }

  toggleTheme() {
    const currentTheme = this.getTheme();
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    this.setTheme(newTheme);
  }

  setupEventListeners() {
    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', () => {
        this.toggleTheme();
      });
    }

    // Branch selection
    const branchSelect = document.getElementById('branch-select');
    if (branchSelect) {
      branchSelect.addEventListener('change', (e) => {
        this.onBranchChange(e.target.value);
      });
    }

    // Network selection
    const networkSelect = document.getElementById('network-select');
    if (networkSelect) {
      networkSelect.addEventListener('change', (e) => {
        this.onNetworkChange(e.target.value);
      });
    }

    // Add IP button
    const addIpBtn = document.getElementById('add-ip-btn');
    if (addIpBtn) {
      addIpBtn.addEventListener('click', () => {
        this.showAddModal();
      });
    }

    // Form submission
    const ipForm = document.getElementById('ip-form');
    if (ipForm) {
      ipForm.addEventListener('submit', (e) => {
        e.preventDefault();
        this.handleFormSubmit();
      });
    }
  }

  async loadBranches() {
    try {
      const response = await fetch('api/branches.php');
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const branches = await response.json();
      
      if (branches.error) {
        throw new Error(branches.error);
      }
      
      this.renderBranchDropdown(branches);
    } catch (error) {
      console.error('Error loading branches:', error);
      this.showToast('Failed to load branches: ' + error.message, 'error');
    }
  }

  renderBranchDropdown(branches) {
    const select = document.getElementById('branch-select');
    if (!select) return;
    
    select.innerHTML = '<option value="">Choose a branch...</option>';
    
    if (!Array.isArray(branches) || branches.length === 0) {
      const option = document.createElement('option');
      option.textContent = 'No branches available';
      option.disabled = true;
      select.appendChild(option);
      return;
    }
    
    branches.forEach(branch => {
      const option = document.createElement('option');
      option.value = branch.id;
      option.textContent = `${branch.name} (${branch.ip_count || 0} IPs)`;
      option.dataset.name = branch.name;
      select.appendChild(option);
    });
  }

  async onBranchChange(branchId) {
    // Reset network selection
    const networkSelect = document.getElementById('network-select');
    if (networkSelect) {
      networkSelect.innerHTML = '<option value="">All Networks</option>';
      networkSelect.disabled = false;
    }
    
    // Show/Hide IP content
    const ipContent = document.getElementById('ip-content');
    const noSelection = document.getElementById('no-selection');
    
    // Destroy existing DataTable if present
    if (this.dataTable) {
      this.dataTable.destroy();
      this.dataTable = null;
    }
    
    if (!branchId) {
      this.currentBranch = null;
      this.currentBranchName = null;
      if (ipContent) ipContent.style.display = 'none';
      if (noSelection) noSelection.style.display = 'block';
      return;
    }
    
    this.currentBranch = parseInt(branchId);
    const selectedOption = document.querySelector(`#branch-select option[value="${branchId}"]`);
    this.currentBranchName = selectedOption ? selectedOption.dataset.name : '';
    this.currentNetwork = null;
    this.currentSubnetId = null;
    
    // Update display
    const branchName = document.getElementById('selected-branch-name');
    const networkName = document.getElementById('selected-network');
    
    if (branchName) branchName.textContent = this.currentBranchName;
    if (networkName) networkName.textContent = 'All Networks';
    if (ipContent) ipContent.style.display = 'block';
    if (noSelection) noSelection.style.display = 'none';
    
    // Load networks for selected branch
    await this.loadNetworks(branchId);
    
    // Initialize table with all IPs from branch
    this.initializeDataTable();
  }

  async loadNetworks(branchId) {
    try {
      const response = await fetch(`api/networks.php?branch_id=${branchId}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const networks = await response.json();
      
      if (networks.error) {
        throw new Error(networks.error);
      }
      
      this.renderNetworkDropdown(networks);
    } catch (error) {
      console.error('Error loading networks:', error);
      this.showToast('Failed to load networks: ' + error.message, 'error');
    }
  }

  renderNetworkDropdown(networks) {
    const select = document.getElementById('network-select');
    if (!select) return;
    
    select.innerHTML = '<option value="">All Networks</option>';
    
    if (!Array.isArray(networks) || networks.length === 0) {
      // Don't disable the select - user can still add IPs
      select.disabled = false;
      return;
    }
    
    networks.forEach(network => {
      const option = document.createElement('option');
      option.value = JSON.stringify({
        network: network.network,
        subnet_id: network.subnet_id
      });
      option.textContent = `${network.network}/${network.prefix} - ${network.subnet_mask} (${network.ip_count || 0} IPs)`;
      select.appendChild(option);
    });
    
    select.disabled = false;
  }

  onNetworkChange(value) {
    // Destroy existing DataTable if present
    if (this.dataTable) {
      this.dataTable.destroy();
      this.dataTable = null;
    }
    
    if (!value) {
      // Show all networks
      this.currentNetwork = null;
      this.currentSubnetId = null;
      
      const networkName = document.getElementById('selected-network');
      if (networkName) networkName.textContent = 'All Networks';
      
      this.initializeDataTable();
      return;
    }
    
    try {
      const data = JSON.parse(value);
      this.currentNetwork = data.network;
      this.currentSubnetId = data.subnet_id;
      
      const networkName = document.getElementById('selected-network');
      if (networkName) networkName.textContent = this.currentNetwork;
      
      // Load IPs for selected network
      this.initializeDataTable();
    } catch (error) {
      console.error('Error parsing network data:', error);
      this.showToast('Invalid network selection', 'error');
    }
  }

  async loadDeviceTypes() {
    try {
      const response = await fetch('api/device_types.php');
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const deviceTypes = await response.json();
      
      if (deviceTypes.error) {
        throw new Error(deviceTypes.error);
      }
      
      this.deviceTypes = Array.isArray(deviceTypes) ? deviceTypes : [];
    } catch (error) {
      console.error('Error loading device types:', error);
      this.showToast('Failed to load device types: ' + error.message, 'error');
      this.deviceTypes = [];
    }
  }

  async loadSubnets() {
    try {
      const response = await fetch('api/subnets.php');
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const subnets = await response.json();
      
      if (subnets.error) {
        throw new Error(subnets.error);
      }
      
      this.subnets = Array.isArray(subnets) ? subnets : [];
    } catch (error) {
      console.error('Error loading subnets:', error);
      this.showToast('Failed to load subnets: ' + error.message, 'error');
      this.subnets = [];
    }
  }

  initializeDataTable() {
    // Check if jQuery and DataTables are available
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
      console.error('jQuery or DataTables not loaded');
      this.showToast('DataTables library not loaded', 'error');
      return;
    }

    // Ensure table element exists
    const tableElement = document.getElementById('ip-table');
    if (!tableElement) {
      console.error('Table element not found');
      return;
    }

    // Destroy existing instance
    if (this.dataTable) {
      try {
        this.dataTable.destroy();
        this.dataTable = null;
      } catch (e) {
        console.warn('Error destroying DataTable:', e);
      }
    }

    // Wait a moment for DOM to be ready
    setTimeout(() => {
      try {
        this.dataTable = $('#ip-table').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: 'api/ips_datatable.php',
            type: 'POST',
            data: (d) => {
              d.branch_id = this.currentBranch;
              d.network = this.currentNetwork || '';
              d.subnet_id = this.currentSubnetId || '';
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
              render: (data) => `<strong>${this.escapeHtml(data)}</strong>`
            },
            { 
              data: 'device_name',
              render: (data) => this.escapeHtml(data)
            },
            { 
              data: 'device_type',
              render: (data) => `<span class="badge bg-primary">${this.escapeHtml(data)}</span>`
            },
            { 
              data: 'subnet_mask',
              render: (data) => this.escapeHtml(data)
            },
            { 
              data: 'description',
              render: (data) => data ? this.escapeHtml(data) : '<em class="text-muted">No description</em>'
            },
            {
              data: null,
              orderable: false,
              searchable: false,
              render: (data, type, row) => {
                return `
                  <div class="action-buttons">
                    <button class="btn btn-warning btn-sm" onclick="ipManager.showEditModal(${row.id})" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="ipManager.confirmDelete(${row.id})" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                `;
              }
            }
          ],
          order: [[0, 'asc']],
          pageLength: 10,
          lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
          language: {
            emptyTable: "No IP addresses found. Click 'Add New IP' to add the first IP address.",
            zeroRecords: "No matching IP addresses found",
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            loadingRecords: 'Loading...',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            paginate: {
              first: 'First',
              last: 'Last',
              next: 'Next',
              previous: 'Previous'
            }
          },
          dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
          responsive: true,
          autoWidth: false,
          drawCallback: function() {
            // Ensure tooltips work after table redraw
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(el => {
              if (el.title && !el.dataset.bsToggle) {
                el.dataset.bsToggle = 'tooltip';
              }
            });
          }
        });
      } catch (error) {
        console.error('Error initializing DataTable:', error);
        this.showToast('Failed to initialize table', 'error');
      }
    }, 100);
  }

  escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  validateIP(ip) {
    if (!ip) return false;
    
    const parts = ip.split('.');
    if (parts.length !== 4) return false;
    
    return parts.every(part => {
      const num = parseInt(part, 10);
      return num >= 0 && num <= 255 && part === num.toString();
    });
  }

  showAddModal() {
    if (!this.currentBranch) {
      this.showToast('Please select a branch first', 'error');
      return;
    }

    const modalTitle = document.getElementById('modal-title');
    const ipForm = document.getElementById('ip-form');
    const ipId = document.getElementById('ip-id');
    const ipAddress = document.getElementById('ip-address');

    if (modalTitle) modalTitle.textContent = 'Add New IP Address';
    if (ipForm) ipForm.reset();
    if (ipId) ipId.value = '';

    // Pre-fill IP address with network if available
    if (ipAddress && this.currentNetwork) {
      const networkPrefix = this.currentNetwork.substring(0, this.currentNetwork.lastIndexOf('.'));
      ipAddress.value = networkPrefix + '.';
    }

    this.populateDeviceTypeSelect();
    this.populateSubnetSelect(this.currentSubnetId);

    const modalElement = document.getElementById('ip-modal');
    if (modalElement) {
      const modal = new bootstrap.Modal(modalElement);
      
      // Focus on IP input after modal is shown
      modalElement.addEventListener('shown.bs.modal', function focusHandler() {
        const ipInput = document.getElementById('ip-address');
        if (ipInput) {
          ipInput.focus();
          if (ipInput.value) {
            ipInput.setSelectionRange(ipInput.value.length, ipInput.value.length);
          }
        }
        // Remove event listener after first use
        modalElement.removeEventListener('shown.bs.modal', focusHandler);
      });
      
      modal.show();
    }
  }

  async showEditModal(ipId) {
    if (!ipId) {
      this.showToast('Invalid IP ID', 'error');
      return;
    }

    try {
      // Fetch the IP data
      const response = await fetch(`api/ips.php?branch_id=${this.currentBranch}&id=${ipId}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();
      
      if (result.error) {
        throw new Error(result.error);
      }
      
      // Find the IP in the current data
      let ip = null;
      if (result.ips && Array.isArray(result.ips)) {
        ip = result.ips.find(item => item.id == ipId);
      }
      
      if (!ip && this.dataTable) {
        // Try to get from DataTable data
        const data = this.dataTable.rows().data().toArray();
        ip = data.find(item => item.id == ipId);
      }
      
      if (!ip) {
        throw new Error('IP address not found');
      }
      
      const modalTitle = document.getElementById('modal-title');
      const ipIdInput = document.getElementById('ip-id');
      const ipAddress = document.getElementById('ip-address');
      const deviceName = document.getElementById('device-name');
      const description = document.getElementById('description');

      if (modalTitle) modalTitle.textContent = 'Edit IP Address';
      if (ipIdInput) ipIdInput.value = ip.id;
      if (ipAddress) ipAddress.value = ip.ip_address;
      if (deviceName) deviceName.value = ip.device_name;
      if (description) description.value = ip.description || '';

      this.populateDeviceTypeSelect(ip.device_type_id);
      this.populateSubnetSelect(ip.subnet_id);

      const modalElement = document.getElementById('ip-modal');
      if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      }
    } catch (error) {
      console.error('Error loading IP for edit:', error);
      this.showToast('Failed to load IP data: ' + error.message, 'error');
    }
  }

  populateDeviceTypeSelect(selectedId = null) {
    const select = document.getElementById('device-type');
    if (!select) return;
    
    select.innerHTML = '<option value="">Select Device Type</option>';

    if (!Array.isArray(this.deviceTypes) || this.deviceTypes.length === 0) {
      const option = document.createElement('option');
      option.textContent = 'No device types available';
      option.disabled = true;
      select.appendChild(option);
      return;
    }

    this.deviceTypes.forEach(type => {
      const option = document.createElement('option');
      option.value = type.id;
      option.textContent = type.name;
      if (selectedId && type.id == selectedId) {
        option.selected = true;
      }
      select.appendChild(option);
    });
  }

  populateSubnetSelect(selectedId = null) {
    const select = document.getElementById('subnet');
    if (!select) return;
    
    select.innerHTML = '<option value="">Select Subnet</option>';

    if (!Array.isArray(this.subnets) || this.subnets.length === 0) {
      const option = document.createElement('option');
      option.textContent = 'No subnets available';
      option.disabled = true;
      select.appendChild(option);
      return;
    }

    this.subnets.forEach(subnet => {
      const option = document.createElement('option');
      option.value = subnet.id;
      option.textContent = `/${subnet.prefix} (${subnet.subnet_mask})`;
      if (selectedId && subnet.id == selectedId) {
        option.selected = true;
      }
      select.appendChild(option);
    });
  }

  async handleFormSubmit() {
    const form = document.getElementById('ip-form');
    if (!form) return;
    
    const formData = new FormData(form);

    const ipData = {
      ip_address: formData.get('ip_address')?.trim(),
      device_name: formData.get('device_name')?.trim(),
      device_type_id: formData.get('device_type_id'),
      subnet_id: formData.get('subnet_id'),
      description: formData.get('description')?.trim() || '',
      branch_id: this.currentBranch,
    };

    // Validate required fields
    if (!ipData.ip_address || !ipData.device_name || !ipData.device_type_id || !ipData.subnet_id) {
      this.showToast('Please fill in all required fields', 'error');
      return;
    }

    // Validate IP address format
    if (!this.validateIP(ipData.ip_address)) {
      this.showToast('Please enter a valid IP address', 'error');
      return;
    }

    const ipId = document.getElementById('ip-id')?.value;
    const isEdit = ipId !== '';

    try {
      let response;
      if (isEdit) {
        ipData.id = parseInt(ipId);
        response = await fetch('api/ips.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(ipData),
        });
      } else {
        response = await fetch('api/ips.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(ipData),
        });
      }

      const result = await response.json();

      if (response.ok) {
        this.showToast(result.message || 'Operation successful', 'success');
        
        // Close modal
        const modalElement = document.getElementById('ip-modal');
        if (modalElement) {
          const modalInstance = bootstrap.Modal.getInstance(modalElement);
          if (modalInstance) {
            modalInstance.hide();
          }
        }
        
        // Reload table
        if (this.dataTable) {
          this.dataTable.ajax.reload(null, false);
        }
        
        // Refresh dropdowns
        this.loadBranches();
        if (this.currentBranch) {
          this.loadNetworks(this.currentBranch);
        }
      } else {
        this.showToast(result.error || 'Operation failed', 'error');
      }
    } catch (error) {
      console.error('Error saving IP:', error);
      this.showToast('Failed to save IP address: ' + error.message, 'error');
    }
  }

  confirmDelete(ipId) {
    if (!ipId) {
      this.showToast('Invalid IP ID', 'error');
      return;
    }
    
    if (confirm('Are you sure you want to delete this IP address? This action cannot be undone.')) {
      this.deleteIP(ipId);
    }
  }

  async deleteIP(ipId) {
    try {
      const response = await fetch(`api/ips.php?id=${ipId}`, {
        method: 'DELETE',
      });

      const result = await response.json();

      if (response.ok) {
        this.showToast(result.message || 'IP deleted successfully', 'success');
        
        // Reload table
        if (this.dataTable) {
          this.dataTable.ajax.reload(null, false);
        }
        
        // Refresh dropdowns
        this.loadBranches();
        if (this.currentBranch) {
          this.loadNetworks(this.currentBranch);
        }
      } else {
        this.showToast(result.error || 'Delete failed', 'error');
      }
    } catch (error) {
      console.error('Error deleting IP:', error);
      this.showToast('Failed to delete IP address: ' + error.message, 'error');
    }
  }

  showToast(message, type = 'success') {
    const toastHtml = `
      <div class="toast ${type}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="toast-body">
          <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
          ${this.escapeHtml(message)}
        </div>
      </div>
    `;

    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container position-fixed top-0 end-0 p-3';
      document.body.appendChild(container);
    }

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = toastHtml;
    const toastElement = tempDiv.firstElementChild;
    
    container.appendChild(toastElement);
    
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
      const toast = new bootstrap.Toast(toastElement);
      toast.show();

      toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
      });
    } else {
      // Fallback if Bootstrap is not loaded
      toastElement.style.display = 'block';
      setTimeout(() => {
        toastElement.remove();
      }, 3000);
    }
  }
}

// Initialize the application
let ipManager;
document.addEventListener('DOMContentLoaded', () => {
  ipManager = new IPManagement();
});