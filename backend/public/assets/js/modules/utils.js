// utils.js - Utility functions

import { MAP_CONFIG } from './config.js';

/**
 * Show toast notification
 */
export function showToast(message, type = "info") {
    // Use requestAnimationFrame to batch DOM operations
    requestAnimationFrame(() => {
        let toastContainer = document.querySelector(".toast-container");
        if (!toastContainer) {
            toastContainer = document.createElement("div");
            toastContainer.className = "toast-container position-fixed bottom-0 end-0 p-3";
            document.body.appendChild(toastContainer);
        }

        const toastEl = document.createElement("div");
        toastEl.className = `toast align-items-center text-bg-${type} border-0`;
        toastEl.setAttribute("role", "alert");
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Batch the appendChild operation
        toastContainer.appendChild(toastEl);

        if (typeof bootstrap !== "undefined") {
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
            toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
        } else {
            setTimeout(() => toastEl.remove(), 3000);
        }
    });
}

/**
 * Validate coordinates
 */
export function validateCoordinates(lat, lng) {
    const latitude = parseFloat(lat);
    const longitude = parseFloat(lng);
    
    if (isNaN(latitude) || isNaN(longitude)) {
        return { valid: false, error: 'Coordinates must be valid numbers' };
    }
    
    if (latitude === 0 || longitude === 0) {
        return { valid: false, error: 'Coordinates cannot be zero' };
    }
    
    const bounds = MAP_CONFIG.PHILIPPINES_BOUNDS;
    if (latitude < bounds.minLat || latitude > bounds.maxLat || 
        longitude < bounds.minLng || longitude > bounds.maxLng) {
        return { 
            valid: true, 
            warning: 'Location appears to be outside the Philippines' 
        };
    }
    
    return { valid: true, lat: latitude, lng: longitude };
}

/**
 * Decode polyline string to coordinates
 */
export function decodePolyline(encoded) {
    if (!encoded || typeof encoded !== "string") return [];

    // If backend already sent an array of [lat,lng], just return it
    if (encoded.startsWith("[")) return JSON.parse(encoded);

    var points = [];
    var index = 0,
        len = encoded.length;
    var lat = 0,
        lng = 0;

    while (index < len) {
        var b,
            shift = 0,
            result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        var dlat = result & 1 ? ~(result >> 1) : result >> 1;
        lat += dlat;

        shift = 0;
        result = 0;
        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);
        var dlng = result & 1 ? ~(result >> 1) : result >> 1;
        lng += dlng;

        points.push([lat / 1e5, lng / 1e5]);
    }
    return points;
}

/**
 * Copy text to clipboard
 */
export function copyToClipboard(text) {
    if (navigator.clipboard) {
        return navigator.clipboard.writeText(text)
            .then(() => {
                showToast("📋 Copied to clipboard!", "success");
                return true;
            })
            .catch(() => {
                return fallbackCopy(text);
            });
    } else {
        return fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand("copy");
        showToast("📋 Copied!", "success");
        return true;
    } catch (err) {
        showToast("Failed to copy", "danger");
        return false;
    } finally {
        document.body.removeChild(textarea);
    }
}

/**
 * Debounce function
 */
export function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Get status color class for Bootstrap
 */
export function getStatusColor(status) {
    const colors = {
        pending: "warning",
        accepted: "info",
        en_route: "primary",
        picked_up: "success",
        cancelled: "danger",
    };
    return colors[status] || "secondary";
}

/**
 * Format distance
 */
export function formatDistance(meters) {
    if (meters < 1000) {
        return `${Math.round(meters)} m`;
    }
    return `${(meters / 1000).toFixed(2)} km`;
}

/**
 * Format duration
 */
export function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes} min`;
}

/**
 * Update element text content
 */
export function updateElementText(selector, text) {
    const element = document.querySelector(selector);
    if (element) element.textContent = text;
}

/**
 * Get current position using Geolocation API
 */
export function getCurrentPosition() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation is not supported by this browser'));
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            resolve,
            reject,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5 minutes
            }
        );
    });
}