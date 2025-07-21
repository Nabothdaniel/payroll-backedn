<?php
class ResponseHandler {
    public static function sendResponse($status_code, $message, $data = null) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($status_code);
        
        $response = [
            "status" => $status_code,
            "message" => $message
        ];

        if ($data !== null) {
            $response["data"] = $data;
        }

        echo json_encode($response);
        exit();
    }

    public static function sendError($status_code, $message) {
        self::sendResponse($status_code, $message);
    }

    public static function sendSuccess($data = null, $message = "Success") {
        self::sendResponse(200, $message, $data);
    }
}
?>
