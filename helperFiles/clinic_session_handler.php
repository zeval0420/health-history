<?php
// Session + access helpers for the clinic app.
// Own session name so it never collides with reserve-a-lab's PHPSESSID on the same host.
if (session_status() === PHP_SESSION_NONE) {
    $cl_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('CLINICSESSID');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $cl_https,
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
}

// Health data: never cache, never frame, no sniffing.
if (!headers_sent()) {
    header('Cache-Control: no-store');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
}

$cl_timeout = 28800; // 8 hours
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $cl_timeout) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['session_expired'] = true;
}
$_SESSION['last_activity'] = time();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('clinic_deny')) {
    function clinic_is_ajax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    }

    /** End the request with a JSON error. */
    function clinic_deny($httpStatus, $message, $status = 'error')
    {
        http_response_code($httpStatus);
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'success' => false, 'message' => $message]);
        exit();
    }

    function clinic_is_logged_in()
    {
        return !empty($_SESSION['clinic_user_id']);
    }

    /** Pages: redirect to login. Ajax: 401 JSON. */
    function clinic_require_login()
    {
        if (clinic_is_logged_in()) {
            return;
        }
        if (clinic_is_ajax()) {
            clinic_deny(401, 'Please log in again.', 'session_expired');
        }
        header('Location: index.php');
        exit();
    }

    /** 403 unless a state-changing request carries the session CSRF token (X-CSRF-Token header or csrf_token field). */
    function clinic_require_csrf()
    {
        if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }
        $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        $expected = $_SESSION['csrf_token'] ?? '';
        if (!is_string($sent) || $expected === '' || !hash_equals($expected, $sent)) {
            clinic_deny(403, 'Invalid or missing security token. Please refresh the page and try again.');
        }
    }

    // Login throttling kept in the visitor's session (same approach as reserve-a-lab).
    function clinic_throttle_wait($bucket)
    {
        return max(0, (int)($_SESSION['_throttle'][$bucket]['locked_until'] ?? 0) - time());
    }

    function clinic_throttle_hit($bucket, $max = 5, $window = 900)
    {
        $now = time();
        $st = $_SESSION['_throttle'][$bucket] ?? ['count' => 0, 'first' => $now, 'locked_until' => 0];
        if ($now - $st['first'] > $window && $st['locked_until'] <= $now) {
            $st = ['count' => 0, 'first' => $now, 'locked_until' => 0];
        }
        $st['count']++;
        if ($st['count'] >= $max) {
            $st['locked_until'] = $now + min(900, 30 * (2 ** ($st['count'] - $max)));
        }
        $_SESSION['_throttle'][$bucket] = $st;
    }

    function clinic_throttle_clear($bucket)
    {
        unset($_SESSION['_throttle'][$bucket]);
    }
}
