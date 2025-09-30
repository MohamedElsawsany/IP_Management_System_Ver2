<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk IP Insert - IP Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <style>
        .preview-box {
            background: var(--dark-bg);
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .preview-item {
            padding: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .preview-item:last-child {
            border-bottom: none;
        }
        
        .info-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-card h6 {
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }
        
        .progress-container {
            display: none;
            margin-top: 1rem;
        }
        
        .result-summary {
            display: none;
            margin-top: 1rem;
        }
        
        .result-summary.show {
            display: block;
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
                Bulk IP Insert
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
            <div class="col-lg-8 mx-auto">
                <div class="selection-bar mb-4">
                    <div class="text-center mb-4">
                        <h2><i class="fas fa-layer-group me-2"></i>Bulk IP Address Insert</h2>
                        <p class="text-muted">Add multiple IP addresses at once to a network</p>
                    </div>

                    <form id="bulk-form">
                        <!-- Branch Selection -->
                        <div class="mb-3">
                            <label for="branch-select" class="form-label">
                                <i class="fas fa-building me-2"></i>
                                Branch *
                            </label>
                            <select class="form-select form-select-lg" id="branch-select" required>
                                <option value="">Choose a branch...</option>
                            </select>
                        </div>

                        <!-- Network Configuration -->
                        <div class="info-card">
                            <h6><i class="fas fa-network-wired me-2"></i>Network Configuration</h6>
                            
                            <div class="row">
                                <div class="col-md-9 mb-3">
                                    <label for="network-prefix" class="form-label">Network Prefix *</label>
                                    <input type="text" class="form-control" id="network-prefix" 
                                           placeholder="192.168.1" pattern="\d{1,3}\.\d{1,3}\.\d{1,3}" required>
                                    <div class="form-text">First three octets (e.g., 192.168.1)</div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="subnet-select" class="form-label">Subnet *</label>
                                    <select class="form-select" id="subnet-select" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- IP Range -->
                        <div class="info-card">
                            <h6><i class="fas fa-arrows-alt-h me-2"></i>IP Address Range</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start-ip" class="form-label">Start IP (Last Octet) *</label>
                                    <input type="number" class="form-control" id="start-ip" 
                                           min="1" max="254" value="1" required>
                                    <div class="form-text">Range: 1-254</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end-ip" class="form-label">End IP (Last Octet) *</label>
                                    <input type="number" class="form-control" id="end-ip" 
                                           min="1" max="254" value="254" required>
                                    <div class="form-text">Range: 1-254</div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong id="ip-count">0</strong> IP addresses will be created
                            </div>
                        </div>

                        <!-- Device Configuration -->
                        <div class="info-card">
                            <h6><i class="fas fa-desktop me-2"></i>Device Configuration</h6>
                            
                            <div class="mb-3">
                                <label for="device-type-select" class="form-label">Device Type *</label>
                                <select class="form-select" id="device-type-select" required>
                                    <option value="">Select device type...</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="device-prefix" class="form-label">Device Name Prefix *</label>
                                <input type="text" class="form-control" id="device-prefix" 
                                       value="Device" required>
                                <div class="form-text">Devices will be named: Prefix-1, Prefix-2, etc.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" rows="2"
                                          placeholder="Optional description for all IPs">Bulk inserted IP range</textarea>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="info-card">
                            <h6><i class="fas fa-cog me-2"></i>Options</h6>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="skip-existing" checked>
                                <label class="form-check-label" for="skip-existing">
                                    Skip existing IP addresses (recommended)
                                </label>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="info-card" id="preview-section" style="display: none;">
                            <h6><i class="fas fa-eye me-2"></i>Preview (First 5 IPs)</h6>
                            <div class="preview-box" id="preview-content">
                                <!-- Preview will be generated here -->
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="progress-container" id="progress-container">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     id="progress-bar" role="progressbar" style="width: 0%">
                                    Processing...
                                </div>
                            </div>
                        </div>

                        <!-- Result Summary -->
                        <div class="result-summary alert" id="result-summary">
                            <!-- Results will be shown here -->
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary btn-lg" id="preview-btn">
                                <i class="fas fa-eye me-2"></i>
                                Preview
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/bulk_insert.js"></script>
</body>
</html>