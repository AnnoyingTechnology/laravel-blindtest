<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use \Colors\RandomColor;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
		'score',
    ];

    protected static function boot() {
        parent::boot();

        static::saving(function ($model) {
            if (!$model->color) {
                $model->color = RandomColor::one([
                    'luminosity'    => 'light',
                    'format'        => 'hex'
                 ]);
            }
        });
    }

	public static function resetScores() :void {
	
		User::query()->update(['score'=>0]);

	}

	public function increaseScoreBy(float $score) :self {

		$this->score += $score;

		return $this;

	}

	protected function username(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst(mb_strtolower($value)),
            set: fn (string $value) => ucfirst(mb_strtolower($value))
        );
    }

}
