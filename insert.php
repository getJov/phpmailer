<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Email</title>
</head>
<body>
    <form action="insert.php" method="post">
        Insert Email <br>
        Name <input type="text" name="name" value="" required> <br>
        Email <input type="email" name="inEmail" required> <br>
        <button type="submit" name="insert">Insert</button>
    </form>

    <?php
        include 'connect.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$conn) {
                echo '<p>Unable to save recipient. Check your database configuration.</p>';
            } else {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['inEmail'] ?? '');

                if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo '<p>Enter a valid name and email address.</p>';
                } else {
                    $statement = mysqli_prepare($conn, 'INSERT INTO emails (emailName, email) VALUES (?, ?)');

                    if ($statement === false) {
                        echo '<p>Unable to save recipient right now.</p>';
                    } else {
                        mysqli_stmt_bind_param($statement, 'ss', $name, $email);

                        if (mysqli_stmt_execute($statement)) {
                            echo '<p>New record created successfully.</p>';
                        } else {
                            echo '<p>Unable to save recipient right now.</p>';
                        }

                        mysqli_stmt_close($statement);
                    }

                    mysqli_close($conn);
                }
            }
        }
    ?>
</body>
</html>
