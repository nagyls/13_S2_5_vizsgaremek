<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Establishment;
use App\Models\Region;
use App\Models\InnerRegion;
use App\Models\Settlement;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Event;
use App\Models\EventMessage;
use App\Models\EventFeedback;
use App\Models\EventFavourite;
use App\Models\Personel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
        ]);

        
        $region = Region::create([
            'title' => 'Bács-Kiskun'
        ]);
        $inner = InnerRegion::create([
            'title' => 'Kiskunfélegyháza',
            'region_id' => $region->id,
        ]);
        $settlement = Settlement::create([
            'title' => 'Kiskunfélegyháza',
            'number' => '6100',
            'inner_region_id' => $inner->id,
        ]);


        $est = Establishment::create([
            'title' => 'PÉGÉ',
            'description' => 'Példa Gimnázium és Egyetem',
            'user_id' => $user->id,
            'website' => 'https://pege.hu',
            'settlement_id' => $settlement->id,
        ]);


        $class = ClassModel::create([
            'establishment_id' => $est->id,
            'user_id' => $user->id,
            'grade' => 13,
            'name' => 'I',
        ]);


        $student = Student::create([
            'alias' => 'primary_student',
            'establishment_id' => $est->id,
            'user_id' => $user->id,
        ]);


        for ($i = 0; $i < 10; $i++) {
            $u = User::factory()->create();
            Student::create([
                'alias' => 'student_' . $i,
                'establishment_id' => $est->id,
                'user_id' => $u->id,
            ]);

            DB::table('class_students')->insert([
                'class_id' => $class->id,
                'user_id' => $u->id,
                'created_at' => now(),
            ]);
        }


        $event = Event::create([
            'user_id' => $user->id,
            'type' => 'local',
            'title' => 'Első esemény',
            'description' => 'Ez az első esemény leírása.',
            'content' => 'Ez az első esemény tartalma.',
        ]);


        DB::table('event_shows')->insert([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'class_id' => $class->id,
            'created_at' => now(),
        ]);


        EventMessage::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'content' => 'Ez egy esemény üzenet.',
        ]);


        EventFeedback::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'answer' => 'y',
        ]);


        EventFavourite::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);


        $pollId = DB::table('polls')->insertGetId([
            'event_id' => $event->id,
            'title' => 'Első szavazás',
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $pollOptionId = DB::table('poll_options')->insertGetId([
            'poll_id' => $pollId,
            'title' => 'Első opció',
            'created_at' => now(),
        ]);

        DB::table('poll_answers')->insert([
            'poll_id' => $pollId,
            'user_id' => $user->id,
            'poll_option_id' => $pollOptionId,
        ]);


        DB::table('establishment_requests')->insert([
            'user_id' => $user->id,
            'establishment_id' => $est->id,
            'role' => 'student',
            'created_at' => now(),
        ]);
        DB::table('establishment_requests')->insert([
            'user_id' => $user->id,
            'establishment_id' => $est->id,
            'role' => 'student',
            'created_at' => now(),
        ]);
        DB::table('establishment_requests')->insert([
            'user_id' => $user->id,
            'establishment_id' => $est->id,
            'role' => 'teacher',
            'created_at' => now(),
        ]);
        DB::table('establishment_requests')->insert([
            'user_id' => $user->id,
            'establishment_id' => $est->id,
            'role' => 'teacher',
            'created_at' => now(),
        ]);


        Personel::create([
            'role' => 'admin',
            'establishment_id' => $est->id,
            'user_id' => $user->id,
        ]);

        // csv beoplvasás
        $csvPath = database_path('seeders/data/jarasok.csv');
        $lastRegion = null;

        if (file_exists($csvPath)) {
            if (($handle = fopen($csvPath, 'r')) !== false) {
                
                $header = fgetcsv($handle, 0, ';');

                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    // üres sorok kihagyása
                    if (count($row) === 0) {
                        continue;
                    }

                    // Columns: 0=Sorszám, 1=Megye, 2=Járás (inner region), 3=települések 
                    $countyRaw = isset($row[1]) ? trim($row[1]) : '';
                    $districtRaw = isset($row[2]) ? trim($row[2]) : '';
                    $settlementsRaw = isset($row[3]) ? trim($row[3]) : '';

                    //ha üres akkor az elözőt használjuk
                    if ($countyRaw === '' && $lastRegion instanceof \App\Models\Region) {
                        $region = $lastRegion;
                    } elseif ($countyRaw !== '') {
                        // Normalize county title
                        $countyTitle = preg_replace('/\s+/', ' ', $countyRaw);
                        $region = \App\Models\Region::firstOrCreate(['title' => $countyTitle]);
                        $lastRegion = $region;
                    } else {

                        continue;
                    }
                    if ($districtRaw === '') {
                        continue;
                    }

                    $innerTitle = preg_replace('/\s+/', ' ', $districtRaw);
                    $inner = \App\Models\InnerRegion::firstOrCreate(
                        ['title' => $innerTitle, 'region_id' => $region->id]
                    );

                    // felbontás
                    $settlementNames = preg_split('/\s*,\s*/u', $settlementsRaw);
                    foreach ($settlementNames as $s) {
                        $s = trim($s);
                       
                        $s = trim($s, "\"'\r\n\t ");
                        if ($s === '') {
                            continue;
                        }
                        
                        \App\Models\Settlement::firstOrCreate(
                            ['title' => $s, 'inner_region_id' => $inner->id],
                            ['number' => '']
                        );
                    }
                }

                fclose($handle);
            }
        }
        $region = $region ?? \App\Models\Region::first();
        $inner = $inner ?? \App\Models\InnerRegion::first();
        $settlement = $settlement ?? \App\Models\Settlement::first();
    }
}
