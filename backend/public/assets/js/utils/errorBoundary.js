// errorBoundary.js - Comprehensive error handling system

import { showToast } from '../modules/utils.js';
import { eventBus, EVENTS } from './eventBus.js';

class ErrorBoundary {
    constructor() {
        this.errorCount = 0;
        this.maxErrors = 10;
        this.errorReportingEnabled = true;
        this.setupGlobalHandlers();
    }

    /**
     * Setup global error handlers
     */
    setupGlobalHandlers() {
        // JavaScript errors
        window.addEventListener('error', (event) => {
            this.handleError(event.error, 'JavaScript Error', {
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno
            });
        });

        // Unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError(event.reason, 'Unhandled Promise Rejection');
            event.preventDefault(); // Prevent console error
        });

        // Network errors
        window.addEventListener('offline', () => {
            this.handleNetworkError('offline');
        });

        window.addEventListener('online', () => {
            this.handleNetworkError('online');
        });

        console.log('✅ Error boundary initialized');
    }

    /**
     * Handle different types of errors
     */
    handleError(error, type, context = {}) {
        this.errorCount++;

        // Prevent error spam
        if (this.errorCount > this.maxErrors) {
            console.warn('Error boundary: Maximum error count reached, suppressing further errors');
            return;
        }

        const errorInfo = {
            message: error?.message || String(error),
            stack: error?.stack,
            type: type,
            context: context,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString(),
            errorCount: this.errorCount
        };

        console.error(`${type}:`, error, context);

        // Emit error event
        eventBus.emit(EVENTS.UI_ERROR, errorInfo);

        // Report error if enabled
        if (this.errorReportingEnabled) {
            this.reportError(errorInfo);
        }

        // Show user-friendly message
        this.showErrorToUser(error, type);
    }

    /**
     * Handle network errors
     */
    handleNetworkError(status) {
        if (status === 'offline') {
            showToast('⚠️ You are offline. Some features may not work properly.', 'warning');
            eventBus.emit(EVENTS.UI_ERROR, { type: 'network', status: 'offline' });
        } else {
            showToast('✅ Connection restored', 'success');
            eventBus.emit(EVENTS.DATA_SYNC_START);
        }
    }

    /**
     * Report error to server
     */
    async reportError(errorInfo) {
        try {
            // Skip error reporting if we're already on an error page or login page
            if (window.location.pathname.includes('/admin/login') || 
                window.location.pathname.includes('/error')) {
                return;
            }
            
            const response = await fetch('/api/errors', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(errorInfo)
            });
            
            // Don't follow redirects for error reporting
            if (!response.ok && response.status !== 302) {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (reportingError) {
            console.error('Failed to report error:', reportingError);
            // Disable error reporting temporarily to prevent loops
            this.errorReportingEnabled = false;
            setTimeout(() => {
                this.errorReportingEnabled = true;
            }, 30000); // Re-enable after 30 seconds
        }
    }

    /**
     * Show user-friendly error message
     */
    showErrorToUser(error, type) {
        let message = 'An unexpected error occurred';
        let toastType = 'danger';

        // Customize message based on error type
        switch (type) {
            case 'JavaScript Error':
                if (error?.message?.includes('fetch')) {
                    message = 'Network error. Please check your connection and try again.';
                } else if (error?.message?.includes('permission')) {
                    message = 'Permission denied. Please check your browser settings.';
                } else {
                    message = 'A technical error occurred. Please refresh the page.';
                }
                break;

            case 'Unhandled Promise Rejection':
                message = 'A background operation failed. The page may need to be refreshed.';
                break;

            case 'Network Error':
                message = 'Unable to connect to the server. Please check your internet connection.';
                break;

            case 'Validation Error':
                message = error?.message || 'Invalid data provided';
                toastType = 'warning';
                break;

            case 'API Error':
                message = error?.message || 'Server error occurred';
                break;

            default:
                message = error?.message || message;
        }

        showToast(message, toastType);
    }

    /**
     * Handle API errors specifically
     */
    handleApiError(error, endpoint) {
        const apiError = {
            ...error,
            endpoint: endpoint,
            type: 'API Error'
        };

        this.handleError(apiError, 'API Error', { endpoint });
    }

    /**
     * Handle validation errors
     */
    handleValidationError(error, field) {
        const validationError = {
            ...error,
            field: field,
            type: 'Validation Error'
        };

        this.handleError(validationError, 'Validation Error', { field });
    }

    /**
     * Manually report an error
     */
    reportManualError(error, type = 'Manual Error', context = {}) {
        this.handleError(error, type, context);
    }

    /**
     * Reset error count
     */
    resetErrorCount() {
        this.errorCount = 0;
    }

    /**
     * Enable/disable error reporting
     */
    setErrorReporting(enabled) {
        this.errorReportingEnabled = enabled;
    }

    /**
     * Get error statistics
     */
    getErrorStats() {
        return {
            errorCount: this.errorCount,
            maxErrors: this.maxErrors,
            reportingEnabled: this.errorReportingEnabled,
            isOnline: navigator.onLine
        };
    }
}

// Validation Error class
export class ValidationError extends Error {
    constructor(message, field = null) {
        super(message);
        this.name = 'ValidationError';
        this.field = field;
    }
}

// API Error class
export class ApiError extends Error {
    constructor(message, status = null, endpoint = null) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.endpoint = endpoint;
    }
}

// Network Error class
export class NetworkError extends Error {
    constructor(message) {
        super(message);
        this.name = 'NetworkError';
    }
}

// Create singleton instance
export const errorBoundary = new ErrorBoundary();

// Make it globally available
window.errorBoundary = errorBoundary;
window.ValidationError = ValidationError;
window.ApiError = ApiError;
window.NetworkError = NetworkError;