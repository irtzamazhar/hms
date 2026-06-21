<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkDataSeeder extends Seeder
{
    private int $adminId;
    private array $doctorIds   = [];
    private array $patientIds  = [];
    private array $medicineIds = [];
    private array $labTestIds  = [];
    private array $deptIds     = [];
    private array $wardBeds    = [];
    private array $supplierIds = [];

    private array $maleNames   = ['Muhammad','Ahmad','Ali','Hassan','Hussain','Usman','Omar','Tariq','Bilal','Faisal','Shahid','Adnan','Kamran','Asad','Waqas','Imran','Zubair','Naeem','Khalid','Arshad','Junaid','Hamza','Sohail','Aamir','Rashid','Salman','Shoaib','Nadeem','Farhan','Zaheer','Rehan','Danish','Sajid','Waseem','Irfan','Rizwan','Yasir','Ahsan','Babar','Zeeshan'];
    private array $femaleNames = ['Ayesha','Fatima','Zainab','Sara','Maryam','Nadia','Hina','Rabia','Sana','Amna','Rizwana','Lubna','Samina','Parveen','Nasreen','Rukhsana','Shaheen','Gulshan','Tahira','Shazia','Nazia','Saima','Asma','Iqra','Bushra','Rubina','Mehnaz','Kousar','Sumaira','Farhat','Sadia','Naila','Anum','Huma','Maham'];
    private array $lastNames   = ['Khan','Ahmed','Ali','Malik','Hussain','Shah','Akhtar','Baig','Siddiqui','Qureshi','Ansari','Mirza','Chaudhry','Iqbal','Riaz','Nawaz','Mehmood','Tariq','Zaman','Raza','Abbas','Farooq','Rehman','Butt','Bhatti','Sheikh','Abbasi','Hashmi','Cheema','Virk','Gill','Bajwa','Rajput','Javed','Aziz'];
    private array $cities      = ['Lahore','Karachi','Islamabad','Faisalabad','Rawalpindi','Multan','Peshawar','Quetta','Sialkot','Gujranwala','Hyderabad','Bahawalpur','Sargodha','Sukkur'];
    private array $shifts      = ['morning','evening','night'];
    private array $bloodGroups = ['A+','A-','B+','B-','O+','O-','AB+','AB-'];

    // ─────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->adminId = DB::table('users')->where('user_type','super_admin')->value('id') ?? 1;

        $this->deptIds   = DB::table('departments')->pluck('id')->toArray();
        $this->doctorIds = DB::table('doctors')->pluck('id')->toArray();

        $this->command->info('► Doctors & Staff');
        $this->seedDoctors(42);
        $this->seedStaff(42);

        $this->command->info('► Wards & Patients');
        $this->seedWards();
        $this->seedPatients(970);

        $this->command->info('► Medicines & Suppliers');
        $this->seedMedicines(180);
        $this->seedSuppliers(16);

        $this->command->info('► Purchases (185)');
        $this->seedPurchases(185);

        $this->command->info('► Appointments (960)');
        $this->seedAppointments(960);

        $this->command->info('► Tokens (950)');
        $this->seedTokens(950);

        $this->command->info('► OPD Visits (940) + Prescriptions');
        $this->seedOpdVisits(940);

        $this->command->info('► IPD Admissions (480) + Treatments');
        $this->seedIpdAdmissions(480);

        $this->command->info('► Pharmacy Sales (960)');
        $this->seedSales(960);

        $this->command->info('► Lab Bookings (965)');
        $this->seedLabBookings(965);

        $this->command->info('► Expenses (970)');
        $this->seedExpenses(970);

        $this->command->info('► Salaries');
        $this->seedSalaries();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->printSummary();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function r(array $a): mixed { return $a[array_rand($a)]; }

    private function name(string $gender): string
    {
        $first = $gender === 'male'
            ? $this->maleNames[array_rand($this->maleNames)]
            : $this->femaleNames[array_rand($this->femaleNames)];
        return $first . ' ' . $this->lastNames[array_rand($this->lastNames)];
    }

    private function phone(): string
    {
        return '03' . str_pad(rand(0, 49), 2, '0', STR_PAD_LEFT) . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
    }

    private function cnic(): string
    {
        return rand(10000, 99999) . '-' . rand(1000000, 9999999) . '-' . rand(1, 9);
    }

    private function maxSeq(string $table, string $col, string $prefix): int
    {
        $len = strlen($prefix) + 1;
        return (int) (DB::table($table)
            ->selectRaw("MAX(CAST(SUBSTRING($col, $len) AS UNSIGNED)) as m")
            ->value('m') ?? 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DOCTORS  (42 new → ~50 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedDoctors(int $n): void
    {
        $specs = [
            ['General Physician','MBBS',800],['Cardiologist','MBBS,FCPS',1500],
            ['General Surgeon','MBBS,FRCS',1200],['Gynecologist','MBBS,FCPS-OG',1000],
            ['Pediatrician','MBBS,DCH',900],['Neurologist','MBBS,FCPS',1800],
            ['Orthopedic Surgeon','MBBS,FRCS',1300],['ENT Specialist','MBBS,DLO',700],
            ['Dermatologist','MBBS,DDVL',1100],['Pulmonologist','MBBS,FCPS',1400],
            ['Gastroenterologist','MBBS,FCPS',1600],['Urologist','MBBS,FRCS',1400],
            ['Ophthalmologist','MBBS,DOMS',1000],['Psychiatrist','MBBS,FCPS',1200],
            ['Endocrinologist','MBBS,FCPS',1500],['Nephrologist','MBBS,FCPS',1700],
            ['Rheumatologist','MBBS,FCPS',1300],['Hematologist','MBBS,FCPS',1400],
            ['Oncologist','MBBS,FCPS',2000],['Emergency Medicine','MBBS,FAEM',1000],
        ];

        $maxDoc = $this->maxSeq('doctors', 'doctor_id', 'DOC-');
        $maxEmp = $this->maxSeq('users', 'employee_id', 'EMP-');
        $days   = ['monday','tuesday','wednesday','thursday','friday'];

        for ($i = 0; $i < $n; $i++) {
            [$spec, $qual, $fee] = $specs[$i % count($specs)];
            $gender = rand(0,1) ? 'male' : 'female';
            $pname  = $this->name($gender);
            $email  = Str::slug($pname) . ($maxDoc + $i + 1) . '@hms-doc.com';

            $user = \App\Models\User::firstOrCreate(['email' => $email], [
                'name'              => 'Dr. ' . $pname,
                'password'          => Hash::make('Doctor@123'),
                'user_type'         => 'doctor',
                'status'            => 'active',
                'employee_id'       => 'EMP-' . str_pad($maxEmp + $i + 1, 4, '0', STR_PAD_LEFT),
                'joining_date'      => now()->subMonths(rand(1,48))->toDateString(),
                'email_verified_at' => now(),
            ]);
            $user->syncRoles(['doctor']);

            $doc = \App\Models\Doctor::firstOrCreate(['user_id' => $user->id], [
                'doctor_id'            => 'DOC-' . str_pad($maxDoc + $i + 1, 4, '0', STR_PAD_LEFT),
                'department_id'        => $this->r($this->deptIds),
                'qualification'        => $qual,
                'specialization'       => $spec,
                'consultation_fee'     => $fee,
                'available_days'       => json_encode($days),
                'available_from'       => '09:00',
                'available_to'         => '17:00',
                'appointment_duration' => $this->r([15,20,30]),
                'phone'                => $this->phone(),
                'status'               => 'active',
            ]);
            $this->doctorIds[] = $doc->id;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STAFF  (42 new → ~50 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedStaff(int $n): void
    {
        $types = [
            ['nurse','Staff Nurse',32000],['nurse','Senior Nurse',38000],
            ['nurse','Head Nurse',45000],['receptionist','Receptionist',28000],
            ['receptionist','Senior Receptionist',35000],['pharmacist','Pharmacist',38000],
            ['pharmacist','Senior Pharmacist',48000],['lab_technician','Lab Technician',35000],
            ['lab_technician','Senior Lab Tech',42000],['accountant','Accountant',40000],
            ['accountant','Finance Officer',50000],['nurse','Ward Nurse',30000],
        ];

        $maxStf = $this->maxSeq('staff', 'staff_id', 'STF-');
        $maxEmp = $this->maxSeq('users', 'employee_id', 'EMP-');

        for ($i = 0; $i < $n; $i++) {
            [$type, $desig, $salary] = $types[$i % count($types)];
            $gender = $type === 'nurse' ? 'female' : (rand(0,1) ? 'male' : 'female');
            $pname  = $this->name($gender);
            $email  = Str::slug($pname) . ($maxStf + $i + 1) . '@hms-staff.com';

            $user = \App\Models\User::firstOrCreate(['email' => $email], [
                'name'              => $pname,
                'password'          => Hash::make('Staff@123'),
                'user_type'         => $type,
                'status'            => 'active',
                'employee_id'       => 'EMP-' . str_pad($maxEmp + $i + 1, 4, '0', STR_PAD_LEFT),
                'joining_date'      => now()->subMonths(rand(1,60))->toDateString(),
                'email_verified_at' => now(),
            ]);
            $user->syncRoles([$type]);

            \App\Models\Staff::firstOrCreate(['user_id' => $user->id], [
                'staff_id'      => 'STF-' . str_pad($maxStf + $i + 1, 4, '0', STR_PAD_LEFT),
                'department_id' => $this->r($this->deptIds),
                'designation'   => $desig,
                'basic_salary'  => $salary,
                'phone'         => $this->phone(),
                'status'        => 'active',
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WARDS & BEDS
    // ─────────────────────────────────────────────────────────────────────────

    private function seedWards(): void
    {
        $newWards = [
            ['Surgical Ward A','SWA','surgical',15,800],
            ['Surgical Ward B','SWB','surgical',15,900],
            ['NICU','NIC','nicu',8,6000],
            ['Cardiac Ward','CRD','general',12,1200],
            ['Neuro Ward','NRW','general',10,1100],
            ['Ortho Ward','ORT','general',14,900],
            ['Male Surgical','MSW','surgical',16,850],
            ['Female Surgical','FSW','surgical',16,850],
        ];

        foreach ($newWards as [$wname, $code, $type, $beds, $charge]) {
            if (DB::table('wards')->where('code', $code)->exists()) continue;

            $wid = DB::table('wards')->insertGetId([
                'name'          => $wname,
                'code'          => $code,
                'ward_type'     => $type,
                'department_id' => $this->r($this->deptIds),
                'total_beds'    => $beds,
                'floor'         => rand(1,4),
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $bedRows = [];
            for ($b = 1; $b <= $beds; $b++) {
                $bedRows[] = [
                    'ward_id'        => $wid,
                    'bed_number'     => $code . '-' . str_pad($b, 2, '0', STR_PAD_LEFT),
                    'bed_type'       => $type === 'nicu' ? 'pediatric' : ($type === 'icu' ? 'electric' : 'standard'),
                    'charge_per_day' => $charge,
                    'status'         => 'available',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            DB::table('beds')->insert($bedRows);
        }

        $this->wardBeds = DB::table('beds')->select('id','ward_id','charge_per_day')->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PATIENTS  (970 new → ~1 000 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedPatients(int $n): void
    {
        $base = (int) DB::table('patients')->max('id') ?? 0;

        foreach (array_chunk(range(1, $n), 250) as $chunk) {
            $rows = [];
            foreach ($chunk as $i) {
                $idx    = $base + $i;
                $gender = rand(0,1) ? 'male' : 'female';
                $at     = now()->subDays(rand(1,730));
                $rows[] = [
                    'mr_number'                  => 'MR-' . str_pad($idx, 6, '0', STR_PAD_LEFT),
                    'name'                       => $this->name($gender),
                    'phone'                      => $this->phone(),
                    'gender'                     => $gender,
                    'age'                        => rand(1,85),
                    'blood_group'                => $this->r($this->bloodGroups),
                    'address'                    => rand(1,999) . ' Block ' . chr(rand(65,72)) . ', ' . $this->r($this->cities),
                    'city'                       => $this->r($this->cities),
                    'cnic'                       => rand(0,1) ? null : $this->cnic(),
                    'emergency_contact_name'     => $this->name($gender === 'male' ? 'female' : 'male'),
                    'emergency_contact_phone'    => $this->phone(),
                    'emergency_contact_relation' => $this->r(['Father','Mother','Spouse','Sibling','Child']),
                    'status'                     => 'active',
                    'registered_by'              => $this->adminId,
                    'created_at'                 => $at,
                    'updated_at'                 => $at,
                ];
            }
            DB::table('patients')->insert($rows);
            $base += count($chunk);
        }

        $this->patientIds = DB::table('patients')->pluck('id')->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MEDICINES  (180 new → ~200 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedMedicines(int $n): void
    {
        // Extra categories
        $extraCats = ['Antifungals','Antivirals','Corticosteroids','Antiepileptics','Antidepressants','Muscle Relaxants','Ophthalmic','Dermatological'];
        $catCount  = DB::table('medicine_categories')->count();
        foreach ($extraCats as $i => $cat) {
            if (!DB::table('medicine_categories')->where('name', $cat)->exists()) {
                DB::table('medicine_categories')->insert([
                    'name' => $cat, 'code' => 'MC-' . str_pad($catCount + $i + 1, 2, '0', STR_PAD_LEFT),
                    'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        $catIds = DB::table('medicine_categories')->pluck('id')->toArray();

        $generics = [
            'Azithromycin','Clarithromycin','Doxycycline','Clindamycin','Trimethoprim',
            'Tramadol','Codeine','Naproxen','Meloxicam','Ketorolac',
            'Atenolol','Bisoprolol','Carvedilol','Ramipril','Enalapril',
            'Valsartan','Telmisartan','Nifedipine','Diltiazem','Verapamil',
            'Glipizide','Pioglitazone','Sitagliptin','Empagliflozin','Insulin Glargine',
            'Fexofenadine','Montelukast','Levocetirizine','Hydroxyzine','Chlorphenamine',
            'Pantoprazole','Esomeprazole','Famotidine','Sucralfate','Domperidone',
            'Metoprolol','Clopidogrel','Warfarin','Enoxaparin','Rivaroxaban',
            'Fluconazole','Itraconazole','Clotrimazole','Terbinafine','Nystatin',
            'Acyclovir','Oseltamivir','Valacyclovir','Sofosbuvir','Entecavir',
            'Prednisolone','Hydrocortisone','Dexamethasone','Betamethasone','Triamcinolone',
            'Phenytoin','Valproate','Carbamazepine','Levetiracetam','Lamotrigine',
            'Sertraline','Fluoxetine','Escitalopram','Amitriptyline','Clonazepam',
            'Diazepam','Alprazolam','Zolpidem','Quetiapine','Risperidone',
            'Baclofen','Cyclobenzaprine','Tizanidine','Carisoprodol','Methocarbamol',
            'Timolol Eye Drops','Latanoprost','Ofloxacin Eye Drops','Tropicamide','Pilocarpine',
            'Mometasone Cream','Hydroquinone Cream','Tretinoin Cream','Calcipotriol','Permethrin',
            'Ferrous Sulphate','Folic Acid','Calcium Carbonate','Zinc Sulphate','Magnesium',
            'Methotrexate','Sulfasalazine','Hydroxychloroquine','Colchicine','Allopurinol',
            'Spironolactone','Furosemide','Hydrochlorothiazide','Indapamide','Torsemide',
            'Salbutamol Nebules','Ipratropium','Formoterol','Budesonide','Tiotropium',
            'Loperamide','ORS','Simethicone','Lactulose','Bisacodyl',
        ];
        $units   = ['tablet','capsule','syrup','injection','drops','cream','sachet','vial','inhaler'];
        $brands  = ['Pfizer','GSK','Novartis','Roche','AstraZeneca','Sanofi','Abbott','MSD','Bayer','Eli Lilly'];
        $base    = DB::table('medicines')->count();

        $rows = [];
        for ($i = 0; $i < $n; $i++) {
            $generic = $generics[$i % count($generics)];
            $pp      = rand(5,200);
            $sp      = round($pp * (rand(15,30) / 10), 2);
            $rows[]  = [
                'name'                  => $generic . ' ' . (rand(1,5)*100) . 'mg',
                'generic_name'          => $generic,
                'brand'                 => $this->r($brands),
                'category_id'           => $this->r($catIds),
                'sku'                   => 'MED-' . str_pad($base + $i + 1, 4, '0', STR_PAD_LEFT),
                'unit'                  => $this->r($units),
                'purchase_price'        => $pp,
                'trade_price'           => round($pp * 1.3, 2),
                'sale_price'            => $sp,
                'pack_size'             => $this->r([10,14,20,28,30]),
                'stock_quantity'        => rand(100,1500),
                'minimum_stock'         => 20,
                'is_controlled'         => rand(0,10) > 8 ? 1 : 0,
                'requires_prescription' => rand(0,10) > 6 ? 1 : 0,
                'status'                => 'active',
                'created_at'            => now(),
                'updated_at'            => now(),
            ];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('medicines')->insert($chunk);
        }
        $this->medicineIds = DB::table('medicines')->pluck('id')->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUPPLIERS  (16 new → ~20 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedSuppliers(int $n): void
    {
        $companies = ['MedDistrib','PharmaCo','HealthSupply','MedTrade','CurePharma','LifeSciences','DrugHouse','MedLine','NovaMed','AlphaPharma','BetaMed','GammaMed','PrimeMed','TrustMed','CareMed','SafeMed'];
        $rows = [];
        for ($i = 0; $i < $n; $i++) {
            $co  = ($companies[$i % count($companies)] ?? 'MedCo') . ' Pvt Ltd';
            $rows[] = [
                'name'            => $co,
                'company'         => $co,
                'email'           => 'info' . ($i+1) . '@' . Str::slug($co) . '.com',
                'phone'           => '0' . rand(21,99) . '-' . rand(1000000,9999999),
                'contact_person'  => $this->name(rand(0,1) ? 'male' : 'female'),
                'city'            => $this->r($this->cities),
                'opening_balance' => rand(0,100000),
                'status'          => 'active',
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }
        DB::table('suppliers')->insert($rows);
        $this->supplierIds = DB::table('suppliers')->pluck('id')->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PURCHASES  (185 new → ~200 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedPurchases(int $n): void
    {
        $base  = $this->maxSeq('purchases', 'purchase_number', 'PO-');
        $meds  = DB::table('medicines')->select('id','purchase_price','sale_price')->get()->keyBy('id');
        $medIdArr = $this->medicineIds;

        for ($p = 1; $p <= $n; $p++) {
            $payStatus = $this->r(['paid','paid','partial','pending']);
            $pDate     = now()->subDays(rand(1,365))->toDateString();
            $pid       = DB::table('purchases')->insertGetId([
                'purchase_number' => 'PO-' . str_pad($base + $p, 6, '0', STR_PAD_LEFT),
                'supplier_id'     => $this->r($this->supplierIds),
                'purchase_date'   => $pDate,
                'invoice_number'  => 'INV-' . strtoupper(Str::random(8)),
                'subtotal'        => 0, 'discount' => 0, 'tax' => 0,
                'total_amount'    => 0, 'paid_amount' => 0, 'due_amount' => 0,
                'payment_method'  => $this->r(['cash','bank_transfer','cheque']),
                'payment_status'  => $payStatus,
                'status'          => 'received',
                'created_by'      => $this->adminId,
                'created_at'      => now(), 'updated_at' => now(),
            ]);

            $selected = (array) array_rand(array_flip($medIdArr), rand(2,6));
            $subtotal = 0;
            $itemRows = [];
            foreach ($selected as $mid) {
                $med      = $meds[$mid] ?? null;
                if (!$med) continue;
                $qty      = rand(50,300);
                $line     = $qty * $med->purchase_price;
                $subtotal += $line;
                $itemRows[] = [
                    'purchase_id'  => $pid,
                    'medicine_id'  => $mid,
                    'batch_number' => 'BT-' . strtoupper(Str::random(6)),
                    'expiry_date'  => now()->addMonths(rand(12,48))->toDateString(),
                    'quantity'     => $qty,
                    'unit_price'   => $med->purchase_price,
                    'discount'     => 0, 'tax' => 0,
                    'total_price'  => $line,
                    'sale_price'   => $med->sale_price,
                    'created_at'   => now(), 'updated_at' => now(),
                ];
            }
            DB::table('purchase_items')->insert($itemRows);

            $tax   = round($subtotal * 0.05, 2);
            $total = $subtotal + $tax;
            $paid  = $payStatus === 'paid' ? $total : ($payStatus === 'partial' ? round($total * 0.5, 2) : 0);
            DB::table('purchases')->where('id', $pid)->update([
                'subtotal' => $subtotal, 'tax' => $tax, 'total_amount' => $total,
                'paid_amount' => $paid, 'due_amount' => $total - $paid,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // APPOINTMENTS  (960 new → ~1 000 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedAppointments(int $n): void
    {
        $base    = $this->maxSeq('appointments', 'appointment_number', 'APT-');
        $doctors = DB::table('doctors')->select('id','department_id','consultation_fee')->get()->keyBy('id');
        $types   = ['opd','follow_up','emergency','teleconsultation'];
        $reasons = ['Fever','Chest pain','Follow-up','Routine check','Headache','Back pain','Joint pain','Skin issue','Stomach ache','Breathing difficulty','Dizziness','Eye problem'];
        $rows    = [];

        for ($i = 1; $i <= $n; $i++) {
            $did    = $this->r($this->doctorIds);
            $doc    = $doctors[$did] ?? null;
            $days   = rand(-365, 30);
            $dt     = now()->addDays($days)->setHour(rand(8,17))->setMinute(0)->setSecond(0);
            $status = $days < -1 ? $this->r(['completed','completed','completed','cancelled','no_show'])
                    : ($days < 0 ? 'completed' : $this->r(['scheduled','confirmed']));
            $rows[] = [
                'appointment_number'   => 'APT-' . str_pad($base + $i, 6, '0', STR_PAD_LEFT),
                'patient_id'           => $this->r($this->patientIds),
                'doctor_id'            => $did,
                'department_id'        => $doc?->department_id,
                'appointment_datetime' => $dt,
                'duration_minutes'     => $this->r([15,20,30]),
                'type'                 => $this->r($types),
                'status'               => $status,
                'reason'               => $this->r($reasons),
                'fee'                  => $doc?->consultation_fee ?? 500,
                'payment_status'       => $status === 'completed' ? 'paid' : 'pending',
                'created_by'           => $this->adminId,
                'created_at'           => now(), 'updated_at' => now(),
            ];
        }
        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('appointments')->insert($chunk);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOKENS  (950 new → ~1 000 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedTokens(int $n): void
    {
        $doctors  = DB::table('doctors')->select('id','department_id')->get()->keyBy('id');
        $statuses = ['waiting','in_progress','completed','completed','completed','cancelled','no_show'];
        $prios    = ['normal','normal','normal','urgent','vip'];

        // Cache existing combos to avoid unique-constraint violations
        $used = DB::table('tokens')
            ->selectRaw("CONCAT(token_number,'-',token_date,'-',shift) as c")
            ->pluck('c')->flip()->toArray();

        $rows = [];
        $inserted = 0;
        $attempts = 0;

        while ($inserted < $n && $attempts < $n * 4) {
            $attempts++;
            $did   = $this->r($this->doctorIds);
            $shift = $this->r($this->shifts);
            $date  = now()->subDays(rand(0,730))->toDateString();
            $num   = rand(1,999);
            $key   = "{$num}-{$date}-{$shift}";
            if (isset($used[$key])) continue;
            $used[$key] = true;

            $rows[] = [
                'token_number'  => $num,
                'token_date'    => $date,
                'patient_id'    => $this->r($this->patientIds),
                'doctor_id'     => $did,
                'department_id' => $doctors[$did]?->department_id,
                'shift'         => $shift,
                'status'        => $this->r($statuses),
                'priority'      => $this->r($prios),
                'created_by'    => $this->adminId,
                'created_at'    => now(), 'updated_at' => now(),
            ];
            $inserted++;
        }
        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('tokens')->insert($chunk);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OPD VISITS + PRESCRIPTIONS  (940 new → ~1 000 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedOpdVisits(int $n): void
    {
        $base     = $this->maxSeq('opd_visits', 'visit_number', 'OPD-');
        $doctors  = DB::table('doctors')->select('id','department_id','consultation_fee')->get()->keyBy('id');
        $complaints = ['Fever','Cough & Cold','Headache','Body ache','Chest pain','Stomach pain','Back pain','Joint pain','Skin rash','Dizziness','Fatigue','Vomiting','Diarrhea','Hypertension','Diabetes follow-up','Breathing difficulty','Urinary complaint','Eye irritation'];
        $diagnoses  = ['Viral Fever','Upper RTI','Hypertension','Diabetes Type 2','Gastritis','Migraine','Arthritis','Dermatitis','Allergic Rhinitis','Anemia','Bronchitis','UTI','Pneumonia','GERD','Anxiety','Hypothyroidism','Dengue Fever','Malaria'];
        $pMethods   = ['cash','cash','cash','card','insurance'];
        $pStatuses  = ['paid','paid','paid','pending','waived'];

        $visitRows = [];
        for ($v = 1; $v <= $n; $v++) {
            $did  = $this->r($this->doctorIds);
            $doc  = $doctors[$did] ?? null;
            $fee  = $doc?->consultation_fee ?? 500;
            $disc = rand(0,1) ? rand(0,200) : 0;
            $visitRows[] = [
                'visit_number'      => 'OPD-' . str_pad($base + $v, 6, '0', STR_PAD_LEFT),
                'patient_id'        => $this->r($this->patientIds),
                'doctor_id'         => $did,
                'department_id'     => $doc?->department_id,
                'visit_date'        => now()->subDays(rand(0,730))->toDateString(),
                'shift'             => $this->r($this->shifts),
                'chief_complaints'  => $this->r($complaints),
                'diagnosis'         => $this->r($diagnoses),
                'treatment'         => 'Prescribed medication and rest advised.',
                'vital_bp'          => rand(100,160) . '/' . rand(60,100),
                'vital_pulse'       => rand(55,115),
                'vital_temperature' => number_format(rand(965,1020)/10,1),
                'vital_weight'      => rand(35,130),
                'vital_spo2'        => rand(91,100),
                'consultation_fee'  => $fee,
                'discount'          => $disc,
                'net_amount'        => $fee - $disc,
                'payment_status'    => $this->r($pStatuses),
                'payment_method'    => $this->r($pMethods),
                'status'            => 'completed',
                'created_by'        => $this->adminId,
                'created_at'        => now(), 'updated_at' => now(),
            ];
        }
        foreach (array_chunk($visitRows, 250) as $chunk) {
            DB::table('opd_visits')->insert($chunk);
        }

        // Prescriptions for ~80% of visits
        $visits = DB::table('opd_visits')->orderByDesc('id')->limit($n)
            ->select('id','patient_id','doctor_id','visit_date')
            ->get();

        $medData   = DB::table('medicines')->select('id','name')->get()->toArray();
        $medIdArr  = array_column($medData, 'id');
        $medNames  = array_combine($medIdArr, array_column($medData, 'name'));
        $dosages   = ['1-0-1','1-1-1','0-0-1','1-0-0','1-1-0'];
        $freqs     = ['Twice daily','Three times daily','Once daily','Four times daily','As needed'];
        $durs      = ['3 days','5 days','7 days','10 days','14 days','1 month'];
        $prescBase = DB::table('prescriptions')->max('id') ?? 0;

        $prescRows = [];
        foreach ($visits as $idx => $visit) {
            if ($idx % 5 === 0) continue; // skip 20%
            $prescRows[] = [
                'opd_visit_id'      => $visit->id,
                'patient_id'        => $visit->patient_id,
                'doctor_id'         => $visit->doctor_id,
                'prescription_date' => $visit->visit_date,
                'notes'             => null,
                'created_at'        => now(), 'updated_at' => now(),
            ];
        }
        foreach (array_chunk($prescRows, 250) as $chunk) {
            DB::table('prescriptions')->insert($chunk);
        }

        // Prescription items (2–4 meds each)
        $prescIds = DB::table('prescriptions')->orderByDesc('id')->limit(count($prescRows))->pluck('id')->toArray();
        $itemRows = [];
        foreach ($prescIds as $pid) {
            $count = rand(2,4);
            $picked = (array) array_rand(array_flip($medIdArr), min($count, count($medIdArr)));
            foreach ($picked as $mid) {
                $itemRows[] = [
                    'prescription_id' => $pid,
                    'medicine_name'   => $medNames[$mid] ?? 'Medicine',
                    'medicine_id'     => $mid,
                    'dosage'          => $this->r($dosages),
                    'frequency'       => $this->r($freqs),
                    'duration'        => $this->r($durs),
                    'route'           => 'oral',
                    'created_at'      => now(), 'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($itemRows, 400) as $chunk) {
            DB::table('prescription_items')->insert($chunk);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IPD ADMISSIONS + TREATMENTS  (480 new → ~500 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedIpdAdmissions(int $n): void
    {
        $base    = $this->maxSeq('ipd_admissions', 'admission_number', 'IPD-');
        $doctors = DB::table('doctors')->select('id','department_id','consultation_fee')->get()->keyBy('id');
        $diags   = ['Pneumonia','Acute MI','Appendicitis','Fracture','Dengue Fever','Typhoid','Sepsis','Stroke','Renal Failure','Post-op Recovery','Pre-term Labour','DKA','Hypertensive Crisis','Cholecystitis','Bowel Obstruction'];
        $admT    = ['emergency','elective','transfer'];

        if (empty($this->wardBeds)) {
            $this->wardBeds = DB::table('beds')->select('id','ward_id','charge_per_day')->get()->toArray();
        }

        $admRows = [];
        for ($adm = 1; $adm <= $n; $adm++) {
            $did    = $this->r($this->doctorIds);
            $doc    = $doctors[$did] ?? null;
            $bed    = $this->r($this->wardBeds);
            $admDT  = now()->subDays(rand(1,730));
            $dischDT = rand(0,3) !== 0 ? $admDT->copy()->addDays(rand(2,15)) : null;
            $status  = $dischDT ? 'discharged' : 'admitted';
            $days    = $dischDT ? $admDT->diffInDays($dischDT) : max(1, $admDT->diffInDays(now()));
            $bCharge = $bed->charge_per_day ?? 500;
            $docFee  = $doc?->consultation_fee ?? 500;
            $total   = round($bCharge*$days + $docFee*2 + rand(500,6000), 2);
            $disc    = rand(0,1) ? rand(0,1500) : 0;
            $net     = $total - $disc;
            $paid    = $status === 'discharged' ? $net : round($net * (rand(0,10)/10), 2);
            $admNum  = $base + $adm;

            $admRows[] = [
                'admission_number'    => 'IPD-' . str_pad($admNum, 6, '0', STR_PAD_LEFT),
                'patient_id'          => $this->r($this->patientIds),
                'doctor_id'           => $did,
                'department_id'       => $doc?->department_id,
                'ward_id'             => $bed->ward_id,
                'bed_id'              => $bed->id,
                'admission_datetime'  => $admDT,
                'discharge_datetime'  => $dischDT,
                'admission_diagnosis' => $this->r($diags),
                'discharge_diagnosis' => $dischDT ? $this->r($diags) : null,
                'admission_type'      => $this->r($admT),
                'status'              => $status,
                'daily_bed_charge'    => $bCharge,
                'doctor_charges'      => $docFee * 2,
                'nursing_charges'     => rand(200,1500),
                'medicine_charges'    => rand(500,6000),
                'lab_charges'         => rand(0,4000),
                'other_charges'       => rand(0,1500),
                'total_amount'        => $total,
                'discount'            => $disc,
                'net_amount'          => $net,
                'paid_amount'         => $paid,
                'payment_status'      => $paid >= $net ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                'admitted_by'         => $this->adminId,
                'created_at'          => $admDT, 'updated_at' => now(),
            ];
        }
        foreach (array_chunk($admRows, 200) as $chunk) {
            DB::table('ipd_admissions')->insert($chunk);
        }

        // Treatments: 2–5 per admission
        $admissions = DB::table('ipd_admissions')->orderByDesc('id')->limit($n)
            ->select('id','doctor_id','admission_datetime')->get();
        $notes = ['IV fluids given','Dressing changed','Vitals monitored','Medication administered','Patient improving','Physio started','Antibiotics course started','Post-op care','Oxygen therapy','Blood transfusion given'];
        $treatRows = [];
        foreach ($admissions as $adm) {
            $admDT = Carbon::parse($adm->admission_datetime);
            for ($t = 0; $t < rand(2,5); $t++) {
                $treatRows[] = [
                    'ipd_admission_id'   => $adm->id,
                    'doctor_id'          => $adm->doctor_id,
                    'treatment_datetime' => $admDT->copy()->addHours(rand(2,96)),
                    'treatment_notes'    => $this->r($notes),
                    'vital_bp'           => rand(100,160) . '/' . rand(60,100),
                    'vital_pulse'        => rand(55,120),
                    'vital_temperature'  => number_format(rand(960,1025)/10,1),
                    'vital_weight'       => rand(35,130),
                    'vital_spo2'         => rand(88,100),
                    'created_at'         => now(), 'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($treatRows, 400) as $chunk) {
            DB::table('ipd_treatments')->insert($chunk);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PHARMACY SALES  (960 new → ~1 000 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedSales(int $n): void
    {
        $base  = $this->maxSeq('sales', 'invoice_number', 'RX-');
        $meds  = DB::table('medicines')->select('id','sale_price','purchase_price')->get()->keyBy('id');
        $midArr = $this->medicineIds;

        for ($s = 1; $s <= $n; $s++) {
            $pid     = rand(0,1) ? $this->r($this->patientIds) : null;
            $discPct = rand(0,1) ? rand(0,10) : 0;
            $hour    = rand(8,22);
            $shift   = $hour < 14 ? 'morning' : ($hour < 20 ? 'evening' : 'night');

            $sid = DB::table('sales')->insertGetId([
                'invoice_number'      => 'RX-' . str_pad($base + $s, 6, '0', STR_PAD_LEFT),
                'patient_id'          => $pid,
                'sale_date'           => now()->subDays(rand(0,730))->toDateString(),
                'shift'               => $shift,
                'subtotal'            => 0, 'discount_percentage' => $discPct,
                'discount_amount'     => 0, 'tax_amount' => 0,
                'total_amount'        => 0, 'paid_amount' => 0, 'change_amount' => 0,
                'payment_method'      => $this->r(['cash','cash','cash','card']),
                'payment_status'      => 'paid',
                'customer_name'       => $pid ? null : 'Walk-in Customer',
                'status'              => 'completed',
                'created_by'          => $this->adminId,
                'created_at'          => now(), 'updated_at' => now(),
            ]);

            $picked   = (array) array_rand(array_flip($midArr), rand(1,5));
            $subtotal = 0;
            $itemRows = [];
            foreach ($picked as $mid) {
                $med  = $meds[$mid] ?? null; if (!$med) continue;
                $qty  = rand(1,10);
                $line = round($qty * $med->sale_price, 2);
                $subtotal += $line;
                $itemRows[] = [
                    'sale_id'             => $sid,
                    'medicine_id'         => $mid,
                    'quantity'            => $qty,
                    'unit_price'          => $med->sale_price,
                    'discount_percentage' => 0, 'discount_amount' => 0, 'tax_amount' => 0,
                    'total_price'         => $line,
                    'profit'              => round(($med->sale_price - $med->purchase_price) * $qty, 2),
                    'created_at'          => now(), 'updated_at' => now(),
                ];
            }
            DB::table('sale_items')->insert($itemRows);

            $discAmt = round($subtotal * $discPct / 100, 2);
            $total   = $subtotal - $discAmt;
            $paid    = $total + rand(0,50);
            DB::table('sales')->where('id', $sid)->update([
                'subtotal' => $subtotal, 'discount_amount' => $discAmt,
                'total_amount' => $total, 'paid_amount' => $paid, 'change_amount' => $paid - $total,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LAB BOOKINGS + ITEMS + REPORTS  (965 new → ~1 000 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedLabBookings(int $n): void
    {
        $base     = $this->maxSeq('lab_bookings', 'booking_number', 'LB-');
        $tests    = DB::table('lab_tests')->select('id','cost')->get()->keyBy('id');
        $testIdArr = $tests->keys()->toArray();
        $statuses = ['pending','sample_collected','processing','completed','completed','completed'];
        $flags    = ['normal','normal','normal','high','low'];

        for ($lb = 1; $lb <= $n; $lb++) {
            $bStatus  = $this->r($statuses);
            $payStatus = $bStatus === 'completed' ? 'paid' : $this->r(['pending','paid','partial']);
            $bid = DB::table('lab_bookings')->insertGetId([
                'booking_number' => 'LB-' . str_pad($base + $lb, 6, '0', STR_PAD_LEFT),
                'patient_id'     => $this->r($this->patientIds),
                'doctor_id'      => $this->r($this->doctorIds),
                'booking_date'   => now()->subDays(rand(0,730))->toDateString(),
                'shift'          => $this->r($this->shifts),
                'total_amount'   => 0, 'discount' => 0, 'net_amount' => 0, 'paid_amount' => 0,
                'payment_method' => $this->r(['cash','card','insurance']),
                'payment_status' => $payStatus,
                'status'         => $bStatus,
                'created_by'     => $this->adminId,
                'created_at'     => now(), 'updated_at' => now(),
            ]);

            $picked  = (array) array_rand(array_flip($testIdArr), rand(1,4));
            $total   = 0;
            $repRows = [];

            foreach ($picked as $tid) {
                $test  = $tests[$tid] ?? null; if (!$test) continue;
                $total += $test->cost;
                $iid = DB::table('lab_booking_items')->insertGetId([
                    'booking_id' => $bid, 'test_id' => $tid,
                    'cost'       => $test->cost, 'discount' => 0, 'net_cost' => $test->cost,
                    'status'     => $bStatus === 'completed' ? 'completed' : 'pending',
                    'created_at' => now(), 'updated_at' => now(),
                ]);

                if ($bStatus === 'completed') {
                    $patId = DB::table('lab_bookings')->where('id',$bid)->value('patient_id');
                    $repRows[] = [
                        'booking_id'          => $bid,
                        'booking_item_id'     => $iid,
                        'test_id'             => $tid,
                        'patient_id'          => $patId,
                        'result_value'        => rand(10,300),
                        'result_flag'         => $this->r($flags),
                        'sample_collected_at' => now()->subHours(rand(2,72)),
                        'result_entered_at'   => now()->subHours(rand(0,24)),
                        'status'              => 'verified',
                        'technician_id'       => $this->adminId,
                        'verified_by'         => $this->adminId,
                        'verified_at'         => now(),
                        'created_at'          => now(), 'updated_at' => now(),
                    ];
                }
            }

            $paid = $payStatus === 'paid' ? $total : ($payStatus === 'partial' ? round($total*0.5,2) : 0);
            DB::table('lab_bookings')->where('id',$bid)->update([
                'total_amount' => $total, 'net_amount' => $total, 'paid_amount' => $paid,
            ]);
            if (!empty($repRows)) {
                DB::table('lab_reports')->insert($repRows);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPENSES  (970 new → ~1 000 total)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedExpenses(int $n): void
    {
        $catIds  = DB::table('expense_categories')->pluck('id')->toArray();
        $titles  = ['Electricity Bill','Water & Sewerage','Internet & Telephone','Office Supplies','Cleaning Supplies','Laundry Services','Equipment Maintenance','Security Services','Generator Fuel','Canteen Supplies','Printing & Stationery','Transport Expense','Medical Gas','Linen & Bedding','Kitchen Supplies','Pest Control','Fire Safety Maintenance','Medical Waste Disposal','Ambulance Fuel','AC Maintenance','Plumbing Services','IT Equipment','Staff Uniform','Medical Equipment Rent','CCTV Maintenance'];
        $modules = ['hospital','pharmacy','laboratory','general'];
        $rows    = [];

        for ($e = 1; $e <= $n; $e++) {
            $status = $this->r(['approved','approved','approved','pending','rejected']);
            $rows[] = [
                'expense_category_id' => $this->r($catIds),
                'title'               => $this->r($titles),
                'amount'              => rand(200,60000),
                'expense_date'        => now()->subDays(rand(0,730))->toDateString(),
                'shift'               => $this->r($this->shifts),
                'reference_number'    => 'EXP-' . strtoupper(Str::random(6)),
                'payment_method'      => $this->r(['cash','bank_transfer','cheque','online']),
                'module'              => $this->r($modules),
                'description'         => 'Regular hospital operational expense.',
                'status'              => $status,
                'created_by'          => $this->adminId,
                'approved_by'         => $status === 'approved' ? $this->adminId : null,
                'created_at'          => now(), 'updated_at' => now(),
            ];
        }
        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('expenses')->insert($chunk);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SALARIES  (all staff → 12 months each)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedSalaries(): void
    {
        $allStaff = DB::table('staff')->select('user_id','basic_salary')->get();

        foreach ($allStaff as $s) {
            if (DB::table('salary_structures')->where('user_id', $s->user_id)->exists()) continue;

            $basic = $s->basic_salary ?: 30000;
            $sid   = DB::table('salary_structures')->insertGetId([
                'user_id'                  => $s->user_id,
                'basic_salary'             => $basic,
                'house_allowance'          => round($basic * 0.20),
                'transport_allowance'      => round($basic * 0.10),
                'medical_allowance'        => round($basic * 0.05),
                'other_allowances'         => 0,
                'income_tax_deduction'     => round($basic * 0.05),
                'provident_fund_deduction' => round($basic * 0.05),
                'other_deductions'         => 0,
                'effective_from'           => now()->subYears(2)->toDateString(),
                'is_current'               => 1,
                'created_at'               => now(), 'updated_at' => now(),
            ]);

            $gross      = $basic * 1.35;
            $deductions = $basic * 0.10;
            $net        = $gross - $deductions;
            $payRows    = [];

            for ($mo = 1; $mo <= 12; $mo++) {
                $dt    = now()->subMonths($mo);
                $month = $dt->month;
                $year  = $dt->year;
                if (DB::table('salary_payments')->where('user_id',$s->user_id)->where('month',$month)->where('year',$year)->exists()) continue;

                $payRows[] = [
                    'user_id'             => $s->user_id,
                    'salary_structure_id' => $sid,
                    'month'               => $month,
                    'year'                => $year,
                    'basic_salary'        => $basic,
                    'total_allowances'    => $gross - $basic,
                    'total_deductions'    => $deductions,
                    'bonus'               => $mo === 7 ? round($basic * 0.5) : 0,
                    'overtime'            => rand(0,1) ? rand(500,3000) : 0,
                    'net_salary'          => $net,
                    'payment_date'        => $dt->copy()->endOfMonth()->toDateString(),
                    'payment_method'      => 'bank_transfer',
                    'status'              => 'paid',
                    'generated_by'        => $this->adminId,
                    'paid_by'             => $this->adminId,
                    'created_at'          => now(), 'updated_at' => now(),
                ];
            }
            if (!empty($payRows)) {
                DB::table('salary_payments')->insert($payRows);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUMMARY
    // ─────────────────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $tables = [
            'patients','doctors','staff','wards','beds','rooms',
            'appointments','tokens','opd_visits','prescriptions','prescription_items',
            'ipd_admissions','ipd_treatments',
            'medicines','suppliers','purchases','purchase_items',
            'sales','sale_items',
            'lab_bookings','lab_booking_items','lab_reports',
            'expenses','salary_structures','salary_payments',
        ];
        $rows = array_map(fn($t) => [$t, number_format(DB::table($t)->count())], $tables);
        $this->command->table(['Table','Total Records'], $rows);
    }
}
