1)Переходим в папку проекта (директрория "task");
2)composer install;
3)Для запуска скрипта вводим команду:

php bin/console app:generate-tree <Путь до input-файла> <Путь до output-файла>

Например:

php bin/console app:generate-tree /Users/ruslan/Desktop/task/input.csv /Users/ruslan/Desktop/task/output.json

4)Написано два теста - на чтение input-файла и генерацию output-файла. Для запуска тестов вводим команду:

php bin/phpunit tests/Service/GenTreeGeneratorTest.php