<?php
require_once __DIR__ . '/../controllers/EmployeeController.php';
require_once __DIR__ . '/../controllers/SalaryCycleController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../utils/ResponseHandler.php';
require_once __DIR__ . '/../controllers/ConfirmSalaries.php';

// Controllers
$authController = new AuthController();
$employeeController = new EmployeeController();
$controller = new SalaryCycleController();
$confirmController = new ConfirmSalaries();


// URI and method
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

$base_path = '/payroll-backend/';
$endpoint = str_replace($base_path, '', $request_uri);
$endpoint = strtok($endpoint, '?'); // Remove query strings

try {
    switch ($endpoint) {
        case 'api/auth/register':
            if ($request_method === 'POST') $authController->register();
            else ResponseHandler::sendError(405, "Method not allowed");
            break;

        case 'api/auth/login':
            if ($request_method === 'POST') $authController->login();
            else ResponseHandler::sendError(405, "Method not allowed");
            break;

        case 'api/employees':
            requireAuth(function ($user) use ($employeeController, $request_method) {
                if ($request_method === 'GET') $employeeController->getAll();
                else if ($request_method === 'POST') $employeeController->create();
                else ResponseHandler::sendError(405, "Method not allowed");
            });
            break;
        case 'api/salary-cycle':
            return requireAuth(function ($token_data) use ($request_method, $controller) {
                switch ($request_method) {
                    case 'POST':
                        return $controller->generateCycle();

                    case 'GET':
                        if (isset($_GET['month']) && isset($_GET['year'])) {
                            return $controller->getCycleByMonthYear($_GET['month'], $_GET['year']);
                        } elseif (isset($_GET['cycle_id'])) {
                            return $controller->getPayoutsByCycle($_GET['cycle_id']);
                        } else {
                            return $controller->getAllCycles();
                        }

                    case 'DELETE':
                        if (isset($_GET['cycle_id'])) {
                            return $controller->deleteCycle($_GET['cycle_id']);
                        }
                        return ResponseHandler::sendError(400, "Cycle ID required to delete.");

                    default:
                        return ResponseHandler::sendError(405, "Method not allowed");
                }
            });
        case 'api/auto-confirm-salaries':
            return requireAuth(function ($token_data) use ($request_method, $confirmController) {
                require_once __DIR__ . '/../controllers/ConfirmSalaries.php';


                switch ($request_method) {
                    case 'POST':
                        return $confirmController->autoConfirmSalaries();

                    default:
                        return ResponseHandler::sendError(405, "Method not allowed for auto-confirm");
                }
            });





        case 'api/test':
            ResponseHandler::sendSuccess(["message" => "API is working"]);
            break;

        default:
            ResponseHandler::sendError(404, "Endpoint not found");
            break;
    }
} catch (Exception $e) {
    ResponseHandler::sendError(500, "Server error: " . $e->getMessage());
}
