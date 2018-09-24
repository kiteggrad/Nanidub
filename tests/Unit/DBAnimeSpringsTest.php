<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Anime;

class DBAnimeSpringsTest extends TestCase
{
    // Тестирование один ко многим

	    public function testCountry()
	    {
	    	$strings_arr = Anime::find(1)->country;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testProducer()
	    {
	    	$strings_arr = Anime::find(1)->producer;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testAuthor()
	    {
	    	$strings_arr = Anime::find(1)->author;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testStudio()
	    {
	    	$strings_arr = Anime::find(1)->studio;
	    	$this->assertNotEmpty($strings_arr);
	    }

		// Обратное 

			public function testCountryAnimes()
		    {
		    	$strings_arr = Anime::find(1)->country->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testProducerAnimes()
		    {
		    	$strings_arr = Anime::find(1)->producer->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testAuthorAnimes()
		    {
		    	$strings_arr = Anime::find(1)->author->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testStudioAnimes()
		    {
		    	$strings_arr = Anime::find(1)->studio->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }

	// Тестирование многие ко многим

	    public function testCategories()
	    {
	    	$strings_arr = Anime::find(1)->categories;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testYears()
	    {
	    	$strings_arr = Anime::find(1)->years;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testGanres()
	    {
	    	$strings_arr = Anime::find(1)->ganres;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testDubbings()
	    {
	    	$strings_arr = Anime::find(1)->dubbings;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testTimings()
	    {
	    	$strings_arr = Anime::find(1)->timings;
	    	$this->assertNotEmpty($strings_arr);
	    }
	    public function testTranslates()
	    {
	    	$strings_arr = Anime::find(1)->translates;
	    	$this->assertNotEmpty($strings_arr);
	    }

	    // Обратное

	    	public function testCategoriesAnimes()
		    {
		    	$strings_arr = Anime::find(1)->categories[0]->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testYearsAnimes()
		    {
		    	$strings_arr = Anime::find(1)->years[0]->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testGanresAnimes()
		    {
		    	$strings_arr = Anime::find(1)->ganres[0]->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testDubbingsAnimes()
		    {
		    	$strings_arr = Anime::find(1)->dubbings[0]->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testTimingsAnimes()
		    {
		    	$strings_arr = Anime::find(1)->timings[0]->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
		    public function testTranslatesAnimes()
		    {
		    	$strings_arr = Anime::find(1)->translates[0]->animes;
		    	$this->assertNotEmpty($strings_arr);
		    }
}
