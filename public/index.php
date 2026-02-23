<?php

$config = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/TraineeService.php';
require_once __DIR__ . '/../src/AttendanceService.php';
require_once __DIR__ . '/../src/EvaluationService.php';
require_once __DIR__ . '/../src/IntegrationService.php';

$db = new Database($config);
$pdo = $db->pdo();

$trainees = new TraineeService($pdo);
$attendance = new AttendanceService($pdo);
$evaluation = new EvaluationService($pdo);
$integration = new IntegrationService($config);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    if ($method === 'GET' && $path === '/health') {
        json_response(['ok' => true, 'service' => 'profile-program-api']);
        return;
    }

    if ($method === 'POST' && $path === '/api/trainees') {
        $payload = read_json_body();
        $result = $trainees->createTrainee($payload);
        json_response(['message' => 'created', 'data' => $result], 201);
        return;
    }

    if ($method === 'POST' && $path === '/api/trainees/bulk') {
        $payload = read_json_body();
        $rows = $payload['rows'] ?? [];
        $result = $trainees->createBulk($rows);
        json_response(['message' => 'bulk_created', 'count' => count($result), 'data' => $result], 201);
        return;
    }

    if ($method === 'GET' && $path === '/api/trainees/search') {
        $q = $_GET['q'] ?? '';
        json_response(['data' => $trainees->profileByCodeOrName($q)]);
        return;
    }

    if ($method === 'POST' && $path === '/api/events') {
        $payload = read_json_body();
        $id = $attendance->createEvent($payload);
        json_response(['message' => 'event_created', 'event_id' => $id], 201);
        return;
    }

    if ($method === 'POST' && preg_match('#^/api/events/(\d+)/attendance$#', $path, $m)) {
        $payload = read_json_body();
        $attendance->confirmAttendance((int) $m[1], $payload['rows'] ?? [], $payload['confirmed_by_user_id'] ?? null);
        json_response(['message' => 'attendance_saved']);
        return;
    }

    if ($method === 'GET' && $path === '/api/attendance/problems') {
        json_response(['data' => $attendance->problematicAbsences()]);
        return;
    }

    if ($method === 'POST' && $path === '/api/evaluations/weekly') {
        $payload = read_json_body();
        $evaluation->upsertWeekly($payload);
        $percent = $evaluation->cumulativePercent((int) $payload['trainee_id']);
        json_response(['message' => 'evaluation_saved', 'cumulative_percent' => $percent]);
        return;
    }

    if ($method === 'GET' && $path === '/api/integrations/external-sql') {
        $endpoint = $_GET['endpoint'] ?? 'trainees';
        json_response(['data' => $integration->pullFromExternalSqlApi($endpoint)]);
        return;
    }

    if ($method === 'GET' && $path === '/api/integrations/kobo') {
        json_response(['data' => $integration->pullFromKoboSubmissions()]);
        return;
    }

    json_response(['message' => 'Not Found'], 404);
} catch (InvalidArgumentException $e) {
    json_response(['error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    json_response(['error' => $e->getMessage()], 500);
}
