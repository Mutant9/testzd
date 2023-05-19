<?php
// Подключаем файл с классом Person
require_once "Person.php";

// Создаем класс People, который работает с массивом экземпляров класса Person
class People {
    // Объявляем свойства класса
    private $ids; // Массив с id людей
    private $persons; // Массив с экземплярами класса Person

    // Создаем конструктор класса
    public function __construct($conditions) {
        // Проверяем валидность входных данных
        if (!is_array($conditions)) {
            throw new Exception("Условия должны быть массивом");
        }
        // Подключаемся к БД
        $conn = new mysqli("localhost", "username", "password", "database");
        // Проверяем подключение
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Создаем SQL-запрос для поиска id людей по всем полям БД с учетом условий
        $sql = "SELECT id FROM persons WHERE ";
        // Добавляем условия к запросу
        foreach ($conditions as $field => $value) {
            // Проверяем валидность поля и значения
            if (!in_array($field, ["id", "name", "surname", "birthdate", "gender", "city"])) {
                throw new Exception("Неверное поле: $field");
            }
            if (is_string($value)) {
                // Экранируем специальные символы в строке
                $value = $conn->real_escape_string($value);
                // Добавляем кавычки к строке
                $value = "'$value'";
            } elseif (is_int($value)) {
                // Ничего не делаем с целым числом
            } elseif (is_array($value)) {
                // Проверяем длину массива
                if (count($value) != 2) {
                    throw new Exception("Неверное значение: $value");
                }
                // Проверяем оператор и значение в массиве
                $operator = $value[0];
                $operand = $value[1];
                if (!in_array($operator, [">", "<", ">=", "<=", "<>", "!="])) {
                    throw new Exception("Неверный оператор: $operator");
                }
                if (is_string($operand)) {
                    // Экранируем специальные символы в строке
                    $operand = $conn->real_escape_string($operand);
                    // Добавляем кавычки к строке
                    $operand = "'$operand'";
                } elseif (is_int($operand)) {
                    // Ничего не делаем с целым числом
                } else {
                    throw new Exception("Неверный операнд: $operand");
                }
                // Формируем значение из массива
                $value = "$operator $operand";
            } else {
                throw new Exception("Неверное значение: $value");
            }
            // Добавляем условие к запросу с логическим И
            $sql .= "$field = $value AND ";
        }
        // Убираем последний AND из запроса
        $sql = rtrim($sql, "AND ");
        // Выполняем запрос и получаем результат
        $result = $conn->query($sql);
        // Проверяем результат
        if ($result->num_rows > 0) {
            // Инициализируем свойство ids пустым массивом
            $this->ids = array();
            // Проходим по всем строкам результата и добавляем id в массив ids
            while ($row = $result->fetch_assoc()) {
                array_push($this->ids, $row["id"]);
            }
        } else {
            echo "No records found";
        }
        // Закрываем подключение и результат
        $result->close();
        $conn->close();
    }

    // Создаем метод для получения массива экземпляров класса Person из массива с id людей полученного в конструкторе
    public function getPersons() {
        // Инициализируем свойство persons пустым массивом
        $this->persons = array();
        // Проходим по всем элементам массива ids и создаем экземпляры класса Person с соответствующими данными из БД
        foreach ($this->ids as $id) {
            // Подключаемся к БД
            $conn = new mysqli("localhost", "username", "password", "database");
            // Проверяем подключение
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            // Создаем SQL-запрос для получения данных из таблицы persons по id
            $sql = "SELECT * FROM persons WHERE id = ?";
            // Подготавливаем запрос
            $stmt = $conn->prepare($sql);
            // Привязываем параметры к запросу
            $stmt->bind_param("i", $id);
            // Выполняем запрос и получаем результат
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    // Получаем ассоциативный массив из результата и создаем экземпляр класса Person с данными из массива
                    while ($row = $result->fetch_assoc()) {
                        array_push($this->persons, new Person($row["id"],
                            $row["name"],
                            $row["surname"],
                            $row["birthdate"],
                            $row["gender"],
                            $row["city"]));
                    }
                } else {
                    echo "No record found for id: {$id}";
                }
            } else {
                echo "Error: " . $stmt->error;
            }
            // Закрываем подключение и запрос
            $stmt->close();
            $conn->close();
        }
        return ($this->persons);
    }

    // Создаем метод для удаления людей из БД с помощью экземпляров класса Person в соответствии с массивом полученным в конструкторе
    public function deletePersons() {
        foreach ($this->persons as &$person) {
            try {
                echo "\nDeleting person with id: {$person.id}";
                person.deleteFromDB();
            } catch (Exception e) {
                echo "\nError deleting person with id: {$person.id}";
                echo "\n" . e.getMessage();
            }
        }
    }

}
?>
