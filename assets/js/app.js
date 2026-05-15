/**
 * Business Event Management System - Main Application
 * Handles UI logic and user interactions
 */

const App = (() => {
    let currentUser = null;
    let currentEvent = null;

    const elements = {
        heroSection: document.getElementById('heroSection'),
        contentArea: document.getElementById('content-area'),
        dashboardView: document.getElementById('dashboardView'),
        authView: document.getElementById('authView'),
        eventsListView: document.getElementById('eventsListView'),
        eventDetailView: document.getElementById('eventDetailView'),
        userName: document.getElementById('userName'),
        userProfile: document.getElementById('userProfile'),
        logoutBtn: document.getElementById('logoutBtn'),
        loginLink: document.getElementById('loginLink'),
        createEventBtn: document.getElementById('createEventBtn'),
        createEventModal: new bootstrap.Modal(document.getElementById('createEventModal'))
    };

    const init = async () => {
        await checkAuth();
        attachEventListeners();
    };

    const checkAuth = async () => {
        const token = API.getToken();
        
        if (token) {
            try {
                const response = await API.auth.getProfile();
                if (response.success) {
                    currentUser = response.data;
                    showDashboard();
                } else {
                    API.clearToken();
                    showAuthView();
                }
            } catch (error) {
                console.error('Auth check failed:', error);
                API.clearToken();
                showAuthView();
            }
        } else {
            showAuthView();
        }
    };

    const attachEventListeners = () => {
        // Navigation
        document.getElementById('loginLink').addEventListener('click', (e) => {
            e.preventDefault();
            showAuthView();
        });

        document.getElementById('logoutBtn').addEventListener('click', handleLogout);

        // Hero buttons
        document.getElementById('browseEventsBtn').addEventListener('click', () => showEventsList());
        document.getElementById('createEventBtn').addEventListener('click', () => showCreateEventModal());

        // Dashboard quick actions
        document.getElementById('createEventBtnQuick').addEventListener('click', () => showCreateEventModal());
        document.getElementById('browseEventsBtnQuick').addEventListener('click', () => showEventsList());
        const viewReportsBtnQuick = document.getElementById('viewReportsBtnQuick');
        if (viewReportsBtnQuick && typeof showReports === 'function') {
            viewReportsBtnQuick.addEventListener('click', showReports);
        } else if (viewReportsBtnQuick) {
            // Reports UI isn't mounted in the current page; keep the app usable.
            viewReportsBtnQuick.style.display = 'none';
        }

        // Auth forms
        document.getElementById('loginFormElement').addEventListener('submit', handleLogin);
        document.getElementById('registerFormElement').addEventListener('submit', handleRegister);

        // Events
        document.getElementById('eventSearch').addEventListener('input', debounce(searchEvents, 300));
        document.getElementById('backToEventsBtn').addEventListener('click', () => showEventsList());

        // Create event modal
        document.getElementById('createEventSubmitBtn').addEventListener('click', handleCreateEvent);

        // Event detail buttons
        document.getElementById('registerEventBtn').addEventListener('click', () => handleRegisterEvent());
        document.getElementById('unregisterEventBtn').addEventListener('click', () => handleUnregisterEvent());
        document.getElementById('editEventBtn').addEventListener('click', () => showEditEventModal());
        document.getElementById('deleteEventBtn').addEventListener('click', () => handleDeleteEvent());
        document.getElementById('viewAttendeesBtnDetail').addEventListener('click', () => showAttendeesList());
    };

    const showDashboard = async () => {
        if (elements.heroSection) elements.heroSection.style.display = 'block';
        if (elements.authView) elements.authView.style.display = 'none';
        if (elements.dashboardView) elements.dashboardView.style.display = 'block';
        if (elements.eventsListView) elements.eventsListView.style.display = 'none';
        if (elements.eventDetailView) elements.eventDetailView.style.display = 'none';

        if (elements.userProfile) elements.userProfile.style.display = 'block';
        if (elements.logoutBtn) elements.logoutBtn.style.display = 'block';
        if (elements.loginLink) elements.loginLink.style.display = 'none';
        if (elements.createEventBtn) elements.createEventBtn.style.display = 'block';

        if (elements.userName) elements.userName.textContent = currentUser.name;

        // Load dashboard data
        await loadDashboardStats();
        await loadUpcomingEvents();
    };

    const showAuthView = () => {
        if (elements.heroSection) elements.heroSection.style.display = 'none';
        if (elements.authView) elements.authView.style.display = 'block';
        if (elements.dashboardView) elements.dashboardView.style.display = 'none';
        if (elements.eventsListView) elements.eventsListView.style.display = 'none';
        if (elements.eventDetailView) elements.eventDetailView.style.display = 'none';

        if (elements.userProfile) elements.userProfile.style.display = 'none';
        if (elements.logoutBtn) elements.logoutBtn.style.display = 'none';
        if (elements.loginLink) elements.loginLink.style.display = 'block';
        if (elements.createEventBtn) elements.createEventBtn.style.display = 'none';
    };

    const showEventsList = async () => {
        elements.dashboardView.style.display = 'none';
        elements.authView.style.display = 'none';
        elements.eventsListView.style.display = 'block';
        elements.eventDetailView.style.display = 'none';

        if (currentUser) {
            elements.createEventBtn.style.display = currentUser.role === 'organizer' || currentUser.role === 'admin' ? 'block' : 'none';
        }

        await loadEventsList();
    };

    const showEventDetail = async (eventId) => {
        elements.dashboardView.style.display = 'none';
        elements.authView.style.display = 'none';
        elements.eventsListView.style.display = 'none';
        elements.eventDetailView.style.display = 'block';

        await loadEventDetail(eventId);
    };

    const loadDashboardStats = async () => {
        try {
            const response = await API.events.stats();
            if (response.success) {
                document.getElementById('totalEvents').textContent = response.data.total_events;
                document.getElementById('ongoingEvents').textContent = response.data.ongoing;
                document.getElementById('completedEvents').textContent = response.data.completed;
            }
        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    };

    const loadUpcomingEvents = async () => {
        try {
            const response = await API.events.list(1, 5, { status: 'scheduled' });
            if (response.success) {
                const container = document.getElementById('upcomingEventsList');
                const events = response.data.events;

                if (events.length === 0) {
                    container.innerHTML = '<p class="text-muted">No upcoming events</p>';
                    return;
                }

                container.innerHTML = events.map(event => `
                    <div class="event-card" onclick="App.showEventDetail(${event.id})">
                        <h5>${event.title}</h5>
                        <p><strong>Location:</strong> ${event.location}</p>
                        <p><strong>Start:</strong> ${formatDate(event.start_date)}</p>
                        <div class="event-meta">
                            <span><i class="fas fa-users"></i> ${event.attendee_count}/${event.max_attendees}</span>
                            <span class="badge badge-${event.status}">${event.status}</span>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Failed to load events:', error);
        }
    };

    const loadEventsList = async (page = 1) => {
        try {
            const response = await API.events.list(page, 20);
            if (response.success) {
                const container = document.getElementById('eventsListContainer');
                const events = response.data.events;

                if (events.length === 0) {
                    container.innerHTML = '<p class="text-muted">No events found</p>';
                    return;
                }

                container.innerHTML = events.map(event => `
                    <div class="event-card fade-in" onclick="App.showEventDetail(${event.id})">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5>${event.title}</h5>
                                <p class="text-muted">${event.description ? event.description.substring(0, 100) : 'No description'}</p>
                                <p><strong>Location:</strong> ${event.location}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge badge-${event.status}">${event.status}</span>
                                <p class="mt-2"><strong>${event.attendee_count}/${event.max_attendees}</strong> attendees</p>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Pagination
                const pagination = response.data.pagination;
                if (pagination.total_pages > 1) {
                    let paginationHTML = '<nav><ul class="pagination mt-3">';
                    for (let i = 1; i <= pagination.total_pages; i++) {
                        paginationHTML += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="App.loadEventsList(${i}); return false;">${i}</a>
                        </li>`;
                    }
                    paginationHTML += '</ul></nav>';
                    container.innerHTML += paginationHTML;
                }
            }
        } catch (error) {
            console.error('Failed to load events:', error);
            document.getElementById('eventsListContainer').innerHTML = '<p class="text-danger">Failed to load events</p>';
        }
    };

    const loadEventDetail = async (eventId) => {
        try {
            const response = await API.events.get(eventId);
            if (response.success) {
                currentEvent = response.data;
                populateEventDetail(currentEvent);

                // Show/hide action buttons based on user role
                updateActionButtons();
            }
        } catch (error) {
            console.error('Failed to load event detail:', error);
        }
    };

    const populateEventDetail = (event) => {
        document.getElementById('eventTitle').textContent = event.title;
        document.getElementById('eventOrganizer').textContent = event.organizer_name;
        document.getElementById('eventLocation').textContent = event.location;
        document.getElementById('eventStartDate').textContent = formatDate(event.start_date);
        document.getElementById('eventEndDate').textContent = event.end_date ? formatDate(event.end_date) : 'N/A';
        document.getElementById('eventDescription').textContent = event.description || 'No description provided';
        document.getElementById('eventCapacity').textContent = event.attendee_count;
        document.getElementById('eventMaxCapacity').textContent = event.max_attendees;

        const statusBadge = document.getElementById('eventStatus');
        statusBadge.textContent = event.status;
        statusBadge.className = `badge badge-${event.status}`;
    };

    const updateActionButtons = () => {
        const registerBtn = document.getElementById('registerEventBtn');
        const unregisterBtn = document.getElementById('unregisterEventBtn');
        const editBtn = document.getElementById('editEventBtn');
        const deleteBtn = document.getElementById('deleteEventBtn');
        const viewAttendeesBtn = document.getElementById('viewAttendeesBtnDetail');

        registerBtn.style.display = 'none';
        unregisterBtn.style.display = 'none';
        editBtn.style.display = 'none';
        deleteBtn.style.display = 'none';
        viewAttendeesBtn.style.display = 'none';

        if (!currentUser) return;

        if (currentUser.id === currentEvent.organizer_id || currentUser.role === 'admin') {
            editBtn.style.display = 'block';
            deleteBtn.style.display = 'block';
            viewAttendeesBtn.style.display = 'block';
        } else {
            registerBtn.style.display = 'block';
            unregisterBtn.style.display = 'block';
        }
    };

    const showCreateEventModal = () => {
        if (!currentUser) {
            showAuthView();
            return;
        }
        document.getElementById('createEventForm').reset();
        document.getElementById('createEventError').style.display = 'none';
        elements.createEventModal.show();
    };

    const searchEvents = async () => {
        const searchTerm = document.getElementById('eventSearch').value;
        try {
            const response = await API.events.list(1, 20, { search: searchTerm });
            if (response.success) {
                const events = response.data.events;
                const container = document.getElementById('eventsListContainer');

                if (events.length === 0) {
                    container.innerHTML = '<p class="text-muted">No events found</p>';
                    return;
                }

                container.innerHTML = events.map(event => `
                    <div class="event-card fade-in" onclick="App.showEventDetail(${event.id})">
                        <h5>${event.title}</h5>
                        <p><strong>Location:</strong> ${event.location}</p>
                        <div class="event-meta">
                            <span><i class="fas fa-users"></i> ${event.attendee_count}/${event.max_attendees}</span>
                            <span class="badge badge-${event.status}">${event.status}</span>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Search failed:', error);
        }
    };

    const handleLogin = async (e) => {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        try {
            const response = await API.auth.login(email, password);
            if (response.success) {
                API.setToken(response.data.token);
                currentUser = response.data.user;
                showDashboard();
            } else {
                document.getElementById('loginError').textContent = response.message;
                document.getElementById('loginError').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('loginError').textContent = 'Login failed. Please try again.';
            document.getElementById('loginError').style.display = 'block';
        }
    };

    const handleRegister = async (e) => {
        e.preventDefault();
        const data = {
            name: document.getElementById('registerName').value,
            email: document.getElementById('registerEmail').value,
            phone: document.getElementById('registerPhone').value,
            company: document.getElementById('registerCompany').value,
            password: document.getElementById('registerPassword').value
        };

        try {
            const response = await API.auth.register(data);
            if (response.success) {
                alert('Registration successful! Please login.');
                document.getElementById('registerTab').click();
                document.getElementById('registerFormElement').reset();
            } else {
                document.getElementById('registerError').textContent = response.message;
                document.getElementById('registerError').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('registerError').textContent = 'Registration failed. Please try again.';
            document.getElementById('registerError').style.display = 'block';
        }
    };

    const handleLogout = async () => {
        try {
            await API.auth.logout();
        } catch (error) {
            console.error('Logout error:', error);
        }
        API.clearToken();
        currentUser = null;
        showAuthView();
    };

    const handleCreateEvent = async () => {
        const data = {
            title: document.getElementById('eventTitleInput').value,
            description: document.getElementById('eventDescriptionInput').value,
            location: document.getElementById('eventLocationInput').value,
            start_date: document.getElementById('eventStartDateInput').value,
            end_date: document.getElementById('eventEndDateInput').value,
            max_attendees: parseInt(document.getElementById('eventMaxAttendeesInput').value)
        };

        try {
            const response = await API.events.create(data);
            if (response.success) {
                alert('Event created successfully!');
                elements.createEventModal.hide();
                await showEventsList();
            } else {
                document.getElementById('createEventError').textContent = response.message;
                document.getElementById('createEventError').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('createEventError').textContent = 'Failed to create event';
            document.getElementById('createEventError').style.display = 'block';
        }
    };

    const handleRegisterEvent = async () => {
        try {
            const response = await API.attendees.register(currentEvent.id);
            if (response.success) {
                alert('Registered successfully!');
                await loadEventDetail(currentEvent.id);
            } else {
                alert(response.message);
            }
        } catch (error) {
            alert('Registration failed');
        }
    };

    const handleUnregisterEvent = async () => {
        if (!confirm('Are you sure you want to unregister?')) return;
        try {
            const response = await API.attendees.unregister(currentEvent.id);
            if (response.success) {
                alert('Unregistered successfully!');
                await loadEventDetail(currentEvent.id);
            } else {
                alert(response.message);
            }
        } catch (error) {
            alert('Unregistration failed');
        }
    };

    const handleDeleteEvent = async () => {
        if (!confirm('Are you sure you want to delete this event?')) return;
        try {
            const response = await API.events.delete(currentEvent.id);
            if (response.success) {
                alert('Event deleted successfully!');
                await showEventsList();
            } else {
                alert(response.message);
            }
        } catch (error) {
            alert('Delete failed');
        }
    };

    // Utility functions
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    const debounce = (func, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func(...args), delay);
        };
    };

    // Public API
    return {
        init,
        showEventDetail,
        loadEventsList,
        showEventDetail
    };
})();

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => App.init());
