// keyboardManager.js - Keyboard shortcuts and power user features

import { eventBus, EVENTS } from '../utils/eventBus.js';
import { showToast } from '../modules/utils.js';
import { taskScheduler } from '../utils/taskScheduler.js';

class KeyboardManager {
    constructor() {
        this.shortcuts = new Map();
        this.contexts = new Map();
        this.currentContext = 'global';
        this.commandPalette = null;
        this.isCommandPaletteOpen = false;
        this.searchResults = [];
        this.selectedIndex = 0;
        this.recentCommands = [];
        this.maxRecentCommands = 10;
        
        this.init();
    }

    init() {
        this.setupGlobalShortcuts();
        this.setupContextualShortcuts();
        this.setupCommandPalette();
        this.setupEventListeners();
        this.loadUserPreferences();
        console.log('⌨️ Keyboard shortcuts manager initialized');
    }

    /**
     * Setup global keyboard shortcuts
     */
    setupGlobalShortcuts() {
        const globalShortcuts = [
            // Navigation
            { key: 'g+o', action: 'goToOverview', description: 'Go to Overview tab' },
            { key: 'g+p', action: 'goToPickups', description: 'Go to Pickups tab' },
            { key: 'g+r', action: 'goToRoutes', description: 'Go to Routes tab' },
            { key: 'g+c', action: 'goToCustomers', description: 'Go to Customers tab' },
            { key: 'g+a', action: 'goToAnalytics', description: 'Go to Analytics tab' },
            
            // Actions
            { key: 'ctrl+r', action: 'refreshDashboard', description: 'Refresh Dashboard' },
            { key: 'ctrl+n', action: 'createNewPickup', description: 'Create New Pickup' },
            { key: 'ctrl+f', action: 'focusSearch', description: 'Focus Search' },
            { key: 'ctrl+k', action: 'openCommandPalette', description: 'Open Command Palette' },
            { key: 'ctrl+shift+k', action: 'openKeyboardHelp', description: 'Show Keyboard Shortcuts' },
            
            // Selection
            { key: 'ctrl+a', action: 'selectAll', description: 'Select All Items' },
            { key: 'ctrl+shift+a', action: 'deselectAll', description: 'Deselect All Items' },
            { key: 'delete', action: 'deleteSelected', description: 'Delete Selected Items' },
            
            // Quick actions
            { key: 'r', action: 'calculateRoute', description: 'Calculate Route for Selected' },
            { key: 'n', action: 'startNavigation', description: 'Start Navigation' },
            { key: 't', action: 'toggleTracking', description: 'Toggle Tracking' },
            { key: 'o', action: 'optimizeRoute', description: 'Optimize Route' },
            
            // View controls
            { key: 'f', action: 'toggleFullscreen', description: 'Toggle Fullscreen' },
            { key: 'm', action: 'toggleMapView', description: 'Toggle Map View' },
            { key: 'l', action: 'toggleListView', description: 'Toggle List View' },
            { key: 'd', action: 'toggleDarkMode', description: 'Toggle Dark Mode' },
            
            // Escape actions
            { key: 'escape', action: 'closeModal', description: 'Close Modal/Panel' },
            { key: 'ctrl+z', action: 'undo', description: 'Undo Last Action' },
            { key: 'ctrl+y', action: 'redo', description: 'Redo Last Action' }
        ];

        globalShortcuts.forEach(shortcut => {
            this.registerShortcut('global', shortcut.key, shortcut.action, shortcut.description);
        });
    }

    /**
     * Setup contextual shortcuts for different sections
     */
    setupContextualShortcuts() {
        // Map context shortcuts
        const mapShortcuts = [
            { key: 'plus', action: 'zoomIn', description: 'Zoom In' },
            { key: 'minus', action: 'zoomOut', description: 'Zoom Out' },
            { key: 'h', action: 'goHome', description: 'Go to Home Location' },
            { key: 'c', action: 'centerMap', description: 'Center Map on Markers' },
            { key: 'shift+click', action: 'multiSelect', description: 'Multi-select Markers' }
        ];

        mapShortcuts.forEach(shortcut => {
            this.registerShortcut('map', shortcut.key, shortcut.action, shortcut.description);
        });

        // Table context shortcuts
        const tableShortcuts = [
            { key: 'j', action: 'selectNext', description: 'Select Next Row' },
            { key: 'k', action: 'selectPrevious', description: 'Select Previous Row' },
            { key: 'space', action: 'toggleRowSelection', description: 'Toggle Row Selection' },
            { key: 'enter', action: 'openRowDetails', description: 'Open Row Details' },
            { key: 'shift+j', action: 'extendSelectionDown', description: 'Extend Selection Down' },
            { key: 'shift+k', action: 'extendSelectionUp', description: 'Extend Selection Up' }
        ];

        tableShortcuts.forEach(shortcut => {
            this.registerShortcut('table', shortcut.key, shortcut.action, shortcut.description);
        });

        // Form context shortcuts
        const formShortcuts = [
            { key: 'ctrl+enter', action: 'submitForm', description: 'Submit Form' },
            { key: 'ctrl+s', action: 'saveForm', description: 'Save Form' },
            { key: 'tab', action: 'nextField', description: 'Next Field' },
            { key: 'shift+tab', action: 'previousField', description: 'Previous Field' }
        ];

        formShortcuts.forEach(shortcut => {
            this.registerShortcut('form', shortcut.key, shortcut.action, shortcut.description);
        });
    }

    /**
     * Register a keyboard shortcut
     */
    registerShortcut(context, key, action, description, callback = null) {
        if (!this.contexts.has(context)) {
            this.contexts.set(context, new Map());
        }

        const contextShortcuts = this.contexts.get(context);
        contextShortcuts.set(key, {
            action,
            description,
            callback,
            key,
            context,
            enabled: true
        });

        // Also add to global shortcuts map for easy lookup
        const globalKey = `${context}:${key}`;
        this.shortcuts.set(globalKey, {
            action,
            description,
            callback,
            key,
            context,
            enabled: true
        });
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        document.addEventListener('keydown', (e) => {
            this.handleKeyDown(e);
        });

        document.addEventListener('keyup', (e) => {
            this.handleKeyUp(e);
        });

        // Context change detection
        document.addEventListener('focusin', (e) => {
            this.updateContext(e.target);
        });

        document.addEventListener('click', (e) => {
            this.updateContext(e.target);
        });
    }

    /**
     * Handle keydown events
     */
    handleKeyDown(e) {
        // Don't handle shortcuts when typing in inputs (unless specifically allowed)
        if (this.isTypingContext(e.target) && !this.isAllowedInInput(e)) {
            return;
        }

        const keyCombo = this.getKeyCombo(e);
        const shortcut = this.findShortcut(keyCombo);

        if (shortcut && shortcut.enabled) {
            e.preventDefault();
            e.stopPropagation();
            
            this.executeShortcut(shortcut, e);
            this.addToRecentCommands(shortcut);
        }
    }

    /**
     * Handle keyup events
     */
    handleKeyUp(e) {
        // Handle key sequences (like 'g+o')
        if (this.sequenceBuffer) {
            clearTimeout(this.sequenceTimeout);
            this.sequenceTimeout = taskScheduler.schedule(() => {
                this.sequenceBuffer = '';
            }, 1000);
        }
    }

    /**
     * Get key combination string
     */
    getKeyCombo(e) {
        const parts = [];
        
        if (e.ctrlKey) parts.push('ctrl');
        if (e.altKey) parts.push('alt');
        if (e.shiftKey) parts.push('shift');
        if (e.metaKey) parts.push('meta');
        
        let key = e.key.toLowerCase();
        
        // Handle special keys
        const specialKeys = {
            ' ': 'space',
            'arrowup': 'up',
            'arrowdown': 'down',
            'arrowleft': 'left',
            'arrowright': 'right',
            'escape': 'escape',
            'enter': 'enter',
            'tab': 'tab',
            'backspace': 'backspace',
            'delete': 'delete'
        };
        
        if (specialKeys[key]) {
            key = specialKeys[key];
        }
        
        // Handle key sequences (like g+o)
        if (!e.ctrlKey && !e.altKey && !e.shiftKey && !e.metaKey) {
            if (this.sequenceBuffer) {
                key = this.sequenceBuffer + '+' + key;
                this.sequenceBuffer = '';
            } else if (key.length === 1 && key.match(/[a-z]/)) {
                this.sequenceBuffer = key;
                this.sequenceTimeout = taskScheduler.schedule(() => {
                    this.sequenceBuffer = '';
                }, 1000);
                return null; // Wait for next key
            }
        }
        
        parts.push(key);
        return parts.join('+');
    }

    /**
     * Find shortcut for key combination
     */
    findShortcut(keyCombo) {
        if (!keyCombo) return null;

        // Check current context first
        const contextShortcuts = this.contexts.get(this.currentContext);
        if (contextShortcuts && contextShortcuts.has(keyCombo)) {
            return contextShortcuts.get(keyCombo);
        }

        // Check global shortcuts
        const globalShortcuts = this.contexts.get('global');
        if (globalShortcuts && globalShortcuts.has(keyCombo)) {
            return globalShortcuts.get(keyCombo);
        }

        return null;
    }

    /**
     * Execute shortcut action
     */
    executeShortcut(shortcut, event) {
        try {
            if (shortcut.callback) {
                shortcut.callback(event);
            } else {
                this.executeBuiltInAction(shortcut.action, event);
            }

            // Show feedback for power users
            if (this.shouldShowFeedback(shortcut)) {
                this.showShortcutFeedback(shortcut);
            }

            // Emit event
            eventBus.emit(EVENTS.SHORTCUT_EXECUTED, {
                shortcut: shortcut.action,
                context: shortcut.context,
                key: shortcut.key
            });

        } catch (error) {
            console.error('Failed to execute shortcut:', error);
            showToast('Shortcut execution failed', 'danger');
        }
    }

    /**
     * Execute built-in actions
     */
    executeBuiltInAction(action, event) {
        switch (action) {
            // Navigation
            case 'goToOverview':
                this.switchTab('overview');
                break;
            case 'goToPickups':
                this.switchTab('operations');
                break;
            case 'goToRoutes':
                this.switchTab('routes');
                break;
            case 'goToCustomers':
                this.switchTab('customers');
                break;
            case 'goToAnalytics':
                this.switchTab('analytics');
                break;

            // Actions
            case 'refreshDashboard':
                window.dashboard?.refreshDashboard();
                break;
            case 'createNewPickup':
                window.location.href = '/admin/pickups/create';
                break;
            case 'focusSearch':
                this.focusSearchInput();
                break;
            case 'openCommandPalette':
                this.openCommandPalette();
                break;
            case 'openKeyboardHelp':
                this.showKeyboardHelp();
                break;

            // Selection
            case 'selectAll':
                this.selectAllItems();
                break;
            case 'deselectAll':
                window.dashboard?.clearSelections();
                break;

            // Quick actions
            case 'calculateRoute':
                this.executeQuickRoute();
                break;
            case 'startNavigation':
                this.executeQuickNavigation();
                break;
            case 'optimizeRoute':
                window.dashboard?.getOptimizedMultiRoute();
                break;

            // View controls
            case 'toggleFullscreen':
                this.toggleFullscreen();
                break;
            case 'toggleMapView':
                this.toggleMapView();
                break;

            // Escape actions
            case 'closeModal':
                this.closeTopModal();
                break;

            // Map actions
            case 'zoomIn':
                this.mapZoom(1);
                break;
            case 'zoomOut':
                this.mapZoom(-1);
                break;
            case 'centerMap':
                this.centerMap();
                break;

            // Table actions
            case 'selectNext':
                this.navigateTable(1);
                break;
            case 'selectPrevious':
                this.navigateTable(-1);
                break;

            default:
                console.warn('Unknown shortcut action:', action);
        }
    }

    /**
     * Setup command palette
     */
    setupCommandPalette() {
        this.createCommandPaletteHTML();
    }

    /**
     * Create command palette HTML
     */
    createCommandPaletteHTML() {
        const paletteHTML = `
            <div id="command-palette" class="command-palette" style="display: none;" role="dialog" aria-labelledby="command-palette-title" aria-modal="true">
                <div class="command-palette-backdrop"></div>
                <div class="command-palette-container">
                    <div class="command-palette-header">
                        <h5 id="command-palette-title" class="mb-0">Command Palette</h5>
                        <button type="button" class="btn-close" aria-label="Close command palette"></button>
                    </div>
                    <div class="command-palette-search">
                        <input type="text" 
                               class="form-control" 
                               placeholder="Type a command or search..." 
                               id="command-search"
                               autocomplete="off"
                               aria-label="Command search">
                    </div>
                    <div class="command-palette-results" id="command-results" role="listbox" aria-label="Command results">
                        <div class="command-section">
                            <div class="command-section-title">Recent Commands</div>
                            <div id="recent-commands"></div>
                        </div>
                        <div class="command-section">
                            <div class="command-section-title">All Commands</div>
                            <div id="all-commands"></div>
                        </div>
                    </div>
                    <div class="command-palette-footer">
                        <div class="command-palette-tips">
                            <span class="tip"><kbd>↑↓</kbd> Navigate</span>
                            <span class="tip"><kbd>Enter</kbd> Execute</span>
                            <span class="tip"><kbd>Esc</kbd> Close</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', paletteHTML);
        this.commandPalette = document.getElementById('command-palette');
        this.setupCommandPaletteEvents();
    }

    /**
     * Setup command palette events
     */
    setupCommandPaletteEvents() {
        const searchInput = document.getElementById('command-search');
        const closeBtn = this.commandPalette.querySelector('.btn-close');
        const backdrop = this.commandPalette.querySelector('.command-palette-backdrop');

        // Search input
        searchInput.addEventListener('input', (e) => {
            this.searchCommands(e.target.value);
        });

        searchInput.addEventListener('keydown', (e) => {
            this.handleCommandPaletteNavigation(e);
        });

        // Close events
        closeBtn.addEventListener('click', () => {
            this.closeCommandPalette();
        });

        backdrop.addEventListener('click', () => {
            this.closeCommandPalette();
        });
    }

    /**
     * Open command palette
     */
    openCommandPalette() {
        this.isCommandPaletteOpen = true;
        this.commandPalette.style.display = 'block';
        
        // Focus search input
        const searchInput = document.getElementById('command-search');
        searchInput.focus();
        searchInput.value = '';
        
        // Load initial commands
        this.loadRecentCommands();
        this.loadAllCommands();
        
        // Add escape listener
        document.addEventListener('keydown', this.commandPaletteEscapeListener);
        
        // Announce to screen readers
        searchInput.setAttribute('aria-expanded', 'true');
    }

    /**
     * Close command palette
     */
    closeCommandPalette() {
        this.isCommandPaletteOpen = false;
        this.commandPalette.style.display = 'none';
        this.selectedIndex = 0;
        
        // Remove escape listener
        document.removeEventListener('keydown', this.commandPaletteEscapeListener);
        
        // Announce to screen readers
        const searchInput = document.getElementById('command-search');
        searchInput.setAttribute('aria-expanded', 'false');
    }

    /**
     * Command palette escape listener
     */
    commandPaletteEscapeListener = (e) => {
        if (e.key === 'Escape' && this.isCommandPaletteOpen) {
            this.closeCommandPalette();
        }
    }

    /**
     * Search commands
     */
    searchCommands(query) {
        const allCommands = this.getAllCommands();
        const filtered = allCommands.filter(cmd => 
            cmd.description.toLowerCase().includes(query.toLowerCase()) ||
            cmd.action.toLowerCase().includes(query.toLowerCase()) ||
            cmd.key.toLowerCase().includes(query.toLowerCase())
        );

        this.displaySearchResults(filtered);
        this.selectedIndex = 0;
        this.updateSelection();
    }

    /**
     * Display search results
     */
    displaySearchResults(commands) {
        const resultsContainer = document.getElementById('all-commands');
        
        if (commands.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No commands found</div>';
            return;
        }

        const html = commands.map((cmd, index) => `
            <div class="command-item" data-index="${index}" role="option" aria-selected="false">
                <div class="command-info">
                    <div class="command-title">${cmd.description}</div>
                    <div class="command-subtitle">${cmd.action} • ${cmd.context}</div>
                </div>
                <div class="command-shortcut">
                    <kbd>${cmd.key}</kbd>
                </div>
            </div>
        `).join('');

        resultsContainer.innerHTML = html;
        this.searchResults = commands;

        // Add click listeners
        resultsContainer.querySelectorAll('.command-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                this.executeCommandByIndex(index);
            });
        });
    }

    /**
     * Handle command palette navigation
     */
    handleCommandPaletteNavigation(e) {
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.searchResults.length - 1);
                this.updateSelection();
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                this.updateSelection();
                break;
            case 'Enter':
                e.preventDefault();
                this.executeCommandByIndex(this.selectedIndex);
                break;
            case 'Escape':
                this.closeCommandPalette();
                break;
        }
    }

    /**
     * Update selection in command palette
     */
    updateSelection() {
        const items = document.querySelectorAll('.command-item');
        items.forEach((item, index) => {
            const isSelected = index === this.selectedIndex;
            item.classList.toggle('selected', isSelected);
            item.setAttribute('aria-selected', isSelected);
        });

        // Scroll selected item into view
        const selectedItem = items[this.selectedIndex];
        if (selectedItem) {
            selectedItem.scrollIntoView({ block: 'nearest' });
        }
    }

    /**
     * Execute command by index
     */
    executeCommandByIndex(index) {
        if (index >= 0 && index < this.searchResults.length) {
            const command = this.searchResults[index];
            this.executeShortcut(command);
            this.closeCommandPalette();
        }
    }

    /**
     * Get all available commands
     */
    getAllCommands() {
        const commands = [];
        
        this.contexts.forEach((contextShortcuts, contextName) => {
            contextShortcuts.forEach((shortcut, key) => {
                commands.push({
                    ...shortcut,
                    context: contextName
                });
            });
        });

        return commands.sort((a, b) => a.description.localeCompare(b.description));
    }

    /**
     * Load recent commands
     */
    loadRecentCommands() {
        const container = document.getElementById('recent-commands');
        
        if (this.recentCommands.length === 0) {
            container.innerHTML = '<div class="no-recent">No recent commands</div>';
            return;
        }

        const html = this.recentCommands.map(cmd => `
            <div class="command-item recent-command">
                <div class="command-info">
                    <div class="command-title">${cmd.description}</div>
                    <div class="command-subtitle">${cmd.action}</div>
                </div>
                <div class="command-shortcut">
                    <kbd>${cmd.key}</kbd>
                </div>
            </div>
        `).join('');

        container.innerHTML = html;
    }

    /**
     * Load all commands
     */
    loadAllCommands() {
        const allCommands = this.getAllCommands();
        this.displaySearchResults(allCommands);
    }

    /**
     * Add command to recent commands
     */
    addToRecentCommands(shortcut) {
        // Remove if already exists
        this.recentCommands = this.recentCommands.filter(cmd => cmd.action !== shortcut.action);
        
        // Add to beginning
        this.recentCommands.unshift(shortcut);
        
        // Limit size
        if (this.recentCommands.length > this.maxRecentCommands) {
            this.recentCommands.pop();
        }

        // Save to localStorage
        this.saveUserPreferences();
    }

    /**
     * Show keyboard help modal
     */
    showKeyboardHelp() {
        const helpModal = this.createKeyboardHelpModal();
        document.body.appendChild(helpModal);
        
        const modal = new bootstrap.Modal(helpModal);
        modal.show();
        
        // Remove modal when hidden
        helpModal.addEventListener('hidden.bs.modal', () => {
            helpModal.remove();
        });
    }

    /**
     * Create keyboard help modal
     */
    createKeyboardHelpModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-labelledby', 'keyboard-help-title');
        modal.setAttribute('aria-hidden', 'true');

        const shortcuts = this.getAllCommands();
        const groupedShortcuts = this.groupShortcutsByContext(shortcuts);

        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="keyboard-help-title">Keyboard Shortcuts</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="keyboard-help-content">
                            ${Object.entries(groupedShortcuts).map(([context, contextShortcuts]) => `
                                <div class="shortcut-section">
                                    <h6 class="shortcut-section-title">${this.capitalizeFirst(context)}</h6>
                                    <div class="shortcut-list">
                                        ${contextShortcuts.map(shortcut => `
                                            <div class="shortcut-item">
                                                <div class="shortcut-description">${shortcut.description}</div>
                                                <div class="shortcut-key"><kbd>${shortcut.key}</kbd></div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="text-muted">
                            <small>Press <kbd>Ctrl+K</kbd> to open the command palette</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Group shortcuts by context
     */
    groupShortcutsByContext(shortcuts) {
        const grouped = {};
        
        shortcuts.forEach(shortcut => {
            if (!grouped[shortcut.context]) {
                grouped[shortcut.context] = [];
            }
            grouped[shortcut.context].push(shortcut);
        });

        return grouped;
    }

    /**
     * Utility methods
     */
    isTypingContext(element) {
        const typingElements = ['input', 'textarea', 'select'];
        const isContentEditable = element.contentEditable === 'true';
        return typingElements.includes(element.tagName.toLowerCase()) || isContentEditable;
    }

    isAllowedInInput(e) {
        // Allow certain shortcuts even when typing
        const allowedKeys = ['ctrl+k', 'ctrl+a', 'ctrl+z', 'ctrl+y', 'escape'];
        const keyCombo = this.getKeyCombo(e);
        return allowedKeys.includes(keyCombo);
    }

    updateContext(element) {
        // Determine context based on focused element or clicked area
        if (element.closest('.map-container')) {
            this.currentContext = 'map';
        } else if (element.closest('table')) {
            this.currentContext = 'table';
        } else if (element.closest('form')) {
            this.currentContext = 'form';
        } else {
            this.currentContext = 'global';
        }
    }

    switchTab(tabName) {
        const tabButton = document.getElementById(`${tabName}-tab`);
        if (tabButton) {
            tabButton.click();
        }
    }

    focusSearchInput() {
        const searchInput = document.querySelector('input[type="search"], input[placeholder*="search" i]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Save user preferences
     */
    saveUserPreferences() {
        const preferences = {
            recentCommands: this.recentCommands,
            customShortcuts: this.getCustomShortcuts()
        };
        
        localStorage.setItem('keyboard_preferences', JSON.stringify(preferences));
    }

    /**
     * Load user preferences
     */
    loadUserPreferences() {
        try {
            const saved = localStorage.getItem('keyboard_preferences');
            if (saved) {
                const preferences = JSON.parse(saved);
                this.recentCommands = preferences.recentCommands || [];
                this.applyCustomShortcuts(preferences.customShortcuts || {});
            }
        } catch (error) {
            console.warn('Failed to load keyboard preferences:', error);
        }
    }

    /**
     * Get custom shortcuts
     */
    getCustomShortcuts() {
        // Return user-customized shortcuts
        return {};
    }

    /**
     * Apply custom shortcuts
     */
    applyCustomShortcuts(customShortcuts) {
        // Apply user-customized shortcuts
        Object.entries(customShortcuts).forEach(([key, shortcut]) => {
            this.registerShortcut(shortcut.context, key, shortcut.action, shortcut.description);
        });
    }

    /**
     * Show shortcut feedback
     */
    showShortcutFeedback(shortcut) {
        // Show brief feedback for executed shortcuts
        const feedback = document.createElement('div');
        feedback.className = 'shortcut-feedback';
        feedback.textContent = shortcut.description;
        
        document.body.appendChild(feedback);
        
        taskScheduler.schedule(() => {
            feedback.classList.add('show');
        }, 10);
        
        taskScheduler.schedule(() => {
            feedback.classList.remove('show');
            taskScheduler.schedule(() => feedback.remove(), 300);
        }, 1500);
    }

    /**
     * Should show feedback for shortcut
     */
    shouldShowFeedback(shortcut) {
        // Show feedback for non-obvious actions
        const feedbackActions = ['refreshDashboard', 'optimizeRoute', 'calculateRoute'];
        return feedbackActions.includes(shortcut.action);
    }
}

// Create singleton instance
export const keyboardManager = new KeyboardManager();

// Make it globally available
window.keyboardManager = keyboardManager;