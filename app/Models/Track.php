<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Track extends Model
{
    use HasFactory;

	// column where we search for a match
	const scorable_columns = ['name', 'artist', 'remix'];
	// minimum soft score match to close answers for that column
	const minimum_soft_score = 0.33;

	protected $fillable = [
		'is_current',
		'is_name_found',
		'is_artist_found',
		'is_remix_found'
	];

	public function getUrl() :string {
	
		return route('track', ['id'=>$this->id]);

	}

	public function setAsCurrent() :void {
	
		// remove any active track
		Track::where('is_current', '=', 1)->update([
			'is_current'		=>0,
			'is_name_found'		=>0,
			'is_artist_found'	=>0,
			'is_remix_found'	=>0
		]);

		// mark this track the active one
		$this->is_current = 1;
		$this->save();

	}

	public static function setAndGetRandom(?array $filters = []) :self {
	
		// fetch a random track from the database
		$query = Track::query();

		// for each provided filters (if any)
		foreach($filters as $filter) {
			// if it looks like a year
			if(is_numeric($filter) && strlen($filter) == 4) {
				// filter to that decade
				$query->whereBetween(
					'year', [
						substr($filter, 0, 2) . '0', 
						substr($filter, 0, 3) . '9'
					]
				);
			}
			// otherwise consider that it's a genre
			else {
				$query->where('genre', 'like', '%' . $filter . '%');
			}

		}

		// fetch a random track
		$track = $query->inRandomOrder()->first();

		// if we failed to find a matching track
		if(!$track) {
			// get a really random one (no filters)
			$track = Track::query()->inRandomOrder()->first();
		}

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

		// iterate through the scorable columns
		foreach (self::scorable_columns as $column) {
			
			$columnSlug = Str::slug($this->$column);
			$is_already_found_column = "is_{$column}_found";

			// Check if the answer matches the column and the flag is not set
			if (
				$answerSlug === $columnSlug && 
				!$this->$is_already_found_column)
			{
				$this->$is_already_found_column = 1;
				$this->save();

				// Increase the user's score
				Auth::user()->increaseScoreBy(1)->save();

				// Return what has been found
				return [
					'found' => $column,
					'score' => 1,
				];
			}
		}


		// partial matches
        $answerWords = explode('-', $answerSlug);

		// for each of the scorable columns
		foreach (self::scorable_columns as $column) {

			if(
				// we have nothing to compare against 
				!$this->$column || 
				// or if this has already been found by someone
				$this->{"is_{$column}_found"}
			) { 
				// don't attempt a match
				continue; 
			}

			// soft comparison with the current column
			$columnWords = explode('-', Str::slug($this->$column));
			$totalWords = count($columnWords);
			$columnScore = 0;

			// look for each provided word
			foreach ($answerWords as $index => $word) {
				$wordIndex = array_search($word, $columnWords);
				// if the word exists in the track name/remix/artist
				if ($wordIndex !== false) {
					// if the word is at the right place
					if ($wordIndex === $index) {
						$columnScore += 1 / $totalWords;
					} 
					// word exists but not at the right place
					else {
						$columnScore += 0.5 / $totalWords;
					}
				}
			}

			// if we reached a high enough score
			// meaning 1/3 words, at the proper place
			// or 2/3 words out of place
			if ($columnScore >= self::minimum_soft_score) {

				$columnScore = round($columnScore, 1);

				$this->{"is_{$column}_found"} = 1;
				$this->save();

				Auth::user()->increaseScoreBy($columnScore)->save();

				return [
					'found' => $column,
					'score' => $columnScore,
				];
			}
		}

		return false;


	}

}
