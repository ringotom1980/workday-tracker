<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

$startedAt = date('Y-m-d H:i:s');
$recordsProcessed = 0;
$status = 'failed';
$message = null;

try {
    $csv = file_get_contents(GOVERNMENT_CALENDAR_CSV_URL);
    if ($csv === false || trim($csv) === '') {
        throw new RuntimeException('無法下載政府行事曆 CSV');
    }

    $handle = fopen('php://temp', 'r+');
    fwrite($handle, $csv);
    rewind($handle);

    $headers = fgetcsv($handle);
    if ($headers === false) {
        throw new RuntimeException('CSV 格式錯誤');
    }

    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO government_calendar
        (calendar_date, year, month, day, is_holiday, is_makeup_workday, source_type, source_name, title, description, source_url, source_updated_at)
        VALUES
        (:calendar_date, :year, :month, :day, :is_holiday, :is_makeup_workday, :source_type, :source_name, :title, :description, :source_url, :source_updated_at)
        ON DUPLICATE KEY UPDATE
          is_holiday = VALUES(is_holiday),
          is_makeup_workday = VALUES(is_makeup_workday),
          source_type = VALUES(source_type),
          source_name = VALUES(source_name),
          title = VALUES(title),
          description = VALUES(description),
          source_url = VALUES(source_url),
          source_updated_at = VALUES(source_updated_at)'
    );

    while (($row = fgetcsv($handle)) !== false) {
        $record = array_combine($headers, $row);
        if (!is_array($record)) {
            continue;
        }

        $rawDate = $record['date'] ?? $record['西元日期'] ?? $record['日期'] ?? null;
        if (!$rawDate) {
            continue;
        }

        $date = preg_replace('/[^0-9]/', '', (string) $rawDate);
        if (strlen($date) === 7) {
            $date = ((int) substr($date, 0, 3) + 1911) . substr($date, 3);
        }

        $calendarDate = DateTime::createFromFormat('Ymd', $date);
        if (!$calendarDate) {
            continue;
        }

        $holidayText = (string) ($record['isHoliday'] ?? $record['是否放假'] ?? '');
        $description = (string) ($record['description'] ?? $record['備註'] ?? '');
        $title = (string) ($record['name'] ?? $record['節日名稱'] ?? $record['假日名稱'] ?? '');
        $isHoliday = in_array($holidayText, ['1', '是', '放假'], true) || str_contains($description, '放假');
        $isMakeupWorkday = str_contains($description, '補行上班') || str_contains($description, '補班');

        $stmt->execute([
            ':calendar_date' => $calendarDate->format('Y-m-d'),
            ':year' => (int) $calendarDate->format('Y'),
            ':month' => (int) $calendarDate->format('n'),
            ':day' => (int) $calendarDate->format('j'),
            ':is_holiday' => $isHoliday ? 1 : 0,
            ':is_makeup_workday' => $isMakeupWorkday ? 1 : 0,
            ':source_type' => 'csv',
            ':source_name' => '新北市政府資料開放平臺',
            ':title' => $title !== '' ? $title : null,
            ':description' => $description !== '' ? $description : null,
            ':source_url' => GOVERNMENT_CALENDAR_CSV_URL,
            ':source_updated_at' => date('Y-m-d H:i:s'),
        ]);

        $recordsProcessed++;
    }

    fclose($handle);
    $pdo->commit();
    $status = 'success';
    $message = '同步完成';
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $message = $exception->getMessage();
} finally {
    try {
        db()->prepare(
            'INSERT INTO system_jobs (job_name, status, message, records_processed, started_at, finished_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            'sync-government-calendar',
            $status,
            $message,
            $recordsProcessed,
            $startedAt,
            date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    }
}

echo sprintf("[%s] %s, records=%d\n", $status, $message, $recordsProcessed);
