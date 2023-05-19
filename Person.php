<?php
// Создаем класс Person
class Person {
    // Объявляем свойства класса
    private $id;
    private $name;
    private $surname;
    private $birthdate;
    private $gender;
    private $city;

    // Создаем конструктор класса
    public function __construct($id, $name, $surname, $birthdate, $gender, $city) {
        // Проверяем валидность входных данных
        if (!is_int($id)) {
            throw new Exception("ID должен быть целым числом");
        }
        if (!preg_match("/^[a-zA-Z]+$/", $name)) {
            throw new Exception("Имя должно содержать только буквы");
        }
        if (!preg_match("/^[a-zA-Z]+$/", $surname)) {
            throw new Exception("Фамилия должна содержать только буквы");
        }
        if (!strtotime($birthdate)) {
            throw new Exception("Дата рождения должна быть в формате YYYY-MM-DD");
        }
        if ($gender !== 0 && $gender !== 1) {
            throw new Exception("Пол должен быть 0 или 1");
        }
        // Присваиваем свойства класса
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->birthdate = $birthdate;
        $this->gender = $gender;
        $this->city = $city;
    }

    // Создаем метод для сохранения полей экземпляра класса в БД
    public function saveToDB() {
        // Подключаемся к БД
        $conn = new mysqli("localhost", "username", "password", "database");
        // Проверяем подключение
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Создаем SQL-запрос для вставки данных в таблицу persons
        $sql = "INSERT INTO persons (id, name, surname, birthdate, gender, city) VALUES (?, ?, ?, ?, ?, ?)";
        // Подготавливаем запрос
        $stmt = $conn->prepare($sql);
        // Привязываем параметры к запросу
        $stmt->bind_param("isssis", $this->id, $this->name, $this->surname, $this->birthdate, $this->gender, $this->city);
        // Выполняем запрос
        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
        // Закрываем подключение и запрос
        $stmt->close();
        $conn->close();
    }

    // Создаем метод для удаления человека из БД в соответствии с id объекта
    public function deleteFromDB() {
        // Подключаемся к БД
        $conn = new mysqli("localhost", "username", "password", "database");
        // Проверяем подключение
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Создаем SQL-запрос для удаления данных из таблицы persons по id
        $sql = "DELETE FROM persons WHERE id = ?";
        // Подготавливаем запрос
        $stmt = $conn->prepare($sql);
        // Привязываем параметры к запросу
        $stmt->bind_param("i", $this->id);
        // Выполняем запрос
        if ($stmt->execute()) {
            echo "Record deleted successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
        // Закрываем подключение и запрос
        $stmt->close();
        $conn->close();
    }

    // Создаем метод для преобразования даты рождения в возраст
    public function getAge() {
        // Получаем текущую дату в формате YYYY-MM-DD
        date_default_timezone_set('UTC');
        $today = date("Y-m-d");
        // Вычисляем разницу между текущей датой и датой рождения в годах
        return floor((strtotime($today) - strtotime($this->birthdate)) / (60 * 60 * 24 * 365));
    }

    // Создаем метод для преобразования пола из двоичной системы в текстовую
    public function getGenderText() {
        // Возвращаем текстовое представление пола в зависимости от значения свойства gender
        return ($this->gender == 0) ? "Мужской" : "Женский";
    }
}
?>
