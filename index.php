<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Management System</title>
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
        }

        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%);
            color: #e2e8f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
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

        .main-container {
            padding: 2rem 0;
        }

        .branch-card {
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            cursor: pointer;
        }

        .branch-card:hover {
            border-color: var(--secondary-blue);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
        }

        .branch-card.active {
            border-color: var(--secondary-blue);
            background: linear-gradient(135deg, var(--card-bg) 0%, #2563eb 100%);
        }

        .btn-branch {
            background: transparent;
            border: none;
            color: #e2e8f0;
            padding: 1.5rem;
            width: 100%;
            text-align: left;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .btn-branch:hover,
        .btn-branch:focus {
            color: white;
            background: transparent;
            border: none;
            outline: none;
            box-shadow: none;
        }

        .ip-table-container {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid var(--border-color);
        }

        .table-dark {
            background: transparent;
        }

        .table-dark th {
            background: var(--primary-blue);
            border-color: var(--border-color);
            color: white;
            font-weight: 600;
        }

        .table-dark td {
            border-color: var(--border-color);
            color: #e2e8f0;
            vertical-align: middle;
        }

        .table-dark tbody tr:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        /* Network grouping styles */
        .network-header-row {
            background: rgba(30, 58, 138, 0.3) !important;
        }

        .network-header {
            padding: 0.75rem 1rem;
            border-bottom: 2px solid var(--primary-blue);
        }

        .network-toggle {
            color: #e2e8f0 !important;
            text-decoration: none;
            font-weight: 600;
        }

        .network-toggle:hover {
            color: white !important;
        }

        .network-arrow {
            transition: transform 0.3s ease;
            margin-right: 0.5rem;
        }

        .network-ips-container {
            background: rgba(15, 23, 42, 0.5);
        }

        .network-ips-content {
            max-height: 500px;
            overflow-y: auto;
        }

        .nested-table {
            margin-left: 2rem;
            border-left: 3px solid var(--secondary-blue);
        }

        .nested-table td {
            padding-left: 1.5rem;
            border-color: rgba(51, 65, 85, 0.5);
        }

        .ip-row:hover {
            background: rgba(59, 130, 246, 0.15) !important;
        }

        .pagination {
            justify-content: center;
            margin-top: 1.5rem;
        }

        .page-link {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: #e2e8f0;
        }

        .page-link:hover {
            background: var(--secondary-blue);
            border-color: var(--secondary-blue);
            color: white;
        }

        .page-item.active .page-link {
            background: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .branch-icon {
            margin-right: 10px;
            color: var(--secondary-blue);
        }

        .ip-count {
            float: right;
            background: var(--secondary-blue);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* CRUD Button Styles */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
        }

        .btn-success {
            background-color: var(--success-green);
            border-color: var(--success-green);
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }

        .btn-warning {
            background-color: var(--warning-orange);
            border-color: var(--warning-orange);
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
            border-color: #d97706;
        }

        .btn-danger {
            background-color: var(--danger-red);
            border-color: var(--danger-red);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }

        /* Modal Styles */
        .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: #e2e8f0;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
        }

        .form-control {
            background: #374151;
            border: 1px solid var(--border-color);
            color: #e2e8f0;
        }

        .form-control:focus {
            background: #374151;
            border-color: var(--secondary-blue);
            color: #e2e8f0;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        .form-select {
            background: #374151;
            border: 1px solid var(--border-color);
            color: #e2e8f0;
        }

        .form-select:focus {
            background: #374151;
            border-color: var(--secondary-blue);
            color: #e2e8f0;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        .form-label {
            color: #e2e8f0;
            font-weight: 500;
        }

        .btn-close {
            filter: invert(1);
        }

        /* Toast Styles */
        .toast-container {
            z-index: 9999;
        }

        .toast {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: #e2e8f0;
        }

        .toast.success {
            border-left: 4px solid var(--success-green);
        }

        .toast.error {
            border-left: 4px solid var(--danger-red);
        }

        .toast-body {
            padding: 1rem;
        }

        /* Enhanced actions bar */
        .actions-bar {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .actions-bar h5 {
            margin: 0;
            color: #e2e8f0;
        }

        .search-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input-group {
            position: relative;
        }

        .search-input-group .form-control {
            padding-right: 2.5rem;
        }

        .clear-search {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
        }

        .clear-search:hover {
            color: #e2e8f0;
        }

        .results-info {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        /* Controls bar */
        .controls-bar {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
            }

            .ip-table-container {
                padding: 1rem;
                overflow-x: auto;
            }

            .table-responsive {
                font-size: 0.9rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .actions-bar,
            .controls-bar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .search-controls {
                width: 100%;
                justify-content: center;
            }

            .table th:last-child,
            .table td:last-child {
                min-width: 120px;
            }

            .nested-table {
                margin-left: 0.5rem;
            }

            .nested-table td {
                padding-left: 0.75rem;
            }
        }
    </style>
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

                    <div class="controls-bar">
                        <div class="search-controls">
                            <div class="search-input-group">
                                <input type="text" class="form-control" id="search-input"
                                    placeholder="Search IPs, devices, descriptions...">
                                <button type="button" class="clear-search" id="clear-search">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <select class="form-select" id="records-per-page" style="width: auto;">
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                            </select>
                        </div>
                    </div>

                    <div class="loading-spinner" id="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading IP addresses...</p>
                    </div>

                    <div class="ip-table-container">
                        <div class="results-info" id="results-info"></div>

                        <div class="table-responsive">
                            <table class="table table-dark table-striped">
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
                                <tbody id="ip-table-body">
                                    <!-- IP data will be loaded here -->
                                </tbody>
                            </table>
                        </div>

                        <nav aria-label="IP pagination">
                            <ul class="pagination" id="pagination-container">
                                <!-- Pagination will be loaded here -->
                            </ul>
                        </nav>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>