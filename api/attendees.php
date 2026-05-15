<?php
/**
 * Attendees API
 * Handles attendee registration, check-in/out, and management
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Core.php';
require_once __DIR__ . '/../../src/Models.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$eventId = $_GET['event_id'] ?? null;
$attendeeId = $_GET['attendee_id'] ?? null;

try {
    if ($method === 'GET' && $action === 'list' && $eventId) {
        handleListAttendees($eventId);
    } elseif ($method === 'POST' && $action === 'register' && $eventId) {
        handleRegisterAttendee($eventId);
    } elseif ($method === 'POST' && $action === 'checkin' && $attendeeId) {
        handleCheckIn($attendeeId);
    } elseif ($method === 'POST' && $action === 'checkout' && $attendeeId) {
        handleCheckOut($attendeeId);
    } elseif ($method === 'DELETE' && $action === 'unregister' && $eventId) {
        handleUnregister($eventId);
    } else {
        echo Response::error('Invalid request', 400);
    }
} catch (Exception $e) {
    Logger::error('Attendees API Error: ' . $e->getMessage());
    echo Response::error('An error occurred: ' . $e->getMessage(), 500);
}

function handleListAttendees($eventId)
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser) {
        echo Response::error('Unauthorized', 401);
        return;
    }

    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 50;

    $eventModel = new Event();
    $event = $eventModel->findById($eventId);

    if (!$event) {
        echo Response::error('Event not found', 404);
        return;
    }

    if ($event['organizer_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $attendeeModel = new Attendee();
    $attendees = $attendeeModel->getEventAttendees($eventId, $page, $limit);
    $totalCount = $attendeeModel->getAttendeeCount($eventId);

    echo Response::success(
        [
            'attendees' => $attendees,
            'pagination' => [
                'current_page' => $page,
                'total_items' => $totalCount,
                'per_page' => $limit,
                'total_pages' => ceil($totalCount / $limit)
            ]
        ],
        'Attendees retrieved successfully',
        200
    );
}

function handleRegisterAttendee($eventId)
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser) {
        echo Response::error('Unauthorized', 401);
        return;
    }

    $eventModel = new Event();
    $event = $eventModel->findById($eventId);

    if (!$event) {
        echo Response::error('Event not found', 404);
        return;
    }

    $attendeeModel = new Attendee();

    if ($attendeeModel->isRegistered($eventId, $currentUser['id'])) {
        echo Response::error('Already registered for this event', 400);
        return;
    }

    // Check capacity
    $attendeeCount = $attendeeModel->getAttendeeCount($eventId);
    if ($attendeeCount >= $event['max_attendees']) {
        echo Response::error('Event is at full capacity', 400);
        return;
    }

    try {
        $attendeeId = $attendeeModel->register([
            'event_id' => $eventId,
            'user_id' => $currentUser['id']
        ]);

        Logger::info('User ' . $currentUser['id'] . ' registered for event ' . $eventId);

        echo Response::success(
            ['attendee_id' => $attendeeId],
            'Registered for event successfully',
            201
        );
    } catch (Exception $e) {
        Logger::error('Registration error: ' . $e->getMessage());
        echo Response::error('Registration failed', 500);
    }
}

function handleCheckIn($attendeeId)
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser || !in_array($currentUser['role'], ['admin', 'organizer'])) {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $attendeeModel = new Attendee();

    try {
        if ($attendeeModel->checkIn($attendeeId)) {
            Logger::info('Attendee ' . $attendeeId . ' checked in by user ' . $currentUser['id']);
            echo Response::success([], 'Check-in successful', 200);
        } else {
            echo Response::error('Check-in failed', 400);
        }
    } catch (Exception $e) {
        Logger::error('Check-in error: ' . $e->getMessage());
        echo Response::error('An error occurred', 500);
    }
}

function handleCheckOut($attendeeId)
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser || !in_array($currentUser['role'], ['admin', 'organizer'])) {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $attendeeModel = new Attendee();

    try {
        if ($attendeeModel->checkOut($attendeeId)) {
            Logger::info('Attendee ' . $attendeeId . ' checked out by user ' . $currentUser['id']);
            echo Response::success([], 'Check-out successful', 200);
        } else {
            echo Response::error('Check-out failed', 400);
        }
    } catch (Exception $e) {
        Logger::error('Check-out error: ' . $e->getMessage());
        echo Response::error('An error occurred', 500);
    }
}

function handleUnregister($eventId)
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser) {
        echo Response::error('Unauthorized', 401);
        return;
    }

    $attendeeModel = new Attendee();

    try {
        if ($attendeeModel->unregister($eventId, $currentUser['id'])) {
            Logger::info('User ' . $currentUser['id'] . ' unregistered from event ' . $eventId);
            echo Response::success([], 'Unregistered successfully', 200);
        } else {
            echo Response::error('Unregistration failed', 400);
        }
    } catch (Exception $e) {
        Logger::error('Unregistration error: ' . $e->getMessage());
        echo Response::error('An error occurred', 500);
    }
}
