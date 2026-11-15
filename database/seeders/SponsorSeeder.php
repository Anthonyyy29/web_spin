<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Isi logo sponsor dari repo lama. Berkasnya sudah ada di public/assets/uploads/sponsor.
     */
    public function run(): void
    {
        $logo = [
            '1745044026_661add56b38906dfd3e4.jpg',
            '1745044034_a4a409eac620826e1d23.jpg',
            '1745044063_e51633baf2a910a04ee0.jpg',
            '1745044105_49e460ed7e1146951a83.jpg',
            '1745044154_f0b493c54d72ca337f36.jpg',
            '1745044189_faca2f52c732b2bd1b3f.jpg',
            '1745044199_6ee3578ba088682dfddf.jpg',
            '1745044213_62ea72d1040c908053f3.jpg',
            '1745044228_251e4142017aed4245d8.jpg',
            '1745044245_c1b6322950d3074a7ecd.jpg',
            '1745044267_e685f98ac1c7809cae8b.jpg',
            '1745044276_8866a831e85425c65cf4.jpg',
            '1745044291_879e463a0967d7268697.jpg',
            '1745044299_47a7bdc84999f5819b5e.jpg',
            '1745044310_1c4ba6e12288a4699763.jpg',
            '1745044319_1ae42fab842fbd0e4966.jpg',
            '1745044329_7f95685b4a50ec3b1fb4.jpg',
            '1745044367_e920ea0dc9aec33982ec.jpg',
            '1745044374_2c07c65d249841630d9a.jpg',
            '1745044388_6d5f8b9a13c18bc07e7c.jpg',
            '1745044397_88cf3ec122906bc764ca.jpg',
            '1745044410_742651324ca98848acc1.jpg',
            '1745044442_1e62772094e130d9a8dd.jpg',
            '1745044499_00c6f7841482fbaaf79c.jpg',
            '1745044509_59ad3f9600ed88565c46.jpg',
            '1745044538_8798daa00974e19687aa.jpg',
            '1745044551_e535c7b82e2ea243f746.jpg',
            '1745044572_a9209db02ecee8f1dcbd.jpg',
            '1745044586_8f17b7e29635e32eaf23.jpg',
            '1745044624_9ecc4ac7bcc9043680c9.jpg',
            '1745044633_62089bf6c6518da8ffce.jpg',
            '1745044643_74d2498d40524d89dc34.jpg',
            '1745044652_5ed525c1bb151d2a8acc.jpg',
            '1746168810_e8caded8f8c7bee6d006.jpg',
        ];

        // Baris hasil seeder lama menyimpan nama berkas tanpa folder; hapus agar tidak ganda.
        Sponsor::whereIn('image', $logo)->delete();

        foreach ($logo as $image) {
            Sponsor::firstOrCreate(['image' => 'sponsor/'.$image]);
        }
    }
}
