<?php
/**
 * Events API
 * Handles event CRUD operations
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core.php';
require_once __DIR__ . '/../src/Models.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

try {
    if ($method === 'GET' && $action === 'list') {
        handleListEvents();
    } elseif ($method === 'GET' && $action === 'detail' && $id) {
        handleGetEvent($id);
    } elseif ($method === 'POST' && $action === 'create') {
        handleCreateEvent();
    } elseif ($method === 'PUT' && $action === 'update' && $id) {
        handleUpdateEvent($id);
    } elseif ($method === 'DELETE' && $action === 'delete' && $id) {
        handleDeleteEvent($id);
    } elseif ($method === 'GET' && $action === 'stats') {
        handleGetEventStats();
    } else {
        echo Response::error('Invalid request', 400);
    }
} catch (Exception $e) {
    Logger::error('Events API Error: ' . $e->getMessage());
    echo Response::error('An error occurred: ' . $e->getMessage(), 500);
}

function handleListEvents()
{
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    $filters = [
        'status' => $_GET['status'] ?? null,
        'organizer_id' => $_GET['organizer_id'] ?? null,
        'search' => $_GET['search'] ?? null
    ];

    $eventModel = new Event();
    $events = $eventModel->getAll($page, $limit, $filters);
    $total = $eventModel->getTotalCount($filters);

    echo Response::success(
        [
            'events' => $events,
            'pagination' => [
                'current_page' => $page,
                'total_items' => $total,
                'per_page' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ],
        'Events retrieved successfully',
        200
    );
}

function handleGetEvent($id)
{
    $eventModel = new Event();
    $event = $eventModel->findById($id);

    if (!$event) {
        echo Response::error('Event not found', 404);
        return;
    }

    echo Response::success($event, 'Event retrieved successfully', 200);
}

function handleCreateEvent()
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser || !in_array($currentUser['role'], ['admin', 'organizer'])) {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $rules = [
        'title' => 'required|min:3',
        'location' => 'required',
        'start_date' => 'required|date'
    ];

    if (!Validator::validate($data, $rules)) {
        echo Response::error('Validation failed', 400, Validator::errors());
        return;
    }

    $data['organizer_id'] = $currentUser['id'];

    try {
        $eventModel = new Event();
        $eventId = $eventModel->create($data);

        Logger::info('Event created by user ' . $currentUser['id']);

        echo Response::success(
            ['event_id' => $eventId],
            'Event created successfully',
            201
        );
    } catch (Exception $e) {
        Logger::error('Event creation error: ' . $e->getMessage());
        echo Response::error('Failed to create event', 500);
    }
}

function handleUpdateEvent($id)
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser) {
        echo Response::error('Unauthorized', 401);
        return;
    }

    $eventModel = new Event();
    $event = $eventModel->findById($id);

    if (!$event) {
        echo Response::error('Event not found', 404);
        return;
    }

    if ($event['organizer_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    try {
        if ($eventModel->update($id, $data)) {
            Logger::info('Event ' . $id . ' updated by user ' . $currentUser['id']);
            echo Response::success([], 'Event updated successfully', 200);
        } else {
            echo Response::error('Failed to update event', 400);
        }
    } catch (Exception $e) {
        Logger::error('Event update error: ' . $e->getMessage());
        echo Response::error('An error occurred', 500);
    }
}

function handleDeleteEvent($id)
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser || !in_array($currentUser['role'], ['admin', 'organizer'])) {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $eventModel = new Event();
    $event = $eventModel->findById($id);

    if (!$event) {
        echo Response::error('Event not found', 404);
        return;
    }

    if ($event['organizer_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
        echo Response::error('Unauthorized', 403);
        return;
    }

    try {
        if ($eventModel->delete($id)) {
            Logger::info('Event ' . $id . ' deleted by user ' . $currentUser['id']);
            echo Response::success([], 'Event deleted successfully', 200);
        } else {
            echo Response::error('Failed to delete event', 400);
        }
    } catch (Exception $e) {
        Logger::error('Event deletion error: ' . $e->getMessage());
        echo Response::error('An error occurred', 500);
    }
}

function handleGetEventStats()
{
    $eventModel = new Event();
    $stats = $eventModel->getEventStats();

    echo Response::success($stats, 'Event statistics retrieved', 200);
}
