<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-network-wired me-2"></i>
                IP Management System
            </a>
        </div>
    </nav>

    <div class="container main-container">
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <h4 class="mb-4">
                    <i class="fas fa-building me-2"></i>
                    Select Branch
                </h4>
                <div id="branch-buttons">
                    <!-- Branch buttons will be loaded here -->
                </div>
            </div>

            <div class="col-lg-8 col-md-12">
                <div id="ip-content" style="display: none;">
                    <div class="actions-bar">
                        <h5>
                            <i class="fas fa-server me-2"></i>
                            IP Addresses - <span id="selected-branch-name"></span>
                        </h5>
                        <button class="btn btn-success" id="add-ip-btn">
                            <i class="fas fa-plus me-2"></i>
                            Add New IP
                        </button>
                    </div>

                    <div class="ip-table-container">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped" id="ip-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-globe me-1"></i>IP Address</th>
                                        <th><i class="fas fa-desktop me-1"></i>Device Name</th>
                                        <th><i class="fas fa-tag me-1"></i>Device Type</th>
                                        <th><i class="fas fa-network-wired me-1"></i>Subnet</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Description</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="no-branch-selected" class="no-data">
                    <i class="fas fa-mouse-pointer fa-3x mb-3"></i>
                    <h5>Select a branch to view IP addresses</h5>
                    <p>Choose a branch from the left panel to display its IP configuration.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- IP Modal -->
    <div class="modal fade" id="ip-modal" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Add New IP Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="ip-form">
                    <div class="modal-body">
                        <input type="hidden" id="ip-id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ip-address" class="form-label">
                                    <i class="fas fa-globe me-1"></i>
                                    IP Address *
                                </label>
                                <input type="text" class="form-control" id="ip-address" name="ip_address" 
                                       placeholder="192.168.1.1" required>
                                <div class="form-text">Enter a valid IPv4 address</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="device-name" class="form-label">
                                    <i class="fas fa-desktop me-1"></i>
                                    Device Name *
                                </label>
                                <input type="text" class="form-control" id="device-name" name="device_name" 
                                       placeholder="Server-01" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="device-type" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    Device Type *
                                </label>
                                <select class="form-select" id="device-type" name="device_type_id" required>
                                    <option value="">Select Device Type</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="subnet" class="form-label">
                                    <i class="fas fa-network-wired me-1"></i>
                                    Subnet *
                                </label>
                                <select class="form-select" id="subnet" name="subnet_id" required>
                                    <option value="">Select Subnet</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-info-circle me-1"></i>
                                Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Optional description of the device or its purpose"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>
                            Save IP Address
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>