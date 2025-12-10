import axios, { AxiosInstance, AxiosError } from 'axios';
import type { ApiError } from '@/types';

// Base URL from environment variable or default to localhost
// Uses API v1 by default for versioned endpoints
const BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8001/api/v1';

// Create axios instance with default config
const api: AxiosInstance = axios.create({
    baseURL: BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    timeout: 10000,
});

// Request interceptor
api.interceptors.request.use(
    (config) => {
        // Add auth token if exists (for future use)
        const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor
api.interceptors.response.use(
    (response) => response,
    (error: AxiosError<ApiError>) => {
        // Handle common errors
        if (error.response) {
            const status = error.response.status;
            const message = error.response.data?.message || 'An error occurred';

            switch (status) {
                case 401:
                    console.error('Unauthorized access');
                    // Could redirect to login
                    break;
                case 403:
                    console.error('Forbidden');
                    break;
                case 404:
                    console.error('Resource not found');
                    break;
                case 422:
                    console.error('Validation error:', error.response.data);
                    break;
                case 500:
                    console.error('Server error:', message);
                    break;
            }
        } else if (error.request) {
            console.error('Network error - no response received');
        }

        return Promise.reject(error);
    }
);

export default api;
