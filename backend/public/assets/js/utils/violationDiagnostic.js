/**
 * Performance Violation Diagnostic Tool
 * Identifies exact sources of Chrome performance violations
 */

class ViolationDiagnostic {
    constructor() {
        this.violations = [];
        this.isMonitoring = false;
        this.startTime = performance.now();
        this.init();
    }

    init() {
        this.monitorDOMContentLoaded();
        this.monitorRequestAnimationFrame();
        this.monitorForcedReflows();
        this.monitorScriptLoading();
        this.startMonitoring();
    }

    startMonitoring() {
        this.isMonitoring = true;
        console.log('🔍 Performance Violation Diagnostic started');
        
        // Monitor for 10 seconds after page load
        setTimeout(() => {
            this.generateReport();
        }, 10000);
    }

    monitorDOMContentLoaded() {
        const originalAddEventListener = document.addEventListener;
        const self = this;
        
        document.addEventListener = function(type, listener, options) {
            if (type === 'DOMContentLoaded') {
                const wrappedListener = function(event) {
                    const startTime = performance.now();
                    const stack = new Error().stack;
                    
                    try {
                        listener.call(this, event);
                    } finally {
                        const duration = performance.now() - startTime;
                        if (duration > 16) {
                            self.recordViolation('DOMContentLoaded', duration, {
                                listener: listener.toString().substring(0, 200),
                                stack: stack,
                                file: self.extractFileFromStack(stack)
                            });
                        }
                    }
                };
                
                return originalAddEventListener.call(this, type, wrappedListener, options);
            }
            
            return originalAddEventListener.call(this, type, listener, options);
        };
    }

    monitorRequestAnimationFrame() {
        const originalRAF = window.requestAnimationFrame;
        const self = this;
        
        window.requestAnimationFrame = function(callback) {
            const wrappedCallback = function(timestamp) {
                const startTime = performance.now();
                const stack = new Error().stack;
                
                try {
                    callback.call(this, timestamp);
                } finally {
                    const duration = performance.now() - startTime;
                    if (duration > 16) {
                        self.recordViolation('requestAnimationFrame', duration, {
                            callback: callback.toString().substring(0, 200),
                            stack: stack,
                            file: self.extractFileFromStack(stack)
                        });
                    }
                }
            };
            
            return originalRAF.call(this, wrappedCallback);
        };
    }

    monitorForcedReflows() {
        // Monitor forced reflows by wrapping DOM manipulation methods
        const self = this;
        
        // Wrap getComputedStyle only (safer approach)
        if (window.getComputedStyle) {
            const originalGetComputedStyle = window.getComputedStyle;
            window.getComputedStyle = function(element, pseudoElement) {
                const startTime = performance.now();
                const result = originalGetComputedStyle.call(window, element, pseudoElement);
                const duration = performance.now() - startTime;
                
                if (duration > 5) {
                    const stack = new Error().stack;
                    self.recordViolation('ForcedReflow', duration, {
                        method: 'getComputedStyle',
                        element: element ? element.tagName : 'unknown',
                        stack: stack,
                        file: self.extractFileFromStack(stack)
                    });
                }
                
                return result;
            };
        }
    }

    monitorScriptLoading() {
        // Monitor script loading errors
        window.addEventListener('error', (event) => {
            if (event.target && event.target.tagName === 'SCRIPT') {
                this.recordViolation('ScriptError', 0, {
                    src: event.target.src,
                    error: event.message,
                    stack: event.error ? event.error.stack : 'No stack trace'
                });
            }
        });
        
        // Monitor module loading errors
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.message && event.reason.message.includes('import')) {
                this.recordViolation('ModuleError', 0, {
                    error: event.reason.message,
                    stack: event.reason.stack
                });
            }
        });
    }

    recordViolation(type, duration, details = {}) {
        const violation = {
            type,
            duration: Math.round(duration * 100) / 100,
            timestamp: performance.now() - this.startTime,
            details,
            url: window.location.href
        };
        
        this.violations.push(violation);
        
        // Log immediately for debugging
        console.warn(`🚨 ${type} Violation: ${violation.duration}ms`, violation);
    }

    extractFileFromStack(stack) {
        if (!stack) return 'unknown';
        
        const lines = stack.split('\n');
        for (let line of lines) {
            if (line.includes('.js:')) {
                const match = line.match(/([^/]+\.js):(\d+):(\d+)/);
                if (match) {
                    return `${match[1]}:${match[2]}`;
                }
            }
        }
        return 'unknown';
    }

    generateReport() {
        console.log('\n📊 PERFORMANCE VIOLATION REPORT');
        console.log('================================');
        
        if (this.violations.length === 0) {
            console.log('✅ No performance violations detected!');
            return;
        }
        
        // Group by type
        const byType = {};
        this.violations.forEach(v => {
            if (!byType[v.type]) {
                byType[v.type] = [];
            }
            byType[v.type].push(v);
        });
        
        // Report by type
        Object.keys(byType).forEach(type => {
            const violations = byType[type];
            console.log(`\n🔴 ${type} Violations: ${violations.length}`);
            
            // Sort by duration (highest first)
            violations.sort((a, b) => b.duration - a.duration);
            
            violations.slice(0, 5).forEach((v, i) => {
                console.log(`  ${i + 1}. ${v.duration}ms at ${v.timestamp.toFixed(0)}ms`);
                if (v.details.file) {
                    console.log(`     File: ${v.details.file}`);
                }
                if (v.details.error) {
                    console.log(`     Error: ${v.details.error}`);
                }
            });
        });
        
        // Export data for further analysis
        window.violationData = {
            violations: this.violations,
            summary: byType,
            totalViolations: this.violations.length,
            worstViolation: this.violations.reduce((worst, current) => 
                current.duration > worst.duration ? current : worst, 
                { duration: 0 }
            )
        };
        
        console.log('\n📋 Violation data exported to window.violationData');
    }

    getViolations() {
        return this.violations;
    }

    clearViolations() {
        this.violations = [];
    }
}

// Auto-start diagnostic
const diagnostic = new ViolationDiagnostic();
window.violationDiagnostic = diagnostic;

console.log('🔍 Performance Violation Diagnostic initialized');