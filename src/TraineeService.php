<?php

require_once __DIR__ . '/helpers.php';

class TraineeService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createTrainee(array $data): array
    {
        if (empty($data['full_name']) || count(array_values(array_filter(explode(' ', trim($data['full_name']))))) < 4) {
            throw new InvalidArgumentException('Full name must be 4 parts at least.');
        }

        if (empty($data['national_id']) || !validate_egypt_national_id($data['national_id'])) {
            throw new InvalidArgumentException('Invalid Egyptian national ID.');
        }

        $birthDate = birth_date_from_national_id($data['national_id']);
        $isVolunteer = !empty($data['is_sonaa_volunteer']) ? 1 : 0;

        if ($isVolunteer && empty($data['sonaa_volunteer_date'])) {
            throw new InvalidArgumentException('Volunteer date is required when trainee is volunteer.');
        }

        $serial = $this->nextSerialByGovernorate((int) $data['governorate_id']);
        $govCode = $this->governorateCode((int) $data['governorate_id']);
        $code = build_trainee_code($govCode, $serial, $data['triful_join_date']);

        $sql = 'INSERT INTO trainees
            (trainee_code, full_name, governorate_id, center_id, national_id, birth_date, college_id, university_id,
             phone, email, is_sonaa_volunteer, sonaa_volunteer_date, triful_join_date, batch_id, group_id)
            VALUES
            (:trainee_code, :full_name, :governorate_id, :center_id, :national_id, :birth_date, :college_id, :university_id,
             :phone, :email, :is_sonaa_volunteer, :sonaa_volunteer_date, :triful_join_date, :batch_id, :group_id)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'trainee_code' => $code,
            'full_name' => trim($data['full_name']),
            'governorate_id' => $data['governorate_id'],
            'center_id' => $data['center_id'] ?? null,
            'national_id' => $data['national_id'],
            'birth_date' => $birthDate,
            'college_id' => $data['college_id'] ?? null,
            'university_id' => $data['university_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'is_sonaa_volunteer' => $isVolunteer,
            'sonaa_volunteer_date' => $data['sonaa_volunteer_date'] ?? null,
            'triful_join_date' => $data['triful_join_date'],
            'batch_id' => $data['batch_id'],
            'group_id' => $data['group_id'] ?? null,
        ]);

        return ['id' => (int) $this->pdo->lastInsertId(), 'trainee_code' => $code];
    }

    public function createBulk(array $rows): array
    {
        $this->pdo->beginTransaction();
        $created = [];
        try {
            foreach ($rows as $row) {
                $created[] = $this->createTrainee($row);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $created;
    }

    public function profileByCodeOrName(string $q): array
    {
        $stmt = $this->pdo->prepare('SELECT TOP 50 t.*, g.name_ar AS governorate_name
            FROM trainees t
            JOIN governorates g ON g.id = t.governorate_id
            WHERE t.trainee_code = :exact OR t.full_name LIKE :name
            ORDER BY t.created_at DESC');

        $stmt->execute([
            'exact' => $q,
            'name' => '%' . $q . '%'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function governorateCode(int $id): string
    {
        $stmt = $this->pdo->prepare('SELECT code FROM governorates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $code = $stmt->fetchColumn();
        if (!$code) {
            throw new InvalidArgumentException('Governorate not found.');
        }
        return $code;
    }

    private function nextSerialByGovernorate(int $govId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) + 1 FROM trainees WHERE governorate_id = :id');
        $stmt->execute(['id' => $govId]);
        return (int) $stmt->fetchColumn();
    }
}
