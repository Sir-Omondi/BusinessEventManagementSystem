<?php
// Minimal end-to-end auth verification for BEMS
$base = 'http://localhost/BusinessEventManagementSystem';

// Login
$loginUrl = $base . '/api/auth.php?action=login';
$payload = json_encode(['email' => 'admin@bems.local', 'password' => 'admin@123']);

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$loginBody = curl_exec($ch);
$loginStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "LOGIN_STATUS=" . $loginStatus . PHP_EOL;
echo "LOGIN_BODY=" . $loginBody . PHP_EOL;

$loginJson = json_decode($loginBody, true);
if (!$loginJson || empty($loginJson['success']) || empty($loginJson['data']['token'])) {
  echo "LOGIN_TOKEN_MISSING" . PHP_EOL;
  exit(0);
}
$token = $loginJson['data']['token'];

// Profile
$profileUrl = $base . '/api/auth.php?action=profile';
$ch = curl_init($profileUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
$profileBody = curl_exec($ch);
$profileStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "PROFILE_STATUS=" . $profileStatus . PHP_EOL;
echo "PROFILE_BODY=" . $profileBody . PHP_EOL;

// Protected stats
$statsUrl = $base . '/api/events.php?action=stats';
$ch = curl_init($statsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
$statsBody = curl_exec($ch);
$statsStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "STATS_STATUS=" . $statsStatus . PHP_EOL;
echo "STATS_BODY=" . $statsBody . PHP_EOL;
?>
