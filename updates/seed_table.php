<?php namespace Codalia\Journal\Updates;

use Seeder;
use Codalia\Journal\Models\Article;
use Codalia\Journal\Models\Category;
use Codalia\Journal\Models\Group;
use Codalia\Journal\Models\Field;

class SeedJournalTables extends Seeder
{

    public $articles = [['title' => 'The Outsider', 'slug' => 'the-outsider', 'category_id' => 14],
		     ['title' => 'Breakfast at Tiffany\'s', 'slug' => 'breakfast-at-tiffany-s', 'category_id' => 9],
		     ['title' => 'The World According to Garp', 'slug' => 'the-world-according-to-garp', 'category_id' => 13],
		     ['title' => 'The Picture of Dorian Gray', 'slug' => 'the-picture-of-dorian-gray', 'category_id' => 8],
		     ['title' => 'The Shining', 'slug' => 'the-shining', 'category_id' => 10],
		     ['title' => 'It', 'slug' => 'it', 'category_id' => 10],
		     ['title' => 'Carrie', 'slug' => 'carrie', 'category_id' => 10],
		     ['title' => 'Alice in Wonderland', 'slug' => 'alice-in-wonderland', 'category_id' => 11],
		     ['title' => 'Death on the Nile', 'slug' => 'death-on-the-nile', 'category_id' => 12],
		     ['title' => 'Ten Little Niggers', 'slug' => 'ten-little-niggers', 'category_id' => 12],
		     ['title' => 'Murder on the Orient Express', 'slug' => 'murder-on-the-orient-express', 'category_id' => 12],
		     ['title' => '1984', 'slug' => '1984', 'category_id' => 16],
		     ['title' => 'Animal Farm. A Fairy Story', 'slug' => 'animal-farm-a-fairy-story', 'category_id' => 16],
		     ['title' => 'The Planet of the Apes', 'slug' => 'the-planet-of-the-apes', 'category_id' => 15],
		     ['title' => 'The Plague', 'slug' => 'the-plague', 'category_id' => 14],
		     ['title' => 'A Prayer for Owen Meany', 'slug' => 'a-prayer-for-owen-meany', 'category_id' => 13],
		     ['title' => 'The Cider House Rules', 'slug' => 'the-cider-house-rules', 'category_id' => 13],
		     ['title' => 'In Cold Blood', 'slug' => 'in-cold-blood', 'category_id' => 9],
		     ['title' => 'The Roots of Heaven', 'slug' => 'the-roots-of-heaven', 'category_id' => 17],
		     ['title' => 'Promise at Dawn', 'slug' => 'promise-at-dawn', 'category_id' => 17],
		     ['title' => 'Foam of the Days', 'slug' => 'foam-of-the-days', 'category_id' => 18],
		     ['title' => 'I Shall Spit on Your Graves', 'slug' => 'i-shall-spit-on-your-graves', 'category_id' => 18]
    ];

    public $categories = [['name' => 'Genre', 'slug' => 'genre'],
                          ['name' => 'Classics', 'slug' => 'classics'],
                          ['name' => 'Drama', 'slug' => 'drama'],
                          ['name' => 'Romance', 'slug' => 'romance'],
                          ['name' => 'Science Fiction', 'slug' => 'science-fiction'],
                          ['name' => 'Thriller', 'slug' => 'thriller'],
                          ['name' => 'Authors', 'slug' => 'authors'],
                          ['name' => 'Oscar Wilde', 'slug' => 'oscar-wilde'],
                          ['name' => 'Truman Capote', 'slug' => 'truman-capote'],
                          ['name' => 'Stephen King', 'slug' => 'stephen-king'],
                          ['name' => 'Lewis Carroll', 'slug' => 'lewis-carroll'],
                          ['name' => 'Agatha Christie', 'slug' => 'agatha-christie'],
                          ['name' => 'John Irving', 'slug' => 'john-irving'],
                          ['name' => 'Albert Camus', 'slug' => 'albert-camus'],
                          ['name' => 'Pierre Boulle', 'slug' => 'pierre-boulle'],
                          ['name' => 'George Orwell', 'slug' => 'george-orwell'],
                          ['name' => 'Romain Gary', 'slug' => 'romain-gary'],
                          ['name' => 'Boris Vian', 'slug' => 'boris-vian'],
                          ['name' => 'Countries', 'slug' => 'countries'],
                          ['name' => 'England', 'slug' => 'england'],
                          ['name' => 'France', 'slug' => 'france'],
                          ['name' => 'United States', 'slug' => 'united-states']
    ];

    public $groups = [['name' => 'Group A'], ['name' => 'Group B'], ['name' => 'Group C'], ['name' => 'Group D']];

    public $fields = [['name' => 'Editor', 'code' => 'editor', 'type' => 'text'],
		      ['name' => 'Standard', 'code' => 'standard', 'type' => 'list'],
		      ['name' => 'Version', 'code' => 'version', 'type' => 'radio'],
		      ['name' => 'Translations', 'code' => 'translations', 'type' => 'checkbox'],
		      ['name' => 'Release date', 'code' => 'release_date', 'type' => 'date'],
		      ['name' => 'Meeting', 'code' => 'meeting', 'type' => 'datetime']
    ];

    public function run()
    {
      foreach ($this->articles as $key => $article) {
	$order = $key + 1;
	$day = (string)$order;
	if ($order < 10) {
	  $day = '0'.$order;
	}

	Article::create(['title' => $article['title'], 'slug' => $article['slug'], 'category_id' => $article['category_id'],
		     'description' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 
		     'status' => 'published', 'created_by' => 1, 'updated_by' => 1, 
		     'created_at' => '2020-03-'.$day.' 04:35:00', 'published_up' => '2020-04-'.$day.' 17:08:54']);
      }

      foreach ($this->categories as $category) {
	Category::create(['name' => $category['name'], 'slug' => $category['slug'], 
		     'description' => '<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 
		     'status' => 'published']);
      }

      foreach ($this->groups as $group) {
	Group::create(['name' => $group['name']]);
      }

      foreach ($this->fields as $field) {
	Field::create(['name' => $field['name'], 'code' => $field['code'], 'type' => $field['type'], 'status' => 'published']);
      }
    }
}

