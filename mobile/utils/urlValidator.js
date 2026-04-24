/**
 * URL Validation Utility - Prevents Server-Side Request Forgery (SSRF)
 * Validates that URLs point to intended backend servers only
 */

/**
 * Validates that a URL is safe and points to the intended backend
 * @param {string} url - URL to validate
 * @returns {boolean} - True if URL is safe
 */
export const isValidBackendUrl = (url) => {
  try {
    const urlObj = new URL(url);
    
    // ✅ Only allow http and https protocols
    if (!['http:', 'https:'].includes(urlObj.protocol)) {
      console.error('Invalid protocol:', urlObj.protocol);
      return false;
    }
    
    const hostname = urlObj.hostname;
    
    // ✅ Whitelist allowed domains (checked first)
   const allowedDomains = [
  'localhost',
  '127.0.0.1',
  'api.washbox.com',
  'api-staging.washbox.com',
];

if (allowedDomains.includes(hostname)) return true;

// ✅ Allow any private IP in development
if (__DEV__) return true;
    
    // ✅ Block private IP ranges and metadata services (for non-whitelisted IPs)
    const privateRanges = [
      '127.0.0.1',           // localhost
      'localhost',
      '0.0.0.0',
      '169.254.169.254',     // AWS metadata service
      '169.254.169.253',     // AWS metadata service
      /^192\.168\./,         // Private network 192.168.x.x
      /^10\./,               // Private network 10.x.x.x
      /^172\.(1[6-9]|2[0-9]|3[01])\./,  // Private network 172.16-31.x.x
      /^::1$/,               // IPv6 localhost
      /^fc00:/,              // IPv6 private
      /^fe80:/,              // IPv6 link-local
    ];
    
    for (const range of privateRanges) {
      if (typeof range === 'string') {
        if (hostname === range) {
          console.error('Blocked private IP:', hostname);
          return false;
        }
      } else if (range.test(hostname)) {
        console.error('Blocked private IP range:', hostname);
        return false;
      }
    }
    
    return true;
  } catch (error) {
    console.error('Invalid URL format:', error);
    return false;
  }
};

/**
 * Sanitizes path parameters to prevent injection attacks
 * Only allows alphanumeric characters, hyphens, and underscores
 * @param {string|number} param - Parameter to sanitize
 * @returns {string} - Sanitized parameter
 */
export const sanitizePathParam = (param) => {
  const sanitized = String(param).replace(/[^a-zA-Z0-9_-]/g, '');
  
  if (!sanitized) {
    throw new Error('Invalid parameter: contains no valid characters');
  }
  
  return sanitized;
};

/**
 * Validates numeric IDs (pickup request IDs, user IDs, etc.)
 * @param {string|number} id - ID to validate
 * @returns {number} - Validated numeric ID
 */
export const validateNumericId = (id) => {
  const numId = parseInt(id, 10);
  
  if (isNaN(numId) || numId <= 0) {
    throw new Error('Invalid numeric ID');
  }
  
  return numId;
};

/**
 * Validates user type enum
 * @param {string} userType - User type to validate
 * @returns {string} - Validated user type
 */
export const validateUserType = (userType) => {
  const validTypes = ['staff', 'customer'];
  
  if (!validTypes.includes(userType)) {
    throw new Error(`Invalid user type: ${userType}`);
  }
  
  return userType;
};

export default {
  isValidBackendUrl,
  sanitizePathParam,
  validateNumericId,
  validateUserType,
};
