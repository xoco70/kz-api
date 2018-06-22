<?php

namespace App;

use App\Traits\RoleTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * @property  mixed name
 * @property  mixed email
 * @property  mixed password
 * @property bool verified
 * @property mixed token
 * @property  mixed clearPassword
 * @property string firstname
 * @property string lastname
 * @property Federation federationOwned
 * @property Association associationOwned
 * @property Club clubOwned
 * @property int id
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, SoftDeletes, RoleTrait, Notifiable, Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'password_confirmation'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        // TODO should test charset ( would fail on Japonese / Chinese / Korean ) --> email
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function (User $user) {
//            $softDeletedUser = User::onlyTrashed()->where('email', '=', $user->email)->first();
//            if ($softDeletedUser != null) {
//                $softDeletedUser->restore();
//                return false;
//            }
            $user->token = str_random(30);
//            if ($user->country_id == 0) {
//                $user->addGeoData();
//            }
            return true;
        });

        // If a User is deleted, you must delete:
        // His tournaments, his competitors

        static::deleting(function ($user) {
            $user->tournaments->each->delete();
            $user->competitors->each->delete();

        });
        static::restoring(function ($user) {
            $user->competitors()->withTrashed()->get()->each->restore();
            $user->tournaments()->withTrashed()->get()->each->restore();
        });
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grade()
    {
        return $this->belongsTo('App\Grade');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function settings()
    {
        return $this->hasOne('App\Settings');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invites()
    {
        return $this->hasMany('App\Invite', 'email', 'email');
    }

    /**
     * Get all user's created (owned) tournmanents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tournaments()
    {
        return $this->hasMany('App\Tournament');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function championships()
    {
        return $this->belongsToMany(Championship::class, 'competitor')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function competitors() // Used to delete competitors when soft deleting
    {
        return $this->hasMany(Competitor::class);
    }

    /**
     * Tournament where I have participated as competitor
     * @return mixed
     */
    public function myTournaments()
    {
        return Tournament::leftJoin('championship', 'championship.tournament_id', '=', 'tournament.id')
            ->leftJoin('competitor', 'competitor.championship_id', '=', 'championship.id')
            ->where('competitor.user_id', '=', $this->id)
            ->select('tournament.*')
            ->distinct();
    }


    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at != null;
    }

    /**
     * @param $firstname
     * @param $lastname
     */
    public function updateUserFullName($firstname, $lastname)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->save();
    }

    /**
     * @return Collection
     */
    public function fillSelect()
    {
        return User::forUser($this)
            ->pluck('name', 'id')
            ->prepend('-', 0);
    }

    /**
     * Check if a user is registered to a tournament
     * @param Tournament $tournament
     * @return bool
     */
    public function isRegisteredTo(Tournament $tournament)
    {
        $ids = $tournament->championships->pluck('id');
        $isRegistered = Competitor::where('user_id', $this->id)
            ->whereIn('championship_id', $ids)
            ->get();

        return sizeof($isRegistered) > 0;
    }

    /**
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->isSuperAdmin();
    }


    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname ?? '' . " " . $this->lastname ?? '';
    }

    public function getAvatarAttribute($avatar)
    {

        if (!str_contains($avatar, 'http') && isset($avatar)) {
            return asset(config('constants.AVATAR_PATH') . $avatar);
        }
        return $avatar;
    }
}
