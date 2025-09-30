class BulkIPInsert {
  constructor() {
    this.branches = [];
    this.deviceTypes = [];
    this.subnets = [];
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
      const savedTheme = localStorage.getItem('ipms-theme');
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
      localStorage.setItem('ipms-theme', theme);
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

    // IP range inputs - update count
    const startIp = document.getElementById('start-ip');
    const endIp = document.getElementById('end-ip');
    
    if (startIp) {
      startIp.addEventListener('input', () => this.updateIPCount());
    }
    
    if (endIp) {
      endIp.addEventListener('input', () => this.updateIPCount());
    }

    // Preview button
    const previewBtn = document.getElementById('preview-btn');
    if (previewBtn) {
      previewBtn.addEventListener('click', () => this.showPreview());
    }

    // Form submission
    const form = document.getElementById('bulk-form');
    if (form) {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        this.handleSubmit();
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
      
      this.branches = Array.isArray(branches) ? branches : [];
      this.renderBranchDropdown();
    } catch (error) {
      console.error('Error loading branches:', error);
      this.showToast('Failed to load branches: ' + error.message, 'error');
    }
  }

  renderBranchDropdown() {
    const select = document.getElementById('branch-select');
    if (!select) return;
    
    select.innerHTML = '<option value="">Choose a branch...</option>';
    
    this.branches.forEach(branch => {
      const option = document.createElement('option');
      option.value = branch.id;
      option.textContent = branch.name;
      select.appendChild(option);
    });
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
      this.renderDeviceTypeDropdown();
    } catch (error) {
      console.error('Error loading device types:', error);
      this.showToast('Failed to load device types: ' + error.message, 'error');
    }
  }

  renderDeviceTypeDropdown() {
    const select = document.getElementById('device-type-select');
    if (!select) return;
    
    select.innerHTML = '<option value="">Select device type...</option>';
    
    this.deviceTypes.forEach(type => {
      const option = document.createElement('option');
      option.value = type.id;
      option.textContent = type.name;
      select.appendChild(option);
    });
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
      this.renderSubnetDropdown();
    } catch (error) {
      console.error('Error loading subnets:', error);
      this.showToast('Failed to load subnets: ' + error.message, 'error');
    }
  }

  renderSubnetDropdown() {
    const select = document.getElementById('subnet-select');
    if (!select) return;
    
    select.innerHTML = '<option value="">Select</option>';
    
    this.subnets.forEach(subnet => {
      const option = document.createElement('option');
      option.value = subnet.id;
      option.textContent = `/${subnet.prefix} (${subnet.subnet_mask})`;
      select.appendChild(option);
    });
  }

  updateIPCount() {
    const start = parseInt(document.getElementById('start-ip')?.value) || 0;
    const end = parseInt(document.getElementById('end-ip')?.value) || 0;
    
    const count = (start > 0 && end > 0 && end >= start) ? (end - start + 1) : 0;
    
    const countElement = document.getElementById('ip-count');
    if (countElement) {
      countElement.textContent = count;
    }
  }

  showPreview() {
    const form = document.getElementById('bulk-form');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const networkPrefix = document.getElementById('network-prefix')?.value;
    const startIp = parseInt(document.getElementById('start-ip')?.value);
    const endIp = parseInt(document.getElementById('end-ip')?.value);
    const devicePrefix = document.getElementById('device-prefix')?.value;

    const previewSection = document.getElementById('preview-section');
    const previewContent = document.getElementById('preview-content');
    
    if (!previewSection || !previewContent) return;

    let html = '';
    const maxPreview = Math.min(5, endIp - startIp + 1);
    
    for (let i = 0; i < maxPreview; i++) {
      const ipNum = startIp + i;
      const ipAddress = `${networkPrefix}.${ipNum}`;
      const deviceName = `${devicePrefix}-${ipNum}`;
      
      html += `
        <div class="preview-item">
          <strong>${ipAddress}</strong> - ${this.escapeHtml(deviceName)}
        </div>
      `;
    }
    
    if (endIp - startIp + 1 > 5) {
      html += `
        <div class="preview-item text-muted">
          <em>... and ${endIp - startIp + 1 - 5} more</em>
        </div>
      `;
    }

    previewContent.innerHTML = html;
    previewSection.style.display = 'block';
    
    // Scroll to preview
    previewSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  async handleSubmit() {
    const form = document.getElementById('bulk-form');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const data = {
      branch_id: document.getElementById('branch-select')?.value,
      network_prefix: document.getElementById('network-prefix')?.value,
      start_ip: parseInt(document.getElementById('start-ip')?.value),
      end_ip: parseInt(document.getElementById('end-ip')?.value),
      subnet_id: document.getElementById('subnet-select')?.value,
      device_type_id: document.getElementById('device-type-select')?.value,
      device_name_prefix: document.getElementById('device-prefix')?.value,
      description: document.getElementById('description')?.value,
      skip_existing: document.getElementById('skip-existing')?.checked
    };

    // Confirm action
    const count = data.end_ip - data.start_ip + 1;
    if (!confirm(`Are you sure you want to insert ${count} IP addresses?\n\nNetwork: ${data.network_prefix}.${data.start_ip} - ${data.network_prefix}.${data.end_ip}`)) {
      return;
    }

    // Show progress
    const progressContainer = document.getElementById('progress-container');
    const submitBtn = document.getElementById('submit-btn');
    const resultSummary = document.getElementById('result-summary');
    
    if (progressContainer) progressContainer.style.display = 'block';
    if (submitBtn) submitBtn.disabled = true;
    if (resultSummary) {
      resultSummary.classList.remove('show', 'alert-success', 'alert-danger', 'alert-warning');
      resultSummary.innerHTML = '';
    }

    try {
      const response = await fetch('api/bulk_ips.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (progressContainer) progressContainer.style.display = 'none';
      if (submitBtn) submitBtn.disabled = false;

      if (response.ok && result.success) {
        this.showResult(result, 'success');
        
        // Reset form after successful insert
        setTimeout(() => {
          if (confirm('IP addresses inserted successfully! Do you want to insert another range?')) {
            form.reset();
            document.getElementById('preview-section').style.display = 'none';
            this.updateIPCount();
          } else {
            window.location.href = 'index.php';
          }
        }, 2000);
      } else {
        this.showResult(result, 'error');
      }
    } catch (error) {
      console.error('Error inserting IPs:', error);
      
      if (progressContainer) progressContainer.style.display = 'none';
      if (submitBtn) submitBtn.disabled = false;
      
      this.showToast('Failed to insert IP addresses: ' + error.message, 'error');
    }
  }

  showResult(result, type) {
    const resultSummary = document.getElementById('result-summary');
    if (!resultSummary) return;

    let alertClass = 'alert-info';
    let icon = 'fa-info-circle';
    
    if (type === 'success') {
      alertClass = 'alert-success';
      icon = 'fa-check-circle';
    } else if (type === 'error') {
      alertClass = 'alert-danger';
      icon = 'fa-exclamation-circle';
    } else if (type === 'warning') {
      alertClass = 'alert-warning';
      icon = 'fa-exclamation-triangle';
    }

    let html = `
      <h5><i class="fas ${icon} me-2"></i>${result.message || 'Operation completed'}</h5>
      <hr>
      <div class="row text-center">
        <div class="col-md-4">
          <strong class="text-success fs-3">${result.inserted || 0}</strong>
          <div>Inserted</div>
        </div>
        <div class="col-md-4">
          <strong class="text-warning fs-3">${result.skipped || 0}</strong>
          <div>Skipped</div>
        </div>
        <div class="col-md-4">
          <strong class="text-info fs-3">${result.total_processed || 0}</strong>
          <div>Total Processed</div>
        </div>
      </div>
    `;

    if (result.errors && result.errors.length > 0) {
      html += `
        <hr>
        <div class="mt-3">
          <strong>Errors (${result.errors.length}):</strong>
          <ul class="mt-2 mb-0">
      `;
      result.errors.slice(0, 10).forEach(error => {
        html += `<li>${this.escapeHtml(error)}</li>`;
      });
      if (result.errors.length > 10) {
        html += `<li><em>... and ${result.errors.length - 10} more errors</em></li>`;
      }
      html += `</ul></div>`;
    }

    resultSummary.innerHTML = html;
    resultSummary.classList.add('show', alertClass);
    resultSummary.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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

  showToast(message, type = 'success') {
    const toastHtml = `
      <div class="toast ${type}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="4000">
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
      toastElement.style.display = 'block';
      setTimeout(() => {
        toastElement.remove();
      }, 4000);
    }
  }
}

// Initialize the application
let bulkIPManager;
document.addEventListener('DOMContentLoaded', () => {
  bulkIPManager = new BulkIPInsert();
});