<?php

namespace App;
 
use App\Role;
use App\Company;
use App\Report;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes;
    use Notifiable;
    
    protected $table = 'user';
    protected $dates = ['delete_at'];

    const VERIFIED_USER = 1;
    const UNVERIFIED_USER = 0;
    
    const ACTIVE_USER = 1;
    const BLOCK_USER = 0;

    const MALE = 'male';
    const FEMALE = 'female';
 
    const ROLE_ADMIN = 1;
    const ROLE_COMPANY_ADMIN = 2;
    const ROLE_MEMBER = 3;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'gender',
        'birthday',
        'email', 
        'password',
        'avatar_url',
        'role_id',
        'company_id',
        'active_status',
        'email_verified_at',
        'verified',
        'verification_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 
        'remember_token',
        'role_id',
        'company_id',
        'deleted_at',
        'verification_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active_status' => 'boolean',
        'verified' => 'boolean'
    ];

    public function isActiveStatus(){
        return $this->active_status == User::ACTIVE_USER;
    }

    public function role(){
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function company(){
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function reports() {
        return $this->hasMany(Report::class);
    }
    
    
    public function isVerified(){
        return $this->verified == User::VERIFIED_USER;
    } 

    public static function genarateVerificationCode(){
        return str_random(40);
    }
    
    //JWT 
    public function getJWTIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return [];
    }
}
