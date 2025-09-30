class BulkIPInsert {
  constructor() {
    this.branches = [];
    this.deviceTypes = [];
    this.subnets = [];
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

    // IP range inputs - update count
    const startIp = document.getElementById('start-ip');
    const endIp = document.getElementById('end-ip');
    
    if (startIp) {
      startIp.addEventListener('input', () => this.updateIPCount());
      startIp.addEventListener('blur', () => this.validateAndFormatIP(startIp));
    }
    
    if (endIp) {
      endIp.addEventListener('input', () => this.updateIPCount());
      endIp.addEventListener('blur', () => this.validateAndFormatIP(endIp));
    }

    // Preview button
    const previewBtn = document.getElementById('preview-btn');
    if (previewBtn) {
      previewBtn.addEventListener('click', () => this.showPreview());
    }

    // Calculate button
    const calculateBtn = document.getElementById('calculate-btn');
    if (calculateBtn) {
      calculateBtn.addEventListener('click', () => this.calculateRange());
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
    
    select.innerHTML = '<option value="">Select subnet...</option>';
    
    this.subnets.forEach(subnet => {
      const option = document.createElement('option');
      option.value = subnet.id;
      option.textContent = `/${subnet.prefix} (${subnet.subnet_mask})`;
      select.appendChild(option);
    });
  }

  validateAndFormatIP(input) {
    const value = input.value.trim();
    if (!value) return;
    
    if (!this.isValidIP(value)) {
      input.classList.add('is-invalid');
      this.showToast('Invalid IP address format', 'error');
    } else {
      input.classList.remove('is-invalid');
    }
  }

  isValidIP(ip) {
    if (!ip) return false;
    
    const parts = ip.split('.');
    if (parts.length !== 4) return false;
    
    return parts.every(part => {
      const num = parseInt(part, 10);
      return num >= 0 && num <= 255 && part === num.toString();
    });
  }

  ip2long(ip) {
    if (!this.isValidIP(ip)) return false;
    
    const parts = ip.split('.');
    return (parseInt(parts[0]) * 16777216) + 
           (parseInt(parts[1]) * 65536) + 
           (parseInt(parts[2]) * 256) + 
           parseInt(parts[3]);
  }

  long2ip(long) {
    const part1 = long & 255;
    const part2 = ((long >> 8) & 255);
    const part3 = ((long >> 16) & 255);
    const part4 = ((long >> 24) & 255);
    
    return part4 + '.' + part3 + '.' + part2 + '.' + part1;
  }

  updateIPCount() {
    const startIpStr = document.getElementById('start-ip')?.value.trim();
    const endIpStr = document.getElementById('end-ip')?.value.trim();
    
    if (!startIpStr || !endIpStr) return;
    
    const startLong = this.ip2long(startIpStr);
    const endLong = this.ip2long(endIpStr);
    
    if (startLong === false || endLong === false) return;
    
    const count = (startLong <= endLong) ? (endLong - startLong + 1) : 0;
    
    const countElement = document.getElementById('ip-count');
    if (countElement) {
      countElement.textContent = count.toLocaleString();
    }
  }

  calculateRange() {
    const startIpStr = document.getElementById('start-ip')?.value.trim();
    const endIpStr = document.getElementById('end-ip')?.value.trim();

    if (!this.isValidIP(startIpStr) || !this.isValidIP(endIpStr)) {
      this.showToast('Please enter valid IP addresses', 'error');
      document.getElementById('range-calc')?.style.setProperty('display', 'none');
      return;
    }

    const startLong = this.ip2long(startIpStr);
    const endLong = this.ip2long(endIpStr);
    const total = endLong - startLong + 1;

    if (total < 1) {
      this.showToast('Start IP must be less than or equal to End IP', 'error');
      document.getElementById('range-calc')?.style.setProperty('display', 'none');
      return;
    }

    const batchSize = 1000;
    const batches = Math.ceil(total / batchSize);
    const estSeconds = Math.ceil(total / 500); // Estimate ~500 IPs per second
    const estTime = this.formatTime(estSeconds);

    const totalIpsEl = document.getElementById('total-ips');
    const batchCountEl = document.getElementById('batch-count');
    const estTimeEl = document.getElementById('est-time');
    const rangeCalcEl = document.getElementById('range-calc');

    if (totalIpsEl) totalIpsEl.textContent = total.toLocaleString();
    if (batchCountEl) batchCountEl.textContent = batches.toLocaleString();
    if (estTimeEl) estTimeEl.textContent = estTime;
    if (rangeCalcEl) rangeCalcEl.style.display = 'block';
  }

  formatTime(seconds) {
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.ceil(seconds / 60)}m`;
    const hours = Math.floor(seconds / 3600);
    const mins = Math.ceil((seconds % 3600) / 60);
    return `${hours}h ${mins}m`;
  }

  showPreview() {
    const form = document.getElementById('bulk-form');
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const startIpStr = document.getElementById('start-ip')?.value.trim();
    const endIpStr = document.getElementById('end-ip')?.value.trim();
    const devicePrefix = document.getElementById('device-prefix')?.value;

    if (!this.isValidIP(startIpStr) || !this.isValidIP(endIpStr)) {
      this.showToast('Please enter valid IP addresses', 'error');
      return;
    }

    const startLong = this.ip2long(startIpStr);
    const endLong = this.ip2long(endIpStr);

    if (startLong > endLong) {
      this.showToast('Start IP must be less than or equal to End IP', 'error');
      return;
    }

    const previewSection = document.getElementById('preview-section');
    const previewContent = document.getElementById('preview-content');
    
    if (!previewSection || !previewContent) return;

    let html = '';
    const total = endLong - startLong + 1;
    const maxPreview = Math.min(5, total);
    
    for (let i = 0; i < maxPreview; i++) {
      const currentLong = startLong + i;
      const ipAddress = this.long2ip(currentLong);
      const ipSuffix = ipAddress.replace(/\./g, '-');
      const deviceName = `${devicePrefix}-${ipSuffix}`;
      
      html += `
        <div class="preview-item" style="padding: 0.5rem; border-bottom: 1px solid var(--border-color);">
          <strong>${ipAddress}</strong> - ${this.escapeHtml(deviceName)}
        </div>
      `;
    }
    
    if (total > 5) {
      html += `
        <div class="preview-item text-muted" style="padding: 0.5rem;">
          <em>... and ${(total - 5).toLocaleString()} more</em>
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

    const startIpStr = document.getElementById('start-ip')?.value.trim();
    const endIpStr = document.getElementById('end-ip')?.value.trim();

    if (!this.isValidIP(startIpStr) || !this.isValidIP(endIpStr)) {
      this.showToast('Please enter valid IP addresses', 'error');
      return;
    }

    const startLong = this.ip2long(startIpStr);
    const endLong = this.ip2long(endIpStr);
    const total = endLong - startLong + 1;

    if (startLong > endLong) {
      this.showToast('Start IP must be less than or equal to End IP', 'error');
      return;
    }

    const data = {
      branch_id: document.getElementById('branch-select')?.value,
      start_ip: startIpStr,
      end_ip: endIpStr,
      subnet_id: document.getElementById('subnet-select')?.value,
      device_type_id: document.getElementById('device-type-select')?.value,
      device_name_prefix: document.getElementById('device-prefix')?.value,
      description: document.getElementById('description')?.value,
      skip_existing: document.getElementById('skip-existing')?.checked,
      batch_size: 1000
    };

    // Confirm action
    if (!confirm(`Are you sure you want to insert ${total.toLocaleString()} IP addresses?\n\nRange: ${data.start_ip} → ${data.end_ip}\n\nThis may take several minutes for large ranges.`)) {
      return;
    }

    // Show progress
    const progressContainer = document.getElementById('progress-container');
    const submitBtn = document.getElementById('submit-btn');
    const resultContainer = document.getElementById('result-container');
    
    if (progressContainer) progressContainer.style.display = 'block';
    if (submitBtn) submitBtn.disabled = true;
    if (resultContainer) {
      resultContainer.style.display = 'none';
    }

    try {
      const response = await fetch('api/bulk_ips_enhanced.php', {
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
        
        // Ask user what to do next
        setTimeout(() => {
          if (confirm('IP addresses inserted successfully! Do you want to insert another range?')) {
            form.reset();
            if (document.getElementById('preview-section')) {
              document.getElementById('preview-section').style.display = 'none';
            }
            if (document.getElementById('range-calc')) {
              document.getElementById('range-calc').style.display = 'none';
            }
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
      this.showResult({
        success: false,
        message: 'Operation failed: ' + error.message,
        inserted: 0,
        skipped: 0,
        total_processed: 0,
        errors: [error.message]
      }, 'error');
    }
  }

  showResult(result, type) {
    const resultContainer = document.getElementById('result-container');
    const resultContent = document.getElementById('result-content');
    
    if (!resultContainer || !resultContent) return;

    let headerClass = 'bg-info';
    let icon = 'fa-info-circle';
    
    if (type === 'success') {
      headerClass = 'bg-success';
      icon = 'fa-check-circle';
    } else if (type === 'error') {
      headerClass = 'bg-danger';
      icon = 'fa-exclamation-circle';
    } else if (type === 'warning') {
      headerClass = 'bg-warning';
      icon = 'fa-exclamation-triangle';
    }

    let html = `
      <div class="row text-center mb-3">
        <div class="col-md-3">
          <div class="stat-box">
            <div class="number text-success">${(result.inserted || 0).toLocaleString()}</div>
            <div class="label">Inserted</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-box">
            <div class="number text-warning">${(result.skipped || 0).toLocaleString()}</div>
            <div class="label">Skipped</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-box">
            <div class="number text-info">${(result.total_processed || 0).toLocaleString()}</div>
            <div class="label">Total Processed</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-box">
            <div class="number text-danger">${(result.errors?.length || 0).toLocaleString()}</div>
            <div class="label">Errors</div>
          </div>
        </div>
      </div>
      <div class="alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'}">
        <i class="fas ${icon} me-2"></i>
        ${this.escapeHtml(result.message || 'Operation completed')}
      </div>
    `;

    if (result.start_ip && result.end_ip) {
      html += `
        <div class="text-muted mb-3">
          <small>
            <strong>Range:</strong> ${this.escapeHtml(result.start_ip)} → ${this.escapeHtml(result.end_ip)}
          </small>
        </div>
      `;
    }

    if (result.errors && result.errors.length > 0) {
      html += `
        <div class="alert alert-warning mt-3">
          <strong>Errors (${result.errors.length}):</strong>
          <ul class="mb-0 mt-2" style="max-height: 200px; overflow-y: auto;">
      `;
      result.errors.slice(0, 20).forEach(error => {
        html += `<li>${this.escapeHtml(error)}</li>`;
      });
      if (result.errors.length > 20) {
        html += `<li><em>... and ${result.errors.length - 20} more errors</em></li>`;
      }
      html += `</ul></div>`;
    }

    html += `
      <div class="mt-3 text-center">
        <button class="btn btn-primary me-2" onclick="location.reload()">
          <i class="fas fa-plus me-2"></i>Insert Another Range
        </button>
        <a href="index.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left me-2"></i>Back to Main
        </a>
      </div>
    `;

    resultContent.innerHTML = html;
    
    // Update header class
    const cardHeader = resultContainer.querySelector('.card-header');
    if (cardHeader) {
      cardHeader.className = `card-header ${headerClass}`;
    }
    
    resultContainer.style.display = 'block';
    resultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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