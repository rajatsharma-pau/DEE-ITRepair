<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\College;
use App\Department;
use App\Section;
use App\Country;
use App\State;
use App\City;
use App\RepairCategory;
use App\ProblemTemplate;
use App\RepairRoutingRule;
use Carbon\Carbon;

class DeeItRepairSeeder extends Seeder
{
    public function run()
    {
        $this->seedCollegesAndDepartments();
        $this->seedRoles();

        $deeCollegeId = College::where('name', 'Directorate of Extension Education')->value('id');

        $deeDepartment = Department::firstOrCreate(
            [
                'college_id' => $deeCollegeId,
                'name' => 'Directorate of Extension Education',
            ],
            [
                'place' => 'Ludhiana',
                'is_active' => 1,
            ]
        );

        $this->seedSections($deeCollegeId, $deeDepartment ? $deeDepartment->id : null);
        $this->seedDesignations();
        $this->seedLocations();
        $this->seedRepairMasters();
    }

    private function seedSections($collegeId, $departmentId)
    {
        $sections = ['Administration', 'Store', 'IT Cell', 'Accounts', 'Plant Clinic', 'ATIC'];

        foreach ($sections as $section) {
            Section::firstOrCreate(
                ['college_id' => $collegeId, 'department_id' => $departmentId, 'name' => $section],
                ['short_name' => $section, 'is_active' => 1]
            );
        }
    }

    private function seedLocations()
    {
        $india = Country::firstOrCreate(
            ['name' => 'India'],
            ['code' => 'IN', 'is_active' => 1]
        );

        $punjab = State::firstOrCreate(
            ['country_id' => $india->id, 'name' => 'Punjab'],
            ['is_active' => 1]
        );

        City::firstOrCreate(
            ['state_id' => $punjab->id, 'name' => 'Ludhiana'],
            ['district' => 'Ludhiana', 'pincode' => '141004', 'is_active' => 1]
        );
    }

    private function seedRoles()
    {
        $now = Carbon::now();

        $roles = [
            ['Superuser', 'superuser', 'Superuser', 'University-level full control'],
            ['Admin', 'admin', 'Admin', 'General admin'],
            ['College Admin', 'college_admin', 'College Admin', 'College/directorate-level admin'],
            ['Department Admin', 'department_admin', 'Department Admin', 'Department/KVK/office-level admin'],
            ['Employee', 'employee', 'Employee', 'Can submit requests and view own records'],
            ['Storekeeper', 'storekeeper', 'Storekeeper', 'Handles assets, estimates, store stock and indents'],
            ['Programmer', 'programmer', 'Programmer', 'Technical verification for computer-related work'],
            ['Store Incharge', 'store_incharge', 'Store Incharge', 'Verification for store/non-computer related work'],
            ['D-4 Seat', 'd4_seat', 'D-4 Seat', 'Manual financial file tracking'],
            ['Director', 'director', 'Director / Head', 'College/directorate viewing and approval role'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role[1]],
                [
                    'name' => $role[0],
                    'slug' => $role[1],
                    'display_name' => $role[2],
                    'description' => $role[3],
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedDesignations()
    {
        // Administrative designations collected from PAU non-scientific/staff designation list.
        // Duplicates are removed by normalized designation name.
        $administrativeDesignations = [
            'Senior Assistant',
            'Junior Microscopist',
            'Assistants at KVK\'s',
            'Steno Typist',
            'Clerk/ Junior Assistant',
            'Lab. Attendant',
            'Messenger',
            'Senior Scale Stenographer',
            'Farm Worker',
            'Superintendent',
            'Lab Technician',
            'Car Driver',
            'Computer Operator',
            'Farm Manager',
            'Assistant Registrar',
            'Information Processor',
            'Chowkidar',
            'Agriculture Sub Inspector',
            'Demonstrator (Agril. Engg.)',
            'Foreman',
            'Business Manager',
            'Tractor Driver',
            'Plant Observer',
            'Library Attendant',
            'Sweeper',
            'Demonstrator',
            'Sales Promotion Assistant',
            'Metrological Attendant',
            'Junior Lab. Assistant',
            'Stenographer (Gr-III)',
            'Agricultural Field Officer',
            'Assistant',
            'Programmer',
            'Personal Assistant',
            'Accounts Officer',
            'Programme Assistant (Lab)',
            'Mechanic',
            'Restorer',
            'Museum Assistant',
            'Senior Lab. Assistant',
            'Sr. Photographer',
            'Book Binder',
            'Beldar',
            'Assistant Accounts Officer',
            'Programme Assistant (Computer)',
            'Deputy Registrar',
            'Lab. Assistant',
            'Budder',
            'Packer',
            'Network-cum-programming Assistant',
            'SPA(T)',
            'Assistant Estate Officer',
            'Telephone Operator',
            'Mate',
            'Bill-cum-Cash Messenger',
            'Gunman',
            'Helper',
            'Head Mali',
            'Security Guard',
            'Cook-cum-Chowkidar',
            'Computering Assistant (Tech. G-III Engg.)',
            'Fieldman',
            'Bus Driver',
            'Assistant Director (Publications)',
            'Junior Engineer',
            'Sub Divisional Officer',
            'Sub Divisional Engineer',
            'Tech. Assistant (Engg)',
            'Executive Engineer',
            'Helper to Plumber',
            'Store Keeper',
            'Audio Visual Aids Assistant',
            'Press Foreman',
            'Junior Lecture Table Assistant',
            'Surveyor',
            'Senior Research Investigator',
            'Agromet Observer',
            'Canner',
            'Steward',
            'Senior Draftsman',
            'Private Secy. To Vice Chancellor',
            'Tractor Mechanic-cum-Operator',
            'Electrician',
            'Technician',
            'Trained Graduate Teacher (Master/Mistress)',
            'Gas-cum-Electric Welder',
            'ETT Teachers',
            'Cook',
            'Junior Library Assistant',
            'Fitter-cum-Cleaner',
            'Sewerman',
            'Work Munshi',
            'Head Draftsman',
            'Junior Draftsman',
            'Work Inspector',
            'Jeep Driver',
            'Draftsman',
            'Bee Keeper',
            'Assistant Architect',
            'Plant Supervisor',
            'Lab. Assistant Refrigeration',
            'Boiler Operator',
            'Clerk',
            'Senior Clerk',
            'Junior Technician',
            'Driver',
            'Daily Wage Worker',
        ];

        // Scientific designations from designations.csv. All entries from this file are seeded as Scientific.
        $scientificDesignations = [
            'Nematologist',
            'Agricultural Economist',
            'Scientist (Processing & Food Engineering)',
            'Plant Breeder',
            'Assistant Professor',
            'Vegetable Breeder',
            'Assistant Professor (Computer Science & Engineering)',
            'Assistant Professor (Animal Science)',
            'Plant Mycologist',
            'Maize Breeder',
            'Scientist (FMPE)',
            'Assistant Professor (Economics)',
            'Horticulturist',
            'Assistant Professor (Sociology)',
            'Scientist (Seed Production)',
            'Olericulturist',
            'Plant Biotechnologist',
            'Assistant Professor (Soil Science)',
            'Assistant Professor (Education)',
            'Scientist (FRM)',
            'Assistant Professor (Agrometeorology)',
            'Biochemist',
            'Chemist',
            'Economist',
            'Pathologist (Pulses)',
            'Assistant Professor (Vegetable)',
            'Scientist (Soil & Water Engineering)',
            'Oilseed Breeder',
            'Assistant Professor (Fruit Science)',
            'Assistant Professor (Plant Pathology)',
            'Assistant Professor (Mathematics)',
            'Assistant Professor (Plant Breeding)',
            'Assistant Professor (Botany)',
            'Microbiologist',
            'Assistant Professor (Agronomy)',
            'Scientist (Soils)',
            'Assistant Professor (Entomology)',
            'Pulse Breeder',
            'Assistant Professor (Vegetables)',
            'Assistant Professor (Zoology)',
            'Assistant Professor (Agricultural Engineering)',
            'District Extension Scientist (Agronomy)',
            'Assistant Plant Physiologist',
            'Assistant Professor (Microbiology)',
            'Assistant Professor (Food Technology)',
            'Assistant Professor (Biochemistry)',
            'District Extension Scientist (Plant Pathology)',
            'Assistant Professor (Biotechnology)',
            'Assistant Floriculturist',
            'Silviculturist',
            'Plant Pathologist',
            'Associate Professor (FMPE)',
            'Scientist (Animal Science)',
            'Extension Scientist (Extension Education)',
            'Extension Scientist (Vegetables)',
            'Mycologist (Mushroom)',
            'Senior Agronomist',
            'Agronomist',
            'Assistant Professor (Processing & Food Engineering)',
            'Associate Professor (Soil Science)',
            'District Extension Scientist (Entomology)',
            'Nanotechnologist (Physics)',
            'Biotechnologist',
            'Scientist (Nanotechnology)',
            'Vegetable Scientist',
            'Soil Scientist',
            'Scientist (F&N)',
            'Assistant Professor (Chemistry)',
            'Professor (Processing & Food Engineering)',
            'Assistant Professor (FMPE)',
            'Agrometeorologist',
            'Associate Professor (Mechanical Engineering)',
            'Pedologist',
            'District Extension Scientist (FMPE)',
            'Rice Breeder',
            'Assistant Professor (Soil & Water Engineering)',
            'Soil Chemist',
            'Assistant Professor (Soil Conservation)',
            'Scientist(HD&FS)',
            'Entomologist',
            'Food Technologist',
            'Bioinformatician',
            'Assistant Professor (Physics)',
            'Assistant Professor (Extension Education)',
            'Assistant Professor (Electrical Engineering)',
            'District Extension Scientist (Soil Science)',
            'Assistant Professor (Nematology)',
            'Botanist',
            'Professor (EE&CM)',
            'Scientist (Agricultural Marketing)',
            'Scientist (ATS)',
            'Olericulturist (Seed Production)',
            'Assistant Scientist (Vegetable)',
            'Nutritionist (Forage)',
            'Quantitative Geneticist',
            'Microbiologist ( Soil Science)',
            'Plant Virologist',
            'Extension Scientist',
            'Assistant Professor (Livestock Production Technology)',
            'Assistant Professor (Statistics)',
            'Associate Professor (Plant Breeding)',
            'Associate Professor',
            'Scientist (Agricultural Engineering)',
            'Zoologist',
            'Assistant Professor (Mechanical Engineering)',
            'Assistant Nutritionist (Forage Evaluation)',
            'Assistant Horticulturist',
            'Assistant Professor (Civil Engineering)',
            'Assistant Professor (Vegetable Crops)',
            'District Extension Scientist (Fruits)',
            'Fruit Scientist',
            'Associate Professor (Extension Education)',
            'Senior Entomologist',
            'Senior Extension Scientist',
            'Extension Scientist (Fruits)',
            'Assistant Professor(Home Science)',
            'Assistant Professor (Home Science)',
            'Assistant Research Engineer',
            'Assistant Professor (ATS)',
            'Sugarcane Breeder',
            'Assitant Residue Analysis',
            'Molecular Geneticist',
            'Associate Professor Chemist',
            'Senior Extension Scientist (Extension Education)',
            'Scientist (REE)',
            'Editor (Punjabi)',
            'Extension Specialist (Food Technology)',
            'Assistant Professor (Business Management)',
            'Assistant Professor (Cytogenetics)',
            'Extension Scientist (Agronomy)',
            'Scientist (Floriculture & Landscaping)',
            'Ornithologist',
            'Scientist (Residue Analysis)',
            'Associate Librarian',
            'Plant Physiologist',
            'Senior Forage Breeder',
            'Professor',
            'Principal Wheat Breeder',
            'Senior Scientist',
            'Principal Scientist',
            'Principal Agronomist',
            'Assistant Director (Physical Education)',
            'Principal Statistician',
            'Principal Acarologist',
            'Principal Entomologist',
            'Principal Plant Pathologist',
            'Soil Physicist',
            'Principal Soil Physicist',
            'Associate Director (T)',
            'Associate Director (IT)',
            'Principal Agricultural Economist',
            'Nanotechnologist (Chemistry)',
            'Senior Scientist (Nanotechnology)',
            'Principal Extension Scientist',
            'Senior Biotechnologist',
            'Principal Biotechnologist',
            'Principal Rice Breeder',
            'Senior Acarologist',
            'Senior Botanist',
            'Principal Botanist',
            'District Extension Scientist',
            'Principal Microbiologist',
            'Principal Vegetable Breeder',
            'Principal Olericulturist',
            'Principal Virologist',
            'Principal Soil Chemist',
            'Cotton Breeder',
            'University Librarian',
            'Senior Plant Pathologist',
            'Principal Pathologist',
            'Principal Ornithologist',
            'Senior Fruit Scientist',
            'Principal Molecular Geneticist',
            'Senior Nematologist',
            'Senior Olericulturist',
            'Senior Agricultural Economist',
            'Principal Zoologist (Rodents)',
            'Principal Fruit Scientist',
            'Principal Forage Breeder',
            'Fruit Molecular Geneticist',
            'Principal Pulse Breeder',
            'Tree Breeder',
            'Senior Tree Breeder',
            'Principal Tree Breeder',
            'Scientist (Extension Education)',
            'Senior Scientist (Extension Education)',
            'Principal Scientist (Extension Education)',
            'Senior Plant Physiologist',
            'Principal Plant Physiologist',
            'Senior Plant Pathology (Pulses)',
            'Senior Plant Pathologist (ST)',
            'Senior Extension Scientist (Fruits)',
            'Senior Extension Scientist (Plant Pathology)',
            'Senior Scientist (P&FE)',
            'Associate Professor (Plant Pathology)',
            'Associate Professor (Plant Protection)',
            'Deputy Director (Trg.)',
            'Professor (Plant Pathology)',
            'Professor (Plant Protection)',
            'Senior Plant Pathologist (Sunflower)',
            'Senior Plant Breeder',
            'Principal Plant Breeder',
            'Senior Barley Breeder',
            'Senior Scientist (Horticulture)',
            'Senior Extension Scientist (Soil Science)',
            'Senior Plant Breeder (Seed Tech.)',
            'Senior Soil Chemist',
            'Senior Microbiologist',
            'Senior Microbiologist (Soils)',
            'Senior Plant Pathologist (Wheat)',
            'Senior Plant Pathologist (Pulses)',
            'Associate Professor (Fruit Science)',
            'Associate Professor (Physiology)/Ergonomics',
            'Senior Millet Breeder',
            'Senior Rice Breeder',
            'Senior Scientist (Floriculture & Landscaping)',
            'Associate Professor (Floriculture & Landscaping)',
            'Associate Professor (Foreign Languages)',
            'Senior Entomologist (Processing & Food Engineering)',
            'Senior Biochemist',
            'Senior Scientist (Food & Nutrition)',
            'Senior Extension Scientist (Home Science)',
            'Scientist (Instrumentation)',
            'Principal Food Technologist',
        ];

        $sortOrder = 1;
        $sortOrder = $this->seedDesignationGroup($administrativeDesignations, 'Administrative', $sortOrder);
        $this->seedDesignationGroup($scientificDesignations, 'Scientific', $sortOrder);
    }

    private function seedDesignationGroup(array $names, $cadre, $sortOrder)
    {
        static $designationMap = null;

        if ($designationMap === null) {
            $designationMap = [];
            foreach (DB::table('designations')->select('id', 'name')->get() as $row) {
                $designationMap[$this->designationKey($row->name)] = $row->id;
            }
        }

        foreach ($names as $name) {
            $name = $this->normalizeText($name);
            if ($name === '') {
                continue;
            }

            $key = $this->designationKey($name);
            $payload = ['name' => $name];

            if (Schema::hasColumn('designations', 'cadre')) {
                $payload['cadre'] = $cadre;
            }

            if (Schema::hasColumn('designations', 'sort_order')) {
                $payload['sort_order'] = $sortOrder;
            }

            if (Schema::hasColumn('designations', 'is_active')) {
                $payload['is_active'] = 1;
            }

            $payload['created_at'] = Carbon::now();
            $payload['updated_at'] = Carbon::now();

            if (isset($designationMap[$key])) {
                $updatePayload = $payload;
                unset($updatePayload['created_at']);
                DB::table('designations')->where('id', $designationMap[$key])->update($updatePayload);
            } else {
                $designationMap[$key] = DB::table('designations')->insertGetId($payload);
            }

            $sortOrder++;
        }

        return $sortOrder;
    }

    private function normalizeText($value)
    {
        $value = str_replace("Â ", ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }

    private function designationKey($value)
    {
        return strtolower($this->normalizeText($value));
    }

    private function seedRepairMasters()
    {
        $categories = [
            ['Computer', 'Computer Related', 'programmer'],
            ['Printer', 'Computer Related', 'programmer'],
            ['Internet / LAN', 'Computer Related', 'programmer'],
            ['Software', 'Computer Related', 'programmer'],
            ['UPS / Battery', 'Computer Related', 'programmer'],
            ['Furniture', 'Non Computer', 'store_incharge'],
            ['Electrical', 'Non Computer', 'store_incharge'],
            ['Stationery', 'General', 'storekeeper'],
            ['General Repair', 'General', 'storekeeper'],
        ];

        foreach ($categories as $categoryData) {
            $category = RepairCategory::firstOrCreate(
                ['name' => $categoryData[0]],
                ['item_group' => $categoryData[1], 'default_handler' => $categoryData[2], 'is_active' => 1]
            );

            if ($categoryData[2] === 'programmer') {
                RepairRoutingRule::firstOrCreate(
                    ['repair_category_id' => $category->id],
                    [
                        'handler_type' => 'role',
                        'handler_value' => 'programmer',
                        'requires_store_verification' => 1,
                        'requires_programmer_verification' => 1,
                        'is_active' => 1,
                    ]
                );
            } elseif ($categoryData[2] === 'store_incharge') {
                RepairRoutingRule::firstOrCreate(
                    ['repair_category_id' => $category->id],
                    [
                        'handler_type' => 'charge',
                        'handler_value' => 'Store Incharge',
                        'requires_store_verification' => 1,
                        'requires_store_incharge_approval' => 1,
                        'is_active' => 1,
                    ]
                );
            } else {
                RepairRoutingRule::firstOrCreate(
                    ['repair_category_id' => $category->id],
                    [
                        'handler_type' => 'role',
                        'handler_value' => 'storekeeper',
                        'requires_store_verification' => 1,
                        'is_active' => 1,
                    ]
                );
            }
        }

        $problemTemplates = [
            ['Computer', 'Computer not starting', 'Computer/System is not starting. Kindly check power supply, SMPS, motherboard and related parts.'],
            ['Computer', 'Computer running slow', 'Computer is running very slow and needs checking/service/upgradation if required.'],
            ['Computer', 'No display', 'Monitor/CPU is on but display is not coming. Kindly verify and repair.'],
            ['Printer', 'Printer not printing', 'Printer is not printing. Kindly check toner/cartridge, roller, paper jam and connectivity.'],
            ['Printer', 'Paper jam', 'Printer paper jam problem. Kindly check roller and service requirement.'],
            ['Printer', 'Toner/cartridge issue', 'Print quality is poor/faded. Toner/cartridge/service may be required.'],
            ['Internet / LAN', 'Internet not working', 'Internet/LAN is not working in the room. Kindly verify network point/cable/switch.'],
            ['Software', 'Software installation required', 'Required software/driver installation or configuration is needed.'],
            ['UPS / Battery', 'UPS backup not available', 'UPS battery backup is not available. Kindly check battery/UPS and estimate repair/replacement.'],
            ['Furniture', 'Chair repair required', 'Chair/table/furniture requires repair/replacement.'],
            ['Electrical', 'Electrical point not working', 'Electrical switch/socket/point is not working and needs repair.'],
            ['Stationery', 'Stationery required', 'Stationery/material is required from store as per office requirement.'],
            ['General Repair', 'Other repair/material requirement', 'Other repair/material requirement. Details are mentioned by employee.'],
        ];

        foreach ($problemTemplates as $templateData) {
            $category = RepairCategory::where('name', $templateData[0])->first();
            ProblemTemplate::firstOrCreate(
                ['title' => $templateData[1], 'repair_category_id' => optional($category)->id],
                [
                    'description' => $templateData[2],
                    'item_group' => optional($category)->item_group,
                    'is_active' => 1,
                ]
            );
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
                'short_name' => $c[0] == 6 ? 'DEE' : null,
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
}
