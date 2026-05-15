<?php
/**
 * User Model
 */

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        $query = "INSERT INTO users (name, email, password, phone, company, role, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $data['name'],
                $data['email'],
                Auth::hashPassword($data['password']),
                $data['phone'] ?? null,
                $data['company'] ?? null,
                $data['role'] ?? 'user'
            ]
        );

        return $this->db->insert_id;
    }

    public function findByEmail($email)
    {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = Database::getInstance()->execute($query, [$email]);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findById($id)
    {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$id]);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAll($page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT id, name, email, phone, company, role, created_at FROM users LIMIT ? OFFSET ?";
        $stmt = Database::getInstance()->execute($query, [$limit, $offset]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function update($id, $data)
    {
        $query = "UPDATE users SET ";
        $updateFields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'email', 'phone', 'company'])) {
                $updateFields[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $query .= implode(", ", $updateFields) . " WHERE id = ?";
        $params[] = $id;

        $stmt = Database::getInstance()->execute($query, $params);
        return $this->db->affected_rows > 0;
    }

    public function delete($id)
    {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$id]);
        return $this->db->affected_rows > 0;
    }

    public function authenticate($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!Auth::verifyPassword($password, $user['password'])) {
            return false;
        }

        unset($user['password']);
        return $user;
    }

    public function getTotalCount()
    {
        $result = $this->db->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        return (int) $row['count'];
    }

    public function promoteToAdmin($targetUserId, $actorUserId)
    {
        // Actor must already be admin
        $actor = $this->findById($actorUserId);
        if (!$actor || $actor['role'] !== 'admin') {
            return false;
        }

        // Target must exist (optionally enforce not-admin, but promotion is idempotent)
        $target = $this->findById($targetUserId);
        if (!$target) {
            return false;
        }

        $query = "UPDATE users SET role = 'admin' WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$targetUserId]);

        return $this->db->affected_rows > 0 || $this->findById($targetUserId)['role'] === 'admin';
    }
}

/**
 * Event Model
 */

class Event
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        $query = "INSERT INTO events (title, description, location, start_date, end_date, 
                  max_attendees, organizer_id, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $data['title'],
                $data['description'] ?? null,
                $data['location'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['max_attendees'] ?? 100,
                $data['organizer_id'],
                $data['status'] ?? 'scheduled'
            ]
        );

        return $this->db->insert_id;
    }

    public function findById($id)
    {
        $query = "SELECT e.*, u.name as organizer_name, COUNT(DISTINCT a.id) as attendee_count 
                  FROM events e 
                  LEFT JOIN users u ON e.organizer_id = u.id 
                  LEFT JOIN attendees a ON e.id = a.event_id 
                  WHERE e.id = ? 
                  GROUP BY e.id";
        $stmt = Database::getInstance()->execute($query, [$id]);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAll($page = 1, $limit = 20, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['organizer_id'])) {
            $where .= " AND organizer_id = ?";
            $params[] = $filters['organizer_id'];
        }

        if (!empty($filters['search'])) {
            $where .= " AND (title LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query = "SELECT e.*, u.name as organizer_name, COUNT(DISTINCT a.id) as attendee_count 
                  FROM events e 
                  LEFT JOIN users u ON e.organizer_id = u.id 
                  LEFT JOIN attendees a ON e.id = a.event_id 
                  {$where} 
                  GROUP BY e.id 
                  ORDER BY e.start_date DESC 
                  LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = Database::getInstance()->execute($query, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function update($id, $data)
    {
        $query = "UPDATE events SET ";
        $updateFields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'location', 'start_date', 'end_date', 'max_attendees', 'status'])) {
                $updateFields[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $query .= implode(", ", $updateFields) . " WHERE id = ?";
        $params[] = $id;

        $stmt = Database::getInstance()->execute($query, $params);
        return $this->db->affected_rows > 0;
    }

    public function delete($id)
    {
        $query = "DELETE FROM events WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$id]);
        return $this->db->affected_rows > 0;
    }

    public function getTotalCount($filters = [])
    {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $query = "SELECT COUNT(*) as count FROM events {$where}";
        
        if (!empty($params)) {
            $stmt = Database::getInstance()->execute($query, $params);
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($query);
        }

        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getEventStats()
    {
        $query = "SELECT 
                  COUNT(DISTINCT id) as total_events,
                  COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled,
                  COUNT(CASE WHEN status = 'ongoing' THEN 1 END) as ongoing,
                  COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
                  FROM events";

        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }
}

/**
 * Attendee Model
 */

class Attendee
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($data)
    {
        $query = "INSERT INTO attendees (event_id, user_id, registration_date, check_in_time, check_out_time, status) 
                  VALUES (?, ?, NOW(), NULL, NULL, ?)";

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $data['event_id'],
                $data['user_id'],
                $data['status'] ?? 'registered'
            ]
        );

        return $this->db->insert_id;
    }

    public function getEventAttendees($eventId, $page = 1, $limit = 50)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT a.*, u.name, u.email, u.company, u.phone 
                  FROM attendees a 
                  JOIN users u ON a.user_id = u.id 
                  WHERE a.event_id = ? 
                  ORDER BY a.registration_date DESC 
                  LIMIT ? OFFSET ?";

        $stmt = Database::getInstance()->execute($query, [$eventId, $limit, $offset]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function checkIn($attendeeId)
    {
        $query = "UPDATE attendees SET check_in_time = NOW(), status = 'checked_in' WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$attendeeId]);
        return $this->db->affected_rows > 0;
    }

    public function checkOut($attendeeId)
    {
        $query = "UPDATE attendees SET check_out_time = NOW(), status = 'checked_out' WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$attendeeId]);
        return $this->db->affected_rows > 0;
    }

    public function isRegistered($eventId, $userId)
    {
        $query = "SELECT id FROM attendees WHERE event_id = ? AND user_id = ?";
        $stmt = Database::getInstance()->execute($query, [$eventId, $userId]);
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function getAttendeeCount($eventId)
    {
        $query = "SELECT COUNT(*) as count FROM attendees WHERE event_id = ?";
        $stmt = Database::getInstance()->execute($query, [$eventId]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function unregister($eventId, $userId)
    {
        $query = "DELETE FROM attendees WHERE event_id = ? AND user_id = ?";
        $stmt = Database::getInstance()->execute($query, [$eventId, $userId]);
        return $this->db->affected_rows > 0;
    }
}

/**
 * Report Model
 */

class Report
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getEventReport($eventId)
    {
        $event = new Event();
        $eventData = $event->findById($eventId);

        $query = "SELECT 
                  COUNT(*) as total_registered,
                  COUNT(CASE WHEN status = 'checked_in' THEN 1 END) as checked_in,
                  COUNT(CASE WHEN status = 'checked_out' THEN 1 END) as checked_out
                  FROM attendees 
                  WHERE event_id = ?";

        $stmt = Database::getInstance()->execute($query, [$eventId]);
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();

        return [
            'event' => $eventData,
            'stats' => $stats
        ];
    }

    public function getAttendeeReport($eventId)
    {
        $attendee = new Attendee();
        $attendees = $attendee->getEventAttendees($eventId, 1, 10000);

        return [
            'event_id' => $eventId,
            'total_attendees' => count($attendees),
            'attendees' => $attendees,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    public function getSummaryReport()
    {
        $eventModel = new Event();
        $attendeeModel = new Attendee();

        $events = $eventModel->getEventStats();

        $query = "SELECT 
                  e.id,
                  e.title,
                  COUNT(a.id) as total_attendees,
                  COUNT(CASE WHEN a.status = 'checked_in' THEN 1 END) as checked_in
                  FROM events e 
                  LEFT JOIN attendees a ON e.id = a.event_id 
                  GROUP BY e.id 
                  ORDER BY e.start_date DESC";

        $result = $this->db->query($query);
        $eventDetails = $result->fetch_all(MYSQLI_ASSOC);

        return [
            'summary' => $events,
            'event_details' => $eventDetails,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
}
