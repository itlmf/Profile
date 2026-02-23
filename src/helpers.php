<?php

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

function read_json_body(): array
{
    $body = file_get_contents('php://input');
    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : [];
}

function validate_egypt_national_id(string $id): bool
{
    if (!preg_match('/^\d{14}$/', $id)) {
        return false;
    }

    $centuryDigit = (int) $id[0];
    if (!in_array($centuryDigit, [2, 3], true)) {
        return false;
    }

    $year = (int) substr($id, 1, 2);
    $month = (int) substr($id, 3, 2);
    $day = (int) substr($id, 5, 2);

    $century = $centuryDigit === 2 ? 1900 : 2000;
    return checkdate($month, $day, $century + $year);
}

function birth_date_from_national_id(string $id): ?string
{
    if (!validate_egypt_national_id($id)) {
        return null;
    }

    $century = $id[0] === '2' ? 1900 : 2000;
    $year = $century + (int) substr($id, 1, 2);
    $month = (int) substr($id, 3, 2);
    $day = (int) substr($id, 5, 2);

    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function build_trainee_code(string $govCode, int $serial, string $joinDate): string
{
    $date = new DateTimeImmutable($joinDate);
    return strtoupper($govCode) . '-' . str_pad((string) $serial, 4, '0', STR_PAD_LEFT) . '-' . $date->format('my');
}
