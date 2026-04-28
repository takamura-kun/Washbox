// Tab Fix - Ensures Bootstrap tabs work correctly on all devices
(function() {
    'use strict';
    
    function initTabFix() {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="pill"]');
        
        if (tabButtons.length === 0) return;
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-bs-target');
                if (!targetId) return;
                
                // Use requestAnimationFrame to prevent blocking
                requestAnimationFrame(() => {
                    // Remove active from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Hide all panes (but don't remove their content)
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                        // Don't set display:none, let CSS handle it
                    });
                    
                    // Add active to clicked button
                    this.classList.add('active');
                    
                    // Show target pane
                    const targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                        
                        // Force reflow for mobile
                        targetPane.offsetHeight;
                        
                        // Scroll to top of content on mobile
                        if (window.innerWidth < 768) {
                            targetPane.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }
                });
            });
        });
        
        // Ensure initial active tab is visible (don't touch inactive tabs)
        const activePane = document.querySelector('.tab-pane.active');
        if (activePane) {
            activePane.classList.add('show');
        }
    }
    
    // Use setTimeout to defer initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initTabFix, 0);
        });
    } else {
        setTimeout(initTabFix, 0);
    }
})();
