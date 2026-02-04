<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Establishment;
use App\Models\EstablishmentRequest;   
use App\Models\Region;
use App\Models\InnerRegion;
use App\Models\Settlement;
use App\Models\ClassModel;
use App\Models\ClassStudent;
use App\Models\Event;
use App\Models\EventShown;
use App\Models\EventMessage;
use App\Models\EventFeedback;
use App\Models\EventFavourite;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollAnswer;
use App\Models\Personel;
use App\Models\Student;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
        ]);   

        Region::factory()->create([
            'title' => 'Bács-Kiskun vármegye',
        ]);
        InnerRegion::factory()->create([
            'title' => 'Kiskunfélegyházi járás',
            'region_id' => 1,
        ]);
        Settlement::factory()->create([
            'title' => 'Kiskunfélegyháza',
            'number'=> '6100',
            'inner_region_id' => 1,
        ]);

        ClassModel::factory()->create([
            'establishment_id' => 1,
            'user_id' => 1,
            'grade' => 13,
            'name' => 'I',
        ]);

        ClassStudent::factory(50)->create([
            'student_id' => 1,
            'class_model_id' => 1,
        ]);

        Event::factory()->create([
            'user_id' => 1,
            'type' => 'local',
            'title' => 'Első esemény',
            'description' => 'Ez az első esemény leírása.',
            'content' => 'Ez az első esemény tartalma.',
            'start_date' => '2024-06-01',
            'end_date' => '2024-06-02', 
            'status' => 'ongoing',
        ]);
        EventShown::factory()->create([
            'user_id' => 1,
            'event_id' => 1,
            'class_model_id' => 1,
        ]);
        EventMessage::factory()->create([
            'user_id' => 1,
            'event_id' => 1,
            'content' => 'Ez egy esemény üzenet.',

        ]);
        EventFeedback::factory()->create([
            'user_id' => 1,
            'event_id' => 1,
            'answor' => 'y',
        ]);
        EventFavourite::factory()->create([
            'user_id' => 1,
            'event_id' => 1,
        ]);

        Poll::factory()->create([
            'user_id' => 1,
            'title' => 'Első szavazás',
            'event_id' => 1,
        ]);
        PollOption::factory()->create([
            'poll_id' => 1,
            'option_text' => 'Első opció',
        ]);
        PollAnswer::factory()->create([
            'user_id' => 1,
            'poll_id' => 1,
            'poll_option_id' => 1,
            
        ]);

        Establishment::factory()->create([
            'title' => 'PÉGÉ',
            'description' => 'Példa Gimnázium és Egyetem',
            'user_id' => 1,
            'settlement_id' => 1,
            'website' => 'https://pÉGÉ.hu',
            'email'=> 'pÉGÉ@pege.hu',
            'phone' => '+36301234567',
            'address' => 'Vidám utca 13.',
    
        ]);
        Personel::factory()->create();
        Student::factory()->create();
        EstablishmentRequest::factory()->create();

    }
}
