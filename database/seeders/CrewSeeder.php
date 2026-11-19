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
            ['Nurul Afifah Azzahra', 'General Manager', 'nurul-afifah-azzahra.webp'],
            ['Fitria Attira', 'Vice General Manager', 'fitria-attira.webp'],
            ['Vitroh Maviroh', 'Sekretaris', 'vitroh-maviroh.webp'],
            ['Syavirni Dwi Amalia', 'Sekretaris', 'syavirni-dwi-amalia.webp'],
            ['Shafa Dhiya Raihanah', 'Bendahara', 'shafa-dhiya-raihanah.webp'],
            ['Noval Rasyah Kurnia Putra', 'Bendahara', 'noval-rasyah-kurnia-putra.webp'],
            ['Allea De Chaelyn Tomo Lawa', 'Head of Podcast', 'allea-de-chaelyn-tomo-lawa.webp'],
            ['Ivanda Leona Putri Vabita', 'Announcer', 'ivanda-leona-putri-vabita.webp'],
            ['Ashfaa Atsqya Fernando', 'Announcer', 'ashfaa-atsqya-fernando.webp'],
            ['Belva Vanya Aulia', 'Announcer', 'belva-vanya-aulia.webp'],
            ['Kahlil Al Fathir', 'Announcer', 'kahlil-al-fathir.webp'],
            ['Kimberly Valencia', 'Announcer', 'kimberly-valencia.webp'],
            ['Lalia Alina', 'Announcer', 'lalia-alina.webp'],
            ['Nail Khairi Nur Hadi', 'Announcer', 'nail-khairi-nur-hadi.webp'],
            ['Nisrina Ihda Jamaliah', 'Announcer', 'nisrina-ihda-jamaliah.webp'],
            ['Priscilla Christiana Nahadin', 'Announcer', 'priscilla-christiana-nahadin.webp'],
            ['Rael Nathania Wibowo', 'Announcer', 'rael-nathania-wibowo.webp'],
            ['Saskia Oktaviani', 'Announcer', 'saskia-oktaviani.webp'],
            ['Sheriel Zelda Fitriani Cholid', 'Announcer', 'sheriel-zelda-fitriani-cholid.webp'],
            ['Shezza Nadyra Hergianto', 'Announcer', 'shezza-nadyra-hergianto.webp'],
            ['Shiren Rizkia Fahira', 'Announcer', 'shiren-rizkia-fahira.webp'],
            ['Yariza Nafisha Hamzah', 'Announcer', 'yariza-nafisha-hamzah.webp'],
            ['Salwa Syafira', 'Sound Operator', 'salwa-syafira.webp'],
            ['Ahmad Faris Firta Budiman', 'Sound Operator', 'ahmad-faris-firta-budiman.webp'],
            ['Audrey Geovanni Rionald Sinaga', 'Sound Operator', 'audrey-geovanni-rionald-sinaga.webp'],
            ['Bimo Septiansyah', 'Sound Operator', 'bimo-septiansyah.webp'],
            ['Dhini Aulya', 'Sound Operator', 'dhini-aulya.webp'],
            ['Fatyanheiza Putri Cahyadi', 'Sound Operator', 'fatyanheiza-putri-cahyadi.webp'],
            ['Meirina Kartika Rahmah', 'Sound Operator', 'meirina-kartika-rahmah.webp'],
            ['Raka Desnoel Sr', 'Sound Operator', 'raka-desnoel-sr.webp'],
            ['Yohanes Dimas Restu Putra', 'Sound Operator', 'yohanes-dimas-restu-putra.webp'],
            ['Zahra Dinda Rahma Dani', 'Sound Operator', 'zahra-dinda-rahma-dani.webp'],
            ['Satriani', 'Human Resource', 'satriani.webp'],
            ['Anggraini Dwina Putri', 'Human Resource', 'anggraini-dwina-putri.webp'],
            ['Azza Nabila Assaida', 'Human Resource', 'azza-nabila-assaida.webp'],
            ['Dewi Novita Sari', 'Human Resource', 'dewi-novita-sari.webp'],
            ['Feby Kumala', 'Human Resource', 'feby-kumala.webp'],
            ['Keisha Ann Aida Rahman', 'Human Resource', 'keisha-ann-aida-rahman.webp'],
            ['Yoga Nanda Saputra', 'Human Resource', 'yoga-nanda-saputra.webp'],
            ['Nayla Agis Maulida', 'Public Relation', 'nayla-agis-maulida.webp'],
            ['Putri Nabila', 'Partnership', 'putri-nabila.webp'],
            ['Azzahra Putri Anastasya', 'Partnership', 'azzahra-putri-anastasya.webp'],
            ['Calysta Jauza Faris', 'Partnership', 'calysta-jauza-faris.webp'],
            ['Divta Dewani Sunandar', 'Partnership', 'divta-dewani-sunandar.webp'],
            ['Shabrina Hilmi Amanu', 'Partnership', 'shabrina-hilmi-amanu.webp'],
            ['Najla Safira Balqis', 'Media Relation', 'najla-safira-balqis.webp'],
            ['Fahri Sohail Risay', 'Media Relation', 'fahri-sohail-risay.webp'],
            ['Suci Mulia Fitri', 'Media Relation', 'suci-mulia-fitri.webp'],
            ['Salwa Azzahra Zein', 'Media Relation', 'salwa-azzahra-zein.webp'],
            ['Zafira Jua Santika', 'Media Relation', 'zafira-jua-santika.webp'],
            ['Miftah Faradilah', 'Head of Socmed', 'miftah-faradilah.webp'],
            ['Syalloma Zidna Azzahra', 'Creative', 'syalloma-zidna-azzahra.webp'],
            ['Farah Syifa Annisa Ramadhani', 'Creative', 'farah-syifa-annisa-ramadhani.webp'],
            ['Ghistyanata Utomo', 'Creative', 'ghistyanata-utomo.webp'],
            ['Khaira Meizahra', 'Creative', 'khaira-meizahra.webp'],
            ['Nur Annissa', 'Creative', 'nur-annissa.webp'],
            ['Salsabila Putri Azhari', 'Creative', 'salsabila-putri-azhari.webp'],
            ['Sayla Ahmad Sumantri Ownie', 'Creative', 'sayla-ahmad-sumantri-ownie.webp'],
            ['Lidya Damayanti', 'Editor Video', 'lidya-damayanti.webp'],
            ['Abdul Zani', 'Editor Video', 'abdul-zani.webp'],
            ['Andhika Efran Nurdiansah', 'Editor Video', 'andhika-efran-nurdiansah.webp'],
            ['Farhan Athallah Shalfianas', 'Editor Video', 'farhan-athallah-shalfianas.webp'],
            ['Inas Nahiza', 'Editor Video', 'inas-nahiza.webp'],
            ['Keyro Haqqu Hemiyari', 'Editor Video', 'keyro-haqqu-hemiyari.webp'],
            ['Shofie Queen Hillul Latifah', 'Editor Video', 'shofie-queen-hillul-latifah.webp'],
            ['Chelsea Alya Mukhbita W.', 'Design & Publishing', 'chelsea-alya-mukhbita-w.webp'],
            ['Aldelvine Zahyan Annurin', 'Design & Publishing', 'aldelvine-zahyan-annurin.webp'],
            ['Anisya Nurfaidah', 'Design & Publishing', 'anisya-nurfaidah.webp'],
            ['Calvien Agustian Tacazily', 'Design & Publishing', 'calvien-agustian-tacazily.webp'],
            ['Devie Damayanti', 'Design & Publishing', 'devie-damayanti.webp'],
            ['Fathin Nayla Putri', 'Design & Publishing', 'fathin-nayla-putri.webp'],
            ['Luthfia Malika', 'Design & Publishing', 'luthfia-malika.webp'],
            ['Shevia Dwi Lestari', 'Design & Publishing', 'shevia-dwi-lestari.webp'],
            ['Zahra Aulia Nafizza', 'Design & Publishing', 'zahra-aulia-nafizza.webp'],
            ['Zhafira Ramadhani', 'Design & Publishing', 'zhafira-ramadhani.webp'],
            ['Adi Ezra Anthoni', 'Web Development', 'adi-ezra-anthoni.webp'],
            ['Bimo Rajendra Fassah', 'Web Development', 'bimo-rajendra-fassah.webp'],
            ['Rafdi Muliawan', 'Web Development', 'rafdi-muliawan.webp'],
            ['Rayya Mahira', 'Web Development', 'rayya-mahira.webp'],
        ];

        foreach ($anggota as [$nama, $jabatan, $foto]) {
            Crew::updateOrCreate(['name' => $nama], [
                'role' => $jabatan,
                'image' => 'crew/'.$foto,
            ]);
        }
    }
}
