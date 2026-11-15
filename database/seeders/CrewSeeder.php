<?php

namespace Database\Seeders;

use App\Models\Crew;
use Illuminate\Database\Seeder;

class CrewSeeder extends Seeder
{
    /**
     * Isi anggota Gen 7. Kadiv disimpan dengan jabatan divisinya dan ditulis
     * paling awal di divisi itu. Yang menjabat ganda memakai jabatan utamanya.
     * Foto sementara memakai berkas pertama di public/assets/uploads.
     */
    public function run(): void
    {
        $gambar = basename(glob(public_path('assets/uploads/*.{png,jpg}'), GLOB_BRACE)[0] ?? 'logo.png');

        $anggota = [
            ['Nurul Afifah Azzahra', 'General Manager'],
            ['Fitria Attira', 'Vice General Manager'],
            ['Vitroh Maviroh', 'Sekretaris'],
            ['Syavirni Dwi Amalia', 'Sekretaris'],
            ['Shafa Dhiya Raihanah', 'Bendahara'],
            ['Noval Rasyah Kurnia Putra', 'Bendahara'],
            ['Allea De Chaelyn Tomo Lawa', 'Head of Podcast'],
            ['Ivanda Leona Putri Vabita', 'Announcer'],
            ['Ashfaa Atsqya Fernando', 'Announcer'],
            ['Belva Vanya Aulia', 'Announcer'],
            ['Kahlil Al Fathir', 'Announcer'],
            ['Kimberly Valencia', 'Announcer'],
            ['Lalia Alina', 'Announcer'],
            ['Nail Khairi Nur Hadi', 'Announcer'],
            ['Nisrina Ihda Jamaliah', 'Announcer'],
            ['Priscilla Christiana Nahadin', 'Announcer'],
            ['Rael Nathania Wibowo', 'Announcer'],
            ['Saskia Oktaviani', 'Announcer'],
            ['Sheriel Zelda Fitriani Cholid', 'Announcer'],
            ['Shezza Nadyra Hergianto', 'Announcer'],
            ['Shiren Rizkia Fahira', 'Announcer'],
            ['Yariza Nafisha Hamzah', 'Announcer'],
            ['Salwa Syafira', 'Sound Operator'],
            ['Ahmad Faris Firta Budiman', 'Sound Operator'],
            ['Audrey Geovanni Rionald Sinaga', 'Sound Operator'],
            ['Bimo Septiansyah', 'Sound Operator'],
            ['Dhini Aulya', 'Sound Operator'],
            ['Fatyanheiza Putri Cahyadi', 'Sound Operator'],
            ['Meirina Kartika Rahmah', 'Sound Operator'],
            ['Raka Desnoel Sr', 'Sound Operator'],
            ['Yohanes Dimas Restu Putra', 'Sound Operator'],
            ['Zahra Dinda Rahma Dani', 'Sound Operator'],
            ['Satriani', 'Human Resource'],
            ['Anggraini Dwina Putri', 'Human Resource'],
            ['Azza Nabila Assaida', 'Human Resource'],
            ['Dewi Novita Sari', 'Human Resource'],
            ['Feby Kumala', 'Human Resource'],
            ['Keisha Ann Aida Rahman', 'Human Resource'],
            ['Yoga Nanda Saputra', 'Human Resource'],
            ['Nayla Agis Maulida', 'Public Relation'],
            ['Putri Nabila', 'Partnership'],
            ['Azzahra Putri Anastasya', 'Partnership'],
            ['Calysta Jauza Faris', 'Partnership'],
            ['Divta Dewani Sunandar', 'Partnership'],
            ['Shabrina Hilmi Amanu', 'Partnership'],
            ['Najla Safira Balqis', 'Media Relation'],
            ['Fahri Sohail Risay', 'Media Relation'],
            ['Suci Mulia Fitri', 'Media Relation'],
            ['Salwa Azzahra Zein', 'Media Relation'],
            ['Zafira Jua Santika', 'Media Relation'],
            ['Miftah Faradilah', 'Head of Socmed'],
            ['Syalloma Zidna Azzahra', 'Creative'],
            ['Farah Syifa Annisa Ramadhani', 'Creative'],
            ['Ghistyanata Utomo', 'Creative'],
            ['Khaira Meizahra', 'Creative'],
            ['Nur Annissa', 'Creative'],
            ['Salsabila Putri Azhari', 'Creative'],
            ['Sayla Ahmad Sumantri Ownie', 'Creative'],
            ['Lidya Damayanti', 'Editor Video'],
            ['Abdul Zani', 'Editor Video'],
            ['Andhika Efran Nurdiansah', 'Editor Video'],
            ['Farhan Athallah Shalfianas', 'Editor Video'],
            ['Inas Nahiza', 'Editor Video'],
            ['Keyro Haqqu Hemiyari', 'Editor Video'],
            ['Shofie Queen Hillul Latifah', 'Editor Video'],
            ['Chelsea Alya Mukhbita W.', 'Design & Publishing'],
            ['Aldelvine Zahyan Annurin', 'Design & Publishing'],
            ['Anisya Nurfaidah', 'Design & Publishing'],
            ['Calvien Agustian Tacazily', 'Design & Publishing'],
            ['Devie Damayanti', 'Design & Publishing'],
            ['Fathin Nayla Putri', 'Design & Publishing'],
            ['Luthfia Malika', 'Design & Publishing'],
            ['Shevia Dwi Lestari', 'Design & Publishing'],
            ['Zahra Aulia Nafizza', 'Design & Publishing'],
            ['Zhafira Ramadhani', 'Design & Publishing'],
            ['Adi Ezra Anthoni', 'Web Development'],
            ['Bimo Rajendra Fassah', 'Web Development'],
            ['Rafdi Muliawan', 'Web Development'],
            ['Rayya Mahira', 'Web Development'],
        ];

        foreach ($anggota as [$nama, $jabatan]) {
            Crew::updateOrCreate(['name' => $nama], ['role' => $jabatan, 'image' => $gambar]);
        }
    }
}
