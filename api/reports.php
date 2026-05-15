<?php
/**
 * Reports API
 * Handles report generation and retrieval
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Core.php';
require_once __DIR__ . '/../../src/Models.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$eventId = $_GET['event_id'] ?? null;

try {
    if ($method === 'GET' && $action === 'event_report' && $eventId) {
        handleEventReport($eventId);
    } elseif ($method === 'GET' && $action === 'attendee_report' && $eventId) {
        handleAttendeeReport($eventId);
    } elseif ($method === 'GET' && $action === 'summary') {
        handleSummaryReport();
    } elseif ($method === 'GET' && $action === 'export' && $eventId) {
        handleExportReport($eventId);
    } else {
        echo Response::error('Invalid request', 400);
    }
} catch (Exception $e) {
    Logger::error('Reports API Error: ' . $e->getMessage());
    echo Response::error('An error occurred: ' . $e->getMessage(), 500);
}

function handleEventReport($eventId)
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

    if ($event['organizer_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $reportModel = new Report();
    $report = $reportModel->getEventReport($eventId);

    echo Response::success($report, 'Event report retrieved successfully', 200);
}

function handleAttendeeReport($eventId)
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

    if ($event['organizer_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $reportModel = new Report();
    $report = $reportModel->getAttendeeReport($eventId);

    echo Response::success($report, 'Attendee report retrieved successfully', 200);
}

function handleSummaryReport()
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser || !in_array($currentUser['role'], ['admin', 'organizer'])) {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $reportModel = new Report();
    $report = $reportModel->getSummaryReport();

    echo Response::success($report, 'Summary report retrieved successfully', 200);
}

function handleExportReport($eventId)
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

    if ($event['organizer_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
        echo Response::error('Unauthorized', 403);
        return;
    }

    $reportModel = new Report();
    $report = $reportModel->getAttendeeReport($eventId);

    // Generate CSV
    $csv = "Name,Email,Company,Phone,Registration Date,Check-in Time,Check-out Time,Status\n";

    foreach ($report['attendees'] as $attendee) {
        $csv .= sprintf(
            '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
            $attendee['name'] ?? '',
            $attendee['email'] ?? '',
            $attendee['company'] ?? '',
            $attendee['phone'] ?? '',
            $attendee['registration_date'] ?? '',
            $attendee['check_in_time'] ?? '',
            $attendee['check_out_time'] ?? '',
            $attendee['status'] ?? ''
        );
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="event_' . $eventId . '_attendees.csv"');

    echo $csv;
}
