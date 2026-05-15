<?php
/**
 * Authentication API
 * Handles user login, registration, and token generation
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Core.php';
require_once __DIR__ . '/../../src/Models.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'POST' && $action === 'register') {
        handleRegister();
    } elseif ($method === 'POST' && $action === 'login') {
        handleLogin();
    } elseif ($method === 'POST' && $action === 'refresh') {
        handleRefreshToken();
    } elseif ($method === 'GET' && $action === 'profile') {
        handleGetProfile();
    } elseif ($method === 'POST' && $action === 'logout') {
        handleLogout();
    } elseif ($method === 'POST' && $action === 'promote_admin') {
        handlePromoteAdmin();
    } else {
        echo Response::error('Invalid request', 400);
    }
} catch (Exception $e) {
    Logger::error('Auth API Error: ' . $e->getMessage());
    echo Response::error('An error occurred: ' . $e->getMessage(), 500);
}

function handleRegister()
{
    $data = json_decode(file_get_contents('php://input'), true);

    $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'password' => 'required|min:6',
        'phone' => 'required',
        'company' => 'required'
    ];

    if (!Validator::validate($data, $rules)) {
        echo Response::error('Validation failed', 400, Validator::errors());
        return;
    }

    $userModel = new User();

    // Check if email already exists
    if ($userModel->findByEmail($data['email'])) {
        echo Response::error('Email already registered', 400);
        return;
    }

    // Role rules:
    // - First ever account becomes admin
    // - All subsequent accounts become user
    $existingUsers = $userModel->getTotalCount();
    $data['role'] = ($existingUsers === 0) ? 'admin' : 'user';

    try {
        $userId = $userModel->create($data);

        $user = $userModel->findById($userId);
        unset($user['password']);

        Logger::info('New user registered: ' . $data['email'] . ' (role=' . $data['role'] . ')');

        echo Response::success(
            [
                'user' => $user,
                'message' => 'Registration successful'
            ],
            'User registered successfully',
            201
        );
    } catch (Exception $e) {
        Logger::error('Registration error: ' . $e->getMessage());
        echo Response::error('Registration failed', 500);
    }
}

function handleLogin()
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['email']) || empty($data['password'])) {
        echo Response::error('Email and password are required', 400);
        return;
    }

    $userModel = new User();
    $user = $userModel->authenticate($data['email'], $data['password']);

    if (!$user) {
        Logger::warning('Failed login attempt for: ' . $data['email']);
        echo Response::error('Invalid email or password', 401);
        return;
    }

    $token = Auth::generateJWT([
        'id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role']
    ]);

    Logger::info('User logged in: ' . $data['email']);

    echo Response::success(
        [
            'token' => $token,
            'user' => $user,
            'expires_in' => JWT_EXPIRATION
        ],
        'Login successful',
        200
    );
}

function handleRefreshToken()
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser) {
        echo Response::error('Unauthorized', 401);
        return;
    }

    $token = Auth::generateJWT($currentUser);

    echo Response::success(
        [
            'token' => $token,
            'expires_in' => JWT_EXPIRATION
        ],
        'Token refreshed successfully',
        200
    );
}

function handleGetProfile()
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser) {
        echo Response::error('Unauthorized', 401);
        return;
    }

    $userModel = new User();
    $user = $userModel->findById($currentUser['id']);

    if (!$user) {
        echo Response::error('User not found', 404);
        return;
    }

    unset($user['password']);

    echo Response::success($user, 'Profile retrieved successfully', 200);
}

function handleLogout()
{
    // Token-based systems don't require server-side logout
    // Client should discard the token
    Logger::info('User logged out');

    echo Response::success(
        [],
        'Logged out successfully',
        200
    );
}

function handlePromoteAdmin()
{
    $currentUser = Auth::getCurrentUser();

    if (!$currentUser || $currentUser['role'] !== 'admin') {
        echo Response::error('Forbidden: admin access required', 403);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['target_user_id'])) {
        echo Response::error('target_user_id is required', 400);
        return;
    }

    $targetUserId = $data['target_user_id'];

    $userModel = new User();
    if (!$userModel->findById($targetUserId)) {
        echo Response::error('Target user not found', 404);
        return;
    }

    if ($userModel->promoteToAdmin($targetUserId, $currentUser['id'])) {
        echo Response::success([], 'User promoted to admin', 200);
        return;
    }

    echo Response::error('Failed to promote user', 400);
}
