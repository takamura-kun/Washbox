// accessibilityManager.js - Comprehensive accessibility support

import { eventBus, EVENTS } from '../utils/eventBus.js';
import { showToast } from '../modules/utils.js';
import { taskScheduler } from '../utils/taskScheduler.js';

class AccessibilityManager {
    constructor() {
        this.focusHistory = [];
        this.announcements = [];
        this.liveRegion = null;
        this.skipLinks = [];
        this.focusTrap = null;
        this.highContrastMode = false;
        this.reducedMotion = false;
        this.screenReaderMode = false;
        
        this.init();
    }

    init() {
        this.createLiveRegion();
        this.setupSkipLinks();
        this.detectUserPreferences();
        this.enhanceExistingElements();
        this.setupFocusManagement();
        this.setupKeyboardNavigation();
        this.monitorDynamicContent();
        console.log('♿ Accessibility manager initialized');
    }

    /**
     * Create ARIA live region for announcements
     */
    createLiveRegion() {
        // Create polite live region
        this.liveRegion = document.createElement('div');
        this.liveRegion.setAttribute('role', 'status');
        this.liveRegion.setAttribute('aria-live', 'polite');
        this.liveRegion.setAttribute('aria-atomic', 'true');
        this.liveRegion.className = 'sr-only';
        this.liveRegion.id = 'aria-live-region';
        document.body.appendChild(this.liveRegion);

        // Create assertive live region for urgent messages
        this.assertiveLiveRegion = document.createElement('div');
        this.assertiveLiveRegion.setAttribute('role', 'alert');
        this.assertiveLiveRegion.setAttribute('aria-live', 'assertive');
        this.assertiveLiveRegion.setAttribute('aria-atomic', 'true');
        this.assertiveLiveRegion.className = 'sr-only';
        this.assertiveLiveRegion.id = 'aria-alert-region';
        document.body.appendChild(this.assertiveLiveRegion);
    }

    /**
     * Announce message to screen readers
     */
    announce(message, priority = 'polite') {
        const region = priority === 'assertive' ? 
            this.assertiveLiveRegion : this.liveRegion;
        
        // Clear previous message
        region.textContent = '';
        
        // Set new message after a brief delay to ensure it's announced
        taskScheduler.schedule(() => {
            region.textContent = message;
            
            // Add to announcements history
            this.announcements.push({
                message,
                priority,
                timestamp: Date.now()
            });
            
            // Keep only last 50 announcements
            if (this.announcements.length > 50) {
                this.announcements.shift();
            }
        }, 100);
    }

    /**
     * Setup skip links for keyboard navigation
     */
    setupSkipLinks() {
        // Skip links disabled - they were causing visual issues
        // Uncomment the code below if you want to re-enable accessibility skip links
        
        /*
        const skipLinksContainer = document.createElement('div');
        skipLinksContainer.className = 'skip-links';
        skipLinksContainer.setAttribute('role', 'navigation');
        skipLinksContainer.setAttribute('aria-label', 'Skip links');

        const skipLinks = [
            { href: '#main-content', text: 'Skip to main content' },
            { href: '#navigation', text: 'Skip to navigation' },
            { href: '#search', text: 'Skip to search' },
            { href: '#footer', text: 'Skip to footer' }
        ];

        skipLinks.forEach(link => {
            const a = document.createElement('a');
            a.href = link.href;
            a.className = 'skip-link';
            a.textContent = link.text;
            
            a.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.href);
                if (target) {
                    target.setAttribute('tabindex', '-1');
                    target.focus();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
            
            skipLinksContainer.appendChild(a);
            this.skipLinks.push(a);
        });

        document.body.insertBefore(skipLinksContainer, document.body.firstChild);
        */
    }

    /**
     * Detect user accessibility preferences
     */
    detectUserPreferences() {
        // Detect reduced motion preference
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.reducedMotion = prefersReducedMotion.matches;
        
        prefersReducedMotion.addEventListener('change', (e) => {
            this.reducedMotion = e.matches;
            this.applyReducedMotion(e.matches);
        });

        // Detect high contrast preference
        const prefersHighContrast = window.matchMedia('(prefers-contrast: high)');
        this.highContrastMode = prefersHighContrast.matches;
        
        prefersHighContrast.addEventListener('change', (e) => {
            this.highContrastMode = e.matches;
            this.applyHighContrast(e.matches);
        });

        // Detect screen reader
        this.detectScreenReader();

        // Apply initial preferences
        if (this.reducedMotion) this.applyReducedMotion(true);
        if (this.highContrastMode) this.applyHighContrast(true);
    }

    /**
     * Detect if screen reader is active
     */
    detectScreenReader() {
        // Check for common screen reader indicators
        const indicators = [
            navigator.userAgent.includes('JAWS'),
            navigator.userAgent.includes('NVDA'),
            navigator.userAgent.includes('VoiceOver'),
            document.body.classList.contains('screen-reader-mode')
        ];

        this.screenReaderMode = indicators.some(indicator => indicator);

        // Listen for screen reader activation
        document.addEventListener('keydown', (e) => {
            // Common screen reader shortcuts
            if ((e.key === 'Insert' && e.shiftKey) || 
                (e.key === 'CapsLock' && e.shiftKey)) {
                this.screenReaderMode = true;
                this.enableScreenReaderMode();
            }
        });
    }

    /**
     * Enable screen reader optimizations
     */
    enableScreenReaderMode() {
        document.body.classList.add('screen-reader-mode');
        
        // Add more descriptive labels
        this.enhanceLabels();
        
        // Announce mode activation
        this.announce('Screen reader mode activated', 'assertive');
    }

    /**
     * Apply reduced motion preferences
     */
    applyReducedMotion(enable) {
        if (enable) {
            document.documentElement.style.setProperty('--animation-duration', '0.01ms');
            document.documentElement.style.setProperty('--transition-duration', '0.01ms');
            document.body.classList.add('reduce-motion');
        } else {
            document.documentElement.style.removeProperty('--animation-duration');
            document.documentElement.style.removeProperty('--transition-duration');
            document.body.classList.remove('reduce-motion');
        }
    }

    /**
     * Apply high contrast mode
     */
    applyHighContrast(enable) {
        if (enable) {
            document.body.classList.add('high-contrast');
        } else {
            document.body.classList.remove('high-contrast');
        }
    }

    /**
     * Enhance existing elements with accessibility attributes
     */
    enhanceExistingElements() {
        // Enhance buttons without labels
        document.querySelectorAll('button:not([aria-label]):not([aria-labelledby])').forEach(button => {
            if (!button.textContent.trim() && button.querySelector('i, svg')) {
                const icon = button.querySelector('i, svg');
                const ariaLabel = this.inferLabelFromIcon(icon);
                if (ariaLabel) {
                    button.setAttribute('aria-label', ariaLabel);
                }
            }
        });

        // Enhance links without text
        document.querySelectorAll('a:not([aria-label])').forEach(link => {
            if (!link.textContent.trim() && link.querySelector('i, svg, img')) {
                const ariaLabel = this.inferLabelFromElement(link);
                if (ariaLabel) {
                    link.setAttribute('aria-label', ariaLabel);
                }
            }
        });

        // Enhance form inputs
        document.querySelectorAll('input, select, textarea').forEach(input => {
            this.enhanceFormInput(input);
        });

        // Enhance images
        document.querySelectorAll('img:not([alt])').forEach(img => {
            img.setAttribute('alt', this.inferAltText(img));
        });

        // Enhance tables
        document.querySelectorAll('table').forEach(table => {
            this.enhanceTable(table);
        });

        // Enhance modals
        document.querySelectorAll('.modal').forEach(modal => {
            this.enhanceModal(modal);
        });

        // Enhance navigation
        document.querySelectorAll('nav:not([aria-label])').forEach(nav => {
            nav.setAttribute('aria-label', 'Navigation');
        });
    }

    /**
     * Infer label from icon classes
     */
    inferLabelFromIcon(icon) {
        const classList = Array.from(icon.classList);
        const iconMap = {
            'bi-search': 'Search',
            'bi-x': 'Close',
            'bi-plus': 'Add',
            'bi-trash': 'Delete',
            'bi-pencil': 'Edit',
            'bi-eye': 'View',
            'bi-download': 'Download',
            'bi-upload': 'Upload',
            'bi-save': 'Save',
            'bi-refresh': 'Refresh',
            'bi-arrow-left': 'Go back',
            'bi-arrow-right': 'Go forward',
            'bi-home': 'Home',
            'bi-gear': 'Settings',
            'bi-person': 'User profile',
            'bi-bell': 'Notifications',
            'bi-envelope': 'Messages',
            'bi-calendar': 'Calendar',
            'bi-map': 'Map',
            'bi-list': 'Menu'
        };

        for (const [iconClass, label] of Object.entries(iconMap)) {
            if (classList.includes(iconClass)) {
                return label;
            }
        }

        return null;
    }

    /**
     * Infer label from element
     */
    inferLabelFromElement(element) {
        // Check title attribute
        if (element.title) return element.title;

        // Check data attributes
        if (element.dataset.label) return element.dataset.label;
        if (element.dataset.title) return element.dataset.title;

        // Check icon
        const icon = element.querySelector('i, svg');
        if (icon) return this.inferLabelFromIcon(icon);

        // Check image
        const img = element.querySelector('img');
        if (img && img.alt) return img.alt;

        return null;
    }

    /**
     * Infer alt text for images
     */
    inferAltText(img) {
        // Check if decorative
        if (img.classList.contains('decorative') || 
            img.getAttribute('role') === 'presentation') {
            return '';
        }

        // Use title or data attributes
        if (img.title) return img.title;
        if (img.dataset.alt) return img.dataset.alt;

        // Use filename as last resort
        const filename = img.src.split('/').pop().split('.')[0];
        return filename.replace(/[-_]/g, ' ');
    }

    /**
     * Enhance form input
     */
    enhanceFormInput(input) {
        // Ensure label association
        if (!input.id) {
            input.id = `input-${Math.random().toString(36).substr(2, 9)}`;
        }

        const label = document.querySelector(`label[for="${input.id}"]`);
        if (!label && !input.getAttribute('aria-label') && !input.getAttribute('aria-labelledby')) {
            // Try to find nearby label
            const nearbyLabel = input.closest('.form-group, .input-group')?.querySelector('label');
            if (nearbyLabel) {
                nearbyLabel.setAttribute('for', input.id);
            } else {
                // Add aria-label from placeholder or name
                const ariaLabel = input.placeholder || input.name || 'Input field';
                input.setAttribute('aria-label', ariaLabel);
            }
        }

        // Add required indicator
        if (input.required && !input.getAttribute('aria-required')) {
            input.setAttribute('aria-required', 'true');
        }

        // Add invalid state
        if (input.classList.contains('is-invalid')) {
            input.setAttribute('aria-invalid', 'true');
            
            // Link to error message
            const errorMsg = input.closest('.form-group')?.querySelector('.invalid-feedback');
            if (errorMsg) {
                const errorId = `error-${input.id}`;
                errorMsg.id = errorId;
                input.setAttribute('aria-describedby', errorId);
            }
        }

        // Add autocomplete hints
        if (!input.autocomplete && input.type) {
            const autocompleteMap = {
                'email': 'email',
                'tel': 'tel',
                'password': 'current-password',
                'text': input.name?.includes('name') ? 'name' : null
            };
            
            const autocomplete = autocompleteMap[input.type];
            if (autocomplete) {
                input.setAttribute('autocomplete', autocomplete);
            }
        }
    }

    /**
     * Enhance table accessibility
     */
    enhanceTable(table) {
        // Add role if not present
        if (!table.getAttribute('role')) {
            table.setAttribute('role', 'table');
        }

        // Add caption if missing
        if (!table.querySelector('caption') && !table.getAttribute('aria-label')) {
            const caption = document.createElement('caption');
            caption.className = 'sr-only';
            caption.textContent = 'Data table';
            table.insertBefore(caption, table.firstChild);
        }

        // Enhance headers
        table.querySelectorAll('th').forEach(th => {
            if (!th.getAttribute('scope')) {
                // Determine scope based on position
                const isInThead = th.closest('thead') !== null;
                const isFirstColumn = th.cellIndex === 0;
                
                if (isInThead) {
                    th.setAttribute('scope', 'col');
                } else if (isFirstColumn) {
                    th.setAttribute('scope', 'row');
                }
            }
        });

        // Add row headers if missing
        const tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.querySelectorAll('tr').forEach((row, index) => {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell && !row.querySelector('th')) {
                    // Check if first cell looks like a header
                    if (firstCell.querySelector('strong, b') || 
                        firstCell.textContent.trim().length < 30) {
                        const th = document.createElement('th');
                        th.setAttribute('scope', 'row');
                        th.innerHTML = firstCell.innerHTML;
                        firstCell.replaceWith(th);
                    }
                }
            });
        }
    }

    /**
     * Enhance modal accessibility
     */
    enhanceModal(modal) {
        // Add dialog role
        if (!modal.getAttribute('role')) {
            modal.setAttribute('role', 'dialog');
        }

        // Add aria-modal
        if (!modal.getAttribute('aria-modal')) {
            modal.setAttribute('aria-modal', 'true');
        }

        // Add aria-labelledby
        const title = modal.querySelector('.modal-title');
        if (title && !modal.getAttribute('aria-labelledby')) {
            if (!title.id) {
                title.id = `modal-title-${Math.random().toString(36).substr(2, 9)}`;
            }
            modal.setAttribute('aria-labelledby', title.id);
        }

        // Add aria-describedby
        const body = modal.querySelector('.modal-body');
        if (body && !modal.getAttribute('aria-describedby')) {
            if (!body.id) {
                body.id = `modal-body-${Math.random().toString(36).substr(2, 9)}`;
            }
            modal.setAttribute('aria-describedby', body.id);
        }
    }

    /**
     * Setup focus management
     */
    setupFocusManagement() {
        // Track focus changes
        document.addEventListener('focusin', (e) => {
            this.focusHistory.push({
                element: e.target,
                timestamp: Date.now()
            });

            // Keep only last 20 focus events
            if (this.focusHistory.length > 20) {
                this.focusHistory.shift();
            }
        });

        // Handle focus loss
        document.addEventListener('focusout', (e) => {
            // Ensure focus doesn't get lost
            taskScheduler.schedule(() => {
                if (!document.activeElement || document.activeElement === document.body) {
                    this.restoreFocus();
                }
            }, 100);
        });
    }

    /**
     * Setup keyboard navigation enhancements
     */
    setupKeyboardNavigation() {
        // Enhance tab navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });

        document.addEventListener('mousedown', () => {
            document.body.classList.remove('keyboard-navigation');
        });

        // Add visible focus indicators
        const style = document.createElement('style');
        style.textContent = `
            .keyboard-navigation *:focus {
                outline: 3px solid #0066cc !important;
                outline-offset: 2px !important;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Monitor dynamic content for accessibility
     */
    monitorDynamicContent() {
        // Watch for new content
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element node
                        this.enhanceElement(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Listen to content loaded events
        eventBus.on(EVENTS.CONTENT_LOADED, (data) => {
            const element = document.getElementById(data.elementId);
            if (element) {
                this.enhanceElement(element);
                this.announce('Content loaded', 'polite');
            }
        });
    }

    /**
     * Enhance a single element
     */
    enhanceElement(element) {
        // Enhance based on element type
        if (element.tagName === 'BUTTON') {
            if (!element.getAttribute('aria-label') && !element.textContent.trim()) {
                const icon = element.querySelector('i, svg');
                if (icon) {
                    const label = this.inferLabelFromIcon(icon);
                    if (label) element.setAttribute('aria-label', label);
                }
            }
        } else if (element.tagName === 'IMG') {
            if (!element.alt) {
                element.alt = this.inferAltText(element);
            }
        } else if (element.tagName === 'TABLE') {
            this.enhanceTable(element);
        } else if (element.classList.contains('modal')) {
            this.enhanceModal(element);
        }

        // Enhance all children
        element.querySelectorAll('button, a, img, input, select, textarea, table').forEach(child => {
            this.enhanceElement(child);
        });
    }

    /**
     * Create focus trap for modals
     */
    createFocusTrap(container) {
        const focusableElements = container.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), ' +
            'input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return null;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        const trapFocus = (e) => {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        };

        container.addEventListener('keydown', trapFocus);

        return {
            activate: () => firstElement.focus(),
            deactivate: () => container.removeEventListener('keydown', trapFocus)
        };
    }

    /**
     * Restore focus to previous element
     */
    restoreFocus() {
        if (this.focusHistory.length > 1) {
            const previousFocus = this.focusHistory[this.focusHistory.length - 2];
            if (previousFocus && previousFocus.element && 
                document.body.contains(previousFocus.element)) {
                previousFocus.element.focus();
            }
        }
    }

    /**
     * Enhance labels with more context
     */
    enhanceLabels() {
        // Add context to ambiguous labels
        document.querySelectorAll('[aria-label="Edit"], [aria-label="Delete"], [aria-label="View"]').forEach(el => {
            const context = this.getElementContext(el);
            if (context) {
                const currentLabel = el.getAttribute('aria-label');
                el.setAttribute('aria-label', `${currentLabel} ${context}`);
            }
        });
    }

    /**
     * Get context for an element
     */
    getElementContext(element) {
        // Look for nearby text that provides context
        const row = element.closest('tr, .card, .list-item');
        if (row) {
            const heading = row.querySelector('h1, h2, h3, h4, h5, h6, .title, .name');
            if (heading) {
                return heading.textContent.trim();
            }
        }
        return null;
    }

    /**
     * Get accessibility report
     */
    getAccessibilityReport() {
        return {
            screenReaderMode: this.screenReaderMode,
            reducedMotion: this.reducedMotion,
            highContrastMode: this.highContrastMode,
            announcements: this.announcements.length,
            focusHistory: this.focusHistory.length,
            skipLinks: this.skipLinks.length
        };
    }
}

// Create singleton instance
export const accessibilityManager = new AccessibilityManager();

// Make it globally available
window.accessibilityManager = accessibilityManager;