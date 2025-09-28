<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                    <h4 class="mb-4">
                        <i class="fas fa-server me-2"></i>
                        IP Addresses - <span id="selected-branch-name"></span>
                    </h4>

                    <div class="loading-spinner" id="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading IP addresses...</p>
                    </div>

                    <div class="ip-table-container">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-globe me-1"></i>IP Address</th>
                                        <th><i class="fas fa-desktop me-1"></i>Device Name</th>
                                        <th><i class="fas fa-tag me-1"></i>Device Type</th>
                                        <th><i class="fas fa-network-wired me-1"></i>Subnet</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Description</th>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>