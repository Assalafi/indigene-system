<?php

namespace Database\Seeders;

use App\Models\Lga;
use App\Models\State;
use Illuminate\Database\Seeder;

/**
 * SRD 42 - versioned baseline: 36 states + FCT and all 774 LGAs/Area Councils.
 * Source: INEC national baseline. Acceptance checks: 37 state records, 774 LGAs.
 */
class GeographySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->states() as $code => $state) {
            $model = State::create([
                'code' => $code,
                'name' => $state['name'],
                'type' => $state['type'],
                'capital' => $state['capital'],
                'status' => 'active',
                'source_name' => 'INEC national baseline (seeded)',
                'source_reference' => 'INEC-Election-Project-Plan-2022',
                'effective_from' => now()->toDateString(),
            ]);

            foreach ($state['lgas'] as $lgaCode => $lgaName) {
                Lga::create([
                    'state_id' => $model->id,
                    'code' => $lgaCode,
                    'name' => $lgaName,
                    'type' => $state['type'] === 'fct' ? 'area_council' : 'lga',
                    'status' => 'active',
                    'source_name' => 'INEC national baseline (seeded)',
                    'source_reference' => 'INEC-Election-Project-Plan-2022',
                    'effective_from' => now()->toDateString(),
                ]);
            }
        }
    }

    private function states(): array
    {
        return [
            'NG-AB' => [
                'name' => 'Abia', 'type' => 'state', 'capital' => 'Umuahia',
                'lgas' => [
                    'AB-01' => 'Aba North', 'AB-02' => 'Aba South', 'AB-03' => 'Arochukwu',
                    'AB-04' => 'Bende', 'AB-05' => 'Ikwuano', 'AB-06' => 'Isiala Ngwa North',
                    'AB-07' => 'Isiala Ngwa South', 'AB-08' => 'Isuikwuato', 'AB-09' => 'Obi Ngwa',
                    'AB-10' => 'Ohafia', 'AB-11' => 'Osisioma', 'AB-12' => 'Ugwunagbo',
                    'AB-13' => 'Ukwa East', 'AB-14' => 'Ukwa West', 'AB-15' => 'Umuahia North',
                    'AB-16' => 'Umuahia South', 'AB-17' => 'Umu Nneochi',
                ],
            ],
            'NG-AD' => [
                'name' => 'Adamawa', 'type' => 'state', 'capital' => 'Yola',
                'lgas' => [
                    'AD-01' => 'Demsa', 'AD-02' => 'Fufore', 'AD-03' => 'Ganye', 'AD-04' => 'Gayuk',
                    'AD-05' => 'Gombi', 'AD-06' => 'Grie', 'AD-07' => 'Hong', 'AD-08' => 'Jada',
                    'AD-09' => 'Lamurde', 'AD-10' => 'Madagali', 'AD-11' => 'Maiha', 'AD-12' => 'Mayo Belwa',
                    'AD-13' => 'Michika', 'AD-14' => 'Mubi North', 'AD-15' => 'Mubi South', 'AD-16' => 'Numan',
                    'AD-17' => 'Shelleng', 'AD-18' => 'Song', 'AD-19' => 'Toungo', 'AD-20' => 'Yola North',
                    'AD-21' => 'Yola South',
                ],
            ],
            'NG-AK' => [
                'name' => 'Akwa Ibom', 'type' => 'state', 'capital' => 'Uyo',
                'lgas' => [
                    'AK-01' => 'Abak', 'AK-02' => 'Eastern Obolo', 'AK-03' => 'Eket', 'AK-04' => 'Esit Eket',
                    'AK-05' => 'Essien Udim', 'AK-06' => 'Etim Ekpo', 'AK-07' => 'Etinan', 'AK-08' => 'Ibeno',
                    'AK-09' => 'Ibesikpo Asutan', 'AK-10' => 'Ibiono-Ibom', 'AK-11' => 'Ika', 'AK-12' => 'Ikono',
                    'AK-13' => 'Ikot Abasi', 'AK-14' => 'Ikot Ekpene', 'AK-15' => 'Ini', 'AK-16' => 'Itu',
                    'AK-17' => 'Mbo', 'AK-18' => 'Mkpat-Enin', 'AK-19' => 'Nsit-Atai', 'AK-20' => 'Nsit-Ibom',
                    'AK-21' => 'Nsit-Ubium', 'AK-22' => 'Obot Akara', 'AK-23' => 'Okobo', 'AK-24' => 'Onna',
                    'AK-25' => 'Oron', 'AK-26' => 'Oruk Anam', 'AK-27' => 'Udung-Uko', 'AK-28' => 'Ukanafun',
                    'AK-29' => 'Uruan', 'AK-30' => 'Urue-Offong/Oruko', 'AK-31' => 'Uyo',
                ],
            ],
            'NG-AN' => [
                'name' => 'Anambra', 'type' => 'state', 'capital' => 'Awka',
                'lgas' => [
                    'AN-01' => 'Aguata', 'AN-02' => 'Anambra East', 'AN-03' => 'Anambra West',
                    'AN-04' => 'Anaocha', 'AN-05' => 'Awka North', 'AN-06' => 'Awka South',
                    'AN-07' => 'Ayamelum', 'AN-08' => 'Dunukofia', 'AN-09' => 'Ekwusigo',
                    'AN-10' => 'Idemili North', 'AN-11' => 'Idemili South', 'AN-12' => 'Ihiala',
                    'AN-13' => 'Njikoka', 'AN-14' => 'Nnewi North', 'AN-15' => 'Nnewi South',
                    'AN-16' => 'Ogbaru', 'AN-17' => 'Onitsha North', 'AN-18' => 'Onitsha South',
                    'AN-19' => 'Orumba North', 'AN-20' => 'Orumba South', 'AN-21' => 'Oyi',
                ],
            ],
            'NG-BA' => [
                'name' => 'Bauchi', 'type' => 'state', 'capital' => 'Bauchi',
                'lgas' => [
                    'BA-01' => 'Alkaleri', 'BA-02' => 'Bauchi', 'BA-03' => 'Bogoro', 'BA-04' => 'Damban',
                    'BA-05' => 'Darazo', 'BA-06' => 'Dass', 'BA-07' => 'Gamawa', 'BA-08' => 'Ganjuwa',
                    'BA-09' => 'Giade', 'BA-10' => 'Itas/Gadau', 'BA-11' => 'Jama\'are', 'BA-12' => 'Katagum',
                    'BA-13' => 'Kirfi', 'BA-14' => 'Misau', 'BA-15' => 'Ningi', 'BA-16' => 'Shira',
                    'BA-17' => 'Tafawa Balewa', 'BA-18' => 'Toro', 'BA-19' => 'Warji', 'BA-20' => 'Zaki',
                ],
            ],
            'NG-BY' => [
                'name' => 'Bayelsa', 'type' => 'state', 'capital' => 'Yenagoa',
                'lgas' => [
                    'BY-01' => 'Brass', 'BY-02' => 'Ekeremor', 'BY-03' => 'Kolokuma/Opokuma',
                    'BY-04' => 'Nembe', 'BY-05' => 'Ogbia', 'BY-06' => 'Sagbama',
                    'BY-07' => 'Southern Ijaw', 'BY-08' => 'Yenagoa',
                ],
            ],
            'NG-BE' => [
                'name' => 'Benue', 'type' => 'state', 'capital' => 'Makurdi',
                'lgas' => [
                    'BE-01' => 'Ado', 'BE-02' => 'Agatu', 'BE-03' => 'Apa', 'BE-04' => 'Buruku',
                    'BE-05' => 'Gboko', 'BE-06' => 'Guma', 'BE-07' => 'Gwer East', 'BE-08' => 'Gwer West',
                    'BE-09' => 'Katsina-Ala', 'BE-10' => 'Konshisha', 'BE-11' => 'Kwande', 'BE-12' => 'Logo',
                    'BE-13' => 'Makurdi', 'BE-14' => 'Obi', 'BE-15' => 'Ogbadibo', 'BE-16' => 'Ohimini',
                    'BE-17' => 'Oju', 'BE-18' => 'Okpokwu', 'BE-19' => 'Otukpo', 'BE-20' => 'Tarka',
                    'BE-21' => 'Ukum', 'BE-22' => 'Ushongo', 'BE-23' => 'Vandeikya',
                ],
            ],
            'NG-BO' => [
                'name' => 'Borno', 'type' => 'state', 'capital' => 'Maiduguri',
                'lgas' => [
                    'BO-01' => 'Abadam', 'BO-02' => 'Askira/Uba', 'BO-03' => 'Bama', 'BO-04' => 'Bayo',
                    'BO-05' => 'Biu', 'BO-06' => 'Chibok', 'BO-07' => 'Damboa', 'BO-08' => 'Dikwa',
                    'BO-09' => 'Gubio', 'BO-10' => 'Guzamala', 'BO-11' => 'Gwoza', 'BO-12' => 'Hawul',
                    'BO-13' => 'Jere', 'BO-14' => 'Kaga', 'BO-15' => 'Kala/Balge', 'BO-16' => 'Konduga',
                    'BO-17' => 'Kukawa', 'BO-18' => 'Kwaya Kusar', 'BO-19' => 'Mafa', 'BO-20' => 'Magumeri',
                    'BO-21' => 'Maiduguri', 'BO-22' => 'Marte', 'BO-23' => 'Mobbar', 'BO-24' => 'Monguno',
                    'BO-25' => 'Ngala', 'BO-26' => 'Nganzai', 'BO-27' => 'Shani',
                ],
            ],
            'NG-CR' => [
                'name' => 'Cross River', 'type' => 'state', 'capital' => 'Calabar',
                'lgas' => [
                    'CR-01' => 'Abi', 'CR-02' => 'Akamkpa', 'CR-03' => 'Akpabuyo', 'CR-04' => 'Bakassi',
                    'CR-05' => 'Bekwarra', 'CR-06' => 'Biase', 'CR-07' => 'Boki', 'CR-08' => 'Calabar Municipal',
                    'CR-09' => 'Calabar South', 'CR-10' => 'Etung', 'CR-11' => 'Ikom', 'CR-12' => 'Obanliku',
                    'CR-13' => 'Obubra', 'CR-14' => 'Obudu', 'CR-15' => 'Odukpani', 'CR-16' => 'Ogoja',
                    'CR-17' => 'Yakurr', 'CR-18' => 'Yala',
                ],
            ],
            'NG-DE' => [
                'name' => 'Delta', 'type' => 'state', 'capital' => 'Asaba',
                'lgas' => [
                    'DE-01' => 'Aniocha North', 'DE-02' => 'Aniocha South', 'DE-03' => 'Bomadi',
                    'DE-04' => 'Burutu', 'DE-05' => 'Ethiope East', 'DE-06' => 'Ethiope West',
                    'DE-07' => 'Ika North East', 'DE-08' => 'Ika South', 'DE-09' => 'Isoko North',
                    'DE-10' => 'Isoko South', 'DE-11' => 'Ndokwa East', 'DE-12' => 'Ndokwa West',
                    'DE-13' => 'Okpe', 'DE-14' => 'Oshimili North', 'DE-15' => 'Oshimili South',
                    'DE-16' => 'Patani', 'DE-17' => 'Sapele', 'DE-18' => 'Udu', 'DE-19' => 'Ughelli North',
                    'DE-20' => 'Ughelli South', 'DE-21' => 'Ukwuani', 'DE-22' => 'Uvwie',
                    'DE-23' => 'Warri North', 'DE-24' => 'Warri South', 'DE-25' => 'Warri South West',
                ],
            ],
            'NG-EB' => [
                'name' => 'Ebonyi', 'type' => 'state', 'capital' => 'Abakaliki',
                'lgas' => [
                    'EB-01' => 'Abakaliki', 'EB-02' => 'Afikpo North', 'EB-03' => 'Afikpo South',
                    'EB-04' => 'Ebonyi', 'EB-05' => 'Ezza North', 'EB-06' => 'Ezza South',
                    'EB-07' => 'Ikwo', 'EB-08' => 'Ishielu', 'EB-09' => 'Ivo', 'EB-10' => 'Izzi',
                    'EB-11' => 'Ohaozara', 'EB-12' => 'Ohaukwu', 'EB-13' => 'Onicha',
                ],
            ],
            'NG-ED' => [
                'name' => 'Edo', 'type' => 'state', 'capital' => 'Benin City',
                'lgas' => [
                    'ED-01' => 'Akoko-Edo', 'ED-02' => 'Egor', 'ED-03' => 'Esan Central',
                    'ED-04' => 'Esan North-East', 'ED-05' => 'Esan South-East', 'ED-06' => 'Esan West',
                    'ED-07' => 'Etsako Central', 'ED-08' => 'Etsako East', 'ED-09' => 'Etsako West',
                    'ED-10' => 'Igueben', 'ED-11' => 'Ikpoba-Okha', 'ED-12' => 'Oredo',
                    'ED-13' => 'Orhionmwon', 'ED-14' => 'Ovia North-East', 'ED-15' => 'Ovia South-West',
                    'ED-16' => 'Owan East', 'ED-17' => 'Owan West', 'ED-18' => 'Uhunmwonde',
                ],
            ],
            'NG-EK' => [
                'name' => 'Ekiti', 'type' => 'state', 'capital' => 'Ado Ekiti',
                'lgas' => [
                    'EK-01' => 'Ado Ekiti', 'EK-02' => 'Efon', 'EK-03' => 'Ekiti East', 'EK-04' => 'Ekiti South-West',
                    'EK-05' => 'Ekiti West', 'EK-06' => 'Emure', 'EK-07' => 'Gbonyin', 'EK-08' => 'Ido Osi',
                    'EK-09' => 'Ijero', 'EK-10' => 'Ikere', 'EK-11' => 'Ikole', 'EK-12' => 'Ilejemeje',
                    'EK-13' => 'Irepodun/Ifelodun', 'EK-14' => 'Ise/Orun', 'EK-15' => 'Moba',
                    'EK-16' => 'Oye',
                ],
            ],
            'NG-EN' => [
                'name' => 'Enugu', 'type' => 'state', 'capital' => 'Enugu',
                'lgas' => [
                    'EN-01' => 'Aninri', 'EN-02' => 'Awgu', 'EN-03' => 'Enugu East', 'EN-04' => 'Enugu North',
                    'EN-05' => 'Enugu South', 'EN-06' => 'Ezeagu', 'EN-07' => 'Igbo Etiti',
                    'EN-08' => 'Igbo Eze North', 'EN-09' => 'Igbo Eze South', 'EN-10' => 'Isi Uzo',
                    'EN-11' => 'Nkanu East', 'EN-12' => 'Nkanu West', 'EN-13' => 'Nsukka',
                    'EN-14' => 'Oji River', 'EN-15' => 'Udenu', 'EN-16' => 'Udi', 'EN-17' => 'Uzo-Uwani',
                ],
            ],
            'NG-FC' => [
                'name' => 'Federal Capital Territory', 'type' => 'fct', 'capital' => 'Abuja',
                'lgas' => [
                    'FC-01' => 'Abaji', 'FC-02' => 'Bwari', 'FC-03' => 'Gwagwalada',
                    'FC-04' => 'Kuje', 'FC-05' => 'Kwali', 'FC-06' => 'Municipal Area Council',
                ],
            ],
            'NG-GO' => [
                'name' => 'Gombe', 'type' => 'state', 'capital' => 'Gombe',
                'lgas' => [
                    'GO-01' => 'Akko', 'GO-02' => 'Balanga', 'GO-03' => 'Billiri', 'GO-04' => 'Dukku',
                    'GO-05' => 'Funakaye', 'GO-06' => 'Gombe', 'GO-07' => 'Kaltungo', 'GO-08' => 'Kwami',
                    'GO-09' => 'Nafada', 'GO-10' => 'Shongom', 'GO-11' => 'Yamaltu/Deba',
                ],
            ],
            'NG-IM' => [
                'name' => 'Imo', 'type' => 'state', 'capital' => 'Owerri',
                'lgas' => [
                    'IM-01' => 'Aboh Mbaise', 'IM-02' => 'Ahiazu Mbaise', 'IM-03' => 'Ehime Mbano',
                    'IM-04' => 'Ezinihitte', 'IM-05' => 'Ideato North', 'IM-06' => 'Ideato South',
                    'IM-07' => 'Ihitte/Uboma', 'IM-08' => 'Ikeduru', 'IM-09' => 'Isiala Mbano',
                    'IM-10' => 'Isu', 'IM-11' => 'Mbaitoli', 'IM-12' => 'Ngor Okpala', 'IM-13' => 'Njaba',
                    'IM-14' => 'Nkwerre', 'IM-15' => 'Nwangele', 'IM-16' => 'Obowo', 'IM-17' => 'Oguta',
                    'IM-18' => 'Ohaji/Egbema', 'IM-19' => 'Okigwe', 'IM-20' => 'Orlu', 'IM-21' => 'Orsu',
                    'IM-22' => 'Oru East', 'IM-23' => 'Oru West', 'IM-24' => 'Owerri Municipal',
                    'IM-25' => 'Owerri North', 'IM-26' => 'Owerri West', 'IM-27' => 'Unuimo',
                ],
            ],
            'NG-JI' => [
                'name' => 'Jigawa', 'type' => 'state', 'capital' => 'Dutse',
                'lgas' => [
                    'JI-01' => 'Auyo', 'JI-02' => 'Babura', 'JI-03' => 'Biriniwa', 'JI-04' => 'Birnin Kudu',
                    'JI-05' => 'Buji', 'JI-06' => 'Dutse', 'JI-07' => 'Gagarawa', 'JI-08' => 'Garki',
                    'JI-09' => 'Gumel', 'JI-10' => 'Guri', 'JI-11' => 'Gwaram', 'JI-12' => 'Gwiwa',
                    'JI-13' => 'Hadejia', 'JI-14' => 'Jahun', 'JI-15' => 'Kafin Hausa', 'JI-16' => 'Kaugama',
                    'JI-17' => 'Kazaure', 'JI-18' => 'Kiri Kasama', 'JI-19' => 'Kiyawa', 'JI-20' => 'Maigatari',
                    'JI-21' => 'Malam Madori', 'JI-22' => 'Miga', 'JI-23' => 'Ringim', 'JI-24' => 'Roni',
                    'JI-25' => 'Sule Tankarkar', 'JI-26' => 'Taura', 'JI-27' => 'Yankwashi',
                ],
            ],
            'NG-KD' => [
                'name' => 'Kaduna', 'type' => 'state', 'capital' => 'Kaduna',
                'lgas' => [
                    'KD-01' => 'Birnin Gwari', 'KD-02' => 'Chikun', 'KD-03' => 'Giwa', 'KD-04' => 'Igabi',
                    'KD-05' => 'Ikara', 'KD-06' => 'Jaba', 'KD-07' => 'Jema\'a', 'KD-08' => 'Kachia',
                    'KD-09' => 'Kaduna North', 'KD-10' => 'Kaduna South', 'KD-11' => 'Kagarko',
                    'KD-12' => 'Kajuru', 'KD-13' => 'Kaura', 'KD-14' => 'Kauru', 'KD-15' => 'Kubau',
                    'KD-16' => 'Kudan', 'KD-17' => 'Lere', 'KD-18' => 'Makarfi', 'KD-19' => 'Sabon Gari',
                    'KD-20' => 'Sanga', 'KD-21' => 'Soba', 'KD-22' => 'Zangon Kataf', 'KD-23' => 'Zaria',
                ],
            ],
            'NG-KN' => [
                'name' => 'Kano', 'type' => 'state', 'capital' => 'Kano',
                'lgas' => [
                    'KN-01' => 'Ajingi', 'KN-02' => 'Albasu', 'KN-03' => 'Bagwai', 'KN-04' => 'Bebeji',
                    'KN-05' => 'Bichi', 'KN-06' => 'Bunkure', 'KN-07' => 'Dala', 'KN-08' => 'Dambatta',
                    'KN-09' => 'Dawakin Kudu', 'KN-10' => 'Dawakin Tofa', 'KN-11' => 'Doguwa',
                    'KN-12' => 'Fagge', 'KN-13' => 'Gabasawa', 'KN-14' => 'Garko', 'KN-15' => 'Garun Mallam',
                    'KN-16' => 'Gaya', 'KN-17' => 'Gezawa', 'KN-18' => 'Gwale', 'KN-19' => 'Gwarzo',
                    'KN-20' => 'Kabo', 'KN-21' => 'Kano Municipal', 'KN-22' => 'Karaye', 'KN-23' => 'Kibiya',
                    'KN-24' => 'Kiru', 'KN-25' => 'Kumbotso', 'KN-26' => 'Kunchi', 'KN-27' => 'Kura',
                    'KN-28' => 'Madobi', 'KN-29' => 'Makoda', 'KN-30' => 'Minjibir', 'KN-31' => 'Nasarawa',
                    'KN-32' => 'Rano', 'KN-33' => 'Rimin Gado', 'KN-34' => 'Rogo', 'KN-35' => 'Shanono',
                    'KN-36' => 'Sumaila', 'KN-37' => 'Takai', 'KN-38' => 'Tarauni', 'KN-39' => 'Tofa',
                    'KN-40' => 'Tsanyawa', 'KN-41' => 'Tudun Wada', 'KN-42' => 'Ungogo', 'KN-43' => 'Warawa',
                    'KN-44' => 'Wudil',
                ],
            ],
            'NG-KT' => [
                'name' => 'Katsina', 'type' => 'state', 'capital' => 'Katsina',
                'lgas' => [
                    'KT-01' => 'Bakori', 'KT-02' => 'Batagarawa', 'KT-03' => 'Batsari', 'KT-04' => 'Baure',
                    'KT-05' => 'Bindawa', 'KT-06' => 'Charanchi', 'KT-07' => 'Dandume', 'KT-08' => 'Danja',
                    'KT-09' => 'Dan Musa', 'KT-10' => 'Daura', 'KT-11' => 'Dutsi', 'KT-12' => 'Dutsin Ma',
                    'KT-13' => 'Faskari', 'KT-14' => 'Funtua', 'KT-15' => 'Ingawa', 'KT-16' => 'Jibia',
                    'KT-17' => 'Kafur', 'KT-18' => 'Kaita', 'KT-19' => 'Kankara', 'KT-20' => 'Kankia',
                    'KT-21' => 'Katsina', 'KT-22' => 'Kurfi', 'KT-23' => 'Kusada', 'KT-24' => 'Mai\'Adua',
                    'KT-25' => 'Malumfashi', 'KT-26' => 'Mani', 'KT-27' => 'Mashi', 'KT-28' => 'Matazu',
                    'KT-29' => 'Musawa', 'KT-30' => 'Rimi', 'KT-31' => 'Sabuwa', 'KT-32' => 'Safana',
                    'KT-33' => 'Sandamu', 'KT-34' => 'Zango',
                ],
            ],
            'NG-KE' => [
                'name' => 'Kebbi', 'type' => 'state', 'capital' => 'Birnin Kebbi',
                'lgas' => [
                    'KE-01' => 'Aleiro', 'KE-02' => 'Arewa Dandi', 'KE-03' => 'Argungu', 'KE-04' => 'Augie',
                    'KE-05' => 'Bagudo', 'KE-06' => 'Birnin Kebbi', 'KE-07' => 'Bunza', 'KE-08' => 'Dandi',
                    'KE-09' => 'Fakai', 'KE-10' => 'Gwandu', 'KE-11' => 'Jega', 'KE-12' => 'Kalgo',
                    'KE-13' => 'Koko/Besse', 'KE-14' => 'Maiyama', 'KE-15' => 'Ngaski', 'KE-16' => 'Sakaba',
                    'KE-17' => 'Shanga', 'KE-18' => 'Suru', 'KE-19' => 'Wasagu/Danko', 'KE-20' => 'Yauri',
                    'KE-21' => 'Zuru',
                ],
            ],
            'NG-KO' => [
                'name' => 'Kogi', 'type' => 'state', 'capital' => 'Lokoja',
                'lgas' => [
                    'KO-01' => 'Adavi', 'KO-02' => 'Ajaokuta', 'KO-03' => 'Ankpa', 'KO-04' => 'Bassa',
                    'KO-05' => 'Dekina', 'KO-06' => 'Ibaji', 'KO-07' => 'Idah', 'KO-08' => 'Igalamela Odolu',
                    'KO-09' => 'Ijumu', 'KO-10' => 'Kabba/Bunu', 'KO-11' => 'Kogi', 'KO-12' => 'Lokoja',
                    'KO-13' => 'Mopa Muro', 'KO-14' => 'Ofu', 'KO-15' => 'Ogori/Magongo', 'KO-16' => 'Okehi',
                    'KO-17' => 'Okene', 'KO-18' => 'Olamaboro', 'KO-19' => 'Omala', 'KO-20' => 'Yagba East',
                    'KO-21' => 'Yagba West',
                ],
            ],
            'NG-KW' => [
                'name' => 'Kwara', 'type' => 'state', 'capital' => 'Ilorin',
                'lgas' => [
                    'KW-01' => 'Asa', 'KW-02' => 'Baruten', 'KW-03' => 'Edu', 'KW-04' => 'Ekiti',
                    'KW-05' => 'Ifelodun', 'KW-06' => 'Ilorin East', 'KW-07' => 'Ilorin South',
                    'KW-08' => 'Ilorin West', 'KW-09' => 'Irepodun', 'KW-10' => 'Isin', 'KW-11' => 'Kaiama',
                    'KW-12' => 'Moro', 'KW-13' => 'Offa', 'KW-14' => 'Oke Ero', 'KW-15' => 'Oyun',
                    'KW-16' => 'Pategi',
                ],
            ],
            'NG-LA' => [
                'name' => 'Lagos', 'type' => 'state', 'capital' => 'Ikeja',
                'lgas' => [
                    'LA-01' => 'Agege', 'LA-02' => 'Ajeromi-Ifelodun', 'LA-03' => 'Alimosho',
                    'LA-04' => 'Amuwo-Odofin', 'LA-05' => 'Apapa', 'LA-06' => 'Badagry',
                    'LA-07' => 'Epe', 'LA-08' => 'Eti Osa', 'LA-09' => 'Ibeju-Lekki', 'LA-10' => 'Ifako-Ijaiye',
                    'LA-11' => 'Ikeja', 'LA-12' => 'Ikorodu', 'LA-13' => 'Kosofe',
                    'LA-14' => 'Lagos Island', 'LA-15' => 'Lagos Mainland', 'LA-16' => 'Mushin',
                    'LA-17' => 'Ojo', 'LA-18' => 'Oshodi-Isolo', 'LA-19' => 'Shomolu',
                    'LA-20' => 'Surulere',
                ],
            ],
            'NG-NA' => [
                'name' => 'Nasarawa', 'type' => 'state', 'capital' => 'Lafia',
                'lgas' => [
                    'NA-01' => 'Akwanga', 'NA-02' => 'Awe', 'NA-03' => 'Doma', 'NA-04' => 'Karu',
                    'NA-05' => 'Keana', 'NA-06' => 'Keffi', 'NA-07' => 'Kokona', 'NA-08' => 'Lafia',
                    'NA-09' => 'Nasarawa', 'NA-10' => 'Nasarawa Eggon', 'NA-11' => 'Obi', 'NA-12' => 'Toto',
                    'NA-13' => 'Wamba',
                ],
            ],
            'NG-NI' => [
                'name' => 'Niger', 'type' => 'state', 'capital' => 'Minna',
                'lgas' => [
                    'NI-01' => 'Agaie', 'NI-02' => 'Agwara', 'NI-03' => 'Bida', 'NI-04' => 'Borgu',
                    'NI-05' => 'Bosso', 'NI-06' => 'Chanchaga', 'NI-07' => 'Edati', 'NI-08' => 'Gbako',
                    'NI-09' => 'Gurara', 'NI-10' => 'Katcha', 'NI-11' => 'Kontagora', 'NI-12' => 'Lapai',
                    'NI-13' => 'Lavun', 'NI-14' => 'Magama', 'NI-15' => 'Mariga', 'NI-16' => 'Mashegu',
                    'NI-17' => 'Mokwa', 'NI-18' => 'Munya', 'NI-19' => 'Paikoro', 'NI-20' => 'Rafi',
                    'NI-21' => 'Rijau', 'NI-22' => 'Shiroro', 'NI-23' => 'Suleja', 'NI-24' => 'Tafa',
                    'NI-25' => 'Wushishi',
                ],
            ],
            'NG-OG' => [
                'name' => 'Ogun', 'type' => 'state', 'capital' => 'Abeokuta',
                'lgas' => [
                    'OG-01' => 'Abeokuta North', 'OG-02' => 'Abeokuta South', 'OG-03' => 'Ado-Odo/Ota',
                    'OG-04' => 'Egbado North', 'OG-05' => 'Egbado South', 'OG-06' => 'Ewekoro',
                    'OG-07' => 'Ifo', 'OG-08' => 'Ijebu East', 'OG-09' => 'Ijebu North',
                    'OG-10' => 'Ijebu North East', 'OG-11' => 'Ijebu Ode', 'OG-12' => 'Ikenne',
                    'OG-13' => 'Imeko Afon', 'OG-14' => 'Ipokia', 'OG-15' => 'Obafemi Owode',
                    'OG-16' => 'Odeda', 'OG-17' => 'Odogbolu', 'OG-18' => 'Ogun Waterside',
                    'OG-19' => 'Remo North', 'OG-20' => 'Sagamu',
                ],
            ],
            'NG-ON' => [
                'name' => 'Ondo', 'type' => 'state', 'capital' => 'Akure',
                'lgas' => [
                    'ON-01' => 'Akoko North-East', 'ON-02' => 'Akoko North-West', 'ON-03' => 'Akoko South-West',
                    'ON-04' => 'Akoko South-East', 'ON-05' => 'Akure North', 'ON-06' => 'Akure South',
                    'ON-07' => 'Ese Odo', 'ON-08' => 'Idanre', 'ON-09' => 'Ifedore', 'ON-10' => 'Ilaje',
                    'ON-11' => 'Ile Oluji/Okeigbo', 'ON-12' => 'Irele', 'ON-13' => 'Odigbo', 'ON-14' => 'Okitipupa',
                    'ON-15' => 'Ondo East', 'ON-16' => 'Ondo West', 'ON-17' => 'Ose', 'ON-18' => 'Owo',
                ],
            ],
            'NG-OS' => [
                'name' => 'Osun', 'type' => 'state', 'capital' => 'Osogbo',
                'lgas' => [
                    'OS-01' => 'Aiyedade', 'OS-02' => 'Aiyedire', 'OS-03' => 'Atakunmosa East',
                    'OS-04' => 'Atakunmosa West', 'OS-05' => 'Boluwaduro', 'OS-06' => 'Boripe',
                    'OS-07' => 'Ede North', 'OS-08' => 'Ede South', 'OS-09' => 'Egbedore',
                    'OS-10' => 'Ejigbo', 'OS-11' => 'Ife Central', 'OS-12' => 'Ife East',
                    'OS-13' => 'Ife North', 'OS-14' => 'Ife South', 'OS-15' => 'Ifedayo', 'OS-16' => 'Ifelodun',
                    'OS-17' => 'Ila', 'OS-18' => 'Ilesa East', 'OS-19' => 'Ilesa West', 'OS-20' => 'Irepodun',
                    'OS-21' => 'Irewole', 'OS-22' => 'Isokan', 'OS-23' => 'Iwo', 'OS-24' => 'Obokun',
                    'OS-25' => 'Odo Otin', 'OS-26' => 'Ola Oluwa', 'OS-27' => 'Olorunda', 'OS-28' => 'Oriade',
                    'OS-29' => 'Orolu', 'OS-30' => 'Osogbo',
                ],
            ],
            'NG-OY' => [
                'name' => 'Oyo', 'type' => 'state', 'capital' => 'Ibadan',
                'lgas' => [
                    'OY-01' => 'Afijio', 'OY-02' => 'Akinyele', 'OY-03' => 'Atiba', 'OY-04' => 'Atisbo',
                    'OY-05' => 'Egbeda', 'OY-06' => 'Ibadan North', 'OY-07' => 'Ibadan North-East',
                    'OY-08' => 'Ibadan North-West', 'OY-09' => 'Ibadan South-East', 'OY-10' => 'Ibadan South-West',
                    'OY-11' => 'Ibarapa Central', 'OY-12' => 'Ibarapa East', 'OY-13' => 'Ibarapa North',
                    'OY-14' => 'Ido', 'OY-15' => 'Irepo', 'OY-16' => 'Iseyin', 'OY-17' => 'Itesiwaju',
                    'OY-18' => 'Iwajowa', 'OY-19' => 'Kajola', 'OY-20' => 'Lagelu', 'OY-21' => 'Ogbomosho North',
                    'OY-22' => 'Ogbomosho South', 'OY-23' => 'Ogo Oluwa', 'OY-24' => 'Olorunsogo',
                    'OY-25' => 'Oluyole', 'OY-26' => 'Ona Ara', 'OY-27' => 'Orelope', 'OY-28' => 'Ori Ire',
                    'OY-29' => 'Oyo East', 'OY-30' => 'Oyo West', 'OY-31' => 'Saki East', 'OY-32' => 'Saki West',
                    'OY-33' => 'Surulere',
                ],
            ],
            'NG-PL' => [
                'name' => 'Plateau', 'type' => 'state', 'capital' => 'Jos',
                'lgas' => [
                    'PL-01' => 'Barkin Ladi', 'PL-02' => 'Bassa', 'PL-03' => 'Bokkos', 'PL-04' => 'Jos East',
                    'PL-05' => 'Jos North', 'PL-06' => 'Jos South', 'PL-07' => 'Kanam', 'PL-08' => 'Kanke',
                    'PL-09' => 'Langtang North', 'PL-10' => 'Langtang South', 'PL-11' => 'Mangu',
                    'PL-12' => 'Mikang', 'PL-13' => 'Pankshin', 'PL-14' => 'Qua\'an Pan', 'PL-15' => 'Riyom',
                    'PL-16' => 'Shendam', 'PL-17' => 'Wase',
                ],
            ],
            'NG-RI' => [
                'name' => 'Rivers', 'type' => 'state', 'capital' => 'Port Harcourt',
                'lgas' => [
                    'RI-01' => 'Abua/Odual', 'RI-02' => 'Ahoada East', 'RI-03' => 'Ahoada West',
                    'RI-04' => 'Akuku-Toru', 'RI-05' => 'Andoni', 'RI-06' => 'Asari-Toru', 'RI-07' => 'Bonny',
                    'RI-08' => 'Degema', 'RI-09' => 'Eleme', 'RI-10' => 'Emohua', 'RI-11' => 'Etche',
                    'RI-12' => 'Gokana', 'RI-13' => 'Ikwerre', 'RI-14' => 'Khana', 'RI-15' => 'Obio/Akpor',
                    'RI-16' => 'Ogba/Egbema/Ndoni', 'RI-17' => 'Ogu/Bolo', 'RI-18' => 'Okrika',
                    'RI-19' => 'Omuma', 'RI-20' => 'Opobo/Nkoro', 'RI-21' => 'Oyigbo',
                    'RI-22' => 'Port Harcourt', 'RI-23' => 'Tai',
                ],
            ],
            'NG-SO' => [
                'name' => 'Sokoto', 'type' => 'state', 'capital' => 'Sokoto',
                'lgas' => [
                    'SO-01' => 'Binji', 'SO-02' => 'Bodinga', 'SO-03' => 'Dange Shuni', 'SO-04' => 'Gada',
                    'SO-05' => 'Goronyo', 'SO-06' => 'Gudu', 'SO-07' => 'Gwadabawa', 'SO-08' => 'Illela',
                    'SO-09' => 'Isa', 'SO-10' => 'Kebbe', 'SO-11' => 'Kware', 'SO-12' => 'Rabah',
                    'SO-13' => 'Sabon Birni', 'SO-14' => 'Shagari', 'SO-15' => 'Silame', 'SO-16' => 'Sokoto North',
                    'SO-17' => 'Sokoto South', 'SO-18' => 'Tambuwal', 'SO-19' => 'Tangaza', 'SO-20' => 'Tureta',
                    'SO-21' => 'Wamako', 'SO-22' => 'Wurno', 'SO-23' => 'Yabo',
                ],
            ],
            'NG-TA' => [
                'name' => 'Taraba', 'type' => 'state', 'capital' => 'Jalingo',
                'lgas' => [
                    'TA-01' => 'Ardo Kola', 'TA-02' => 'Bali', 'TA-03' => 'Donga', 'TA-04' => 'Gashaka',
                    'TA-05' => 'Gassol', 'TA-06' => 'Ibi', 'TA-07' => 'Jalingo', 'TA-08' => 'Karim Lamido',
                    'TA-09' => 'Kumi', 'TA-10' => 'Lau', 'TA-11' => 'Sardauna', 'TA-12' => 'Takum',
                    'TA-13' => 'Ussa', 'TA-14' => 'Wukari', 'TA-15' => 'Yorro', 'TA-16' => 'Zing',
                ],
            ],
            'NG-YO' => [
                'name' => 'Yobe', 'type' => 'state', 'capital' => 'Damaturu',
                'lgas' => [
                    'YO-01' => 'Bade', 'YO-02' => 'Bursari', 'YO-03' => 'Damaturu', 'YO-04' => 'Fika',
                    'YO-05' => 'Fune', 'YO-06' => 'Geidam', 'YO-07' => 'Gujba', 'YO-08' => 'Gulani',
                    'YO-09' => 'Jakusko', 'YO-10' => 'Karasuwa', 'YO-11' => 'Machina', 'YO-12' => 'Nangere',
                    'YO-13' => 'Nguru', 'YO-14' => 'Potiskum', 'YO-15' => 'Tarmuwa', 'YO-16' => 'Yunusari',
                    'YO-17' => 'Yusufari',
                ],
            ],
            'NG-ZA' => [
                'name' => 'Zamfara', 'type' => 'state', 'capital' => 'Gusau',
                'lgas' => [
                    'ZA-01' => 'Anka', 'ZA-02' => 'Bakura', 'ZA-03' => 'Birnin Magaji/Kiyaw', 'ZA-04' => 'Bukkuyum',
                    'ZA-05' => 'Bungudu', 'ZA-06' => 'Gummi', 'ZA-07' => 'Gusau', 'ZA-08' => 'Kaura Namoda',
                    'ZA-09' => 'Maradun', 'ZA-10' => 'Maru', 'ZA-11' => 'Shinkafi', 'ZA-12' => 'Talata Mafara',
                    'ZA-13' => 'Chafe', 'ZA-14' => 'Zurmi',
                ],
            ],
        ];
    }
}
