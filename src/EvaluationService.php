<?php

class EvaluationService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function upsertWeekly(array $data): void
    {
        foreach (['participation_score', 'reflection_score', 'tasks_score', 'meetings_score', 'communication_score'] as $field) {
            $v = (int) $data[$field];
            if ($v < 1 || $v > 5) {
                throw new InvalidArgumentException("$field must be between 1 and 5");
            }
        }

        $stmt = $this->pdo->prepare('MERGE weekly_evaluations AS target
            USING (SELECT :trainee_id AS trainee_id, :week_number AS week_number) AS source
            ON target.trainee_id = source.trainee_id AND target.week_number = source.week_number
            WHEN MATCHED THEN
                UPDATE SET batch_id=:batch_id, participation_score=:participation_score, reflection_score=:reflection_score,
                    tasks_score=:tasks_score, meetings_score=:meetings_score, communication_score=:communication_score,
                    notes=:notes, created_by_user_id=:created_by_user_id
            WHEN NOT MATCHED THEN
                INSERT (trainee_id, batch_id, week_number, participation_score, reflection_score, tasks_score, meetings_score, communication_score, notes, created_by_user_id)
                VALUES (:trainee_id, :batch_id, :week_number, :participation_score, :reflection_score, :tasks_score, :meetings_score, :communication_score, :notes, :created_by_user_id);');

        $stmt->execute([
            'trainee_id' => $data['trainee_id'],
            'batch_id' => $data['batch_id'],
            'week_number' => $data['week_number'],
            'participation_score' => $data['participation_score'],
            'reflection_score' => $data['reflection_score'],
            'tasks_score' => $data['tasks_score'],
            'meetings_score' => $data['meetings_score'],
            'communication_score' => $data['communication_score'],
            'notes' => $data['notes'] ?? null,
            'created_by_user_id' => $data['created_by_user_id'] ?? null,
        ]);
    }

    public function cumulativePercent(int $traineeId): ?float
    {
        $stmt = $this->pdo->prepare('SELECT cumulative_percent FROM vw_trainee_evaluation_percentage WHERE trainee_id = :id');
        $stmt->execute(['id' => $traineeId]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (float) $value : null;
    }
}
