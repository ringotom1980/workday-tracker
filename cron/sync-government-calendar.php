<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

$startedAt = date('Y-m-d H:i:s');
$recordsProcessed = 0;
$status = 'failed';
$message = null;

function text_entity(string $value): string
{
    return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
}

function csv_value(array $record, array $keys): ?string
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $record) && trim((string) $record[$key]) !== '') {
            return trim((string) $record[$key]);
        }
    }

    return null;
}

function normalize_calendar_date(string $rawDate): ?DateTime
{
    $date = preg_replace('/[^0-9]/', '', $rawDate);
    if (!is_string($date) || $date === '') {
        return null;
    }

    if (strlen($date) === 7) {
        $date = ((int) substr($date, 0, 3) + 1911) . substr($date, 3);
    }

    if (strlen($date) !== 8) {
        return null;
    }

    $calendarDate = DateTime::createFromFormat('Ymd', $date);
    return $calendarDate instanceof DateTime ? $calendarDate : null;
}

try {
    $csv = file_get_contents(GOVERNMENT_CALENDAR_CSV_URL);
    if ($csv === false || trim($csv) === '') {
        throw new RuntimeException('Cannot download government calendar CSV.');
    }

    $handle = fopen('php://temp', 'r+');
    if ($handle === false) {
        throw new RuntimeException('Cannot open temporary CSV stream.');
    }

    fwrite($handle, $csv);
    rewind($handle);

    $headers = fgetcsv($handle);
    if ($headers === false) {
        throw new RuntimeException('Invalid CSV header.');
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

    $dateKeys = ['date', 'Date', text_entity('&#35199;&#20803;&#26085;&#26399;'), text_entity('&#26085;&#26399;')];
    $holidayKeys = ['isHoliday', 'IsHoliday', 'isholiday', text_entity('&#26159;&#21542;&#25918;&#20551;')];
    $descriptionKeys = ['description', 'Description', text_entity('&#20633;&#35387;'), text_entity('&#35498;&#26126;')];
    $titleKeys = ['name', 'Name', 'title', 'Title', text_entity('&#31680;&#26085;&#21517;&#31281;'), text_entity('&#20551;&#26085;&#21517;&#31281;')];
    $yesText = text_entity('&#26159;');
    $holidayTextMatch = text_entity('&#25918;&#20551;');
    $makeupWorkdayText = text_entity('&#35036;&#34892;&#19978;&#29677;');
    $makeupText = text_entity('&#35036;&#29677;');
    $sourceName = text_entity('&#26032;&#21271;&#24066;&#25919;&#24220;&#36039;&#26009;&#38283;&#25918;&#24179;&#33274;');

    while (($row = fgetcsv($handle)) !== false) {
        $record = array_combine($headers, $row);
        if (!is_array($record)) {
            continue;
        }

        $rawDate = csv_value($record, $dateKeys);
        if ($rawDate === null) {
            continue;
        }

        $calendarDate = normalize_calendar_date($rawDate);
        if (!$calendarDate) {
            continue;
        }

        $holidayText = csv_value($record, $holidayKeys) ?? '';
        $description = csv_value($record, $descriptionKeys) ?? '';
        $title = csv_value($record, $titleKeys) ?? '';

        $isHoliday = in_array($holidayText, ['1', $yesText, $holidayTextMatch], true) || str_contains($description, $holidayTextMatch);
        $isMakeupWorkday = str_contains($description, $makeupWorkdayText) || str_contains($description, $makeupText);

        $stmt->execute([
            ':calendar_date' => $calendarDate->format('Y-m-d'),
            ':year' => (int) $calendarDate->format('Y'),
            ':month' => (int) $calendarDate->format('n'),
            ':day' => (int) $calendarDate->format('j'),
            ':is_holiday' => $isHoliday ? 1 : 0,
            ':is_makeup_workday' => $isMakeupWorkday ? 1 : 0,
            ':source_type' => 'csv',
            ':source_name' => $sourceName,
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
    $message = 'Sync completed.';
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

echo sprintf("[%s] %s records=%d\n", $status, $message, $recordsProcessed);
