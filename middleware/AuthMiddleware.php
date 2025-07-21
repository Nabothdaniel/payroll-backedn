<?php
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/ResponseHandler.php';

class AuthMiddleware {
    public static function validateToken() {
        $headers = getallheaders();
        
        // Check if Authorization header exists
        if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
            ResponseHandler::sendError(401, "Access denied. No token provided.");
            return false;
        }

        // Get token from header
        $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
        $token = str_replace('Bearer ', '', $auth_header);

        // Validate token
        $decoded = JWTHandler::validateToken($token);
        if (!$decoded) {
            ResponseHandler::sendError(401, "Invalid or expired token.");
            return false;
        }

        return $decoded;
    }
}

// Function to apply middleware to routes
function requireAuth($callback) {
    $token_data = AuthMiddleware::validateToken();
    if ($token_data) {
        return $callback($token_data);
    }
    return false;
}
?>
