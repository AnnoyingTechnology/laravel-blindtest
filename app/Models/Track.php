<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Track extends Model
{
    use HasFactory;

	protected $fillable = [
		'is_current',
		'is_name_found',
		'is_artist_found'
	];

	public function getUrl() :string {
	
		return route('track', ['id'=>$this->id]);

	}

	public function setAsCurrent() :void {
	
		// remove any active track
		Track::where('is_current', '=', 1)->update([
			'is_current'		=>0,
			'is_name_found'		=>0,
			'is_artist_found'	=>0
		]);

		// mark this track as active
		$this->is_current = 1;
		$this->save();

	}

	public static function setAndGetRandom(?string $genre = null) :self {
	
		// fetch a random track from the database
		$query = Track::query();

		if($genre) {
			$query->where('genre', 'like', '%' . $genre . '%');
		}

		$track = $query->inRandomOrder()->first();

		// mark that track as the is_current
		$track->setAsCurrent();

		return $track;

	}

	public static function getCurrent() :self {
	
		return Track::where('is_current',1)->first();

	}

	public function match(
		string $answer
	) :array|false {
	
		// slugify for softer comparisons
		$answerSlug = Str::slug($answer);

		// perfect name match
        if(
			$answerSlug === Str::slug($this->name) && 
			!$this->is_name_found
		) {
			
			$this->is_name_found = 1;
			$this->save();

			// increase the user's score
			Auth::user()
				->increaseScoreBy(1)
				->save();

			// return what has been found
            return [
				'found'		=>'name',
				'score'		=>1,
			];
        }

		// perfect artist match
		if(
			$answerSlug === Str::slug($this->artist) && 
			!$this->is_artist_found
		) {
			
			$this->is_artist_found = 1;
			$this->save();

			// increase the user's score
			Auth::user()
				->increaseScoreBy(1)
				->save();

			// return what has been found
            return [
				'found'		=>'artist',
				'score'		=>1,
			];
        }

		// partial matches
        $answerWords = explode('-', $answerSlug);

		// soft comparison with name
        $nameWords = explode('-', Str::slug($this->name));
        $totalWords = count($nameWords);
        $nameScore = 0;

		// look for each provided words
        foreach ($answerWords as $index => $word) {
            $nameIndex = array_search($word, $nameWords);
            if ($nameIndex !== false) {
                if ($nameIndex === $index) {
                    $nameScore += 1 / $totalWords;
                } else {
                    $nameScore += 0.5 / $totalWords;
                }
            }
        }

		if($nameScore >= 0.25 && !$this->is_name_found) {

			$nameScore = round($nameScore, 1);

			if($nameScore > 0.33) {
			
				$this->is_name_found = 1;
				$this->save();

			}
			
			Auth::user()
				->increaseScoreBy($nameScore)
				->save();

			return [
				'found'		=>'name',
				'score'		=>$nameScore,
			];
		}

		// soft comparison with name
        $artistWords = explode('-', Str::slug($this->artist));
        $totalWords = count($nameWords);
        $artistScore = 0;

		// look for each provided words
        foreach ($answerWords as $index => $word) {
            $artistIndex = array_search($word, $artistWords);
            if ($artistIndex !== false) {
                if ($artistIndex === $index) {
                    $artistScore += 1 / $totalWords;
                } else {
                    $artistScore += 0.5 / $totalWords;
                }
            }
        }

		if($artistScore >= 0.25 && !$this->is_artist_found) {

			$artistScore = round($artistScore, 1);

			if($artistScore > 0.33) {

				$this->is_artist_found = 1;
				$this->save();

			}

			Auth::user()
				->increaseScoreBy($artistScore)
				->save();

			return [
				'found'		=>'name',
				'score'		=>$artistScore,
			];
		}

		return false;


	}

}
