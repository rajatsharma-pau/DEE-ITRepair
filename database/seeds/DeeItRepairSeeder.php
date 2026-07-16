<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Role;
use App\College;
use App\Department;
use App\Directorate;
use App\Section;
use App\Designation;
use App\Country;
use App\State;
use App\City;
use App\Employee;
use App\EmployeeCharge;
use App\EmployeeServiceMovement;
use App\RepairCategory;
use App\ProblemTemplate;
use App\RepairRoutingRule;
use App\Vendor;
use App\Asset;
use App\AssetHistory;
use App\StoreItem;
use App\StoreStockMovement;

class DeeItRepairSeeder extends Seeder
{
    public function run()
    {
        $this->seedCollegesAndDepartments();
        $this->seedRoles();

        $directorate = Directorate::firstOrCreate(['short_name' => 'DEE'], ['name' => 'Directorate of Extension Education', 'is_active' => 1]);
        $deeCollegeId = College::where('name', 'Directorate of Extension Education')->value('id');
        $deeDepartmentId = Department::where('college_id', $deeCollegeId)->where('name', 'Communication Centre')->value('id') ?: Department::where('college_id', $deeCollegeId)->value('id');

        $sections = ['Administration','Store','IT Cell','Accounts','Plant Clinic','ATIC'];
        foreach ($sections as $s) {
            Section::firstOrCreate(['directorate_id' => $directorate->id, 'name' => $s], ['short_name' => $s, 'is_active' => 1]);
        }

        $designations = [
            ['Clerk','Administrative',1], ['Senior Clerk','Administrative',2], ['Assistant','Administrative',3], ['Senior Assistant','Administrative',4], ['Superintendent','Administrative',5],
            ['Assistant Professor','Scientific',10], ['Associate Professor','Scientific',11], ['Professor','Scientific',12],
            ['Programmer','Technical',20], ['Junior Technician','Technical',21], ['Driver','Supporting',30], ['Daily Wage Worker','Supporting',31],
        ];
        foreach ($designations as $d) {
            Designation::firstOrCreate(['name' => $d[0]], ['cadre' => $d[1], 'sort_order' => $d[2], 'is_active' => 1]);
        }

        $india = Country::firstOrCreate(['name' => 'India'], ['code' => 'IN', 'is_active' => 1]);
        $punjab = State::firstOrCreate(['country_id' => $india->id, 'name' => 'Punjab'], ['is_active' => 1]);
        City::firstOrCreate(['state_id' => $punjab->id, 'name' => 'Ludhiana'], ['district' => 'Ludhiana', 'pincode' => '141004', 'is_active' => 1]);

        $superuser = $this->makeEmployee('Super User PAU', '9876543200', 'superuser', 'Superintendent', 'Administration', $directorate->id);
        $admin = $this->makeEmployee('DEE Department Admin', '9876543210', 'department_admin', 'Superintendent', 'Administration', $directorate->id);
        $storekeeper = $this->makeEmployee('Store Keeper', '9876543211', 'storekeeper', 'Senior Assistant', 'Store', $directorate->id);
        // Example: same login can act as both Department Admin and Storekeeper.
        // Do not create another user with the same phone; attach multiple roles to same user.
        $storekeeper->user->syncRoleNames(['department_admin','storekeeper'], $deeCollegeId, $deeDepartmentId);
        $programmer = $this->makeEmployee('Programmer DEE', '9876543212', 'programmer', 'Programmer', 'IT Cell', $directorate->id);
        $employee = $this->makeEmployee('Test Employee', '9876543213', 'employee', 'Clerk', 'Administration', $directorate->id);
        $director = $this->makeEmployee('Director DEE', '9876543214', 'director', 'Professor', 'Administration', $directorate->id, 'Dr.');
        $storeIncharge = $this->makeEmployee('Store Incharge Officer', '9876543215', 'employee', 'Assistant Professor', 'Store', $directorate->id, 'Dr.');
        $d4Seat = $this->makeEmployee('D-4 Seat', '9876543216', 'd4_seat', 'Assistant', 'Administration', $directorate->id, 'Mr.');

        EmployeeCharge::firstOrCreate(['employee_id' => $director->id, 'charge_name' => 'Head'], ['from_date' => date('Y-m-d'), 'is_active' => 1]);
        EmployeeCharge::firstOrCreate(['employee_id' => $storeIncharge->id, 'charge_name' => 'Store Incharge'], ['from_date' => date('Y-m-d'), 'is_active' => 1]);

        $vendors = [
            ['KC Computers', 'Computer', '9811111111', 'Kochar Market, Ludhiana'],
            ['ABC Electrical Works', 'Electrical', '9822222222', 'Ludhiana'],
            ['Ludhiana Furniture House', 'Furniture', '9833333333', 'Ludhiana'],
            ['General Store Vendor', 'General', '9844444444', 'Ludhiana'],
        ];
        foreach ($vendors as $v) {
            Vendor::firstOrCreate(['name' => $v[0]], [
                'vendor_type' => $v[1],
                'mobile' => $v[2],
                'address' => $v[3],
                'is_active' => 1,
            ]);
        }

        EmployeeServiceMovement::firstOrCreate([
            'employee_id' => $employee->id,
            'movement_type' => 'Joining',
            'effective_date' => '2020-01-01'
        ], ['to_designation_id' => Designation::where('name','Clerk')->value('id'), 'remarks' => 'Initial joining', 'created_by' => $admin->user_id]);


        $assets = [
            ['DEE-PC-001','INV-PC-001','Computer','Desktop Computer','Dell','Optiplex','SN-PC-001','i5, 8GB RAM, 512GB SSD','With Employee',$employee->id,'Working'],
            ['DEE-PRN-001','INV-PRN-001','Printer','Laser Printer','HP','LaserJet','SN-PRN-001',null,'In Store',null,'Working'],
            ['DEE-UPS-001','INV-UPS-001','UPS','UPS 1 KVA','APC','Back-UPS','SN-UPS-001',null,'In Store',null,'Working'],
            ['DEE-CHR-001','INV-CHR-001','Chair','Office Chair','Godrej','Executive','SN-CHR-001',null,'With Employee',$storekeeper->id,'Working'],
        ];
        foreach ($assets as $a) {
            $asset = Asset::firstOrCreate(['asset_code' => $a[0]], [
                'directorate_id' => $directorate->id,
                'college_id' => $deeCollegeId,
                'department_id' => $deeDepartmentId,
                'inventory_no' => $a[1],
                'asset_category' => $a[2],
                'item_name' => $a[3],
                'make' => $a[4],
                'model' => $a[5],
                'serial_no' => $a[6],
                'configuration' => $a[7],
                'asset_state' => $a[8],
                'assigned_to_employee_id' => $a[9],
                'condition_status' => $a[10],
                'state_date' => date('Y-m-d'),
                'location' => 'DEE Store',
            ]);
            AssetHistory::firstOrCreate(['asset_id' => $asset->id, 'action_type' => 'Created'], [
                'employee_id' => $asset->assigned_to_employee_id,
                'action_by' => $admin->user_id,
                'to_state' => $asset->asset_state,
                'action_date' => date('Y-m-d'),
                'remarks' => 'Initial asset seed record',
            ]);
        }

        $storeItems = [
            ['PAPER-A4','A4 Paper','Stationery','Ream',25,5],
            ['PEN-BLUE','Blue Pen','Stationery','Nos',100,20],
            ['FILE-COVER','File Cover','Stationery','Nos',75,15],
            ['MARKER-BLACK','Black Marker','Stationery','Nos',20,5],
            ['ENVELOPE-A4','A4 Envelope','Stationery','Nos',50,10],
        ];
        foreach ($storeItems as $si) {
            $item = StoreItem::firstOrCreate(['item_code' => $si[0]], [
                'directorate_id' => $directorate->id,
                'college_id' => $deeCollegeId,
                'department_id' => $deeDepartmentId,
                'name' => $si[1],
                'category' => $si[2],
                'unit' => $si[3],
                'opening_stock' => $si[4],
                'current_stock' => $si[4],
                'reorder_level' => $si[5],
                'location' => 'Store',
                'is_active' => 1,
            ]);
            StoreStockMovement::firstOrCreate(['store_item_id' => $item->id, 'movement_type' => 'Opening'], [
                'quantity' => $si[4],
                'balance_after' => $si[4],
                'movement_date' => date('Y-m-d'),
                'created_by' => $admin->user_id,
                'remarks' => 'Opening stock seed record',
            ]);
        }

        $categories = [
            ['Computer','Computer Related','programmer'],
            ['Printer','Computer Related','programmer'],
            ['Internet / LAN','Computer Related','programmer'],
            ['Software','Computer Related','programmer'],
            ['UPS / Battery','Computer Related','programmer'],
            ['Furniture','Non Computer','store_incharge'],
            ['Electrical','Non Computer','store_incharge'],
            ['Stationery','General','storekeeper'],
            ['General Repair','General','storekeeper'],
        ];
        foreach ($categories as $c) {
            $cat = RepairCategory::firstOrCreate(['name' => $c[0]], ['item_group' => $c[1], 'default_handler' => $c[2], 'is_active' => 1]);
            if ($c[2] == 'programmer') {
                RepairRoutingRule::firstOrCreate(['repair_category_id' => $cat->id], ['handler_type' => 'role', 'handler_value' => 'programmer', 'requires_store_verification' => 1, 'requires_programmer_verification' => 1, 'is_active' => 1]);
            } elseif ($c[2] == 'store_incharge') {
                RepairRoutingRule::firstOrCreate(['repair_category_id' => $cat->id], ['handler_type' => 'charge', 'handler_value' => 'Store Incharge', 'requires_store_verification' => 1, 'requires_store_incharge_approval' => 1, 'is_active' => 1]);
            } else {
                RepairRoutingRule::firstOrCreate(['repair_category_id' => $cat->id], ['handler_type' => 'role', 'handler_value' => 'storekeeper', 'requires_store_verification' => 1, 'is_active' => 1]);
            }
        }

        $problemTemplates = [
            ['Computer','Computer not starting','Computer/System is not starting. Kindly check power supply, SMPS, motherboard and related parts.'],
            ['Computer','Computer running slow','Computer is running very slow and needs checking/service/upgradation if required.'],
            ['Computer','No display','Monitor/CPU is on but display is not coming. Kindly verify and repair.'],
            ['Printer','Printer not printing','Printer is not printing. Kindly check toner/cartridge, roller, paper jam and connectivity.'],
            ['Printer','Paper jam','Printer paper jam problem. Kindly check roller and service requirement.'],
            ['Printer','Toner/cartridge issue','Print quality is poor/faded. Toner/cartridge/service may be required.'],
            ['Internet / LAN','Internet not working','Internet/LAN is not working in the room. Kindly verify network point/cable/switch.'],
            ['Software','Software installation required','Required software/driver installation or configuration is needed.'],
            ['UPS / Battery','UPS backup not available','UPS battery backup is not available. Kindly check battery/UPS and estimate repair/replacement.'],
            ['Furniture','Chair repair required','Chair/table/furniture requires repair/replacement.'],
            ['Electrical','Electrical point not working','Electrical switch/socket/point is not working and needs repair.'],
            ['Stationery','Stationery required','Stationery/material is required from store as per office requirement.'],
            ['General Repair','Other repair/material requirement','Other repair/material requirement. Details are mentioned by employee.'],
        ];
        foreach ($problemTemplates as $pt) {
            $cat = RepairCategory::where('name', $pt[0])->first();
            ProblemTemplate::firstOrCreate(['title' => $pt[1], 'repair_category_id' => optional($cat)->id], [
                'description' => $pt[2],
                'item_group' => optional($cat)->item_group,
                'is_active' => 1,
            ]);
        }

    }



    private function seedRoles()
    {
        $roles = [
            ['superuser','Superuser','University-level full control'],
            ['admin','Admin','General admin'],
            ['college_admin','College Admin','College/directorate-level admin'],
            ['department_admin','Department Admin','Department/KVK/office-level admin'],
            ['employee','Employee','Can submit requests and view own records'],
            ['storekeeper','Storekeeper','Handles assets, estimates, store stock and indents'],
            ['programmer','Programmer','Technical verification for computer-related work'],
            ['d4_seat','D-4 Seat','Manual financial file tracking'],
            ['director','Director / Head','College/directorate viewing and approval role'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r[0]], ['display_name' => $r[1], 'description' => $r[2], 'is_active' => 1]);
        }
    }

    private function seedCollegesAndDepartments()
    {
        $now = date('Y-m-d H:i:s');
        $colleges = [
            [1, 'College of Agriculture'],
            [2, 'College of Basic Science'],
            [3, 'College of Agricultural Engineering and Technology'],
            [4, 'College of Community Science'],
            [5, 'College of Horticulture'],
            [6, 'Directorate of Extension Education'],
            [7, 'Directorate of Research'],
            [8, 'Dean Postgraduate Studies'],
            [9, 'Directorate of Students\' Welfare'],
            [10, 'Mohinder Singh Randhawa Library'],
            [11, 'College of Agriculture, Ballowal Saunkhri'],
            [12, 'Registrar Office'],
            [13, 'Laboratories'],
        ];
        foreach ($colleges as $c) {
            DB::table('colleges')->updateOrInsert(['id' => $c[0]], [
                'name' => $c[1],
                'short_name' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $departments = [
            [1,1,'Office of Dean College of Agriculture','Ludhiana'],
            [2,1,'Agronomy','Ludhiana'],
            [3,1,'Climate Change & Agricultural Meteorology','Ludhiana'],
            [4,1,'Entomology','Ludhiana'],
            [5,1,'Extension Education','Ludhiana'],
            [6,1,'Food Science and Technology','Ludhiana'],
            [7,1,'Plant Breeding and Genetics','Ludhiana'],
            [8,1,'Plant Pathology','Ludhiana'],
            [9,1,'School of Agricultural Biotechnology','Ludhiana'],
            [10,1,'School of Organic Farming','Ludhiana'],
            [11,1,'Soil Science','Ludhiana'],
            [12,2,'Agricultural Journalism, Language and Culture','Ludhiana'],
            [13,2,'Biochemistry','Ludhiana'],
            [14,2,'Botany','Ludhiana'],
            [15,2,'Chemistry','Ludhiana'],
            [16,2,'Economics and Sociology','Ludhiana'],
            [17,2,'Mathematics, Statistics and Physics','Ludhiana'],
            [18,2,'Microbiology','Ludhiana'],
            [19,2,'School of Business Studies','Ludhiana'],
            [20,2,'Zoology','Ludhiana'],
            [21,3,'Civil Engineering','Ludhiana'],
            [22,3,'Electrical Engineering & Information Technology','Ludhiana'],
            [23,3,'Farm Machinery and Power Engineering','Ludhiana'],
            [24,3,'Mechanical Engineering','Ludhiana'],
            [25,3,'Processing and Food Engineering','Ludhiana'],
            [26,3,'Renewable Energy Engineering','Ludhiana'],
            [27,3,'Soil and Water Engineering','Ludhiana'],
            [28,3,'Training and Placement Cell','Ludhiana'],
            [29,4,'Apparel & Textile Science','Ludhiana'],
            [30,4,'Extension Education & Communication Management','Ludhiana'],
            [31,4,'Food and Nutrition','Ludhiana'],
            [32,4,'Human Development and Family Studies','Ludhiana'],
            [33,4,'Resource Management & Consumer Science','Ludhiana'],
            [34,5,'Office of Dean College of Horticulture & Forestry','Ludhiana'],
            [35,5,'Floriculture and Landscaping','Ludhiana'],
            [36,5,'Forestry and Natural Resources','Ludhiana'],
            [37,5,'Fruit Science','Ludhiana'],
            [38,5,'Vegetable Science','Ludhiana'],
            [39,6,'Agricultural Technology Information Center-Plant Clinic','Ludhiana'],
            [40,6,'Communication Centre','Ludhiana'],
            [41,6,'Skill Development Centre','Ludhiana'],
            [42,6,'Krishi Vigyan Kendra - Amritsar','Amritsar'],
            [43,6,'Krishi Vigyan Kendra - Bahowal (Hoshiarpur)','Hoshiarpur'],
            [44,6,'Krishi Vigyan Kendra - Bathinda','Bathinda'],
            [45,6,'Krishi Vigyan Kendra - Budh Singh Wala, Moga','Moga'],
            [46,6,'Krishi Vigyan Kendra - Faridkot','Faridkot'],
            [47,6,'Krishi Vigyan Kendra - Fatehgarh Sahib','Fatehgarh Sahib'],
            [48,6,'Krishi Vigyan Kendra - Ferozepur','Ferozepur'],
            [49,6,'Krishi Vigyan Kendra - Goneana, Sri Muktsar Sahib','Sri Muktsar Sahib'],
            [50,6,'Krishi Vigyan Kendra - Gurdaspur','Gurdaspur'],
            [51,6,'Krishi Vigyan Kendra - Kapurthala','Kapurthala'],
            [52,6,'Krishi Vigyan Kendra - Kheri, Sangrur','Sangrur'],
            [53,6,'Krishi Vigyan Kendra - Langroya, Nawanshahar','Nawanshahar'],
            [54,6,'Krishi Vigyan Kendra - Mansa','Mansa'],
            [55,6,'Krishi Vigyan Kendra - Nurmahal, Jalandhar','Jalandhar'],
            [56,6,'Krishi Vigyan Kendra - Pathankot','Pathankot'],
            [57,6,'Krishi Vigyan Kendra - Patiala','Patiala'],
            [58,6,'Krishi Vigyan Kendra - Ropar','Ropar'],
            [59,6,'Krishi Vigyan Kendra - Samrala','Samrala'],
            [60,6,'Farm Advisory Service Scheme - Abohar/Fazilka','Fazilka'],
            [61,6,'Farm Advisory Service Scheme - Amritsar','Amritsar'],
            [62,6,'Farm Advisory Service Scheme - Bathinda','Bathinda'],
            [63,6,'Farm Advisory Service Scheme - Barnala','Barnala'],
            [64,6,'Farm Advisory Service Scheme - Chandigarh','Chandigarh'],
            [65,6,'Farm Advisory Service Scheme - Faridkot','Faridkot'],
            [66,6,'Farm Advisory Service Scheme - Ferozepur','Ferozepur'],
            [67,6,'Farm Advisory Service Scheme - Gurdaspur','Gurdaspur'],
            [68,6,'Farm Advisory Service Scheme - Hoshiarpur','Hoshiarpur'],
            [69,6,'Farm Advisory Service Scheme - Jalandhar','Jalandhar'],
            [70,6,'Farm Advisory Service Scheme - Kapurthala','Kapurthala'],
            [71,6,'Farm Advisory Service Scheme - Patiala','Patiala'],
            [72,6,'Farm Advisory Service Scheme - Ropar','Ropar'],
            [73,6,'Farm Advisory Service Scheme - Sangrur','Sangrur'],
            [74,6,'Farm Advisory Service Scheme - Tarntaran','Tarntaran'],
            [75,7,'Director Farm','Ludhiana'],
            [76,7,'Director Seeds','Ludhiana'],
            [77,7,'Regional Research Station - Abohar','Abohar'],
            [78,7,'Research Station - Ajnala, Amritsar','Amritsar'],
            [79,7,'Fruit Research Sub-Station - Bahadurgarh, Patiala','Patiala'],
            [80,7,'Regional Research Station for Kandi Area - Ballowal Saunkhri','Ballowal Saunkhri'],
            [81,7,'Regional Research Station - Bathinda','Bathinda'],
            [82,7,'Regional Research Station - Faridkot','Faridkot'],
            [83,7,'Fruit Research Sub-Station - Gangian, Hoshiarpur','Hoshiarpur'],
            [84,7,'Regional Research Station - Gurdaspur','Gurdaspur'],
            [85,7,'Fruit Research Station - Jallowal-Lesriwal, Jalandhar','Jalandhar'],
            [86,7,'Regional Research Station - Kapurthala','Kapurthala'],
            [87,7,'Raja Harinder Singh Seed Farm - Faridkot','Faridkot'],
            [88,7,'University Seed Farm - Ladhowal','Ladhowal'],
            [89,7,'University Seed Farm - Nabha','Nabha'],
            [90,7,'University Seed Farm - Naraingarh, Fatehgarh Sahib','Fatehgarh Sahib'],
            [91,7,'University Seed Farm - Usman, Tarn Taran','Tarn Taran'],
            [92,8,'Office of Dean Postgraduate Studies','Ludhiana'],
            [93,9,'Office of Directorate of Students\' Welfare','Ludhiana'],
            [94,9,'University Counselling & Placement Guidance Cell','Ludhiana'],
            [95,10,'Office of Mohinder Singh Randhawa Library','Ludhiana'],
            [96,3,'Dean Office of College of Agricultural Engineering and Technology','Ludhiana'],
            [97,11,'Office of Dean, Ballowal Saunkhri','Ballowal Saunkhri'],
            [98,12,'Office of Registrar','Ludhiana'],
            [99,13,'EMN Lab','Ludhiana'],
        ];

        foreach ($departments as $d) {
            DB::table('departments')->updateOrInsert(['id' => $d[0]], [
                'college_id' => $d[1],
                'name' => $d[2],
                'place' => $d[3],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function makeEmployee($name, $phone, $role, $designationName, $sectionName, $directorateId, $salutation = 'Mr.')
    {
        $collegeId = College::where('name', 'Directorate of Extension Education')->value('id');
        $departmentId = Department::where('college_id', $collegeId)->where('name', 'Communication Centre')->value('id');
        $userCollegeId = $role == 'superuser' ? null : $collegeId;
        $userDepartmentId = in_array($role, ['superuser','college_admin','director']) ? null : $departmentId;

        $user = User::firstOrCreate(['phone' => $phone], [
            'name' => $name,
            'email' => null,
            'password' => Hash::make('password'),
            'role' => $role,
            'college_id' => $userCollegeId,
            'department_id' => $userDepartmentId,
            'is_active' => 1,
            'must_change_password' => 0,
        ]);
        $user->syncRoleNames([$role], $userCollegeId, $userDepartmentId);

        $parts = explode(' ', $name, 2);
        $designationId = Designation::where('name', $designationName)->value('id');
        $sectionId = Section::where('name', $sectionName)->value('id');

        return Employee::firstOrCreate(['user_id' => $user->id], [
            'directorate_id' => $directorateId,
            'college_id' => College::where('name', 'Directorate of Extension Education')->value('id'),
            'department_id' => Department::where('college_id', College::where('name', 'Directorate of Extension Education')->value('id'))->where('name', 'Communication Centre')->value('id'),
            'section_id' => $sectionId,
            'designation_id' => $designationId,
            'salutation' => $salutation,
            'first_name' => $parts[0],
            'last_name' => $parts[1] ?? '',
            'full_name' => $name,
            'phone' => $phone,
            'job_type' => 'Permanent',
            'date_of_birth' => '1980-01-01',
            'date_of_joining' => '2010-01-01',
            'retirement_age' => 60,
            'calculated_retirement_date' => '2040-01-31',
            'final_retirement_date' => '2040-01-31',
            'calculated_increment_date' => date('Y').'-01-01',
            'final_increment_date' => date('Y').'-01-01',
            'manual_country' => 'India',
            'manual_state' => 'Punjab',
            'manual_city' => 'Ludhiana',
            'zip' => '141004',
            'status' => 'Active',
        ]);
    }
}
