<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\InnerRegion;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->resetTables();

        $this->importJarasCsv();

        $kiskunSettlementId = $this->ensureSettlement(
            'Bács-Kiskun',
            'Kiskunfélegyháza',
            'Kiskunfélegyháza',
            '6100'
        );

        $szegedSettlementId = $this->ensureSettlement(
            'Csongrád-Csanád',
            'Szeged',
            'Szeged',
            '6720'
        );

        $adminSzegedId = $this->createUser('Teszt Admin Szeged', 'testAdmin@example.com');
        $adminKiskunId = $this->createUser('Tóth Tamás', 'toth.tamas@diak.szbi-pg.hu');

        $this->seedSchoolScenario(
            adminUserId: $adminSzegedId,
            settlementId: $szegedSettlementId,
            establishmentTitle: 'Szentbenedek Teszt Gimnázium',
            establishmentDescription: 'Teszt adatokkal feltöltött szegedi gimnázium.',
            namePrefix: 'Szeged'
        );

        $this->seedSchoolScenario(
            adminUserId: $adminKiskunId,
            settlementId: $kiskunSettlementId,
            establishmentTitle: 'Kiskunfélegyházi Teszt Technikum',
            establishmentDescription: 'Teszt adatokkal feltöltött kiskunfélegyházi intézmény.',
            namePrefix: 'Kiskun'
        );

        $this->seedGenericData($szegedSettlementId, $kiskunSettlementId);
    }

    private function importJarasCsv(): void
    {
        $csvPath = database_path('seeders/data/jarasok.csv');
        $lastRegion = null;

        if (!file_exists($csvPath)) {
            return;
        }

        $lines = file($csvPath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        $logicalRows = [];
        $currentRow = '';

        foreach ($lines as $lineIndex => $rawLine) {
            $line = trim((string) $rawLine);
            if ($line === '') {
                continue;
            }

            // Remove UTF-8 BOM from the first line if present.
            if ($lineIndex === 0) {
                $line = preg_replace('/^\xEF\xBB\xBF/u', '', $line) ?? $line;
            }

            if (substr_count($line, ';') >= 3) {
                if ($currentRow !== '') {
                    $logicalRows[] = $currentRow;
                }

                $currentRow = $line;
            } elseif ($currentRow !== '') {
                // Some settlement lists are split across lines in the source CSV.
                $currentRow .= ' ' . $line;
            }
        }

        if ($currentRow !== '') {
            $logicalRows[] = $currentRow;
        }

        foreach ($logicalRows as $rawRow) {
            $row = str_getcsv($rawRow, ';');
            if (count($row) < 4) {
                continue;
            }

            $countyRaw = isset($row[1]) ? trim($row[1]) : '';
            $districtRaw = isset($row[2]) ? trim($row[2]) : '';
            $settlementsRaw = isset($row[3]) ? trim($row[3]) : '';

            if ($countyRaw === 'Megye' && $districtRaw === 'Járás') {
                continue;
            }

            $countyRaw = $this->normalizeCountyTitle($countyRaw);

            if ($countyRaw === '' && $lastRegion instanceof Region) {
                $region = $lastRegion;
            } elseif ($countyRaw !== '') {
                $countyTitle = preg_replace('/\s+/', ' ', $countyRaw);
                $region = Region::firstOrCreate(['title' => $countyTitle]);
                $lastRegion = $region;
            } else {
                continue;
            }

            if ($districtRaw === '') {
                continue;
            }

            $innerTitle = preg_replace('/\s+/', ' ', $districtRaw);
            $inner = InnerRegion::firstOrCreate([
                'title' => $innerTitle,
                'region_id' => $region->id,
            ]);

            $settlementNames = preg_split('/\s*,\s*/u', $settlementsRaw);
            foreach ($settlementNames as $name) {
                $name = trim((string) $name, "\"'\r\n\t ");
                if ($name === '') {
                    continue;
                }

                Settlement::firstOrCreate(
                    ['title' => $name, 'inner_region_id' => $inner->id],
                    ['number' => '']
                );
            }
        }
    }

    private function normalizeCountyTitle(string $countyTitle): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($countyTitle));
        if ($normalized === null) {
            return '';
        }

        $legacyCountyNames = [
            'Csongrád' => 'Csongrád-Csanád',
        ];

        if (array_key_exists($normalized, $legacyCountyNames)) {
            return $legacyCountyNames[$normalized];
        }

        return $normalized;
    }

    private function ensureSettlement(string $regionTitle, string $innerTitle, string $settlementTitle, string $number): int
    {
        $region = Region::firstOrCreate(['title' => $regionTitle]);
        $inner = InnerRegion::firstOrCreate([
            'title' => $innerTitle,
            'region_id' => $region->id,
        ]);

        $settlement = Settlement::firstOrCreate(
            ['title' => $settlementTitle, 'inner_region_id' => $inner->id],
            ['number' => $number]
        );

        if (($settlement->number ?? '') === '' && $number !== '') {
            $settlement->number = $number;
            $settlement->save();
        }

        return (int) $settlement->id;
    }

    private function resetTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'poll_answers',
            'poll_options',
            'polls',
            'event_messages',
            'event_shows',
            'event_requests',
            'events',
            'establishment_requests',
            'class_students',
            'students',
            'staffs',
            'classes',
            'establishments',
            'settlements',
            'inner_regions',
            'regions',
            'personal_access_tokens',
            'password_reset_tokens',
            'users',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createUser(string $name, string $email): int
    {
        return (int) DB::table('users')->insertGetId([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('Password123'),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedSchoolScenario(
        int $adminUserId,
        int $settlementId,
        string $establishmentTitle,
        string $establishmentDescription,
        string $namePrefix
    ): void {
        $year = (int) now()->year;
        $localStart = Carbon::create($year, 5, 7, 9, 0, 0);
        $localEnd = Carbon::create($year, 5, 7, 10, 0, 0);
        $globalStart = Carbon::create($year, 5, 12, 11, 0, 0);
        $globalEnd = Carbon::create($year, 5, 12, 12, 0, 0);
        $openStart = Carbon::create($year, 5, 19, 10, 0, 0);
        $openEnd = Carbon::create($year, 5, 19, 11, 30, 0);
        $weeklyStart = Carbon::create($year, 5, 5, 8, 30, 0);
        $weeklyEnd = Carbon::create($year, 5, 5, 9, 30, 0);
        $weeklyUntil = Carbon::create($year, 5, 26, 23, 59, 59);

        $establishmentId = (int) DB::table('establishments')->insertGetId([
            'title' => $establishmentTitle,
            'description' => $establishmentDescription,
            'user_id' => $adminUserId,
            'website' => 'https://example.com',
            'email' => strtolower($namePrefix) . '@teszt-iskola.hu',
            'phone' => '+361234567',
            'address' => $namePrefix . ' Fő tér 1.',
            'accepts_join_requests' => true,
            'settlement_id' => $settlementId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $adminUserId)->update(['establishment_id' => $establishmentId]);

        DB::table('staffs')->insert([
            'role' => 'admin',
            'alias' => $namePrefix . ' Admin',
            'establishment_id' => $establishmentId,
            'user_id' => $adminUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $teacherUserIds = [];
        for ($i = 1; $i <= 2; $i++) {
            $teacherUserId = $this->createUser(
                $namePrefix . ' Tanár ' . $i,
                strtolower($namePrefix) . '.teacher' . $i . '@example.com'
            );

            DB::table('staffs')->insert([
                'role' => 'teacher',
                'alias' => $namePrefix . 'Tanár' . $i,
                'establishment_id' => $establishmentId,
                'user_id' => $teacherUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $teacherUserId)->update(['establishment_id' => $establishmentId]);

            $teacherUserIds[] = $teacherUserId;
        }

        $studentUserIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $studentUserId = $this->createUser(
                $namePrefix . ' Diák ' . $i,
                strtolower($namePrefix) . '.student' . $i . '@example.com'
            );

            DB::table('students')->insert([
                'alias' => $namePrefix . 'Diák' . $i,
                'establishment_id' => $establishmentId,
                'user_id' => $studentUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $studentUserId)->update(['establishment_id' => $establishmentId]);

            $studentUserIds[] = $studentUserId;
        }

        for ($i = 1; $i <= 2; $i++) {
            $requestUserId = $this->createUser(
                $namePrefix . ' Kérvényező Diák ' . $i,
                strtolower($namePrefix) . '.request.student' . $i . '@example.com'
            );

            DB::table('establishment_requests')->insert([
                'status' => 'pending',
                'role' => 'student',
                'alias' => $namePrefix . 'ReqDiák' . $i,
                'user_id' => $requestUserId,
                'establishment_id' => $establishmentId,
                'created_at' => now(),
            ]);
        }

        for ($i = 1; $i <= 2; $i++) {
            $requestUserId = $this->createUser(
                $namePrefix . ' Kérvényező Tanár ' . $i,
                strtolower($namePrefix) . '.request.teacher' . $i . '@example.com'
            );

            DB::table('establishment_requests')->insert([
                'status' => 'pending',
                'role' => 'teacher',
                'alias' => $namePrefix . 'ReqTanár' . $i,
                'user_id' => $requestUserId,
                'establishment_id' => $establishmentId,
                'created_at' => now(),
            ]);
        }

        DB::table('classes')->insert([
            'user_id' => null,
            'name' => '9.B',
            'grade' => 9,
            'capacity' => 30,
            'establishment_id' => $establishmentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fullClassId = (int) DB::table('classes')->insertGetId([
            'user_id' => $teacherUserIds[0],
            'name' => '10.A',
            'grade' => 10,
            'capacity' => 30,
            'establishment_id' => $establishmentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($studentUserIds as $studentUserId) {
            DB::table('class_students')->insert([
                'class_id' => $fullClassId,
                'user_id' => $studentUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $eventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $establishmentId,
            'title' => $namePrefix . ' Teszt iskolai esemény',
            'chat_enabled' => true,
            'description' => 'Egész iskolára kiterjedő teszt esemény.',
            'content' => 'Seeder által létrehozott minta esemény.',
            'user_id' => $adminUserId,
            'start_date' => $localStart,
            'end_date' => $localEnd,
            'status' => 'ended',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $globalEventId = (int) DB::table('events')->insertGetId([
            'type' => 'global',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $establishmentId,
            'title' => $namePrefix . ' Globális esemény',
            'chat_enabled' => false,
            'description' => 'Globális típusú teszt esemény.',
            'content' => 'Seeder által létrehozott globális minta esemény.',
            'user_id' => $adminUserId,
            'start_date' => $globalStart,
            'end_date' => $globalEnd,
            'status' => 'ended',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $openEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $establishmentId,
            'title' => $namePrefix . ' Nyitott esemény',
            'chat_enabled' => true,
            'description' => 'Jelenleg futó nyitott teszt esemény.',
            'content' => 'Seeder által létrehozott nyitott minta esemény.',
            'user_id' => $adminUserId,
            'start_date' => $openStart,
            'end_date' => $openEnd,
            'status' => 'ongoing',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $weeklyEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'evfolyam_szintu',
            'target_class_ids' => null,
            'target_grade_ids' => json_encode([10]),
            'establishment_id' => $establishmentId,
            'title' => $namePrefix . ' Heti esemény',
            'chat_enabled' => true,
            'description' => 'Hetente ismétlődő teszt esemény.',
            'content' => 'Seeder által létrehozott heti minta esemény.',
            'user_id' => $adminUserId,
            'start_date' => $weeklyStart,
            'end_date' => $weeklyEnd,
            'status' => 'upcoming',
            'cancelled_at' => null,
            'is_recurring' => true,
            'recurrence_frequency' => 'weekly',
            'recurrence_until' => $weeklyUntil,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('event_shows')->insert([
            'is_favourite' => false,
            'event_id' => $eventId,
            'user_id' => $adminUserId,
            'answer' => 'y',
            'establishment_id' => $establishmentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($studentUserIds as $index => $studentUserId) {
            DB::table('event_shows')->insert([
                'is_favourite' => false,
                'event_id' => $eventId,
                'user_id' => $studentUserId,
                'answer' => $index < 3 ? 'y' : 'n',
                'establishment_id' => $establishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $visibleUserIds = array_merge([$adminUserId], $teacherUserIds, $studentUserIds);
        foreach ($visibleUserIds as $index => $userId) {
            DB::table('event_shows')->insert([
                'is_favourite' => $index % 3 === 0,
                'event_id' => $globalEventId,
                'user_id' => $userId,
                'answer' => $index % 2 === 0 ? 'y' : 'n',
                'establishment_id' => $establishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_shows')->insert([
                'is_favourite' => false,
                'event_id' => $openEventId,
                'user_id' => $userId,
                'answer' => 'NA',
                'establishment_id' => $establishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_shows')->insert([
                'is_favourite' => $index % 4 === 0,
                'event_id' => $weeklyEventId,
                'user_id' => $userId,
                'answer' => 'NA',
                'establishment_id' => $establishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->seedWeeklyOccurrencesFromParent($weeklyEventId, $establishmentId, $visibleUserIds);

        for ($i = 0; $i < 3; $i++) {
            DB::table('event_messages')->insert([
                'event_id' => $eventId,
                'user_id' => $studentUserIds[$i],
                'content' => 'Teszt komment ' . ($i + 1) . ' - ' . $namePrefix,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $pollId = (int) DB::table('polls')->insertGetId([
            'event_id' => $eventId,
            'title' => $namePrefix . ' Teszt szavazás',
            'user_id' => $adminUserId,
            'start_date' => now()->subDays(7)->toDateString(),
            'deadline' => now()->subDays(2)->toDateString(),
            'is_timed' => true,
            'hidden_results' => false,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $optionIds = [];
        foreach (['Igen', 'Nem', 'Talán'] as $optionTitle) {
            $optionIds[] = (int) DB::table('poll_options')->insertGetId([
                'poll_id' => $pollId,
                'title' => $optionTitle,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            DB::table('poll_answers')->insert([
                'poll_id' => $pollId,
                'user_id' => $studentUserIds[$i],
                'poll_option_id' => $optionIds[$i % count($optionIds)],
            ]);
        }
    }

    private function seedGenericData(int $szegedSettlementId, int $kiskunSettlementId): void
    {
        $year = (int) now()->year;
        $genericLocalStart = Carbon::create($year, 5, 8, 9, 15, 0);
        $genericLocalEnd = Carbon::create($year, 5, 8, 10, 15, 0);
        $genericGlobalStart = Carbon::create($year, 5, 13, 13, 0, 0);
        $genericGlobalEnd = Carbon::create($year, 5, 13, 14, 0, 0);
        $genericOpenStart = Carbon::create($year, 5, 19, 14, 0, 0);
        $genericOpenEnd = Carbon::create($year, 5, 19, 15, 0, 0);
        $genericWeeklyStart = Carbon::create($year, 5, 6, 10, 30, 0);
        $genericWeeklyEnd = Carbon::create($year, 5, 6, 11, 30, 0);
        $genericWeeklyUntil = Carbon::create($year, 5, 27, 23, 59, 59);

        $genericAdminId = $this->createUser('Generic Teszt Admin', 'generic.admin@example.com');

        $genericEstablishmentId = (int) DB::table('establishments')->insertGetId([
            'title' => 'Generic Teszt Szakképző Iskola',
            'description' => 'Általános tesztadatok Postman ellenőrzéshez.',
            'user_id' => $genericAdminId,
            'website' => 'https://generic-teszt.example.com',
            'email' => 'generic@teszt-iskola.hu',
            'phone' => '+3699123456',
            'address' => 'Szeged Teszt utca 10.',
            'accepts_join_requests' => true,
            'settlement_id' => $szegedSettlementId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $genericAdminId)->update(['establishment_id' => $genericEstablishmentId]);

        DB::table('staffs')->insert([
            'role' => 'admin',
            'alias' => 'GenericAdmin',
            'establishment_id' => $genericEstablishmentId,
            'user_id' => $genericAdminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $genericTeacherIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $teacherId = $this->createUser('Generic Tanár ' . $i, 'generic.teacher' . $i . '@example.com');

            DB::table('staffs')->insert([
                'role' => $i === 1 ? 'admin' : 'teacher',
                'alias' => 'GenTanar' . $i,
                'establishment_id' => $genericEstablishmentId,
                'user_id' => $teacherId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $teacherId)->update(['establishment_id' => $genericEstablishmentId]);
            $genericTeacherIds[] = $teacherId;
        }

        $genericStudentIds = [];
        for ($i = 1; $i <= 8; $i++) {
            $studentId = $this->createUser('Generic Diák ' . $i, 'generic.student' . $i . '@example.com');

            DB::table('students')->insert([
                'alias' => 'GenDiak' . $i,
                'establishment_id' => $genericEstablishmentId,
                'user_id' => $studentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $studentId)->update(['establishment_id' => $genericEstablishmentId]);
            $genericStudentIds[] = $studentId;
        }

        $classAlphaId = (int) DB::table('classes')->insertGetId([
            'user_id' => $genericTeacherIds[1],
            'name' => '11.C',
            'grade' => 11,
            'capacity' => 28,
            'establishment_id' => $genericEstablishmentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('classes')->insert([
            'user_id' => null,
            'name' => '12.D',
            'grade' => 12,
            'capacity' => 30,
            'establishment_id' => $genericEstablishmentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (array_slice($genericStudentIds, 0, 6) as $studentId) {
            DB::table('class_students')->insert([
                'class_id' => $classAlphaId,
                'user_id' => $studentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $localEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'osztaly_szintu',
            'target_class_ids' => json_encode([$classAlphaId]),
            'target_grade_ids' => null,
            'establishment_id' => $genericEstablishmentId,
            'title' => 'Generic Osztályszintű Esemény',
            'chat_enabled' => true,
            'description' => 'Osztályszintű tesztesemény.',
            'content' => 'Osztályra célzott teszt tartalom.',
            'user_id' => $genericAdminId,
            'start_date' => $genericLocalStart,
            'end_date' => $genericLocalEnd,
            'status' => 'upcoming',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $globalEventId = (int) DB::table('events')->insertGetId([
            'type' => 'global',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $genericEstablishmentId,
            'title' => 'Generic Globális Esemény',
            'chat_enabled' => false,
            'description' => 'Globális teszt esemény.',
            'content' => 'Globális eseménytartalom.',
            'user_id' => $genericAdminId,
            'start_date' => $genericGlobalStart,
            'end_date' => $genericGlobalEnd,
            'status' => 'ended',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $openEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $genericEstablishmentId,
            'title' => 'Generic Nyitott Esemény',
            'chat_enabled' => true,
            'description' => 'Jelenleg is futó nyitott teszt esemény.',
            'content' => 'Nyitott esemény minta tartalom.',
            'user_id' => $genericAdminId,
            'start_date' => $genericOpenStart,
            'end_date' => $genericOpenEnd,
            'status' => 'ongoing',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $weeklyEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'evfolyam_szintu',
            'target_class_ids' => null,
            'target_grade_ids' => json_encode([11]),
            'establishment_id' => $genericEstablishmentId,
            'title' => 'Generic Heti Esemény',
            'chat_enabled' => true,
            'description' => 'Hetente ismétlődő teszt esemény.',
            'content' => 'Heti esemény minta tartalom.',
            'user_id' => $genericAdminId,
            'start_date' => $genericWeeklyStart,
            'end_date' => $genericWeeklyEnd,
            'status' => 'upcoming',
            'cancelled_at' => null,
            'is_recurring' => true,
            'recurrence_frequency' => 'weekly',
            'recurrence_until' => $genericWeeklyUntil,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $visibleUsers = array_merge([$genericAdminId], $genericTeacherIds, $genericStudentIds);
        foreach ($visibleUsers as $idx => $userId) {
            DB::table('event_shows')->insert([
                'is_favourite' => $idx % 3 === 0,
                'event_id' => $globalEventId,
                'user_id' => $userId,
                'answer' => $idx % 2 === 0 ? 'y' : 'n',
                'establishment_id' => $genericEstablishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_shows')->insert([
                'is_favourite' => false,
                'event_id' => $openEventId,
                'user_id' => $userId,
                'answer' => 'NA',
                'establishment_id' => $genericEstablishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_shows')->insert([
                'is_favourite' => $idx % 4 === 0,
                'event_id' => $weeklyEventId,
                'user_id' => $userId,
                'answer' => 'NA',
                'establishment_id' => $genericEstablishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->seedWeeklyOccurrencesFromParent($weeklyEventId, $genericEstablishmentId, $visibleUsers);

        DB::table('event_messages')->insert([
            [
                'event_id' => $globalEventId,
                'user_id' => $genericStudentIds[0],
                'content' => 'Generic komment 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $globalEventId,
                'user_id' => $genericStudentIds[1],
                'content' => 'Generic komment 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $openPollId = (int) DB::table('polls')->insertGetId([
            'event_id' => $localEventId,
            'title' => 'Generic Nyitott Szavazás',
            'user_id' => $genericAdminId,
            'start_date' => $genericLocalStart->copy()->toDateString(),
            'deadline' => $genericLocalStart->copy()->addDays(5)->toDateString(),
            'is_timed' => true,
            'hidden_results' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $openOptionAId = (int) DB::table('poll_options')->insertGetId([
            'poll_id' => $openPollId,
            'title' => 'Opció A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $openOptionBId = (int) DB::table('poll_options')->insertGetId([
            'poll_id' => $openPollId,
            'title' => 'Opció B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('poll_answers')->insert([
            [
                'poll_id' => $openPollId,
                'user_id' => $genericStudentIds[0],
                'poll_option_id' => $openOptionAId,
            ],
            [
                'poll_id' => $openPollId,
                'user_id' => $genericStudentIds[2],
                'poll_option_id' => $openOptionBId,
            ],
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $requestUserId = $this->createUser('Generic Kérő Diák ' . $i, 'generic.request.student' . $i . '@example.com');
            DB::table('establishment_requests')->insert([
                'status' => $i === 1 ? 'pending' : ($i === 2 ? 'approved' : 'rejected'),
                'role' => 'student',
                'alias' => 'GenReqDiak' . $i,
                'user_id' => $requestUserId,
                'establishment_id' => $genericEstablishmentId,
                'created_at' => now(),
            ]);
        }

        for ($i = 1; $i <= 2; $i++) {
            $requestUserId = $this->createUser('Generic Kérő Tanár ' . $i, 'generic.request.teacher' . $i . '@example.com');
            DB::table('establishment_requests')->insert([
                'status' => $i === 1 ? 'pending' : 'approved',
                'role' => 'teacher',
                'alias' => 'GenReqTanar' . $i,
                'user_id' => $requestUserId,
                'establishment_id' => $genericEstablishmentId,
                'created_at' => now(),
            ]);
        }

        $kiskunEstablishmentId = (int) DB::table('establishments')->where('title', 'Kiskunfélegyházi Teszt Technikum')->value('id');
        if ($kiskunEstablishmentId > 0) {
            DB::table('event_requests')->insert([
                'event_id' => $globalEventId,
                'establishment_id' => $kiskunEstablishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $anotherGenericAdminId = $this->createUser('Második Generic Admin', 'generic.second.admin@example.com');
        $secondGenericEstablishmentId = (int) DB::table('establishments')->insertGetId([
            'title' => 'Kiskun Teszt Alapítványi Iskola',
            'description' => 'Második generikus intézmény.',
            'user_id' => $anotherGenericAdminId,
            'website' => 'https://generic-two.example.com',
            'email' => 'generic-two@teszt-iskola.hu',
            'phone' => '+3676123456',
            'address' => 'Kiskunfélegyháza Teszt köz 2.',
            'accepts_join_requests' => false,
            'settlement_id' => $kiskunSettlementId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $anotherGenericAdminId)->update(['establishment_id' => $secondGenericEstablishmentId]);
        DB::table('staffs')->insert([
            'role' => 'admin',
            'alias' => 'Gen2Admin',
            'establishment_id' => $secondGenericEstablishmentId,
            'user_id' => $anotherGenericAdminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondLocalStart = Carbon::create($year, 5, 9, 8, 45, 0);
        $secondLocalEnd = Carbon::create($year, 5, 9, 9, 45, 0);
        $secondGlobalStart = Carbon::create($year, 5, 14, 12, 15, 0);
        $secondGlobalEnd = Carbon::create($year, 5, 14, 13, 15, 0);
        $secondOpenStart = Carbon::create($year, 5, 19, 16, 0, 0);
        $secondOpenEnd = Carbon::create($year, 5, 19, 17, 0, 0);
        $secondWeeklyStart = Carbon::create($year, 5, 7, 9, 45, 0);
        $secondWeeklyEnd = Carbon::create($year, 5, 7, 10, 45, 0);
        $secondWeeklyUntil = Carbon::create($year, 5, 28, 23, 59, 59);

        $secondEndedLocalEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $secondGenericEstablishmentId,
            'title' => 'Generic 2 Lezárt Helyi Esemény',
            'chat_enabled' => true,
            'description' => 'Második intézmény lezárt helyi teszteseménye.',
            'content' => 'Második intézmény helyi lezárt minta esemény.',
            'user_id' => $anotherGenericAdminId,
            'start_date' => $secondLocalStart,
            'end_date' => $secondLocalEnd,
            'status' => 'ended',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondGlobalEventId = (int) DB::table('events')->insertGetId([
            'type' => 'global',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $secondGenericEstablishmentId,
            'title' => 'Generic 2 Globális Esemény',
            'chat_enabled' => false,
            'description' => 'Második intézmény globális teszteseménye.',
            'content' => 'Második intézmény globális minta esemény.',
            'user_id' => $anotherGenericAdminId,
            'start_date' => $secondGlobalStart,
            'end_date' => $secondGlobalEnd,
            'status' => 'ended',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondOpenEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $secondGenericEstablishmentId,
            'title' => 'Generic 2 Nyitott Esemény',
            'chat_enabled' => true,
            'description' => 'Második intézmény jelenleg futó eseménye.',
            'content' => 'Második intézmény nyitott minta esemény.',
            'user_id' => $anotherGenericAdminId,
            'start_date' => $secondOpenStart,
            'end_date' => $secondOpenEnd,
            'status' => 'ongoing',
            'cancelled_at' => null,
            'is_recurring' => false,
            'recurrence_frequency' => null,
            'recurrence_until' => null,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $secondWeeklyEventId = (int) DB::table('events')->insertGetId([
            'type' => 'local',
            'target_group' => 'teljes_iskola',
            'target_class_ids' => null,
            'target_grade_ids' => null,
            'establishment_id' => $secondGenericEstablishmentId,
            'title' => 'Generic 2 Heti Esemény',
            'chat_enabled' => true,
            'description' => 'Második intézmény heti ismétlődő eseménye.',
            'content' => 'Második intézmény heti minta esemény.',
            'user_id' => $anotherGenericAdminId,
            'start_date' => $secondWeeklyStart,
            'end_date' => $secondWeeklyEnd,
            'status' => 'upcoming',
            'cancelled_at' => null,
            'is_recurring' => true,
            'recurrence_frequency' => 'weekly',
            'recurrence_until' => $secondWeeklyUntil,
            'recurrence_parent_event_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([$secondEndedLocalEventId, $secondGlobalEventId, $secondOpenEventId, $secondWeeklyEventId] as $secondEventId) {
            DB::table('event_shows')->insert([
                'is_favourite' => false,
                'event_id' => $secondEventId,
                'user_id' => $anotherGenericAdminId,
                'answer' => 'NA',
                'establishment_id' => $secondGenericEstablishmentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->seedWeeklyOccurrencesFromParent($secondWeeklyEventId, $secondGenericEstablishmentId, [$anotherGenericAdminId]);
    }

    private function seedWeeklyOccurrencesFromParent(int $parentEventId, int $establishmentId, array $visibleUserIds): void
    {
        $parent = DB::table('events')->where('id', $parentEventId)->first();
        if (!$parent) {
            return;
        }

        $startDate = Carbon::parse((string) $parent->start_date);
        $endDate = Carbon::parse((string) $parent->end_date);
        $untilDate = Carbon::parse((string) $parent->recurrence_until)->endOfDay();

        $nextStart = $startDate->copy()->addWeek();
        $nextEnd = $endDate->copy()->addWeek();

        while ($nextStart->lte($untilDate)) {
            $occurrenceId = (int) DB::table('events')->insertGetId([
                'type' => $parent->type,
                'target_group' => $parent->target_group,
                'target_class_ids' => $parent->target_class_ids,
                'target_grade_ids' => $parent->target_grade_ids,
                'establishment_id' => $establishmentId,
                'title' => $parent->title,
                'chat_enabled' => $parent->chat_enabled,
                'description' => $parent->description,
                'content' => $parent->content,
                'user_id' => $parent->user_id,
                'start_date' => $nextStart->copy(),
                'end_date' => $nextEnd->copy(),
                'status' => $parent->status,
                'cancelled_at' => null,
                'is_recurring' => true,
                'recurrence_frequency' => 'weekly',
                'recurrence_until' => $parent->recurrence_until,
                'recurrence_parent_event_id' => $parentEventId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($visibleUserIds as $userId) {
                DB::table('event_shows')->insert([
                    'is_favourite' => false,
                    'event_id' => $occurrenceId,
                    'user_id' => $userId,
                    'answer' => 'NA',
                    'establishment_id' => $establishmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $nextStart->addWeek();
            $nextEnd->addWeek();
        }
    }
}
