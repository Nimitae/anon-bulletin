<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class DBConfig
{
    public static $DB_CONNSTRING;
    public static $DB_USERNAME;
    public static $DB_PASSWORD;
    public static $DB_ADMIN_EMAIL;

    public function __construct()
    {
        self::$DB_CONNSTRING = "mysql:host=localhost;dbname=bulletin";
        self::$DB_USERNAME = "bulletin";
        self::$DB_PASSWORD = "bulletin";
    }
}

new DBConfig();

class Message
{
    private static $idList = array();
    private $id;
    private $timestamp;
    private $message;
    private $poster;

    public function __construct($id, $timestamp, $message, $poster)
    {
        $this->id = $id;
        $this->timestamp = $timestamp;
        $this->message = $message;
        $this->poster = $poster;
    }

    public static function create($id, $timestamp, $message, $poster)
    {
        if (!isset(self::$idList[$id])) {
            self::$idList[$id] = new Message($id, $timestamp, $message, $poster);
        }
        return self::$idList[$id];
    }

    public function getID()
    {
        return $this->id;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }
}

class MessageDAO
{
    public function getAllMessages()
    {
        $sqlQuery = "SELECT * FROM messages ORDER BY timestamp DESC;";
        $dbh = new PDO(DBconfig::$DB_CONNSTRING, DBConfig::$DB_USERNAME, DBConfig::$DB_PASSWORD);
        $queryResultSet = $dbh->query($sqlQuery);
        $messageResults = $queryResultSet->fetchAll(PDO::FETCH_ASSOC);
        return $messageResults;
    }

    public function insertNewMessage($message, $poster)
    {
        if ($poster == NULL){
            $poster2 = "Anonymous";
        } else {
            $poster2 = $poster;
        }
        $sqlInsert = "INSERT INTO messages VALUES (NULL, NULL, :message, :poster);";
        $dbh = new PDO(DBconfig::$DB_CONNSTRING, DBConfig::$DB_USERNAME, DBConfig::$DB_PASSWORD);
        $stmt = $dbh->prepare($sqlInsert);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':poster', $poster2);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

$messageDAO = new MessageDAO();
if (isset($_POST["message"]) && $_POST["message"] != NULL) {
    $messageDAO->insertNewMessage($_POST["message"], $_POST["poster"]);
}
$messageResults = $messageDAO->getAllMessages();
$messages = array();
foreach ($messageResults as $row) {
    $newMessage = new Message($row['id'], $row['timestamp'], $row['message'], $row['poster']);
    $messages[] = $newMessage;
}
?>
<link rel="stylesheet" href="./css/bootstrap.css" type="text/css"/>
<h1>Anon Bulletin</h1>
<form action="index.php" method="post">
    <input type="text" name="poster" placeholder="Anonymous">
    <input type="text" name="message">
    <input type="submit" class="btn-xs btn-info ">
</form>
<table class="table">
    <thead>
    <th>
        Timestamp
    </th>
    <th>
        User
    </th>
    <th>
        Message
    </th>
    </thead>
    <tbody>
    <?php foreach ($messages as $message) : ?>
        <tr>
            <td>
                <?php print htmlspecialchars($message->getTimestamp()); ?>
            </td>
            <td>
                <?php print htmlspecialchars($message->getPoster()); ?>
            </td>
            <td>
                <?php print htmlspecialchars($message->getMessage()); ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

