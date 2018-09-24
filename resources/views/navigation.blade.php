
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style>
		.scroll {
			height: 200px;
			width: 300px;
			border: 1px solid #000;
			overflow-x: scroll;
			overflow-y: scroll;
		}
	</style>
</head>
<body>
	<form method="POST">
		@csrf
		<div>
			<div style="display: inline-block;">
				<div>Категория</div>
				<div class="scroll">
					<ul style="list-style: none">
						@foreach ($categories as $category)
							<li><label for=""><input type="checkbox" value="{{ $category->id }}" name="categories[]">{{ $category->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
			<div style="display: inline-block;">
				<div>Жанр</div>
				<div class="scroll">
					<ul style="list-style: none">
						@foreach ($genres as $genre)
							<li><label for=""><input type="checkbox" value="{{ $genre->id }}" name="genres[]">{{ $genre->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
			<div style="display: inline-block;">
				<div>Страна</div>
				<div class="scroll"> 
					<ul style="list-style: none">
						@foreach ($countries as $country)
							<li><label for=""><input type="checkbox" value="{{ $country->id }}" name="countries[]">{{ $country->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
		<div>
			<div style="display: inline-block;">
				<div>Режиссер</div>
				<div class="scroll">
					<ul style="list-style: none">
						@foreach ($producers as $producer)
							<li><label for=""><input type="checkbox" value="{{ $producer->id }}" name="producers[]">{{ $producer->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
			<div style="display: inline-block;">
				<div>Студия</div>
				<div class="scroll">
					<ul style="list-style: none">
						@foreach ($studios as $studio)
							<li><label for=""><input type="checkbox" value="{{ $studio->id }}" name="studios[]">{{ $studio->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
			<div style="display: inline-block;">
				<div>Озвучивание</div>
				<div class="scroll"> 
					<ul style="list-style: none">
						@foreach ($dubbings as $dubbing)
							<li><label for=""><input type="checkbox" value="{{ $dubbing->id }}" name="dubbings[]">{{ $dubbing->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
		<div>
			<div style="display: inline-block;">
				<div>Автор оригинала / сценарист</div>
				<div class="scroll">
					<ul style="list-style: none">
						@foreach ($authors as $author)
							<li><label for=""><input type="checkbox" value="{{ $author->id }}" name="authors[]">{{ $author->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
			<div style="display: inline-block;">
				<div>Год</div>
				<div class="scroll">
					<ul style="list-style: none">
						@foreach ($years as $year)
							<li><label for=""><input type="checkbox" value="{{ $year->id }}" name="years[]">{{ $year->year }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
			<div style="display: inline-block;">
				<div>Тайминг и работа со звуком</div>
				<div class="scroll"> 
					<ul style="list-style: none">
						@foreach ($timings as $timing)
							<li><label for=""><input type="checkbox" value="{{ $timing->id }}" name="timings[]">{{ $timing->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
		</div>
		<div>
			<div style="display: inline-block;">
				<div>Перевод</div>
				<div class="scroll">
					<ul style="list-style: none">
						@foreach ($translates as $translate)
							<li><label for=""><input type="checkbox" value="{{ $translate->id }}" name="translates[]">{{ $translate->name }}</label></li>
						@endforeach
					</ul>
				</div>
			</div>
			<div style="display: inline-block;">
				<div>Сортировать</div>
				<select style="list-style: none" name="orderBy">
					<option>По дате добавления</option>
					<option>По рейтингу</option>
				</select>
			</div>
			<div style="display: inline-block;">
				<div>Искать</div>
				<input type="text" name="search_text">
			</div>
		</div>
		<input type="submit" placeholder="Отправить">
	</form>
</body>
</html>
