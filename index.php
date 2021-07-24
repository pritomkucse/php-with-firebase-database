<?php
require __DIR__ . "/private/configs.php";
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory())
    ->withServiceAccount(__DIR__ . '/private/fb1.json')
    ->withDatabaseUri("https://connection-with-php-default-rtdb.firebaseio.com/");

$database = $factory->createDatabase();
$tableName = "users";
//$database->getReference($tableName)->limitToFirst(2)->getSnapshot()->getValue();
//https://github.com/ndeet/unzipper

$error = "";
$action = isset($_REQUEST["action"]) ? trim($_REQUEST["action"]) : "";
if ($action == "create") {
    $name = isset($_REQUEST["name"]) ? trim($_REQUEST["name"]) : "";
    $roll = isset($_REQUEST["roll"]) ? trim($_REQUEST["roll"]) : "";
    if (mb_strlen($name) == 0) {
        $error = "Name required";
    }
    else if (mb_strlen($roll) == 0) {
        $error = "Role required";
    }
    else {
        insert($database, $tableName, [
            'ID'.time() => [
                "Name" => $name,
                "Roll" => $roll
            ]
        ]);
        ob_start();
        header("Location: " . BASE_URL);
        ob_end_flush();
        return;
    }
}
else if ($action == "delete") {
    $ID = isset($_REQUEST["ID"]) ? trim($_REQUEST["ID"]) : "";
    if (mb_strlen($ID) > 0) {
        delete($database, $tableName, $ID);
        ob_start();
        header("Location: " . BASE_URL);
        ob_end_flush();
        return;
    }
}
if (strlen($error) > 0) {
    echo "<h2 style='color:red;'>$error</h2>";
}
?>


<?php
$list = list2($database, $tableName);

$database = $factory->createDatabase();
$tableName = "users";

function get($database, $tableName, string $userID = NULL)
{
    if (empty($userID) || !isset($userID)) {
        return null;
    }
    if ($database->getReference($tableName)->getSnapshot()->hasChild($userID)) {
        return $database->getReference($tableName)->getChild($userID)->getValue();
    }
    else {
        return null;
    }
}

function list2($database, $tableName)
{
    return $database->getReference($tableName)->getSnapshot()->getValue();
}

function insert($database, $tableName, array $data)
{
    if (empty($data) || !isset($data)) {
        return false;
    }
    foreach ($data as $key => $value) {
        $database->getReference()->getChild($tableName)->getChild($key)->set($value);
    }
    return true;
}

function delete($database, $tableName, string $userID) {
    if (empty($userID) || !isset($userID)) {
        return false;
    }
    if ($database->getReference($tableName)->getSnapshot()->hasChild($userID)){
        $database->getReference($tableName)->getChild($userID)->remove();
        return true;
    }
    else {
        return false;
    }
}

?>
<title>PHP with Firebase Realtime Database</title>

<form method="post" action="<?= BASE_URL ?>?action=create">
    <table>
        <tr>
            <td>
                Name:
            </td>
            <td>
                <input type="text" required maxlength="20" name="name"/>
            </td>
        </tr>
        <tr>
            <td>
                Roll:
            </td>
            <td>
                <input type="text" required minlength="6" maxlength="6" name="roll"/>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button>Create</button>
            </td>
        </tr>
    </table>
</form>

<table class="list_table">
    <tr>
        <th>Name</th>
        <th>Roll</th>
        <th class="action_column">Action</th>
    </tr>
    <?php foreach($list as $key => $row) { ?>
    <tr>
        <td><b><?= $row["Name"] ?></b></td>
        <td><?= $row["Roll"] ?></td>
        <td class="action_column">
            <a href="<?= BASE_URL ?>?action=delete&ID=<?= $key ?>">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>
<style type="text/css">
    h2, form {
        margin: 0;
    }
    .list_table {
        width: 500px;
        max-width: 90%;
        border: 1px solid black;
        padding: 7px;
    }
    .list_table th {
        text-align: left;
    }
    .list_table th.action_column {
        text-align: right;
    }
    .list_table td {
        border-top: 1px solid blueviolet;
        padding-bottom: 10px;
        padding-top: 10px;
    }
    .list_table td.action_column {
        text-align: right;
    }
</style>
