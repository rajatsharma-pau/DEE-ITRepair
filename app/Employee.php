<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    protected $fillable = [
        'user_id','college_id','department_id','section_id','designation_id','employee_code','salutation','first_name','middle_name','last_name','full_name','phone','email',
        'gpf_no','nps_no','pan_no','aadhaar_no','salary_account_no','job_type','date_of_birth','date_of_joining','retirement_age',
        'calculated_retirement_date','manual_retirement_date','final_retirement_date','calculated_increment_date','manual_increment_date','final_increment_date','increment_remarks',
        'manual_designation','photo','room_no','address_line_1','address_line_2','country_id','state_id','city_id','manual_country','manual_state','manual_city','zip','status','remarks'
    ];

    protected $dates = [
        'date_of_birth','date_of_joining','calculated_retirement_date','manual_retirement_date','final_retirement_date','calculated_increment_date','manual_increment_date','final_increment_date'
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function college(){ return $this->belongsTo(College::class); }
    public function department(){ return $this->belongsTo(Department::class); }
    public function section(){ return $this->belongsTo(Section::class); }
    public function designation(){ return $this->belongsTo(Designation::class); }
    public function country(){ return $this->belongsTo(Country::class); }
    public function state(){ return $this->belongsTo(State::class); }
    public function city(){ return $this->belongsTo(City::class); }
    public function serviceMovements(){ return $this->hasMany(EmployeeServiceMovement::class)->orderBy('effective_date','desc'); }
    public function transfers(){ return $this->hasMany(EmployeeTransfer::class)->orderBy('transfer_date','desc'); }
    public function charges(){ return $this->hasMany(EmployeeCharge::class)->orderBy('from_date','desc'); }
    public function activeCharges(){ return $this->hasMany(EmployeeCharge::class)->where('is_active', true)->where(function($q){ $q->whereNull('to_date')->orWhere('to_date','>=',date('Y-m-d')); }); }
    public function repairRequests(){ return $this->hasMany(RepairRequest::class); }
    public function assignedRepairRequests(){ return $this->hasMany(RepairRequest::class, 'assigned_to_employee_id'); }

    public function assets(){ return $this->hasMany(Asset::class, 'assigned_to_employee_id'); }
    public function storeIndents(){ return $this->hasMany(StoreIndent::class); }
    public function issuedStoreIndents(){ return $this->hasMany(StoreIndent::class, 'issued_by_employee_id'); }

    public function getDisplayNameAttribute()
    {
        return trim(($this->salutation ? $this->salutation.' ' : '').($this->full_name ?: trim($this->first_name.' '.$this->middle_name.' '.$this->last_name)));
    }

    public function getDesignationNameAttribute()
    {
        return $this->manual_designation ?: optional($this->designation)->name;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    public function hasActiveCharge($chargeName)
    {
        return $this->activeCharges()->where('charge_name', $chargeName)->exists();
    }
}
