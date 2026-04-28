// components.js - Reusable UI components

import { getStatusColor } from '../modules/utils.js';
import { eventBus, EVENTS } from '../utils/eventBus.js';

/**
 * Base Component class
 */
class Component {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.options = { ...this.defaultOptions, ...options };
        this.element = null;
        this.isDestroyed = false;
        
        if (!this.container) {
            throw new Error('Container element not found');
        }
        
        this.init();
    }

    get defaultOptions() {
        return {};
    }

    init() {
        this.render();
        this.bindEvents();
    }

    render() {
        // Override in subclasses
    }

    bindEvents() {
        // Override in subclasses
    }

    destroy() {
        if (this.element && this.element.parentNode) {
            this.element.parentNode.removeChild(this.element);
        }
        this.isDestroyed = true;
    }

    emit(event, data) {
        eventBus.emit(event, data);
    }

    on(event, callback) {
        eventBus.on(event, callback, this);
    }
}

/**
 * Status Badge Component
 */
export class StatusBadge extends Component {
    get defaultOptions() {
        return {
            status: 'pending',
            size: 'normal', // small, normal, large
            showIcon: true,
            clickable: false
        };
    }

    render() {
        const { status, size, showIcon, clickable } = this.options;
        const colorClass = getStatusColor(status);
        const sizeClass = size === 'small' ? 'badge-sm' : size === 'large' ? 'badge-lg' : '';
        const clickableClass = clickable ? 'badge-clickable' : '';
        
        const icon = showIcon ? this.getStatusIcon(status) : '';
        
        this.element = document.createElement('span');
        this.element.className = `badge bg-${colorClass} ${sizeClass} ${clickableClass}`.trim();
        this.element.innerHTML = `${icon}${status.replace('_', ' ')}`;
        
        if (this.container) {
            this.container.appendChild(this.element);
        }
    }

    bindEvents() {
        if (this.options.clickable && this.element) {
            this.element.addEventListener('click', () => {
                this.emit(EVENTS.UI_STATUS_CLICKED, { status: this.options.status });
            });
        }
    }

    getStatusIcon(status) {
        const icons = {
            pending: '<i class="bi bi-clock me-1"></i>',
            accepted: '<i class="bi bi-check-circle me-1"></i>',
            en_route: '<i class="bi bi-truck me-1"></i>',
            picked_up: '<i class="bi bi-check-square me-1"></i>',
            cancelled: '<i class="bi bi-x-circle me-1"></i>'
        };
        return icons[status] || '';
    }

    updateStatus(newStatus) {
        this.options.status = newStatus;
        this.render();
    }
}

/**
 * Loading Spinner Component
 */
export class LoadingSpinner extends Component {
    get defaultOptions() {
        return {
            size: 'normal', // small, normal, large
            message: 'Loading...',
            overlay: false,
            color: 'primary'
        };
    }

    render() {
        const { size, message, overlay, color } = this.options;
        const sizeClass = size === 'small' ? 'spinner-sm' : size === 'large' ? 'spinner-lg' : '';
        
        this.element = document.createElement('div');
        this.element.className = `loading-spinner ${overlay ? 'loading-overlay' : ''}`;
        
        this.element.innerHTML = `
            <div class="d-flex align-items-center justify-content-center ${overlay ? 'h-100' : ''}">
                <div class="spinner-border text-${color} ${sizeClass}" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${message ? `<span class="ms-2">${message}</span>` : ''}
            </div>
        `;
        
        if (this.container) {
            this.container.appendChild(this.element);
        }
    }

    updateMessage(message) {
        const messageSpan = this.element.querySelector('span:not(.visually-hidden)');
        if (messageSpan) {
            messageSpan.textContent = message;
        }
    }

    show() {
        if (this.element) {
            this.element.style.display = 'block';
        }
    }

    hide() {
        if (this.element) {
            this.element.style.display = 'none';
        }
    }
}

/**
 * Pickup Card Component
 */
export class PickupCard extends Component {
    get defaultOptions() {
        return {
            pickup: null,
            selectable: false,
            showActions: true,
            compact: false
        };
    }

    render() {
        const { pickup, selectable, showActions, compact } = this.options;
        
        if (!pickup) {
            throw new Error('Pickup data is required');
        }

        const cardClass = compact ? 'pickup-card-compact' : 'pickup-card';
        const selectedClass = pickup.selected ? 'selected' : '';
        
        this.element = document.createElement('div');
        this.element.className = `${cardClass} ${selectedClass} card mb-2`;
        this.element.setAttribute('data-pickup-id', pickup.id);
        
        this.element.innerHTML = `
            <div class="card-body ${compact ? 'p-2' : ''}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="pickup-info flex-grow-1">
                        <h6 class="card-title mb-1">
                            ${selectable ? `<input type="checkbox" class="form-check-input me-2" ${pickup.selected ? 'checked' : ''}>` : ''}
                            ${pickup.customer?.name || 'Customer'}
                        </h6>
                        <p class="card-text small text-muted mb-1">${pickup.pickup_address || 'No address'}</p>
                        <div class="pickup-meta small">
                            <span class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                ${new Date(pickup.created_at).toLocaleDateString()}
                            </span>
                            ${pickup.pickup_date ? `
                                <span class="text-muted ms-2">
                                    <i class="bi bi-clock me-1"></i>
                                    ${new Date(pickup.pickup_date).toLocaleString()}
                                </span>
                            ` : ''}
                        </div>
                    </div>
                    <div class="pickup-status">
                        <div class="status-badge-container"></div>
                    </div>
                </div>
                ${showActions ? this.renderActions(pickup) : ''}
            </div>
        `;

        // Add status badge
        const statusContainer = this.element.querySelector('.status-badge-container');
        new StatusBadge(statusContainer, { status: pickup.status, size: 'small' });
        
        if (this.container) {
            this.container.appendChild(this.element);
        }
    }

    renderActions(pickup) {
        return `
            <div class="pickup-actions mt-2 pt-2 border-top">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <button type="button" class="btn btn-outline-primary" data-action="route">
                        <i class="bi bi-signpost"></i> Route
                    </button>
                    <button type="button" class="btn btn-outline-success" data-action="navigate">
                        <i class="bi bi-play-circle"></i> Navigate
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-action="details">
                        <i class="bi bi-eye"></i> Details
                    </button>
                </div>
            </div>
        `;
    }

    bindEvents() {
        if (!this.element) return;

        // Handle selection
        const checkbox = this.element.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('change', (e) => {
                this.handleSelection(e.target.checked);
            });
        }

        // Handle actions
        const actionButtons = this.element.querySelectorAll('[data-action]');
        actionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const action = e.currentTarget.getAttribute('data-action');
                this.handleAction(action);
            });
        });

        // Handle card click (if not selectable)
        if (!this.options.selectable) {
            this.element.addEventListener('click', () => {
                this.handleAction('details');
            });
        }
    }

    handleSelection(selected) {
        this.options.pickup.selected = selected;
        
        if (selected) {
            this.element.classList.add('selected');
        } else {
            this.element.classList.remove('selected');
        }

        this.emit(selected ? EVENTS.PICKUP_SELECTED : EVENTS.PICKUP_DESELECTED, {
            pickup: this.options.pickup
        });
    }

    handleAction(action) {
        const pickup = this.options.pickup;
        
        switch (action) {
            case 'route':
                this.emit('pickup:route', { pickupId: pickup.id });
                break;
            case 'navigate':
                this.emit('pickup:navigate', { pickupId: pickup.id });
                break;
            case 'details':
                this.emit('pickup:details', { pickupId: pickup.id });
                break;
        }
    }

    updatePickup(pickup) {
        this.options.pickup = pickup;
        this.render();
        this.bindEvents();
    }

    setSelected(selected) {
        const checkbox = this.element.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.checked = selected;
            this.handleSelection(selected);
        }
    }
}

/**
 * Route Panel Component
 */
export class RoutePanel extends Component {
    get defaultOptions() {
        return {
            route: null,
            type: 'single', // single, multi
            collapsible: true,
            showPrintButton: true
        };
    }

    render() {
        const { route, type, collapsible, showPrintButton } = this.options;
        
        if (!route) {
            this.element = document.createElement('div');
            this.element.innerHTML = '<p class="text-muted">No route calculated</p>';
            return;
        }

        const headerClass = type === 'multi' ? 'bg-purple' : 'bg-primary';
        const title = type === 'multi' ? 'Optimized Route Summary' : 'Route Details';
        
        this.element = document.createElement('div');
        this.element.className = 'route-panel card border-0 shadow-sm';
        
        this.element.innerHTML = `
            <div class="card-header ${headerClass} text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-${type === 'multi' ? 'route' : 'signpost'} me-2"></i>
                    ${title}
                </h6>
                <div class="header-actions">
                    ${collapsible ? '<button class="btn btn-sm btn-light me-1" data-action="toggle"><i class="bi bi-chevron-up"></i></button>' : ''}
                    <button class="btn btn-sm btn-light" data-action="close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body route-content">
                ${this.renderRouteContent(route, type)}
                <hr>
                <div class="route-actions d-grid gap-2">
                    ${this.renderActions(type, showPrintButton)}
                </div>
            </div>
        `;
        
        if (this.container) {
            this.container.appendChild(this.element);
        }
    }

    renderRouteContent(route, type) {
        if (type === 'multi') {
            return `
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Total Distance</small>
                        <h5>${route.distance || 'Unknown'}</h5>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Total Time</small>
                        <h5>${route.duration || 'Unknown'}</h5>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Stops:</small>
                    <ol class="mt-2 ps-3">
                        ${route.stops ? route.stops.map((stop, idx) => 
                            `<li><strong>${idx === 0 ? 'Start' : `Stop ${idx}`}:</strong> ${stop.name || `Location ${idx + 1}`}</li>`
                        ).join('') : ''}
                    </ol>
                </div>
            `;
        } else {
            return `
                <div class="mb-3">
                    <h5 class="text-success">
                        <i class="bi bi-signpost"></i> ${route.distance?.text || route.distance || 'Unknown'}
                    </h5>
                    <p class="text-muted">
                        <i class="bi bi-clock"></i> ${route.duration?.text || route.duration || 'Unknown'}
                    </p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">From:</small>
                    <p class="mb-0"><b>${route.from || 'WashBox Branch'}</b></p>
                </div>
                <div class="mb-3">
                    <small class="text-muted">To:</small>
                    <p class="mb-0"><b>${route.to || 'Customer Location'}</b></p>
                    ${route.eta ? `<small class="text-muted">${route.eta} ETA</small>` : ''}
                </div>
            `;
        }
    }

    renderActions(type, showPrintButton) {
        const actions = [];
        
        if (type === 'multi') {
            actions.push(`
                <button class="btn btn-success" data-action="start-multi">
                    <i class="bi bi-play-circle me-2"></i>Start Multi-Pickup Run
                </button>
            `);
        } else {
            actions.push(`
                <button class="btn btn-success" data-action="start-single">
                    <i class="bi bi-play-circle me-2"></i>Start Navigation
                </button>
            `);
        }

        if (showPrintButton) {
            actions.push(`
                <button class="btn btn-outline-primary" data-action="print">
                    <i class="bi bi-printer me-2"></i>Print ${type === 'multi' ? 'Schedule' : 'Directions'}
                </button>
            `);
        }

        actions.push(`
            <button class="btn btn-outline-danger" data-action="clear">
                <i class="bi bi-x-circle me-2"></i>Clear Route
            </button>
        `);

        return actions.join('');
    }

    bindEvents() {
        if (!this.element) return;

        const actionButtons = this.element.querySelectorAll('[data-action]');
        actionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const action = e.currentTarget.getAttribute('data-action');
                this.handleAction(action);
            });
        });
    }

    handleAction(action) {
        const route = this.options.route;
        
        switch (action) {
            case 'toggle':
                this.toggleCollapse();
                break;
            case 'close':
                this.emit('route:close');
                this.destroy();
                break;
            case 'start-single':
                this.emit('route:start', { type: 'single', route });
                break;
            case 'start-multi':
                this.emit('route:start', { type: 'multi', route });
                break;
            case 'print':
                this.emit('route:print', { type: this.options.type, route });
                break;
            case 'clear':
                this.emit('route:clear');
                this.destroy();
                break;
        }
    }

    toggleCollapse() {
        const content = this.element.querySelector('.route-content');
        const icon = this.element.querySelector('[data-action="toggle"] i');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.className = 'bi bi-chevron-up';
        } else {
            content.style.display = 'none';
            icon.className = 'bi bi-chevron-down';
        }
    }

    updateRoute(route) {
        this.options.route = route;
        this.render();
        this.bindEvents();
    }
}

/**
 * Map Controls Component
 */
export class MapControls extends Component {
    get defaultOptions() {
        return {
            showSearch: true,
            showRefresh: true,
            showFullscreen: false,
            showLayers: true
        };
    }

    render() {
        const { showSearch, showRefresh, showFullscreen, showLayers } = this.options;
        
        this.element = document.createElement('div');
        this.element.className = 'map-controls position-absolute top-0 start-0 m-3';
        this.element.style.zIndex = '1000';
        
        this.element.innerHTML = `
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    ${showSearch ? this.renderSearchControl() : ''}
                    <div class="btn-group-vertical" role="group">
                        ${showRefresh ? '<button class="btn btn-sm btn-outline-secondary" data-action="refresh" title="Refresh"><i class="bi bi-arrow-clockwise"></i></button>' : ''}
                        ${showFullscreen ? '<button class="btn btn-sm btn-outline-secondary" data-action="fullscreen" title="Fullscreen"><i class="bi bi-fullscreen"></i></button>' : ''}
                        ${showLayers ? '<button class="btn btn-sm btn-outline-secondary" data-action="layers" title="Layers"><i class="bi bi-layers"></i></button>' : ''}
                    </div>
                </div>
            </div>
        `;
        
        if (this.container) {
            this.container.appendChild(this.element);
        }
    }

    renderSearchControl() {
        return `
            <div class="search-control mb-2">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Search address..." data-search="input">
                    <button class="btn btn-outline-secondary" type="button" data-action="search">
                        <i class="bi bi-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary" type="button" data-action="location" title="Use current location">
                        <i class="bi bi-geo-alt"></i>
                    </button>
                </div>
            </div>
        `;
    }

    bindEvents() {
        if (!this.element) return;

        // Search input
        const searchInput = this.element.querySelector('[data-search="input"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleAction('search');
                }
            });
        }

        // Action buttons
        const actionButtons = this.element.querySelectorAll('[data-action]');
        actionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const action = e.currentTarget.getAttribute('data-action');
                this.handleAction(action);
            });
        });
    }

    handleAction(action) {
        const searchInput = this.element.querySelector('[data-search="input"]');
        
        switch (action) {
            case 'search':
                if (searchInput) {
                    this.emit('map:search', { query: searchInput.value });
                }
                break;
            case 'location':
                this.emit('map:currentLocation');
                break;
            case 'refresh':
                this.emit('map:refresh');
                break;
            case 'fullscreen':
                this.emit('map:fullscreen');
                break;
            case 'layers':
                this.emit('map:layers');
                break;
        }
    }

    setSearchValue(value) {
        const searchInput = this.element.querySelector('[data-search="input"]');
        if (searchInput) {
            searchInput.value = value;
        }
    }
}

