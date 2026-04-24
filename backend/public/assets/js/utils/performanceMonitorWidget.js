/**
 * Performance Monitor Widget
 * Displays real-time performance metrics on the dashboard
 */

class PerformanceMonitorWidget {
    constructor() {
        this.widget = null;
        this.isVisible = false;
        this.updateInterval = null;
        this.metrics = {
            violations: 0,
            avgDuration: 0,
            worstViolation: { type: null, duration: 0 },
            memoryUsage: 0,
            loadTime: 0
        };
    }

    init() {
        this.createWidget();
        this.startMonitoring();
        this.setupEventListeners();
        
        if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
            this.show();
        }
    }

    createWidget() {
        this.widget = document.createElement('div');
        this.widget.id = 'performance-monitor-widget';
        this.widget.innerHTML = `
            <div class="perf-widget-header">
                <span class="perf-widget-title">⚡ Performance</span>
                <button class="perf-widget-toggle" onclick="window.performanceMonitorWidget.toggle()">
                    <i class="bi bi-chevron-up"></i>
                </button>
            </div>
            <div class="perf-widget-content">
                <div class="perf-metric">
                    <span class="perf-label">Violations:</span>
                    <span class="perf-value" id="perf-violations">0</span>
                </div>
                <div class="perf-metric">
                    <span class="perf-label">Avg Duration:</span>
                    <span class="perf-value" id="perf-avg-duration">0ms</span>
                </div>
                <div class="perf-metric">
                    <span class="perf-label">Worst:</span>
                    <span class="perf-value" id="perf-worst">None</span>
                </div>
                <div class="perf-metric">
                    <span class="perf-label">Memory:</span>
                    <span class="perf-value" id="perf-memory">0MB</span>
                </div>
                <div class="perf-actions">
                    <button class="perf-btn" onclick="window.performanceMonitorWidget.clearViolations()">
                        Clear
                    </button>
                    <button class="perf-btn" onclick="window.performanceMonitorWidget.exportMetrics()">
                        Export
                    </button>
                </div>
            </div>
        `;
        
        const styles = `
            <style>
                #performance-monitor-widget {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    width: 250px;
                    background: var(--card-bg);
                    color: var(--text-primary);
                    border-radius: 12px;
                    box-shadow: var(--shadow-lg);
                    backdrop-filter: blur(10px);
                    border: 1px solid var(--border-color);
                    font-family: system-ui, -apple-system, sans-serif;
                    font-size: 12px;
                    z-index: 10000;
                    transition: all 0.3s ease;
                    display: none;
                }
                
                .perf-widget-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 12px 16px;
                    border-bottom: 1px solid var(--border-color);
                    cursor: pointer;
                }
                
                .perf-widget-title {
                    font-weight: 600;
                    font-size: 13px;
                    color: var(--text-primary);
                }
                
                .perf-widget-toggle {
                    background: none;
                    border: none;
                    color: var(--text-primary);
                    cursor: pointer;
                    padding: 4px;
                    border-radius: 4px;
                    transition: background 0.2s;
                }
                
                .perf-widget-toggle:hover {
                    background: var(--border-color);
                }
                
                .perf-widget-content {
                    padding: 12px 16px;
                    max-height: 200px;
                    overflow-y: auto;
                }
                
                .perf-widget-content.collapsed {
                    display: none;
                }
                
                .perf-metric {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 8px;
                    padding: 4px 0;
                }
                
                .perf-label {
                    color: var(--text-secondary);
                }
                
                .perf-value {
                    font-weight: 600;
                    color: #10b981;
                }
                
                .perf-value.warning {
                    color: #f59e0b;
                }
                
                .perf-value.danger {
                    color: #ef4444;
                }
                
                .perf-actions {
                    display: flex;
                    gap: 8px;
                    margin-top: 12px;
                    padding-top: 12px;
                    border-top: 1px solid var(--border-color);
                }
                
                .perf-btn {
                    flex: 1;
                    background: rgba(59, 130, 246, 0.1);
                    border: 1px solid rgba(59, 130, 246, 0.3);
                    color: #3b82f6;
                    padding: 6px 12px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 11px;
                    transition: all 0.2s;
                }
                
                .perf-btn:hover {
                    background: rgba(59, 130, 246, 0.2);
                    border-color: rgba(59, 130, 246, 0.5);
                }
                
                [data-theme="dark"] .perf-btn {
                    color: #60a5fa;
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
        document.body.appendChild(this.widget);
    }

    startMonitoring() {
        this.updateInterval = setInterval(() => {
            this.updateMetrics();
        }, 2000);
    }

    updateMetrics() {
        if (!this.isVisible) return;
        
        if (window.performanceGuard) {
            const violations = window.performanceGuard.getViolations();
            const summary = window.performanceGuard.getViolationSummary();
            
            this.metrics.violations = violations.length;
            
            if (Object.keys(summary).length > 0) {
                const totalDuration = Object.values(summary).reduce((sum, s) => sum + s.totalDuration, 0);
                const totalCount = Object.values(summary).reduce((sum, s) => sum + s.count, 0);
                this.metrics.avgDuration = totalCount > 0 ? (totalDuration / totalCount) : 0;
                
                let worstType = null;
                let worstDuration = 0;
                Object.entries(summary).forEach(([type, data]) => {
                    if (data.maxDuration > worstDuration) {
                        worstDuration = data.maxDuration;
                        worstType = type;
                    }
                });
                
                this.metrics.worstViolation = { type: worstType, duration: worstDuration };
            }
        }
        
        if (performance.memory) {
            this.metrics.memoryUsage = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024);
        }
        
        this.updateDisplay();
    }

    updateDisplay() {
        const violationsEl = document.getElementById('perf-violations');
        const avgDurationEl = document.getElementById('perf-avg-duration');
        const worstEl = document.getElementById('perf-worst');
        const memoryEl = document.getElementById('perf-memory');
        
        if (violationsEl) {
            violationsEl.textContent = this.metrics.violations;
            violationsEl.className = 'perf-value ' + (this.metrics.violations > 5 ? 'danger' : this.metrics.violations > 2 ? 'warning' : '');
        }
        
        if (avgDurationEl) {
            avgDurationEl.textContent = `${this.metrics.avgDuration.toFixed(1)}ms`;
            avgDurationEl.className = 'perf-value ' + (this.metrics.avgDuration > 50 ? 'danger' : this.metrics.avgDuration > 25 ? 'warning' : '');
        }
        
        if (worstEl) {
            const worst = this.metrics.worstViolation;
            worstEl.textContent = worst.type ? `${worst.type} (${worst.duration.toFixed(1)}ms)` : 'None';
            worstEl.className = 'perf-value ' + (worst.duration > 100 ? 'danger' : worst.duration > 50 ? 'warning' : '');
        }
        
        if (memoryEl) {
            memoryEl.textContent = `${this.metrics.memoryUsage}MB`;
            memoryEl.className = 'perf-value ' + (this.metrics.memoryUsage > 100 ? 'danger' : this.metrics.memoryUsage > 50 ? 'warning' : '');
        }
    }

    setupEventListeners() {
        window.addEventListener('performanceViolation', (event) => {
            if (this.isVisible) {
                this.updateMetrics();
            }
        });
        
        document.addEventListener('keydown', (event) => {
            if (event.ctrlKey && event.shiftKey && event.key === 'P') {
                event.preventDefault();
                this.toggle();
            }
        });
    }

    show() {
        if (this.widget) {
            this.widget.style.display = 'block';
            this.isVisible = true;
            this.updateMetrics();
        }
    }

    hide() {
        if (this.widget) {
            this.widget.style.display = 'none';
            this.isVisible = false;
        }
    }

    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }

    clearViolations() {
        if (window.performanceGuard) {
            window.performanceGuard.clearViolations();
            this.updateMetrics();
            console.log('🧹 Performance violations cleared');
        }
    }

    exportMetrics() {
        const exportData = {
            timestamp: new Date().toISOString(),
            metrics: this.metrics,
            violations: window.performanceGuard ? window.performanceGuard.getViolations() : [],
            summary: window.performanceGuard ? window.performanceGuard.getViolationSummary() : {},
            optimizerMetrics: window.postLoadOptimizer ? window.postLoadOptimizer.getMetrics() : {},
            stabilizerStats: window.dataStabilizer ? window.dataStabilizer.getStats() : {}
        };
        
        console.log('📊 Performance Metrics Export:', exportData);
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(JSON.stringify(exportData, null, 2))
                .then(() => console.log('📋 Metrics copied to clipboard'))
                .catch(() => console.log('❌ Failed to copy to clipboard'));
        }
    }

    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        
        if (this.widget) {
            this.widget.remove();
        }
    }
}

// Initialize performance monitor widget - Deferred
const performanceMonitorWidget = new PerformanceMonitorWidget();

// Defer initialization to prevent blocking
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => performanceMonitorWidget.init(), 500);
    });
} else {
    setTimeout(() => performanceMonitorWidget.init(), 500);
}

window.performanceMonitorWidget = performanceMonitorWidget;

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PerformanceMonitorWidget, performanceMonitorWidget };
}

console.log('📊 Performance Monitor Widget initialized - Press Ctrl+Shift+P to toggle');
