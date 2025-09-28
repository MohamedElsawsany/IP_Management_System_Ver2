class IPManagement {
    constructor() {
        this.currentBranch = null;
        this.currentPage = 1;
        this.recordsPerPage = 10;
        this.init();
    }

    init() {
        this.loadBranches();
    }

    async loadBranches() {
        try {
            const branches = await this.fetchBranches();
            this.renderBranches(branches);
        } catch (error) {
            console.error('Error loading branches:', error);
            this.showError('Failed to load branches');
        }
    }

    async fetchBranches() {
        const response = await fetch('api/branches.php');
        if (!response.ok) throw new Error('Failed to fetch branches');
        return await response.json();
    }

    renderBranches(branches) {
        const container = document.getElementById('branch-buttons');
        container.innerHTML = '';

        branches.forEach(branch => {
            const branchCard = document.createElement('div');
            branchCard.className = 'branch-card';
            branchCard.innerHTML = `
                <button class="btn btn-branch" data-branch-id="${branch.id}" data-branch-name="${branch.name}">
                    <i class="fas fa-building branch-icon"></i>
                    ${branch.name}
                    <span class="ip-count">${branch.ip_count} IPs</span>
                </button>
            `;

            branchCard.addEventListener('click', () => this.selectBranch(branch.id, branch.name, branchCard));
            container.appendChild(branchCard);
        });
    }

    selectBranch(branchId, branchName, cardElement) {
        document.querySelectorAll('.branch-card').forEach(card => card.classList.remove('active'));
        cardElement.classList.add('active');

        this.currentBranch = branchId;
        this.currentPage = 1;

        document.getElementById('selected-branch-name').textContent = branchName;
        document.getElementById('ip-content').style.display = 'block';
        document.getElementById('no-branch-selected').style.display = 'none';

        this.loadIPs();
    }

    async loadIPs() {
        this.showLoading(true);

        try {
            const data = await this.fetchIPs(this.currentBranch, this.currentPage);
            this.renderIPTable(data.ips);
            this.renderPagination(data.pagination);
        } catch (error) {
            console.error('Error loading IPs:', error);
            this.showError('Failed to load IP addresses');
        } finally {
            this.showLoading(false);
        }
    }

    async fetchIPs(branchId, page) {
        const response = await fetch(`api/ips.php?branch_id=${branchId}&page=${page}`);
        if (!response.ok) throw new Error('Failed to fetch IPs');
        return await response.json();
    }

    renderIPTable(ips) {
        const tbody = document.getElementById('ip-table-body');
        tbody.innerHTML = '';

        if (ips.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No IP addresses found for this branch
                    </td>
                </tr>
            `;
            return;
        }

        ips.forEach(ip => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${ip.ip_address}</strong></td>
                <td>${ip.device_name}</td>
                <td><span class="badge bg-primary">${ip.device_type}</span></td>
                <td>${ip.subnet_mask}</td>
                <td>${ip.description || '<em class="text-muted">No description</em>'}</td>
            `;
            tbody.appendChild(row);
        });
    }

    renderPagination(pagination) {
        const container = document.getElementById('pagination-container');
        container.innerHTML = '';

        if (pagination.total_pages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${pagination.current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
        `;
        container.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            container.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">
                Next <i class="fas fa-chevron-right"></i>
            </a>
        `;
        container.appendChild(nextLi);

        // Add click events
        container.addEventListener('click', (e) => {
            e.preventDefault();
            const link = e.target.closest('a[data-page]');
            if (link && !link.closest('.disabled')) {
                this.currentPage = parseInt(link.dataset.page);
                this.loadIPs();
            }
        });
    }

    showLoading(show) {
        document.getElementById('loading-spinner').style.display = show ? 'block' : 'none';
    }

    showError(message) {
        alert(message); // You can implement a toast or modal system here
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new IPManagement();
});