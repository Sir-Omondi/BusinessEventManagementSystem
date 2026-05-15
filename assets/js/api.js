/**
 * Business Event Management System - API Module
 * Handles all API calls to the backend
 */

const API = (() => {
    const BASE_URL = 'http://localhost/BusinessEventManagementSystem/api/';
    let token = localStorage.getItem('auth_token');

    return {
        setToken(newToken) {
            token = newToken;
            localStorage.setItem('auth_token', newToken);
        },

        getToken() {
            return token;
        },

        clearToken() {
            token = null;
            localStorage.removeItem('auth_token');
        },

        // Authentication endpoints
        auth: {
            register(data) {
                return fetch(BASE_URL + 'auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                }).then(res => res.json());
            },

            login(email, password) {
                return fetch(BASE_URL + 'auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                }).then(res => res.json());
            },

            getProfile() {
                return fetch(BASE_URL + 'auth.php?action=profile', {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            logout() {
                return fetch(BASE_URL + 'auth.php?action=logout', {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            }
        },

        // Events endpoints
        events: {
            list(page = 1, limit = 20, filters = {}) {
                let url = BASE_URL + `events.php?action=list&page=${page}&limit=${limit}`;
                
                if (filters.status) url += `&status=${filters.status}`;
                if (filters.search) url += `&search=${encodeURIComponent(filters.search)}`;
                if (filters.organizer_id) url += `&organizer_id=${filters.organizer_id}`;

                return fetch(url, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            get(id) {
                return fetch(BASE_URL + `events.php?action=detail&id=${id}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            create(data) {
                return fetch(BASE_URL + 'events.php?action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(data)
                }).then(res => res.json());
            },

            update(id, data) {
                return fetch(BASE_URL + `events.php?action=update&id=${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(data)
                }).then(res => res.json());
            },

            delete(id) {
                return fetch(BASE_URL + `events.php?action=delete&id=${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            stats() {
                return fetch(BASE_URL + 'events.php?action=stats', {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            }
        },

        // Attendees endpoints
        attendees: {
            list(eventId, page = 1, limit = 50) {
                return fetch(BASE_URL + `attendees.php?action=list&event_id=${eventId}&page=${page}&limit=${limit}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            register(eventId) {
                return fetch(BASE_URL + `attendees.php?action=register&event_id=${eventId}`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            unregister(eventId) {
                return fetch(BASE_URL + `attendees.php?action=unregister&event_id=${eventId}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            checkIn(attendeeId) {
                return fetch(BASE_URL + `attendees.php?action=checkin&attendee_id=${attendeeId}`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            checkOut(attendeeId) {
                return fetch(BASE_URL + `attendees.php?action=checkout&attendee_id=${attendeeId}`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            }
        },

        // Reports endpoints
        reports: {
            eventReport(eventId) {
                return fetch(BASE_URL + `reports.php?action=event_report&event_id=${eventId}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            attendeeReport(eventId) {
                return fetch(BASE_URL + `reports.php?action=attendee_report&event_id=${eventId}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            summary() {
                return fetch(BASE_URL + 'reports.php?action=summary', {
                    headers: { 'Authorization': `Bearer ${token}` }
                }).then(res => res.json());
            },

            exportCSV(eventId) {
                window.location.href = BASE_URL + `reports.php?action=export&event_id=${eventId}`;
            }
        }
    };
})();
