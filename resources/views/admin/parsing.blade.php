<div>
	<h3>Пробный парсинг по ссылке (не сохранит в бд)</h3>
	<form method="POST">
		@csrf
		<input type="text" name="link" placeholder="Ссылка на аниме">
		<input name="testParse" type="submit" value="Показать результаты парсинга">
	</form>
	@if(isset($_POST['testParse']))
		<p>Результаты парсинга:</p>
		<?php dump($parse_result??null) ?>
		<p>Ошибки:</p>
		<?php dump($parse_errors??null) ?>
	@endif

	<h3>Спарсить всё с анидаба и сохранить в бд</h3>
	<form method="POST">
		@csrf
		<input name="parseAll" type="submit" value="Парсить всё">
	</form>
	@if(isset($_POST['patseAll']))
		<p>Анидаб успешно спаршен в бд</p>
		<p>Ошибки:</p>
		<?php dump($parse_errors??null) ?>
	@endif

	<h3>Итерация по страницам каталога аниме</h3>
	<form method="POST">
		@csrf
		<input name="iter" type="submit" value="Итерация">
	</form>
	@if(isset($_POST['iter']))
		<p>Итерация произведена:</p>
		<?php dump($iter_result??null) ?>
	@endif
</div>