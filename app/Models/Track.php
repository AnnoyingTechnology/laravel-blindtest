<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;

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
		'is_remix_found',
		'scoring_factor'
	];

	public function getUrl() :string {
	
		//return route('track', ['id'=>$this->id]);
		return URL::signedRoute('track', ['id'=>$this->id]);

	}

	public function setAsCurrent() :void {
	
		// remove any active track
		Track::where('is_current', '=', 1)->update([
			'is_current'		=>0,
			'is_name_found'		=>0,
			'is_artist_found'	=>0,
			'is_remix_found'	=>0,
			'scoring_factor'	=>1
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

	public static function getPopularGenres(): array {

		return Cache::get('popularGenres', function () {
			
			// retrieve all genres
			$genres = Track::pluck('genre');

			// initialize an empty array to hold genre counts
			$genreCounts = [];
	
			// loop through each genre entry
			foreach ($genres as $genre) {
				if(!$genre) { continue; }
				// Split the genres by "/"
				$individualGenres = explode(
					'/', 
					str_replace(
						[' ','-'], 
						'/', 
						$genre
					)
				);
	
				// count each individual genre
				foreach ($individualGenres as $individualGenre) {
					if(!$individualGenre) { continue; }
					$individualGenre = mb_strtolower(trim($individualGenre));
					if (isset($genreCounts[$individualGenre])) {
						$genreCounts[$individualGenre]++;
					} else {
						$genreCounts[$individualGenre] = 1;
					}
				}
			}
	
			// sort genres by their count in descending order
			arsort($genreCounts);
	
			// return the sorted genres as an array (top 15 only)
			return array_slice(array_keys($genreCounts),0,15);

		});

    }

	public function getClues() :array {

		$clues = [];

		// get cluyfied name, remix, artist
		foreach(self::scorable_columns as $column) {

			// ignore empty columns (remix only)
			if(!$this->$column || !strlen($this->$column)) { continue; }

			$input = $this->$column;

			$length = Str::length($input);

			// Ensure at least one character is visible for strings less than 5 characters
			$visibleCount = ($length < 5) ? 1 : max(1, ceil($length * 0.2));

			// Get positions to keep visible
			if ($length < 5) {
				// If the length is less than 5, show one character
				$visibleIndexes = array_rand(array_flip(range(0, $length - 1)));
				$visibleIndexes = [$visibleIndexes]; // array_rand returns a single number if num is 1
			} else {
				$visibleIndexes = array_rand(array_flip(range(0, $length - 1)), $visibleCount);
			}
			
			// Normalize the result to an array
			if (!is_array($visibleIndexes)) {
				$visibleIndexes = [$visibleIndexes];
			}
			$visibleIndexes = array_flip($visibleIndexes);
		
			// Build the obscured string
			$clues[] = collect(str_split($input))
				->map(function ($char, $index) use ($visibleIndexes) {
					return $char === ' ' || isset($visibleIndexes[$index]) ? $char : '*';
				})
				->implode('');

		}

		// decrease the maximum score reachable
		$this->decrement(
			'scoring_factor', 
			$this->scoring_factor >= 0.25 ? 
				0.25 : 
				0
		);

		// return the clues
		return $clues;
	}

	public function giveup() :self {

		$this->is_name_found = 1;
		$this->is_artist_found = 1;
		$this->is_remix_found = 1;

		return $this;

	}

	public function match(
		string $answer
	) :array|false {
	
		// slugify for softer comparisons
		$answerSlug = Str::slug($answer);

		// iterate through the scorable columns
		foreach (self::scorable_columns as $column) {
			
			$score = 1 * $this->scoring_factor;

			$columnSlug = Str::slug($this->$column);
			$is_already_found_column = "is_{$column}_found";

			// Check if the answer matches the column and the flag is not set
			if (
				$answerSlug === $columnSlug && 
				!$this->$is_already_found_column
			) {
				$this->$is_already_found_column = $score;
				$this->save();

				// Increase the user's score
				Auth::user()->increaseScoreBy($score)->save();

				// Return what has been found
				return [
					'found' => $column,
					'score' => $score,
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

				// apply scoring factor
				$columnScore *= $this->scoring_factor;

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
