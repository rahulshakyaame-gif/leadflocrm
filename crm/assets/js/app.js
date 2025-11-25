// State Management
const state = {
    currentView: 'dashboard',
    leads: [],
    calls: [],
    user: null,
    users: [] // For admin assignment
};

// DOM Elements
const viewContainer = document.getElementById('view-container');
const pageTitle = document.getElementById('page-title');
const navItems = document.querySelectorAll('.nav-item');
const modalOverlay = document.getElementById('modal-overlay');
const leadModal = document.getElementById('lead-modal');
const callModal = document.getElementById('call-modal');
const leadForm = document.getElementById('lead-form');
const callForm = document.getElementById('call-form');

// Initialization
document.addEventListener('DOMContentLoaded', async () => {
    await checkAuth();
    if (state.user.role === 'admin') {
        await loadUsers();
        document.getElementById('admin-link').style.display = 'flex';
        document.getElementById('import-link').style.display = 'flex';
        document.getElementById('integrations-link').style.display = 'flex';
        document.getElementById('assign-group').style.display = 'block';
    }
    
    // Update profile info
    document.querySelector('.user-profile .name').textContent = state.user.name;
    document.querySelector('.user-profile .role').textContent = state.user.role;

    loadView('dashboard');
    setupEventListeners();
});

async function checkAuth() {
    try {
        const response = await fetch('api/auth.php?action=check');
        const data = await response.json();
        if (!data.authenticated) {
            window.location.href = 'login.html';
        }
        state.user = data.user;
    } catch (e) {
        window.location.href = 'login.html';
    }
}

async function loadUsers() {
    try {
        const response = await fetch('api/users.php');
        if (response.ok) {
            state.users = await response.json();
            const select = document.getElementById('lead-assigned-to');
            // Keep "Unassigned"
            select.innerHTML = '<option value="">Unassigned</option>' + 
                state.users.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
        }
    } catch (e) {
        console.error('Failed to load users', e);
    }
}

// Event Listeners
function setupEventListeners() {
    // Navigation
    navItems.forEach(item => {
        if (item.getAttribute('href') === '#') {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const view = item.dataset.view;
                loadView(view);
                
                // Update active state
                navItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
            });
        }
    });

    // Modals
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    document.getElementById('add-lead-btn').addEventListener('click', () => {
        openLeadModal();
    });

    // Forms
    leadForm.addEventListener('submit', handleLeadSubmit);
    callForm.addEventListener('submit', handleCallSubmit);
}

// View Management
async function loadView(view) {
    state.currentView = view;
    viewContainer.innerHTML = '<div class="loading">Loading...</div>';

    switch (view) {
        case 'dashboard':
            pageTitle.textContent = 'Dashboard';
            await renderDashboard();
            break;
        case 'leads':
            pageTitle.textContent = 'Leads Management';
            await renderLeads();
            break;
        case 'kanban':
            pageTitle.textContent = 'Kanban Board';
            await renderKanban();
            break;
        case 'calls':
            pageTitle.textContent = 'Call History';
            await renderCalls();
            break;
    }
}

// Render Functions
async function renderDashboard() {
    try {
        const leads = await api.get('leads.php');
        const calls = await api.get('calls.php');
        const reports = await api.get('reports.php');
        
        state.leads = leads;
        
        const totalLeads = leads.length;
        const newLeads = leads.filter(l => l.status === 'New').length;
        const convertedLeads = leads.filter(l => l.status === 'Converted').length;
        const totalCalls = calls.length;

        viewContainer.innerHTML = `
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Total Leads</h3>
                    <div class="value">${totalLeads}</div>
                </div>
                <div class="stat-card">
                    <h3>New Leads</h3>
                    <div class="value" style="color: var(--primary-color)">${newLeads}</div>
                </div>
                <div class="stat-card">
                    <h3>Converted</h3>
                    <div class="value" style="color: var(--success)">${convertedLeads}</div>
                </div>
                <div class="stat-card">
                    <h3>Total Calls</h3>
                    <div class="value">${totalCalls}</div>
                </div>
            </div>

            <div class="charts-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="card" style="padding: 1rem;">
                    <h3>Leads by Status</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="card" style="padding: 1rem;">
                    <h3>Leads by Source</h3>
                    <canvas id="sourceChart"></canvas>
                </div>
            </div>

            <div class="section-header">
                <h2>Recent Activity</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Action</th>
                            <th>Date</th>
                            <th>Outcome/Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${calls.slice(0, 5).map(call => `
                            <tr>
                                <td>${call.lead_name || 'Unknown'}</td>
                                <td>Call Logged</td>
                                <td>${new Date(call.call_date).toLocaleDateString()}</td>
                                <td>${call.outcome}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Render Charts
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: reports.leads_by_status.map(i => i.status),
                datasets: [{
                    data: reports.leads_by_status.map(i => i.count),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6b7280']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: reports.leads_by_source.map(i => i.source),
                datasets: [{
                    label: 'Leads',
                    data: reports.leads_by_source.map(i => i.count),
                    backgroundColor: '#3b82f6'
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

    } catch (error) {
        viewContainer.innerHTML = `<div class="error">Error loading dashboard: ${error.message}</div>`;
    }
}

async function renderLeads() {
    try {
        const leads = await api.get('leads.php');
        state.leads = leads;

        const isAdmin = state.user.role === 'admin';

        viewContainer.innerHTML = `
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Source</th>
                            <th>Status</th>
                            ${isAdmin ? '<th>Assigned To</th>' : ''}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${leads.map(lead => `
                            <tr>
                                <td>
                                    <div style="font-weight: 500">${lead.name}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted)">${lead.email || ''}</div>
                                </td>
                                <td>${lead.phone}</td>
                                <td>${lead.source || '-'}</td>
                                <td><span class="status-badge status-${lead.status}">${lead.status}</span></td>
                                ${isAdmin ? `<td>${lead.assigned_user_name || '<span style="color: #ccc;">Unassigned</span>'}</td>` : ''}
                                <td>
                                    <button class="action-btn" onclick="openCallModal(${lead.id}, '${lead.name}')" title="Log Call">
                                        <i class="fa-solid fa-phone"></i>
                                    </button>
                                    <button class="action-btn" onclick="openLeadModal(${lead.id})" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    ${isAdmin ? `
                                    <button class="action-btn" onclick="deleteLead(${lead.id})" title="Delete" style="color: var(--danger)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        viewContainer.innerHTML = `<div class="error">Error loading leads: ${error.message}</div>`;
    }
}

async function renderKanban() {
    try {
        const leads = await api.get('leads.php');
        state.leads = leads;

        const statuses = ['New', 'Contacted', 'Interested', 'Qualified', 'Lost', 'Converted'];
        const leadsByStatus = {};
        
        statuses.forEach(status => {
            leadsByStatus[status] = leads.filter(l => l.status === status);
        });

        viewContainer.innerHTML = `
            <div class="kanban-board">
                ${statuses.map(status => `
                    <div class="kanban-column" data-status="${status}">
                        <div class="kanban-column-header">
                            <span class="kanban-column-title">${status}</span>
                            <span class="kanban-column-count">${leadsByStatus[status].length}</span>
                        </div>
                        <div class="kanban-cards" data-status="${status}">
                            ${leadsByStatus[status].map(lead => `
                                <div class="kanban-card" draggable="true" data-lead-id="${lead.id}">
                                    <div class="kanban-card-name">${lead.name}</div>
                                    <div class="kanban-card-info">
                                        <div class="kanban-card-phone">
                                            <i class="fa-solid fa-phone"></i>
                                            ${lead.phone}
                                        </div>
                                        ${lead.email ? `<div><i class="fa-solid fa-envelope"></i> ${lead.email}</div>` : ''}
                                    </div>
                                    ${lead.source ? `<span class="kanban-card-source">${lead.source}</span>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        // Setup drag and drop
        setupDragAndDrop();

    } catch (error) {
        viewContainer.innerHTML = `<div class="error">Error loading kanban: ${error.message}</div>`;
    }
}

function setupDragAndDrop() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-cards');

    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
    });
}

let draggedElement = null;

function handleDragStart(e) {
    draggedElement = e.target;
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', e.target.innerHTML);
}

function handleDragEnd(e) {
    e.target.classList.remove('dragging');
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    e.target.closest('.kanban-column')?.classList.add('drag-over');
}

function handleDragLeave(e) {
    if (e.target.classList.contains('kanban-cards')) {
        e.target.closest('.kanban-column')?.classList.remove('drag-over');
    }
}

async function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }

    const column = e.target.closest('.kanban-column');
    column?.classList.remove('drag-over');

    if (draggedElement) {
        const leadId = draggedElement.dataset.leadId;
        const newStatus = e.target.dataset.status;

        if (newStatus) {
            try {
                // Update lead status via API
                await api.put('leads.php', {
                    id: leadId,
                    status: newStatus
                });

                // Refresh kanban view
                await renderKanban();
            } catch (error) {
                alert('Error updating lead status: ' + error.message);
            }
        }
    }

    return false;
}

async function renderCalls() {
    try {
        const calls = await api.get('calls.php');
        
        viewContainer.innerHTML = `
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Outcome</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${calls.map(call => `
                            <tr>
                                <td>${call.lead_name || 'Unknown'}</td>
                                <td>${new Date(call.call_date).toLocaleString()}</td>
                                <td>${formatDuration(call.duration)}</td>
                                <td>${call.outcome}</td>
                                <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${call.notes || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } catch (error) {
        viewContainer.innerHTML = `<div class="error">Error loading calls: ${error.message}</div>`;
    }
}

// Modal Functions
function openLeadModal(id = null) {
    modalOverlay.classList.remove('hidden');
    leadModal.style.display = 'block';
    callModal.style.display = 'none';
    
    const title = document.getElementById('modal-title');
    
    if (id) {
        const lead = state.leads.find(l => l.id == id);
        title.textContent = 'Edit Lead';
        document.getElementById('lead-id').value = lead.id;
        document.getElementById('lead-name').value = lead.name;
        document.getElementById('lead-phone').value = lead.phone;
        document.getElementById('lead-email').value = lead.email;
        document.getElementById('lead-source').value = lead.source;
        document.getElementById('lead-status').value = lead.status;
        if (state.user.role === 'admin') {
            document.getElementById('lead-assigned-to').value = lead.assigned_to || '';
        }
    } else {
        title.textContent = 'Add New Lead';
        leadForm.reset();
        document.getElementById('lead-id').value = '';
        if (state.user.role === 'admin') {
            document.getElementById('lead-assigned-to').value = '';
        }
    }
}

function openCallModal(leadId, leadName) {
    modalOverlay.classList.remove('hidden');
    leadModal.style.display = 'none';
    callModal.style.display = 'block';
    
    document.getElementById('call-lead-id').value = leadId;
    document.getElementById('call-lead-name').value = leadName;
    callForm.reset();
    // Re-set hidden and readonly fields after reset
    document.getElementById('call-lead-id').value = leadId;
    document.getElementById('call-lead-name').value = leadName;
}

function closeModal() {
    modalOverlay.classList.add('hidden');
}

// Form Handlers
async function handleLeadSubmit(e) {
    e.preventDefault();
    const formData = new FormData(leadForm);
    const data = Object.fromEntries(formData.entries());
    
    try {
        if (data.id) {
            await api.put('leads.php', data);
        } else {
            await api.post('leads.php', data);
        }
        closeModal();
        loadView(state.currentView); // Refresh view
    } catch (error) {
        alert('Error saving lead: ' + error.message);
    }
}

async function handleCallSubmit(e) {
    e.preventDefault();
    const formData = new FormData(callForm);
    const data = Object.fromEntries(formData.entries());
    
    try {
        await api.post('calls.php', data);
        closeModal();
        if (state.currentView === 'calls' || state.currentView === 'dashboard') {
            loadView(state.currentView);
        } else {
            alert('Call logged successfully');
        }
    } catch (error) {
        alert('Error logging call: ' + error.message);
    }
}

// Helper Functions
async function deleteLead(id) {
    if (confirm('Are you sure you want to delete this lead?')) {
        try {
            await api.delete(`leads.php?id=${id}`);
            loadView(state.currentView);
        } catch (error) {
            alert('Error deleting lead: ' + error.message);
        }
    }
}

function formatDuration(seconds) {
    if (!seconds) return '-';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}m ${secs}s`;
}

// Expose functions to global scope for onclick handlers
window.openLeadModal = openLeadModal;
window.openCallModal = openCallModal;
window.deleteLead = deleteLead;
