<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class Gen7MemberSeeder extends Seeder
{
    /**
     * Temporary password for every seeded member account.
     */
    public const DEFAULT_PASSWORD = '1234';

    /**
     * Gen 7 members per role name. The username is the full name; a member with two jobs gets one account per job,
     * with the job in parentheses after the name.
     *
     * @var array<string, list<string>>
     */
    private const MEMBERS = [
        'gm' => [
            'Nurul Afifah Azzahra',
        ],
        'vgm' => [
            'Fitria Attira',
        ],
        'sekretaris' => [
            'Vitroh Maviroh',
            'Syavirni Dwi Amalia (Sekretaris)',
        ],
        'finance' => [
            'Shafa Dhiya Raihanah (Finance)',
            'Noval Rasyah Kurnia Putra (Finance)',
        ],
        'head_of_podcast' => [
            'Allea De Chaelyn Tomo Lawa',
        ],
        'kadiv_announcer' => [
            'Ivanda Leona Putri Vabita',
        ],
        'announcer' => [
            'Ashfaa Atsqya Fernando',
            'Belva Vanya Aulia',
            'Kahlil Al Fathir',
            'Kimberly Valencia',
            'Lalia Alina',
            'Nail Khairi Nur Hadi',
            'Nisrina Ihda Jamaliah',
            'Noval Rasyah Kurnia Putra (Announcer)',
            'Priscilla Christiana Nahadin',
            'Rael Nathania Wibowo',
            'Saskia Oktaviani',
            'Shafa Dhiya Raihanah (Announcer)',
            'Sheriel Zelda Fitriani Cholid',
            'Shezza Nadyra Hergianto',
            'Shiren Rizkia fahira',
            'Yariza Nafisha Hamzah',
        ],
        'kadiv_sound_operator' => [
            'Salwa Syafira',
        ],
        'sound_operator' => [
            'Ahmad Faris Firta Budiman',
            'Audrey Geovanni Rionald Sinaga',
            'Bimo Septiansyah',
            'Dhini Aulya',
            'Fatyanheiza Putri Cahyadi',
            'Meirina Kartika Rahmah',
            'Raka Desnoel Sr',
            'Syavirni Dwi Amalia (Sound Operator)',
            'Yohanes Dimas Restu Putra',
            'Zahra Dinda Rahma Dani',
        ],
        'kadiv_human_resource' => [
            'Satriani',
        ],
        'human_resource' => [
            'Anggraini Dwina Putri',
            'Azza Nabila Assaida',
            'Dewi Novita Sari',
            'Feby Kumala',
            'Keisha Ann Aida Rahman',
            'Yoga Nanda Saputra',
        ],
        'head_of_public_relation' => [
            'Nayla Agis Maulida',
        ],
        'kadiv_partnership' => [
            'Putri Nabila',
        ],
        'partnership' => [
            'Azzahra Putri Anastasya',
            'Calysta Jauza Faris',
            'Divta Dewani Sunandar',
            'Shabrina Hilmi Amanu',
        ],
        'kadiv_media_relation' => [
            'Najla Safira Balqis',
        ],
        'media_relation' => [
            'Fahri Sohail Risay',
            'Rizma Ayu Revalina',
            'Salwa Azzahra Zein',
            'Zafira Jua Santika',
        ],
        'head_of_social_media' => [
            'Miftah Faradilah',
        ],
        'kadiv_creative' => [
            'Syalloma Zidna Azzahra',
        ],
        'creative' => [
            'Farah Syifa Annisa Ramadhani',
            'Ghistyanata Utomo',
            'Khaira Meizahra',
            'Nur Annissa',
            'Salsabila Putri Azhari',
            'Sayla Ahmad Sumantri Ownie',
        ],
        'kadiv_video_editor' => [
            'Lidya Damayanti',
        ],
        'video_editor' => [
            'Abdul Zani',
            'Andhika Efran Nurdiansah',
            'Farhan Athallah Shalfianas',
            'Inas Nahiza',
            'Keyro Haqqu Hemiyari',
            'Shofie Queen Hillul Latifah',
        ],
        'kadiv_design_and_publishing' => [
            'Chelsea Alya Mukhbita Widyadhana',
        ],
        'design_and_publishing' => [
            'Aldelvine Zahyan Annurin',
            'Anisya Nurfaidah',
            'Calvien Agustian Tacazily',
            'Devie Damayanti',
            'Fathin Nayla Putri',
            'Luthfia Malika',
            'Shevia Dwi Lestari',
            'Zahra Aulia Nafizza',
            'Zhafira Ramadhani',
        ],
        'kadiv_web_development' => [
            'Adi Ezra Anthoni',
        ],
        'web_development' => [
            'Bimo Rajendra Fassah',
            'Rafdi Muliawan',
            'Rayya Mahira',
        ],
    ];

    /**
     * Create an account for every Gen 7 member, one per job.
     */
    public function run(): void
    {
        $roleIds = Role::pluck('id', 'name');

        foreach (self::MEMBERS as $roleName => $names) {
            foreach ($names as $name) {
                User::create([
                    'username' => $name,
                    'password' => self::DEFAULT_PASSWORD,
                    'role_id' => $roleIds[$roleName],
                ]);
            }
        }
    }
}
