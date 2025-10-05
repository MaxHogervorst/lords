/**
 * Logger Utility
 * Provides logging that can be disabled in production builds
 */

const isDevelopment = import.meta.env.DEV;

/**
 * HTTP request logger
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {string} url - Request URL
 * @param {*} data - Optional request/response data
 */
function http(method, url, data = null) {
    if (!isDevelopment) return;

    if (data) {
        console.log(`[HTTP] ${method}`, url, typeof data === 'string' ? 'HTML content' : data);
    } else {
        console.log(`[HTTP] ${method}`, url);
    }
}

/**
 * Error logger (always enabled, even in production)
 * @param {string} message - Error message
 * @param {Error|*} error - Error object or additional data
 */
function error(message, error = null) {
    if (error) {
        console.error(`[ERROR] ${message}`, error);
    } else {
        console.error(`[ERROR] ${message}`);
    }
}

/**
 * Warning logger
 * @param {string} message - Warning message
 * @param {*} data - Optional additional data
 */
function warn(message, data = null) {
    if (!isDevelopment) return;

    if (data) {
        console.warn(`[WARN] ${message}`, data);
    } else {
        console.warn(`[WARN] ${message}`);
    }
}

/**
 * Info logger
 * @param {string} message - Info message
 * @param {*} data - Optional additional data
 */
function info(message, data = null) {
    if (!isDevelopment) return;

    if (data) {
        console.info(`[INFO] ${message}`, data);
    } else {
        console.info(`[INFO] ${message}`);
    }
}

/**
 * Debug logger (most verbose)
 * @param {string} message - Debug message
 * @param {*} data - Optional additional data
 */
function debug(message, data = null) {
    if (!isDevelopment) return;

    if (data) {
        console.debug(`[DEBUG] ${message}`, data);
    } else {
        console.debug(`[DEBUG] ${message}`);
    }
}

export const logger = {
    http,
    error,
    warn,
    info,
    debug,
    isDevelopment
};
