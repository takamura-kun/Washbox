/**
 * api.js
 * Centralized API client with:
 * - Auth token injection
 * - 401 auto-logout
 * - Request timeout (10s)
 * - No sensitive data in logs
 */
import { API_BASE_URL } from '../constants/config';
import { getItem, removeItem } from './secureStorage';
import { STORAGE_KEYS } from '../constants/config';

const TIMEOUT_MS = 10000;

let _logoutCallback = null;

export const setLogoutCallback = (fn) => {
  _logoutCallback = fn;
};

const withTimeout = (promise) => {
  return Promise.race([
    promise,
    new Promise((_, reject) =>
      setTimeout(() => reject(new Error('Request timed out')), TIMEOUT_MS)
    ),
  ]);
};

export const apiRequest = async (endpoint, options = {}) => {
  const token = await getItem(STORAGE_KEYS.TOKEN);

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...options.headers,
  };

  const response = await withTimeout(
    fetch(`${API_BASE_URL}${endpoint}`, { ...options, headers })
  );

  if (response.status === 401) {
    await removeItem(STORAGE_KEYS.TOKEN);
    await removeItem(STORAGE_KEYS.CUSTOMER);
    if (_logoutCallback) _logoutCallback();
    throw new Error('Session expired. Please login again.');
  }

  return response;
};

export const get = (endpoint, options = {}) =>
  apiRequest(endpoint, { ...options, method: 'GET' });

export const post = (endpoint, body, options = {}) =>
  apiRequest(endpoint, { ...options, method: 'POST', body: JSON.stringify(body) });

export const put = (endpoint, body, options = {}) =>
  apiRequest(endpoint, { ...options, method: 'PUT', body: JSON.stringify(body) });

export const del = (endpoint, options = {}) =>
  apiRequest(endpoint, { ...options, method: 'DELETE' });
