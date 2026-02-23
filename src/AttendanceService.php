<?php

class AttendanceService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createEvent(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO events (batch_id, event_type, title, event_date, mentor_user_id, attendance_mode, evidence_url, created_by_user_id)
            VALUES (:batch_id, :event_type, :title, :event_date, :mentor_user_id, :attendance_mode, :evidence_url, :created_by_user_id)');
        $stmt->execute([
            'batch_id' => $data['batch_id'],
            'event_type' => $data['event_type'],
            'title' => $data['title'],
            'event_date' => $data['event_date'],
            'mentor_user_id' => $data['mentor_user_id'] ?? null,
            'attendance_mode' => $data['attendance_mode'],
            'evidence_url' => $data['evidence_url'] ?? null,
            'created_by_user_id' => $data['created_by_user_id'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function confirmAttendance(int $eventId, array $attendanceRows, ?int $userId): void
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($attendanceRows as $row) {
                $stmt = $this->pdo->prepare('MERGE attendance_records AS target
                    USING (SELECT :event_id AS event_id, :trainee_id AS trainee_id) AS source
                    ON target.event_id = source.event_id AND target.trainee_id = source.trainee_id
                    WHEN MATCHED THEN
                        UPDATE SET is_present = :is_present, absence_reason = :absence_reason, excuse_status = :excuse_status,
                            scanned_qr_token = :scanned_qr_token, confirmed_by_user_id = :confirmed_by_user_id, confirmed_at = SYSDATETIME()
                    WHEN NOT MATCHED THEN
                        INSERT (event_id, trainee_id, is_present, absence_reason, excuse_status, scanned_qr_token, confirmed_by_user_id, confirmed_at)
                        VALUES (:event_id, :trainee_id, :is_present, :absence_reason, :excuse_status, :scanned_qr_token, :confirmed_by_user_id, SYSDATETIME());');
                $stmt->execute([
                    'event_id' => $eventId,
                    'trainee_id' => $row['trainee_id'],
                    'is_present' => !empty($row['is_present']) ? 1 : 0,
                    'absence_reason' => $row['absence_reason'] ?? null,
                    'excuse_status' => $row['excuse_status'] ?? null,
                    'scanned_qr_token' => $row['scanned_qr_token'] ?? null,
                    'confirmed_by_user_id' => $userId,
                ]);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function problematicAbsences(): array
    {
        $stmt = $this->pdo->query("SELECT trainee_id, session_absent_count FROM vw_trainee_attendance_summary WHERE session_absent_count > 3");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
