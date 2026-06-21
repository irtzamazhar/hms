<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\IpdAdmission;
use App\Models\IpdTreatment;
use App\Models\LabBooking;
use App\Models\LabBookingItem;
use App\Models\LabTest;
use App\Models\LabTestCategory;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalaryPayment;
use App\Models\SalaryStructure;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Token;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $adminId = User::where('user_type', 'super_admin')->value('id') ?? 1;

        // ── 1. DOCTORS ────────────────────────────────────────
        $this->command->info('Seeding doctors...');
        $doctorData = [
            ['Dr. Ahmed Siddiqui',   'ahmed.siddiqui@hms.com',   1, 'MBBS, FCPS',    'General Physician',   800],
            ['Dr. Sara Khan',        'sara.khan@hms.com',         6, 'MBBS, MRCP',    'Cardiologist',        1500],
            ['Dr. Imran Malik',      'imran.malik@hms.com',       3, 'MBBS, FRCS',    'General Surgeon',     1200],
            ['Dr. Farah Naz',        'farah.naz@hms.com',         4, 'MBBS, FCPS-OG', 'Gynecologist',        1000],
            ['Dr. Usman Tariq',      'usman.tariq@hms.com',       5, 'MBBS, DCH',     'Pediatrician',        900],
            ['Dr. Nadia Rehman',     'nadia.rehman@hms.com',      11,'MBBS, FCPS',    'Neurologist',         1800],
            ['Dr. Zafar Iqbal',      'zafar.iqbal@hms.com',       7, 'MBBS, FRCS',    'Orthopedic Surgeon',  1300],
            ['Dr. Hina Baig',        'hina.baig@hms.com',         8, 'MBBS, DLO',     'ENT Specialist',      700],
        ];

        $lastDoctorNum  = Doctor::count();
        $lastEmpNum     = User::whereNotNull('employee_id')
            ->get()->map(fn ($u) => (int) ltrim(substr($u->employee_id, 4), '0') ?: 0)->max() ?? 0;

        $doctors = [];
        foreach ($doctorData as $i => [$name, $email, $deptId, $qual, $spec, $fee]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name'               => $name,
                'password'           => Hash::make('Doctor@123'),
                'user_type'          => 'doctor',
                'status'             => 'active',
                'employee_id'        => 'EMP-' . str_pad($lastEmpNum + $i + 1, 4, '0', STR_PAD_LEFT),
                'joining_date'       => now()->subMonths(rand(6, 36)),
                'email_verified_at'  => now(),
            ]);
            $user->syncRoles(['doctor']);

            $docNum = $lastDoctorNum + $i + 1;
            $doctor = Doctor::firstOrCreate(['user_id' => $user->id], [
                'doctor_id'        => 'DOC-' . str_pad($docNum, 4, '0', STR_PAD_LEFT),
                'department_id'    => $deptId,
                'qualification'    => $qual,
                'specialization'   => $spec,
                'consultation_fee' => $fee,
                'available_days'   => json_encode(['Monday','Tuesday','Wednesday','Thursday','Friday']),
                'available_from'   => '09:00',
                'available_to'     => '17:00',
                'status'           => 'active',
            ]);
            $doctors[] = $doctor;
        }
        // include existing doctor
        $doctors = array_merge($doctors, Doctor::whereNotIn('id', array_column($doctors, 'id'))->get()->all());

        // ── 2. STAFF ──────────────────────────────────────────
        $this->command->info('Seeding staff...');
        $staffData = [
            ['Nurse Fatima Ali',    'fatima.ali@hms.com',    'nurse',          'Head Nurse',       1, 35000],
            ['Asim Raza',          'asim.raza@hms.com',     'receptionist',   'Senior Receptionist', 1, 30000],
            ['Bilal Hussain',      'bilal.hussain@hms.com', 'pharmacist',     'Senior Pharmacist',14, 40000],
            ['Sana Qureshi',       'sana.qureshi@hms.com',  'lab_technician', 'Lab Technician',   13, 38000],
            ['Tariq Mehmood',      'tariq.mehmood@hms.com', 'accountant',     'Finance Officer',   1, 45000],
            ['Nurse Aisha Malik',  'aisha.malik@hms.com',   'nurse',          'Staff Nurse',       5, 32000],
            ['Kashif Nawaz',       'kashif.nawaz@hms.com',  'pharmacist',     'Pharmacist',       14, 35000],
            ['Rabia Zahid',        'rabia.zahid@hms.com',   'lab_technician', 'Senior Lab Tech',  13, 42000],
        ];

        $staffIds = [];
        $existingStaffCount = Staff::max('id') ?? 0;
        // Re-read max after doctors were created
        $lastEmpNum = User::whereNotNull('employee_id')
            ->get()->map(fn ($u) => (int) ltrim(substr($u->employee_id, 4), '0') ?: 0)->max() ?? 0;

        foreach ($staffData as $i => [$name, $email, $type, $desig, $deptId, $salary]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name'               => $name,
                'password'           => Hash::make('Staff@123'),
                'user_type'          => $type,
                'status'             => 'active',
                'employee_id'        => 'EMP-' . str_pad($lastEmpNum + $i + 1, 4, '0', STR_PAD_LEFT),
                'joining_date'       => now()->subMonths(rand(3, 24)),
                'email_verified_at'  => now(),
            ]);
            $user->syncRoles([$type]);

            $staffNum = $existingStaffCount + $i + 1;
            $staff = Staff::firstOrCreate(['user_id' => $user->id], [
                'staff_id'      => 'STF-' . str_pad($staffNum, 4, '0', STR_PAD_LEFT),
                'department_id' => $deptId,
                'designation'   => $desig,
                'basic_salary'  => $salary,
                'status'        => 'active',
            ]);
            $staffIds[] = ['user_id' => $user->id, 'salary' => $salary];
        }

        // ── 3. WARDS & BEDS ───────────────────────────────────
        $this->command->info('Seeding wards & beds...');
        $wardData = [
            ['Male General Ward',    'MGW',  'general',    1, 20, 500],
            ['Female General Ward',  'FGW',  'general',    1, 20, 500],
            ['Private Ward A',       'PWA',  'private',    1,  8, 2000],
            ['Private Ward B',       'PWB',  'private',    2,  8, 2500],
            ['ICU',                  'ICU',  'icu',        15, 6, 5000],
            ['Maternity Ward',       'MAT',  'maternity',  4, 12, 1000],
            ['Pediatric Ward',       'PED',  'pediatric',  5, 10, 800],
            ['Emergency Ward',       'EMW',  'emergency',  2, 10, 1500],
        ];

        $wardIds = [];
        foreach ($wardData as [$name, $code, $type, $deptId, $beds, $charge]) {
            $ward = Ward::firstOrCreate(['code' => $code], [
                'name'          => $name,
                'ward_type'     => $type,
                'department_id' => $deptId,
                'total_beds'    => $beds,
                'status'        => 'active',
            ]);
            $wardIds[] = $ward->id;

            if ($ward->beds()->count() === 0) {
                for ($b = 1; $b <= $beds; $b++) {
                    $ward->beds()->create([
                        'bed_number'     => $code . '-' . str_pad($b, 2, '0', STR_PAD_LEFT),
                        'bed_type'       => in_array($type, ['icu']) ? 'electric' : 'standard',
                        'charge_per_day' => $charge,
                        'status'         => 'available',
                    ]);
                }
            }
        }

        // ── 4. PATIENTS ───────────────────────────────────────
        $this->command->info('Seeding patients...');
        $patientNames = [
            'Muhammad Ali Khan', 'Ayesha Bibi', 'Hassan Ahmed', 'Zainab Fatima', 'Abdul Rehman',
            'Sadia Perveen', 'Kamran Akhtar', 'Rukhsana Begum', 'Shahid Mehmood', 'Nazia Sultana',
            'Adnan Siddiqui', 'Parveen Akhtar', 'Umar Farooq', 'Samina Malik', 'Tariq Bashir',
            'Nasreen Bano', 'Zaheer Abbas', 'Maryam Hussain', 'Faisal Riaz', 'Gulshan Kausar',
            'Arshad Mehmood', 'Shaheen Akhtar', 'Sajid Raza', 'Amna Bibi', 'Waqas Ahmed',
            'Rizwana Khan', 'Naeem Ullah', 'Lubna Jabeen', 'Asad Zaman', 'Tahira Khatoon',
        ];

        $patientIds = [];
        $lastMR = Patient::max('id') ?? 0;
        foreach ($patientNames as $i => $name) {
            $mrNum = 'MR-' . str_pad($lastMR + $i + 1, 6, '0', STR_PAD_LEFT);
            $gender = $i % 3 === 0 ? 'male' : ($i % 3 === 1 ? 'female' : 'male');
            $patient = Patient::create([
                'mr_number'                 => $mrNum,
                'name'                      => $name,
                'phone'                     => '03' . rand(10, 49) . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT),
                'gender'                    => $gender,
                'age'                       => rand(5, 75),
                'blood_group'               => collect(['A+','A-','B+','B-','O+','O-','AB+','AB-'])->random(),
                'address'                   => rand(1, 99) . ' Block ' . chr(rand(65, 72)) . ', Lahore',
                'city'                      => collect(['Lahore','Karachi','Islamabad','Faisalabad','Rawalpindi'])->random(),
                'emergency_contact_name'    => 'Contact of ' . explode(' ', $name)[0],
                'emergency_contact_phone'   => '03' . rand(10, 49) . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT),
                'emergency_contact_relation'=> collect(['Father','Mother','Spouse','Sibling'])->random(),
                'status'                    => 'active',
                'registered_by'             => $adminId,
                'created_at'                => now()->subDays(rand(1, 180)),
            ]);
            $patientIds[] = $patient->id;
        }

        // ── 5. MEDICINE CATEGORIES & MEDICINES ───────────────
        $this->command->info('Seeding medicines...');
        $medCategories = [
            'Antibiotics', 'Analgesics', 'Antihypertensives', 'Antidiabetics',
            'Antihistamines', 'Vitamins & Supplements', 'Antacids', 'Cardiovascular',
        ];
        $catIds = [];
        foreach ($medCategories as $i => $cat) {
            $c = MedicineCategory::firstOrCreate(['name' => $cat], [
                'code'   => 'MC-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
            $catIds[] = $c->id;
        }

        $medicines = [
            ['Amoxicillin 500mg',    'Amoxicillin',    'Amoxil',     'tablet',   $catIds[0], 15,  35,  40,  0],
            ['Ciprofloxacin 500mg',  'Ciprofloxacin',  'Cipro',      'tablet',   $catIds[0], 18,  40,  50,  0],
            ['Paracetamol 500mg',    'Paracetamol',    'Panadol',    'tablet',   $catIds[1],  5,  10,  15,  0],
            ['Ibuprofen 400mg',      'Ibuprofen',      'Brufen',     'tablet',   $catIds[1],  8,  18,  25,  0],
            ['Diclofenac 50mg',      'Diclofenac',     'Voltaren',   'tablet',   $catIds[1], 10,  20,  30,  0],
            ['Amlodipine 5mg',       'Amlodipine',     'Norvasc',    'tablet',   $catIds[2], 20,  45,  60,  0],
            ['Losartan 50mg',        'Losartan',       'Cozaar',     'tablet',   $catIds[2], 25,  55,  70,  0],
            ['Metformin 500mg',      'Metformin',      'Glucophage', 'tablet',   $catIds[3], 12,  25,  35,  0],
            ['Glibenclamide 5mg',    'Glibenclamide',  'Daonil',     'tablet',   $catIds[3], 10,  22,  30,  0],
            ['Cetirizine 10mg',      'Cetirizine',     'Zyrtec',     'tablet',   $catIds[4], 15,  30,  40,  0],
            ['Loratadine 10mg',      'Loratadine',     'Claritin',   'tablet',   $catIds[4], 12,  28,  38,  0],
            ['Vitamin C 500mg',      'Ascorbic Acid',  'Cevit',      'tablet',   $catIds[5],  8,  15,  20,  0],
            ['Vitamin D3 1000IU',    'Cholecalciferol','D-Sol',      'capsule',  $catIds[5], 30,  60,  80,  0],
            ['Omeprazole 20mg',      'Omeprazole',     'Losec',      'capsule',  $catIds[6], 18,  38,  50,  0],
            ['Ranitidine 150mg',     'Ranitidine',     'Zantac',     'tablet',   $catIds[6], 10,  20,  28,  0],
            ['Aspirin 75mg',         'Aspirin',        'Disprin',    'tablet',   $catIds[7],  5,  12,  18,  0],
            ['Atorvastatin 20mg',    'Atorvastatin',   'Lipitor',    'tablet',   $catIds[7], 40,  80, 100,  0],
            ['Metronidazole 400mg',  'Metronidazole',  'Flagyl',     'tablet',   $catIds[0], 12,  25,  32,  0],
            ['Insulin Regular',      'Insulin',        'Actrapid',   'injection',$catIds[3], 80, 150, 180,  1],
            ['Salbutamol Inhaler',   'Salbutamol',     'Ventolin',   'inhaler',  $catIds[1], 60, 110, 140,  0],
        ];

        $medicineIds = [];
        foreach ($medicines as $i => [$name, $generic, $brand, $unit, $catId, $pp, $tp, $sp, $ctrl]) {
            $med = Medicine::create([
                'name'                  => $name,
                'generic_name'          => $generic,
                'brand'                 => $brand,
                'sku'                   => 'MED-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'unit'                  => $unit,
                'category_id'           => $catId,
                'purchase_price'        => $pp,
                'trade_price'           => $tp,
                'sale_price'            => $sp,
                'stock_quantity'        => rand(50, 500),
                'minimum_stock'         => 20,
                'is_controlled'         => $ctrl,
                'requires_prescription' => $ctrl,
                'status'                => 'active',
            ]);
            $medicineIds[] = $med->id;
        }

        // ── 6. SUPPLIERS ──────────────────────────────────────
        $this->command->info('Seeding suppliers...');
        $suppliersData = [
            ['MedPharma Distributors', 'MedPharma Pvt Ltd',   'info@medpharma.pk',    '042-35001234', 'Rizwan Ahmed',   'Lahore'],
            ['National Pharma',        'National Pharma Ltd',  'sales@nationalpharma.pk','021-34567890','Kamran Malik',  'Karachi'],
            ['Star Medical Supplies',  'Star Medical Pvt Ltd', 'info@starmedical.pk',  '051-2345678',  'Ayaz Hussain',  'Islamabad'],
            ['City Drug House',        'City Drug House',      'cdhpk@gmail.com',      '042-37654321', 'Naveed Iqbal',  'Lahore'],
        ];

        $supplierIds = [];
        foreach ($suppliersData as [$name, $company, $email, $phone, $contact, $city]) {
            $sup = Supplier::create([
                'name'            => $name,
                'company'         => $company,
                'email'           => $email,
                'phone'           => $phone,
                'contact_person'  => $contact,
                'city'            => $city,
                'opening_balance' => rand(0, 50000),
                'status'          => 'active',
            ]);
            $supplierIds[] = $sup->id;
        }

        // ── 7. PURCHASES ──────────────────────────────────────
        $this->command->info('Seeding purchases...');
        for ($p = 1; $p <= 15; $p++) {
            $selectedMeds = array_rand(array_flip($medicineIds), rand(3, 6));
            $subtotal     = 0;
            $purchaseDate = now()->subDays(rand(1, 90))->toDateString();

            $purchase = Purchase::create([
                'purchase_number' => 'PO-' . str_pad($p, 6, '0', STR_PAD_LEFT),
                'supplier_id'     => $supplierIds[array_rand($supplierIds)],
                'purchase_date'   => $purchaseDate,
                'invoice_number'  => 'INV-' . strtoupper(Str::random(6)),
                'subtotal'        => 0,
                'discount'        => 0,
                'tax'             => 0,
                'total_amount'    => 0,
                'paid_amount'     => 0,
                'due_amount'      => 0,
                'payment_method'  => collect(['cash','bank_transfer','cheque'])->random(),
                'payment_status'  => collect(['paid','partial','pending'])->random(),
                'status'          => 'received',
                'created_by'      => $adminId,
            ]);

            foreach ((array) $selectedMeds as $medId) {
                $med = Medicine::find($medId);
                $qty = rand(50, 200);
                $lineTotal = $qty * $med->purchase_price;
                $subtotal += $lineTotal;
                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'medicine_id'  => $medId,
                    'batch_number' => 'BT-' . strtoupper(Str::random(6)),
                    'expiry_date'  => now()->addMonths(rand(12, 36))->format('Y-m-d'),
                    'quantity'     => $qty,
                    'unit_price'   => $med->purchase_price,
                    'discount'     => 0,
                    'tax'          => 0,
                    'total_price'  => $lineTotal,
                    'sale_price'   => $med->sale_price,
                ]);
            }

            $tax = round($subtotal * 0.05, 2);
            $total = $subtotal + $tax;
            $paid  = $purchase->payment_status === 'paid' ? $total
                   : ($purchase->payment_status === 'partial' ? round($total * 0.5, 2) : 0);  // pending = 0

            $purchase->update([
                'subtotal'     => $subtotal,
                'tax'          => $tax,
                'total_amount' => $total,
                'paid_amount'  => $paid,
                'due_amount'   => $total - $paid,
            ]);
        }

        // ── 8. LAB TEST CATEGORIES & TESTS ───────────────────
        $this->command->info('Seeding lab tests...');
        $labCategoryData = [
            'Haematology'         => [
                ['CBC (Complete Blood Count)',   'CBC',  800, 'haematology', 'EDTA Blood', 'RBC: 4.5-5.5, WBC: 4-11, Plt: 150-400'],
                ['ESR',                          'ESR',  300, 'haematology', 'EDTA Blood', 'Male: 0-15, Female: 0-20 mm/hr'],
                ['Bleeding Time',                'BT',   200, 'haematology', 'Capillary Blood', '1-3 minutes'],
                ['Clotting Time',                'CT',   200, 'haematology', 'Capillary Blood', '5-11 minutes'],
            ],
            'Biochemistry'        => [
                ['Blood Sugar Fasting',          'BSF',  300, 'biochemistry','Blood Serum', '70-100 mg/dL'],
                ['Blood Sugar Random',           'BSR',  300, 'biochemistry','Blood Serum', 'Below 200 mg/dL'],
                ['HbA1c',                        'HBA1C',600, 'biochemistry','EDTA Blood',  'Below 5.7%'],
                ['Kidney Function Tests',        'KFT',  800, 'biochemistry','Blood Serum', 'Creatinine: 0.6-1.2'],
                ['Liver Function Tests',         'LFT', 1000, 'biochemistry','Blood Serum', 'ALT: 7-40 U/L'],
                ['Lipid Profile',                'LP',   900, 'biochemistry','Blood Serum', 'Total Cholesterol: <200 mg/dL'],
            ],
            'Microbiology'        => [
                ['Urine D/R',                    'UDR',  400, 'microbiology','Urine',       'No bacteria, 0-5 RBCs'],
                ['Urine C/S',                    'UCS',  800, 'microbiology','Mid-stream Urine','No growth'],
                ['Stool Exam',                   'SE',   400, 'microbiology','Stool',       'No ova/cysts'],
                ['Blood C/S',                    'BCS', 1200, 'microbiology','Blood',       'No growth'],
            ],
            'Serology'            => [
                ['Typhoid Test (Widal)',          'TYPH', 500, 'serology',   'Blood Serum', 'Negative'],
                ['Hepatitis B (HBsAg)',           'HBV',  600, 'serology',   'Blood Serum', 'Non-reactive'],
                ['Hepatitis C (Anti-HCV)',        'HCV',  600, 'serology',   'Blood Serum', 'Non-reactive'],
                ['HIV Screening',                 'HIV',  700, 'serology',   'Blood Serum', 'Non-reactive'],
                ['Dengue NS1 Antigen',            'DNS1', 800, 'serology',   'Blood Serum', 'Negative'],
            ],
            'Radiology & Imaging' => [
                ['X-Ray Chest PA',               'XRCP',  700, 'radiology', 'N/A', 'N/A'],
                ['X-Ray KUB',                    'XRKUB', 700, 'radiology', 'N/A', 'N/A'],
                ['Ultrasound Abdomen',           'USAB', 1500, 'radiology', 'N/A', 'N/A'],
                ['ECG (12-lead)',                 'ECG',   500, 'radiology', 'N/A', 'Normal sinus rhythm'],
            ],
        ];

        $labTestIds = [];
        $labCatIndex = 0;
        foreach ($labCategoryData as $catName => $tests) {
            $labCatIndex++;
            $cat = LabTestCategory::firstOrCreate(['name' => $catName], [
                'code'   => 'LTC-' . str_pad($labCatIndex, 2, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
            foreach ($tests as [$name, $code, $price, $type, $sample, $normal]) {
                $test = LabTest::firstOrCreate(['code' => $code], [
                    'category_id'      => $cat->id,
                    'name'             => $name,
                    'cost'             => $price,
                    'sample_type'      => $sample,
                    'normal_range'     => $normal,
                    'turnaround_hours' => rand(2, 24),
                    'status'           => 'active',
                ]);
                $labTestIds[] = $test->id;
            }
        }

        // ── 9. APPOINTMENTS ───────────────────────────────────
        $this->command->info('Seeding appointments...');
        $apptCount = 0;
        $apptIds   = [];
        foreach (range(1, 40) as $a) {
            $doctor  = $doctors[array_rand($doctors)];
            $patient = Patient::find($patientIds[array_rand($patientIds)]);
            $days    = rand(-30, 10);
            $hour    = rand(9, 16);
            $dt      = now()->addDays($days)->setHour($hour)->setMinute(0)->setSecond(0);
            $status  = $days < -1 ? collect(['completed','cancelled','no_show'])->random()
                     : ($days < 0 ? 'completed' : 'scheduled');

            $apptCount++;
            $appt = Appointment::create([
                'appointment_number'   => 'APT-' . str_pad($apptCount, 6, '0', STR_PAD_LEFT),
                'patient_id'           => $patient->id,
                'doctor_id'            => $doctor->id,
                'department_id'        => $doctor->department_id,
                'appointment_datetime' => $dt,
                'duration_minutes'     => 20,
                'type'                 => collect(['opd','follow_up','emergency'])->random(),
                'status'               => $status,
                'reason'               => collect(['Fever','Chest pain','Follow-up','Routine checkup','Headache','Back pain'])->random(),
                'fee'                  => $doctor->consultation_fee,
                'payment_status'       => $status === 'completed' ? 'paid' : 'pending',
                'created_by'           => $adminId,
            ]);
            $apptIds[] = $appt->id;
        }

        // ── 10. TOKENS ────────────────────────────────────────
        $this->command->info('Seeding tokens...');
        $shifts = ['morning', 'evening', 'night'];
        $tokenIds = [];
        $usedTokenCombos = [];
        foreach (range(1, 50) as $t) {
            $doctor   = $doctors[array_rand($doctors)];
            $patient  = Patient::find($patientIds[array_rand($patientIds)]);
            $status    = collect(['waiting','in_progress','completed','cancelled','no_show'])->random();

            // Generate unique (token_number, token_date, shift) combo
            $attempts = 0;
            do {
                $tokenDate   = now()->subDays(rand(0, 59))->toDateString();
                $shift       = $shifts[array_rand($shifts)];
                $tokenNumber = rand(1, 999);
                $comboKey    = "{$tokenNumber}-{$tokenDate}-{$shift}";
                $attempts++;
            } while (isset($usedTokenCombos[$comboKey]) && $attempts < 20);
            $usedTokenCombos[$comboKey] = true;

            $token = Token::create([
                'token_number'  => $tokenNumber,
                'token_date'    => $tokenDate,
                'patient_id'    => $patient->id,
                'doctor_id'     => $doctor->id,
                'department_id' => $doctor->department_id,
                'shift'         => $shift,
                'status'        => $status,
                'priority'      => collect(['normal','urgent','vip'])->random(),
                'created_by'    => $adminId,
            ]);
            $tokenIds[] = $token->id;
        }

        // ── 11. OPD VISITS ────────────────────────────────────
        $this->command->info('Seeding OPD visits...');
        $complaints  = ['Fever','Cough & Cold','Headache','Body ache','Chest pain','Stomach pain','Back pain','Joint pain','Skin rash','Eye irritation'];
        $diagnoses   = ['Viral Fever','Upper RTI','Hypertension','Diabetes Type 2','Gastritis','Migraine','Arthritis','Dermatitis','Allergic Rhinitis','Anemia'];

        for ($v = 1; $v <= 60; $v++) {
            $doctor   = $doctors[array_rand($doctors)];
            $patient  = Patient::find($patientIds[array_rand($patientIds)]);
            $visitDate = now()->subDays(rand(0, 60))->toDateString();
            $fee       = $doctor->consultation_fee;
            $discount  = rand(0, 1) ? rand(0, 100) : 0;
            $net       = $fee - $discount;
            $pStatus   = collect(['paid','pending','waived'])->random();

            OpdVisit::create([
                'visit_number'      => 'OPD-' . str_pad($v, 6, '0', STR_PAD_LEFT),
                'patient_id'        => $patient->id,
                'doctor_id'         => $doctor->id,
                'department_id'     => $doctor->department_id,
                'visit_date'        => $visitDate,
                'shift'             => $shifts[array_rand($shifts)],
                'chief_complaints'  => $complaints[array_rand($complaints)],
                'diagnosis'         => $diagnoses[array_rand($diagnoses)],
                'treatment'         => 'Prescribed medication and rest.',
                'vital_bp'          => rand(100, 140) . '/' . rand(60, 90),
                'vital_pulse'       => rand(60, 100),
                'vital_temperature' => number_format(rand(970, 1010) / 10, 1),
                'vital_weight'      => rand(45, 100),
                'vital_spo2'        => rand(94, 100),
                'consultation_fee'  => $fee,
                'discount'          => $discount,
                'net_amount'        => $net,
                'payment_status'    => $pStatus,
                'payment_method'    => collect(['cash','card','insurance'])->random(),
                'status'            => 'completed',
                'created_by'        => $adminId,
            ]);
        }

        // ── 12. IPD ADMISSIONS ────────────────────────────────
        $this->command->info('Seeding IPD admissions...');
        $allBeds = Bed::where('status', 'available')->get();
        $bedIdx  = 0;

        for ($adm = 1; $adm <= 20; $adm++) {
            $doctor   = $doctors[array_rand($doctors)];
            $patient  = Patient::find($patientIds[array_rand($patientIds)]);
            $wardId   = $wardIds[array_rand($wardIds)];
            $ward     = Ward::find($wardId);
            $bed      = $allBeds[$bedIdx % count($allBeds)] ?? null;
            $bedIdx++;

            $admDT    = now()->subDays(rand(5, 60));
            $dischDT  = rand(0, 1) ? $admDT->copy()->addDays(rand(2, 10)) : null;
            $status   = $dischDT ? 'discharged' : 'admitted';
            $days     = $dischDT ? $admDT->diffInDays($dischDT) : $admDT->diffInDays(now());
            $bedCharge = $bed?->charge_per_day ?? 500;
            $total    = ($bedCharge * $days) + ($doctor->consultation_fee * 2) + rand(500, 3000);
            $discount = rand(0, 1) ? rand(0, 500) : 0;
            $net      = $total - $discount;
            $paid     = $status === 'discharged' ? $net : rand(0, (int) $net);

            $admission = IpdAdmission::create([
                'admission_number'    => 'IPD-' . str_pad($adm, 6, '0', STR_PAD_LEFT),
                'patient_id'          => $patient->id,
                'doctor_id'           => $doctor->id,
                'department_id'       => $doctor->department_id,
                'ward_id'             => $wardId,
                'bed_id'              => $bed?->id,
                'admission_datetime'  => $admDT,
                'discharge_datetime'  => $dischDT,
                'admission_diagnosis' => $diagnoses[array_rand($diagnoses)],
                'discharge_diagnosis' => $status === 'discharged' ? $diagnoses[array_rand($diagnoses)] : null,
                'admission_type'      => collect(['emergency','elective','transfer'])->random(),
                'status'              => $status,
                'daily_bed_charge'    => $bedCharge,
                'doctor_charges'      => $doctor->consultation_fee * 2,
                'nursing_charges'     => rand(200, 800),
                'medicine_charges'    => rand(500, 3000),
                'other_charges'       => rand(0, 500),
                'total_amount'        => $total,
                'discount'            => $discount,
                'net_amount'          => $net,
                'paid_amount'         => $paid,
                'payment_status'      => $paid >= $net ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                'admitted_by'         => $adminId,
            ]);

            if ($bed) {
                $bed->update(['status' => $status === 'admitted' ? 'occupied' : 'available']);
            }

            // IPD treatments
            foreach (range(1, rand(2, 4)) as $tr) {
                IpdTreatment::create([
                    'ipd_admission_id'   => $admission->id,
                    'doctor_id'          => $doctor->id,
                    'treatment_datetime' => $admDT->copy()->addHours(rand(2, 48)),
                    'treatment_notes'    => collect(['IV fluids administered','Dressing changed','Vitals monitored','Medication given as prescribed','Patient responding well to treatment'])->random(),
                    'vital_bp'           => rand(100, 140) . '/' . rand(60, 90),
                    'vital_pulse'        => rand(60, 100),
                    'vital_temperature'  => number_format(rand(970, 1010) / 10, 1),
                    'vital_weight'       => rand(45, 100),
                    'vital_spo2'         => rand(94, 100),
                ]);
            }
        }

        // ── 13. PHARMACY SALES ────────────────────────────────
        $this->command->info('Seeding pharmacy sales...');
        for ($s = 1; $s <= 40; $s++) {
            $patient  = rand(0, 1) ? Patient::find($patientIds[array_rand($patientIds)]) : null;
            $saleDate = now()->subDays(rand(0, 60))->toDateString();
            $selectedMeds = (array) array_rand(array_flip($medicineIds), rand(2, 5));
            $subtotal = 0;

            $sale = Sale::create([
                'invoice_number'     => 'RX-' . str_pad($s, 6, '0', STR_PAD_LEFT),
                'patient_id'         => $patient?->id,
                'customer_name'      => $patient ? $patient->name : 'Walk-in Customer',
                'customer_phone'     => $patient?->phone ?? '03001234567',
                'sale_date'          => $saleDate,
                'shift'              => $shifts[array_rand($shifts)],
                'subtotal'           => 0,
                'discount_percentage'=> rand(0, 1) ? rand(0, 10) : 0,
                'discount_amount'    => 0,
                'tax_amount'         => 0,
                'total_amount'       => 0,
                'paid_amount'        => 0,
                'change_amount'      => 0,
                'payment_method'     => collect(['cash','card'])->random(),
                'payment_status'     => 'paid',
                'status'             => 'completed',
                'created_by'         => $adminId,
            ]);

            foreach ($selectedMeds as $medId) {
                $med = Medicine::find($medId);
                $qty = rand(1, 5);
                $lineTotal = $qty * $med->sale_price;
                $subtotal += $lineTotal;
                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'medicine_id'        => $medId,
                    'batch_id'           => null,
                    'quantity'           => $qty,
                    'unit_price'         => $med->sale_price,
                    'discount_percentage'=> 0,
                    'discount_amount'    => 0,
                    'tax_amount'         => 0,
                    'total_price'        => $lineTotal,
                    'profit'             => ($med->sale_price - $med->purchase_price) * $qty,
                ]);
            }

            $discPct = $sale->discount_percentage;
            $discAmt = round($subtotal * $discPct / 100, 2);
            $tax     = 0;
            $total   = $subtotal - $discAmt + $tax;
            $paid    = $total + rand(0, 100);

            $sale->update([
                'subtotal'       => $subtotal,
                'discount_amount'=> $discAmt,
                'total_amount'   => $total,
                'paid_amount'    => $paid,
                'change_amount'  => $paid - $total,
            ]);
        }

        // ── 14. LAB BOOKINGS ──────────────────────────────────
        $this->command->info('Seeding lab bookings...');
        for ($lb = 1; $lb <= 35; $lb++) {
            $patient  = Patient::find($patientIds[array_rand($patientIds)]);
            $doctor   = $doctors[array_rand($doctors)];
            $bookDate = now()->subDays(rand(0, 60))->toDateString();
            $selectedTests = (array) array_rand(array_flip($labTestIds), rand(1, 4));

            $total = 0;
            $booking = LabBooking::create([
                'booking_number' => 'LB-' . str_pad($lb, 6, '0', STR_PAD_LEFT),
                'patient_id'     => $patient->id,
                'doctor_id'      => $doctor->id,
                'booking_date'   => $bookDate,
                'shift'          => $shifts[array_rand($shifts)],
                'total_amount'   => 0,
                'discount'       => 0,
                'net_amount'     => 0,
                'paid_amount'    => 0,
                'payment_method' => collect(['cash','card','insurance'])->random(),
                'payment_status' => collect(['paid','pending'])->random(),
                'status'         => collect(['pending','sample_collected','processing','completed'])->random(),
                'created_by'     => $adminId,
            ]);

            foreach ((array) $selectedTests as $testId) {
                $test = LabTest::find($testId);
                $total += $test->cost;
                LabBookingItem::create([
                    'booking_id' => $booking->id,
                    'test_id'    => $testId,
                    'cost'       => $test->cost,
                    'discount'   => 0,
                    'net_cost'   => $test->cost,
                    'status'     => $booking->status === 'completed' ? 'completed' : 'pending',
                ]);
            }

            $paid = $booking->payment_status === 'paid' ? $total : 0;
            $booking->update([
                'total_amount' => $total,
                'net_amount'   => $total,
                'paid_amount'  => $paid,
            ]);
        }

        // ── 15. EXPENSES ──────────────────────────────────────
        $this->command->info('Seeding expenses...');
        $expCatIds = ExpenseCategory::pluck('id')->toArray();
        $expTitles = [
            'Electricity Bill', 'Water & Sewerage', 'Internet & Telephone', 'Office Supplies',
            'Cleaning Supplies', 'Laundry Services', 'Equipment Maintenance', 'Security Services',
            'Generator Fuel', 'Canteen Supplies', 'Printing & Stationery', 'Transport Expense',
        ];
        $modules = ['hospital', 'pharmacy', 'laboratory'];

        for ($e = 1; $e <= 30; $e++) {
            $expDate = now()->subDays(rand(0, 90))->toDateString();
            $amt     = rand(500, 25000);
            $status  = collect(['pending','approved','rejected'])->random();

            Expense::create([
                'expense_category_id' => $expCatIds[array_rand($expCatIds)],
                'title'               => $expTitles[array_rand($expTitles)],
                'amount'              => $amt,
                'expense_date'        => $expDate,
                'shift'               => $shifts[array_rand($shifts)],
                'reference_number'    => 'EXP-' . strtoupper(Str::random(6)),
                'payment_method'      => collect(['cash','bank_transfer','cheque'])->random(),
                'module'              => $modules[array_rand($modules)],
                'description'         => 'Monthly expense for hospital operations.',
                'status'              => $status,
                'created_by'          => $adminId,
                'approved_by'         => $status === 'approved' ? $adminId : null,
            ]);
        }

        // ── 16. SALARY STRUCTURES & PAYMENTS ─────────────────
        $this->command->info('Seeding salaries...');
        $allStaff = Staff::with('user')->get();
        foreach ($allStaff as $staffMember) {
            $basic = $staffMember->basic_salary ?: 30000;

            $structure = SalaryStructure::firstOrCreate(
                ['user_id' => $staffMember->user_id],
                [
                    'basic_salary'             => $basic,
                    'house_allowance'          => round($basic * 0.2),
                    'transport_allowance'      => round($basic * 0.1),
                    'medical_allowance'        => round($basic * 0.05),
                    'other_allowances'         => 0,
                    'income_tax_deduction'     => round($basic * 0.05),
                    'provident_fund_deduction' => round($basic * 0.05),
                    'other_deductions'         => 0,
                    'effective_from'           => now()->subYear()->toDateString(),
                    'is_current'               => true,
                ]
            );

            // 3 months of salary payments
            foreach (range(1, 3) as $monthOffset) {
                $month = now()->subMonths($monthOffset)->month;
                $year  = now()->subMonths($monthOffset)->year;
                $gross = $structure->basic_salary + $structure->house_allowance
                       + $structure->transport_allowance + $structure->medical_allowance;
                $deductions = $structure->income_tax_deduction + $structure->provident_fund_deduction;
                $net = $gross - $deductions;

                SalaryPayment::firstOrCreate(
                    ['user_id' => $staffMember->user_id, 'month' => $month, 'year' => $year],
                    [
                        'salary_structure_id' => $structure->id,
                        'basic_salary'        => $structure->basic_salary,
                        'total_allowances'    => $gross - $structure->basic_salary,
                        'total_deductions'    => $deductions,
                        'bonus'               => 0,
                        'overtime'            => 0,
                        'net_salary'          => $net,
                        'payment_date'        => now()->subMonths($monthOffset)->endOfMonth()->toDateString(),
                        'payment_method'      => 'bank_transfer',
                        'status'              => 'paid',
                        'generated_by'        => $adminId,
                        'paid_by'             => $adminId,
                    ]
                );
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->table(
            ['Table', 'Records'],
            collect([
                'doctors', 'staff', 'wards', 'beds', 'patients',
                'appointments', 'tokens', 'opd_visits', 'ipd_admissions',
                'medicines', 'suppliers', 'purchases', 'sales', 'lab_tests',
                'lab_bookings', 'expenses', 'salary_structures', 'salary_payments',
            ])->map(fn ($t) => [$t, DB::table($t)->count()])->toArray()
        );
    }
}
