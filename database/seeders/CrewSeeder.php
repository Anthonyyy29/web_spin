<?php

namespace Database\Seeders;

use App\Models\Crew;
use Illuminate\Database\Seeder;

class CrewSeeder extends Seeder
{
    /**
     * Isi anggota Gen 7: nama, jabatan, dan nama berkas foto di
     * public/assets/uploads/crew. Kadiv disimpan dengan jabatan divisinya dan
     * ditulis paling awal di divisi itu. Yang menjabat ganda memakai jabatan utamanya.
     */
    public function run(): void
    {
        $anggota = [
            ['Nurul Afifah Azzahra', 'General Manager', 'nurul-afifah-azzahra.jpg'],
            ['Fitria Attira', 'Vice General Manager', 'fitria-attira.jpg'],
            ['Vitroh Maviroh', 'Sekretaris', 'vitroh-maviroh.jpg'],
            ['Syavirni Dwi Amalia', 'Sekretaris', 'syavirni-dwi-amalia.jpg'],
            ['Shafa Dhiya Raihanah', 'Bendahara', 'shafa-dhiya-raihanah.jpg'],
            ['Noval Rasyah Kurnia Putra', 'Bendahara', 'noval-rasyah-kurnia-putra.jpg'],
            ['Allea De Chaelyn Tomo Lawa', 'Head of Podcast', 'allea-de-chaelyn-tomo-lawa.jpg'],
            ['Ivanda Leona Putri Vabita', 'Announcer', 'ivanda-leona-putri-vabita.jpg'],
            ['Ashfaa Atsqya Fernando', 'Announcer', 'ashfaa-atsqya-fernando.jpg'],
            ['Belva Vanya Aulia', 'Announcer', 'belva-vanya-aulia.jpg'],
            ['Kahlil Al Fathir', 'Announcer', 'kahlil-al-fathir.jpg'],
            ['Kimberly Valencia', 'Announcer', 'kimberly-valencia.jpg'],
            ['Lalia Alina', 'Announcer', 'lalia-alina.jpg'],
            ['Nail Khairi Nur Hadi', 'Announcer', 'nail-khairi-nur-hadi.jpg'],
            ['Nisrina Ihda Jamaliah', 'Announcer', 'nisrina-ihda-jamaliah.jpg'],
            ['Priscilla Christiana Nahadin', 'Announcer', 'priscilla-christiana-nahadin.jpg'],
            ['Rael Nathania Wibowo', 'Announcer', 'rael-nathania-wibowo.jpg'],
            ['Saskia Oktaviani', 'Announcer', 'saskia-oktaviani.jpg'],
            ['Sheriel Zelda Fitriani Cholid', 'Announcer', 'sheriel-zelda-fitriani-cholid.jpg'],
            ['Shezza Nadyra Hergianto', 'Announcer', 'shezza-nadyra-hergianto.jpg'],
            ['Shiren Rizkia Fahira', 'Announcer', 'shiren-rizkia-fahira.jpg'],
            ['Yariza Nafisha Hamzah', 'Announcer', 'yariza-nafisha-hamzah.jpg'],
            ['Salwa Syafira', 'Sound Operator', 'salwa-syafira.jpg'],
            ['Ahmad Faris Firta Budiman', 'Sound Operator', 'ahmad-faris-firta-budiman.jpg'],
            ['Audrey Geovanni Rionald Sinaga', 'Sound Operator', 'audrey-geovanni-rionald-sinaga.jpg'],
            ['Bimo Septiansyah', 'Sound Operator', 'bimo-septiansyah.jpg'],
            ['Dhini Aulya', 'Sound Operator', 'dhini-aulya.jpg'],
            ['Fatyanheiza Putri Cahyadi', 'Sound Operator', 'fatyanheiza-putri-cahyadi.jpg'],
            ['Meirina Kartika Rahmah', 'Sound Operator', 'meirina-kartika-rahmah.jpg'],
            ['Raka Desnoel Sr', 'Sound Operator', 'raka-desnoel-sr.jpg'],
            ['Yohanes Dimas Restu Putra', 'Sound Operator', 'yohanes-dimas-restu-putra.jpg'],
            ['Zahra Dinda Rahma Dani', 'Sound Operator', 'zahra-dinda-rahma-dani.jpg'],
            ['Satriani', 'Human Resource', 'satriani.jpg'],
            ['Anggraini Dwina Putri', 'Human Resource', 'anggraini-dwina-putri.jpg'],
            ['Azza Nabila Assaida', 'Human Resource', 'azza-nabila-assaida.jpg'],
            ['Dewi Novita Sari', 'Human Resource', 'dewi-novita-sari.jpg'],
            ['Feby Kumala', 'Human Resource', 'feby-kumala.jpg'],
            ['Keisha Ann Aida Rahman', 'Human Resource', 'keisha-ann-aida-rahman.jpg'],
            ['Yoga Nanda Saputra', 'Human Resource', 'yoga-nanda-saputra.jpg'],
            ['Nayla Agis Maulida', 'Public Relation', 'nayla-agis-maulida.jpg'],
            ['Putri Nabila', 'Partnership', 'putri-nabila.jpg'],
            ['Azzahra Putri Anastasya', 'Partnership', 'azzahra-putri-anastasya.jpg'],
            ['Calysta Jauza Faris', 'Partnership', 'calysta-jauza-faris.jpg'],
            ['Divta Dewani Sunandar', 'Partnership', 'divta-dewani-sunandar.jpg'],
            ['Shabrina Hilmi Amanu', 'Partnership', 'shabrina-hilmi-amanu.jpg'],
            ['Najla Safira Balqis', 'Media Relation', 'najla-safira-balqis.jpg'],
            ['Fahri Sohail Risay', 'Media Relation', 'fahri-sohail-risay.jpg'],
            ['Suci Mulia Fitri', 'Media Relation', 'suci-mulia-fitri.jpg'],
            ['Salwa Azzahra Zein', 'Media Relation', 'salwa-azzahra-zein.jpg'],
            ['Zafira Jua Santika', 'Media Relation', 'zafira-jua-santika.jpg'],
            ['Miftah Faradilah', 'Head of Socmed', 'miftah-faradilah.jpg'],
            ['Syalloma Zidna Azzahra', 'Creative', 'syalloma-zidna-azzahra.jpg'],
            ['Farah Syifa Annisa Ramadhani', 'Creative', 'farah-syifa-annisa-ramadhani.jpg'],
            ['Ghistyanata Utomo', 'Creative', 'ghistyanata-utomo.jpg'],
            ['Khaira Meizahra', 'Creative', 'khaira-meizahra.jpg'],
            ['Nur Annissa', 'Creative', 'nur-annissa.jpg'],
            ['Salsabila Putri Azhari', 'Creative', 'salsabila-putri-azhari.jpg'],
            ['Sayla Ahmad Sumantri Ownie', 'Creative', 'sayla-ahmad-sumantri-ownie.jpg'],
            ['Lidya Damayanti', 'Editor Video', 'lidya-damayanti.jpg'],
            ['Abdul Zani', 'Editor Video', 'abdul-zani.jpg'],
            ['Andhika Efran Nurdiansah', 'Editor Video', 'andhika-efran-nurdiansah.jpg'],
            ['Farhan Athallah Shalfianas', 'Editor Video', 'farhan-athallah-shalfianas.jpg'],
            ['Inas Nahiza', 'Editor Video', 'inas-nahiza.jpg'],
            ['Keyro Haqqu Hemiyari', 'Editor Video', 'keyro-haqqu-hemiyari.jpg'],
            ['Shofie Queen Hillul Latifah', 'Editor Video', 'shofie-queen-hillul-latifah.jpg'],
            ['Chelsea Alya Mukhbita W.', 'Design & Publishing', 'chelsea-alya-mukhbita-w.jpg'],
            ['Aldelvine Zahyan Annurin', 'Design & Publishing', 'aldelvine-zahyan-annurin.jpg'],
            ['Anisya Nurfaidah', 'Design & Publishing', 'anisya-nurfaidah.jpg'],
            ['Calvien Agustian Tacazily', 'Design & Publishing', 'calvien-agustian-tacazily.jpg'],
            ['Devie Damayanti', 'Design & Publishing', 'devie-damayanti.jpg'],
            ['Fathin Nayla Putri', 'Design & Publishing', 'fathin-nayla-putri.jpg'],
            ['Luthfia Malika', 'Design & Publishing', 'luthfia-malika.jpg'],
            ['Shevia Dwi Lestari', 'Design & Publishing', 'shevia-dwi-lestari.jpg'],
            ['Zahra Aulia Nafizza', 'Design & Publishing', 'zahra-aulia-nafizza.jpg'],
            ['Zhafira Ramadhani', 'Design & Publishing', 'zhafira-ramadhani.jpg'],
            ['Adi Ezra Anthoni', 'Web Development', 'adi-ezra-anthoni.jpg'],
            ['Bimo Rajendra Fassah', 'Web Development', 'bimo-rajendra-fassah.jpg'],
            ['Rafdi Muliawan', 'Web Development', 'rafdi-muliawan.jpg'],
            ['Rayya Mahira', 'Web Development', 'rayya-mahira.jpg'],
        ];

        foreach ($anggota as [$nama, $jabatan, $foto]) {
            Crew::updateOrCreate(['name' => $nama], [
                'role' => $jabatan,
                'image' => 'crew/'.$foto,
            ]);
        }
    }
}
