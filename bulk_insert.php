<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Bulk IP Insert - IP Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1e3a8a;
            --secondary-blue: #3b82f6;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-muted: #94a3b8;
            --text-primary: #e2e8f0;
        }

        /* Light mode variables */
        [data-theme="light"] {
            --primary-blue: #1e3a8a;
            --secondary-blue: #3b82f6;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --dark-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-muted: #64748b;
            --text-primary: #0f172a;
        }

        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--card-bg) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .navbar {
            background: var(--primary-blue) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 50px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .theme-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border-color: var(--secondary-blue);
        }

        .theme-toggle i {
            font-size: 1.2rem;
            color: var(--text-primary);
            transition: transform 0.3s ease;
        }

        .theme-toggle:hover i {
            transform: rotate(20deg);
        }

        .main-container {
            padding: 2rem 0;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.5rem;
            transition: background 0.3s ease;
        }

        [data-theme="light"] .card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: var(--primary-blue);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background: var(--dark-bg);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        [data-theme="light"] .form-control,
        [data-theme="light"] .form-select {
            background: #ffffff;
        }

        .form-control:focus, .form-select:focus {
            background: var(--dark-bg);
            border-color: var(--secondary-blue);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            outline: none;
        }

        [data-theme="light"] .form-control:focus,
        [data-theme="light"] .form-select:focus {
            background: #ffffff;
        }

        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .form-text {
            color: var(--text-muted);
        }

        .form-check-input {
            background-color: var(--dark-bg);
            border: 2px solid var(--border-color);
        }

        .form-check-input:checked {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }

        .form-check-label {
            color: var(--text-primary);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .alert {
            border-radius: 10px;
            border: 1px solid var(--border-color);
            padding: 1rem;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--secondary-blue);
            color: var(--text-primary);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success-green);
            color: var(--text-primary);
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: var(--warning-orange);
            color: var(--text-primary);
        }

        .ip-range-calc {
            background: var(--dark-bg);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .stat-box {
            text-align: center;
            padding: 1rem;
            background: var(--dark-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin: 0.5rem 0;
        }

        .stat-box .number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondary-blue);
        }

        .stat-box .label {
            font-size: 0.9rem;
            color: var(--text-primary);
            opacity: 0.8;
        }

        .progress {
            height: 30px;
            background: var(--dark-bg);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .cidr-example {
            background: var(--dark-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--secondary-blue);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }

        .cidr-example h6 {
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .theme-toggle {
                top: 70px;
                right: 10px;
                padding: 0.4rem 0.8rem;
            }

            .theme-toggle i {
                font-size: 1rem;
            }

            .main-container {
                padding: 1rem 0;
            }

            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="theme-toggle" title="Toggle theme">
        <i class="fas fa-sun"></i>
    </button>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-network-wired me-2"></i>
                IP Management System
            </a>
            <div class="navbar-text text-white mx-auto d-none d-md-block">
                <i class="fas fa-layer-group me-2"></i>
                Enhanced Bulk IP Insert
            </div>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Main
                </a>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>
                            Bulk IP Address Insert - Full Range Support
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This enhanced version supports full IP ranges across all octets (e.g., 172.16.0.0 to 172.31.255.255).
                            Large ranges will be processed in batches to ensure reliability.
                        </div>

                        <form id="bulk-form">
                            <!-- Branch Selection -->
                            <div class="mb-4">
                                <label for="branch-select" class="form-label">
                                    <i class="fas fa-building me-2"></i>
                                    Branch *
                                </label>
                                <select class="form-select" id="branch-select" required>
                                    <option value="">Choose a branch...</option>
                                </select>
                            </div>

                            <!-- IP Range -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-network-wired me-2"></i>IP Address Range</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="start-ip" class="form-label">Start IP Address *</label>
                                            <input type="text" class="form-control" id="start-ip" 
                                                   placeholder="172.16.0.0" required>
                                            <div class="form-text">Full IPv4 address (e.g., 172.16.0.0)</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="end-ip" class="form-label">End IP Address *</label>
                                            <input type="text" class="form-control" id="end-ip" 
                                                   placeholder="172.31.255.255" required>
                                            <div class="form-text">Full IPv4 address (e.g., 172.31.255.255)</div>
                                        </div>
                                    </div>

                                    <div class="cidr-example">
                                        <h6><i class="fas fa-book me-2"></i>Common CIDR Examples:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>172.16.0.0/12:</strong> 172.16.0.0 → 172.31.255.255 (1,048,576 IPs)
                                            </div>
                                            <div class="col-md-6">
                                                <strong>10.0.0.0/8:</strong> 10.0.0.0 → 10.255.255.255 (16,777,216 IPs)
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <strong>192.168.0.0/16:</strong> 192.168.0.0 → 192.168.255.255 (65,536 IPs)
                                            </div>
                                            <div class="col-md-6">
                                                <strong>192.168.1.0/24:</strong> 192.168.1.0 → 192.168.1.255 (256 IPs)
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ip-range-calc" id="range-calc" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stat-box">
                                                    <div class="number" id="total-ips">0</div>
                                                    <div class="label">Total IP Addresses</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="stat-box">
                                                    <div class="number" id="est-time">--</div>
                                                    <div class="label">Estimated Time</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="stat-box">
                                                    <div class="number" id="batch-count">0</div>
                                                    <div class="label">Batches</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuration -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuration</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="subnet-select" class="form-label">Subnet Mask *</label>
                                            <select class="form-select" id="subnet-select" required>
                                                <option value="">Select subnet...</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="device-type-select" class="form-label">Device Type *</label>
                                            <select class="form-select" id="device-type-select" required>
                                                <option value="">Select device type...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="device-prefix" class="form-label">Device Name Prefix *</label>
                                        <input type="text" class="form-control" id="device-prefix" 
                                               value="Device" required>
                                        <div class="form-text">Devices will be named: Prefix-172-16-0-1, Prefix-172-16-0-2, etc.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" rows="2"
                                                  placeholder="Optional description">Bulk inserted IP range</textarea>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="skip-existing" checked>
                                        <label class="form-check-label" for="skip-existing">
                                            Skip existing IP addresses (recommended)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress -->
                            <div id="progress-container" style="display: none;">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5><i class="fas fa-spinner fa-spin me-2"></i>Processing...</h5>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                                 role="progressbar" style="width: 100%">
                                                Inserting IP addresses...
                                            </div>
                                        </div>
                                        <p class="text-center mt-2 mb-0">
                                            <small>Please wait while we process your request. This may take a few minutes for large ranges.</small>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Results -->
                            <div id="result-container" style="display: none;">
                                <div class="card">
                                    <div class="card-header bg-success">
                                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Results</h5>
                                    </div>
                                    <div class="card-body" id="result-content">
                                        <!-- Results will be inserted here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary" id="calculate-btn">
                                    <i class="fas fa-calculator me-2"></i>
                                    Calculate Range
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="submit-btn">
                                    <i class="fas fa-upload me-2"></i>
                                    Insert IP Addresses
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        class EnhancedBulkInsert {
            constructor() {
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
                $('#theme-toggle').on('click', () => this.toggleTheme());
                
                $('#calculate-btn').on('click', () => this.calculateRange());
                $('#start-ip, #end-ip').on('blur', () => this.calculateRange());
                $('#bulk-form').on('submit', (e) => {
                    e.preventDefault();
                    this.handleSubmit();
                });
            }

            async loadBranches() {
                try {
                    const response = await fetch('api/branches.php');
                    const branches = await response.json();
                    
                    const select = $('#branch-select');
                    branches.forEach(branch => {
                        select.append(`<option value="${branch.id}">${branch.name}</option>`);
                    });
                } catch (error) {
                    console.error('Error loading branches:', error);
                }
            }

            async loadDeviceTypes() {
                try {
                    const response = await fetch('api/device_types.php');
                    const types = await response.json();
                    
                    const select = $('#device-type-select');
                    types.forEach(type => {
                        select.append(`<option value="${type.id}">${type.name}</option>`);
                    });
                } catch (error) {
                    console.error('Error loading device types:', error);
                }
            }

            async loadSubnets() {
                try {
                    const response = await fetch('api/subnets.php');
                    const subnets = await response.json();
                    
                    const select = $('#subnet-select');
                    subnets.forEach(subnet => {
                        select.append(`<option value="${subnet.id}">/${subnet.prefix} (${subnet.subnet_mask})</option>`);
                    });
                } catch (error) {
                    console.error('Error loading subnets:', error);
                }
            }

            ip2long(ip) {
                const parts = ip.split('.');
                return (parseInt(parts[0]) << 24) + 
                       (parseInt(parts[1]) << 16) + 
                       (parseInt(parts[2]) << 8) + 
                       parseInt(parts[3]);
            }

            calculateRange() {
                const startIp = $('#start-ip').val().trim();
                const endIp = $('#end-ip').val().trim();

                if (!this.isValidIP(startIp) || !this.isValidIP(endIp)) {
                    $('#range-calc').hide();
                    return;
                }

                const startLong = this.ip2long(startIp);
                const endLong = this.ip2long(endIp);
                const total = endLong - startLong + 1;

                if (total < 1) {
                    $('#range-calc').hide();
                    return;
                }

                const batchSize = 1000;
                const batches = Math.ceil(total / batchSize);
                const estSeconds = Math.ceil(total / 500); // Estimate ~500 IPs per second
                const estTime = this.formatTime(estSeconds);

                $('#total-ips').text(total.toLocaleString());
                $('#batch-count').text(batches.toLocaleString());
                $('#est-time').text(estTime);
                $('#range-calc').show();
            }

            isValidIP(ip) {
                const regex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
                return regex.test(ip);
            }

            formatTime(seconds) {
                if (seconds < 60) return `${seconds}s`;
                if (seconds < 3600) return `${Math.ceil(seconds / 60)}m`;
                return `${Math.ceil(seconds / 3600)}h`;
            }

            async handleSubmit() {
                const form = $('#bulk-form')[0];
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const data = {
                    branch_id: $('#branch-select').val(),
                    start_ip: $('#start-ip').val().trim(),
                    end_ip: $('#end-ip').val().trim(),
                    subnet_id: $('#subnet-select').val(),
                    device_type_id: $('#device-type-select').val(),
                    device_name_prefix: $('#device-prefix').val(),
                    description: $('#description').val(),
                    skip_existing: $('#skip-existing').is(':checked'),
                    batch_size: 1000
                };

                const total = this.ip2long(data.end_ip) - this.ip2long(data.start_ip) + 1;

                if (!confirm(`Are you sure you want to insert ${total.toLocaleString()} IP addresses?\n\nRange: ${data.start_ip} → ${data.end_ip}\n\nThis operation may take several minutes.`)) {
                    return;
                }

                $('#progress-container').show();
                $('#submit-btn').prop('disabled', true);
                $('#result-container').hide();

                try {
                    const response = await fetch('api/bulk_ips_enhanced.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    $('#progress-container').hide();
                    $('#submit-btn').prop('disabled', false);

                    if (response.ok && result.success) {
                        this.showResults(result);
                    } else {
                        alert('Error: ' + (result.error || 'Unknown error occurred'));
                    }
                } catch (error) {
                    $('#progress-container').hide();
                    $('#submit-btn').prop('disabled', false);
                    alert('Failed to insert IP addresses: ' + error.message);
                }
            }

            showResults(result) {
                const html = `
                    <div class="row text-center mb-3">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="number text-success">${result.inserted.toLocaleString()}</div>
                                <div class="label">Inserted</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="number text-warning">${result.skipped.toLocaleString()}</div>
                                <div class="label">Skipped</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="number text-info">${result.total_processed.toLocaleString()}</div>
                                <div class="label">Total Processed</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="number text-danger">${result.errors.length}</div>
                                <div class="label">Errors</div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        ${result.message}
                    </div>
                    <div class="text-muted">
                        <small>
                            <strong>Range:</strong> ${result.start_ip} → ${result.end_ip}
                        </small>
                    </div>
                    ${result.errors.length > 0 ? `
                        <div class="alert alert-warning mt-3">
                            <strong>Errors (${result.errors.length}):</strong>
                            <ul class="mb-0 mt-2">
                                ${result.errors.slice(0, 10).map(e => `<li>${e}</li>`).join('')}
                                ${result.errors.length > 10 ? `<li><em>... and ${result.errors.length - 10} more</em></li>` : ''}
                            </ul>
                        </div>
                    ` : ''}
                    <div class="mt-3 text-center">
                        <button class="btn btn-primary me-2" onclick="location.reload()">
                            <i class="fas fa-plus me-2"></i>Insert Another Range
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Main
                        </a>
                    </div>
                `;

                $('#result-content').html(html);
                $('#result-container').show();
                $('#result-container')[0].scrollIntoView({ behavior: 'smooth' });
            }
        }

        $(document).ready(() => {
            new EnhancedBulkInsert();
        });
    </script>
</body>
</html>