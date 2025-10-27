/**
 * Main Alpine.js Application
 * Initializes stores and components
 */
import Alpine from 'alpinejs';
import ky from 'ky';
import * as bootstrap from 'bootstrap';
import { createIcons, Plus, Save, Edit, Check, Trash, Trash2, User, Users, X, FileText, Home, Settings, Euro, LogOut, FileSpreadsheet, DollarSign, GlassWater } from 'lucide';
import stores from './alpine/stores/index.js';
import { initModals } from './alpine/modal.js';
import { logger } from './utils/logger.js';
import { MODALS } from './constants/modals.js';

// Import Tabler CSS and JS
import '@tabler/core/dist/css/tabler.min.css';
import '@tabler/icons-webfont/dist/tabler-icons.min.css';
import '@tabler/core/dist/js/tabler.min.js';

// Import plugins
import { Notyf } from 'notyf';
import flatpickr from 'flatpickr';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect/index.js';

// Expose libraries globally
window.bootstrap = bootstrap;
window.Notyf = Notyf;
window.flatpickr = flatpickr;
window.monthSelectPlugin = monthSelectPlugin;

// Import components
import membersManager from './alpine/components/members.js';
import memberOrderModal from './alpine/components/member-order-modal.js';
import memberEditModal from './alpine/components/member-edit-modal.js';
import groupOrderModal from './alpine/components/group-order-modal.js';
import groupEditModal from './alpine/components/group-edit-modal.js';
import productsManager from './alpine/components/products.js';
import productEditModal from './alpine/components/product-edit-modal.js';
import invoiceManager from './alpine/components/invoice.js';
import groupsManager from './alpine/components/groups.js';
import fiscusManager from './alpine/components/fiscus.js';
import confirmModal from './alpine/components/confirm-modal.js';
import searchableDropdown from './alpine/components/searchable-dropdown.js';

// Configure ky with CSRF token and default headers
const http = ky.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
    },
    hooks: {
        afterResponse: [
            async (_request, _options, response) => {
                // Attach data property for axios-like interface
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    if (contentType?.includes('application/json')) {
                        const data = await response.clone().json();
                        response.data = data;
                    }
                }
                return response;
            },
        ],
    },
});

// Wrap ky methods to return axios-like response format
const httpWrapper = {
    async get(url, options = {}) {
        logger.http('GET', url);
        try {
            const response = await http.get(url, options);
            const contentType = response.headers.get('content-type');

            // Check if response is JSON or HTML
            let data;
            if (contentType?.includes('application/json')) {
                data = await response.json();
            } else {
                // For HTML responses (like modal content), return as text
                data = await response.text();
            }

            logger.http('GET response', url, data);
            return { data };
        } catch (error) {
            logger.error('HTTP GET error', { url, error });
            throw error;
        }
    },
    async post(url, data, options = {}) {
        logger.http('POST', url, data);
        try {
            let response;

            // For FormData, use fetch directly to avoid ky configuration issues
            if (data instanceof FormData) {
                // Use fetch directly for FormData to avoid ky header conflicts
                response = await fetch(url, {
                    method: 'POST',
                    body: data,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: response.statusText }));
                    const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
                    error.response = { status: response.status, data: errorData };
                    throw error;
                }
            } else {
                // For JSON, use ky as normal
                response = await http.post(url, {
                    ...options,
                    body: JSON.stringify(data),
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    }
                });
            }

            const responseData = await response.json();
            logger.http('POST response', url, responseData);
            return { data: responseData };
        } catch (error) {
            logger.error('HTTP POST error', { url, error });

            // If it's a ky HTTPError, transform it to axios-like format
            if (error.response && !error.response.data) {
                try {
                    const errorData = await error.response.json();
                    error.response = {
                        status: error.response.status,
                        data: errorData
                    };
                } catch (e) {
                    // If response is not JSON, just keep the original error
                }
            }

            throw error;
        }
    },
    async put(url, data, options = {}) {
        logger.http('PUT', url, data);
        try {
            const requestOptions = {
                ...options,
            };

            // FormData should be passed as-is, ky will handle it correctly
            if (data instanceof FormData) {
                requestOptions.body = data;
                // Don't set Content-Type header for FormData, let the browser set it with boundary
            } else {
                requestOptions.body = JSON.stringify(data);
                requestOptions.headers = {
                    'Content-Type': 'application/json',
                    ...options.headers
                };
            }

            const response = await http.put(url, requestOptions);
            const responseData = await response.json();
            logger.http('PUT response', url, responseData);
            return { data: responseData };
        } catch (error) {
            logger.error('HTTP PUT error', { url, error });
            throw error;
        }
    },
    async delete(url, options = {}) {
        logger.http('DELETE', url);
        try {
            const response = await http.delete(url, options);
            const data = await response.json();
            logger.http('DELETE response', url, data);
            return { data };
        } catch (error) {
            logger.error('HTTP DELETE error', { url, error });
            throw error;
        }
    },
};

// Make utilities available globally
window.http = httpWrapper;
window.ky = http;
window.logger = logger;
window.MODALS = MODALS;

/**
 * Global confirm helper function
 * Replaces native confirm() with custom modal
 * @param {Object} options - Configuration options
 * @returns {Promise} - Resolves when confirmed, rejects when cancelled
 */
window.confirmAction = (options = {}) => {
    return new Promise((resolve, reject) => {
        // Get or create confirm modal component
        const confirmModalEl = document.querySelector('[x-data*="confirmModal"]');
        if (!confirmModalEl) {
            logger.error('Confirm modal not found in DOM');
            reject(new Error('Confirm modal not available'));
            return;
        }

        // Get Alpine component instance
        const modalComponent = Alpine.$data(confirmModalEl);
        if (!modalComponent) {
            logger.error('Confirm modal Alpine component not initialized');
            reject(new Error('Confirm modal not initialized'));
            return;
        }

        // Show modal with promise handlers
        modalComponent.show({
            ...options,
            onConfirm: () => {
                resolve(true);
            }
        });

        // Handle cancel by rejecting (modal close without confirm)
        const handleCancel = () => {
            reject(new Error('User cancelled'));
        };

        // Listen for modal hidden event
        const modalEl = document.getElementById('confirm-modal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', handleCancel, { once: true });
        }
    });
};

// Register stores
document.addEventListener('alpine:init', () => {
    // Initialize modals
    initModals();

    // Register all stores
    Object.keys(stores).forEach(storeName => {
        Alpine.store(storeName, stores[storeName]);
    });

    // Register components
    Alpine.data('membersManager', membersManager);
    Alpine.data('memberOrderModal', () => memberOrderModal);
    Alpine.data('memberEditModal', memberEditModal);
    Alpine.data('groupOrderModal', () => groupOrderModal);
    Alpine.data('groupEditModal', groupEditModal);
    Alpine.data('productsManager', productsManager);
    Alpine.data('productEditModal', productEditModal);
    Alpine.data('invoiceManager', invoiceManager);
    Alpine.data('groupsManager', groupsManager);
    Alpine.data('fiscusManager', fiscusManager);
    Alpine.data('confirmModal', confirmModal);
    Alpine.data('searchableDropdown', searchableDropdown);
});

// Start Alpine
window.Alpine = Alpine;
Alpine.start();

// Initialize Lucide icons once after Alpine starts
document.addEventListener('DOMContentLoaded', () => {
    createIcons({
        icons: {
            Plus,
            Save,
            Edit,
            Check,
            Trash,
            Trash2,
            User,
            Users,
            X,
            FileText,
            Home,
            Settings,
            Euro,
            LogOut,
            FileSpreadsheet,
            DollarSign,
            GlassWater,
        },
    });
});

// Make icon refresh available globally for modals
window.refreshIcons = () => {
    createIcons({
        icons: {
            Plus,
            Save,
            Edit,
            Check,
            Trash,
            Trash2,
            User,
            Users,
            X,
            FileText,
            Home,
            Settings,
            Euro,
            LogOut,
            FileSpreadsheet,
            DollarSign,
            GlassWater,
        },
    });
};
