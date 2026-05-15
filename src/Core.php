<?php
/**
 * Database Connection Class
 * Singleton pattern for database connections
 */

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                DB_PORT
            );

            if ($this->connection->connect_error) {
                throw new Exception('Database Connection Error: ' . $this->connection->connect_error);
            }

            $this->connection->set_charset('utf8mb4');
        } catch (Exception $e) {
            Logger::error('Database Connection Failed: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function execute($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->connection->error);
            }

            if (!empty($params)) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_bool($param)) {
                        $types .= 'i';
                    } else {
                        $types .= 's';
                    }
                }
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }

            return $stmt;
        } catch (Exception $e) {
            Logger::error('Database Execute Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

class Logger
{
    private static $logFile = __DIR__ . '/../storage/logs/app.log';

    public static function log($message, $level = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        
        error_log($logMessage, 3, self::$logFile);
    }

    public static function info($message)
    {
        self::log($message, 'INFO');
    }

    public static function error($message)
    {
        self::log($message, 'ERROR');
    }

    public static function warning($message)
    {
        self::log($message, 'WARNING');
    }

    public static function debug($message)
    {
        if (APP_DEBUG) {
            self::log($message, 'DEBUG');
        }
    }
}

class Response
{
    public static function success($data, $message = 'Success', $statusCode = 200)
    {
        http_response_code($statusCode);
        return json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public static function error($message, $statusCode = 400, $errors = [])
    {
        http_response_code($statusCode);
        return json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

class Validator
{
    private static $errors = [];

    public static function validate($data, $rules)
    {
        self::$errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleArray = explode('|', $fieldRules);

            foreach ($ruleArray as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $ruleValue] = explode(':', $rule);
                } else {
                    $ruleName = $rule;
                    $ruleValue = null;
                }

                self::applyRule($field, $value, $ruleName, $ruleValue);
            }
        }

        return empty(self::$errors);
    }

    private static function applyRule($field, $value, $rule, $ruleValue)
    {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    self::$errors[$field] = ucfirst($field) . ' is required';
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    self::$errors[$field] = ucfirst($field) . ' must be a valid email';
                }
                break;

            case 'min':
                if (strlen($value) < $ruleValue) {
                    self::$errors[$field] = ucfirst($field) . ' must be at least ' . $ruleValue . ' characters';
                }
                break;

            case 'max':
                if (strlen($value) > $ruleValue) {
                    self::$errors[$field] = ucfirst($field) . ' cannot exceed ' . $ruleValue . ' characters';
                }
                break;

            case 'unique':
                if (!self::isUnique($ruleValue, $field, $value)) {
                    self::$errors[$field] = ucfirst($field) . ' already exists';
                }
                break;

            case 'date':
                if (!self::isValidDate($value)) {
                    self::$errors[$field] = ucfirst($field) . ' must be a valid date';
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    self::$errors[$field] = ucfirst($field) . ' must be numeric';
                }
                break;
        }
    }

    private static function isUnique($table, $field, $value)
    {
        $db = Database::getInstance()->getConnection();
        $result = $db->query("SELECT COUNT(*) as count FROM {$table} WHERE {$field} = '{$value}'");
        $row = $result->fetch_assoc();
        return $row['count'] == 0;
    }

    private static function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public static function errors()
    {
        return self::$errors;
    }
}

class Auth
{
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_HASH_ALGO);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function generateJWT($data)
    {
        $issuedAt = time();
        $expire = $issuedAt + JWT_EXPIRATION;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'data' => $data
        ];

        return self::base64UrlEncode(json_encode($payload));
    }

    public static function verifyJWT($token)
    {
        try {
            $payload = json_decode(self::base64UrlDecode($token), true);

            if ($payload === null) {
                return false;
            }

            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }

            return $payload['data'] ?? null;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        $base64 = str_replace(['-', '_'], ['+', '/'], $data);
        // Restore padding required by base64_decode
        $pad = strlen($base64) % 4;
        if ($pad !== 0) {
            $base64 .= str_repeat('=', 4 - $pad);
        }
        return base64_decode($base64);
    }

    public static function getBearerToken()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public static function getCurrentUser()
    {
        $token = self::getBearerToken();
        if (!$token) {
            return null;
        }

        return self::verifyJWT($token);
    }
}
