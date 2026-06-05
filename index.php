<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Multiple Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <div class="inEmail">
        <br>

        <div class="displayEmail">
            Recipients:
            <?php
                include 'connect.php';

                function escapeHtml($value)
                {
                    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }

                if (!$conn) {
                    echo '<p>Unable to load recipients. Check your database configuration.</p>';
                } else {
                    $sql = "SELECT emailName, email FROM emails";
                    $result = mysqli_query($conn, $sql);

                    if ($result === false) {
                        echo '<p>Unable to load recipients right now.</p>';
                    } elseif (mysqli_num_rows($result) > 0) {
                        echo '<table class="table table-striped"><tr><th>Name</th><th>Email</th></tr>';
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr><td>' . escapeHtml($row['emailName']) . '</td><td>' . escapeHtml($row['email']) . '</td></tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '0 results';
                    }

                    mysqli_close($conn);
                }
            ?>
        </div>
    </div>
    <br><br><br> <hr>

    <div class="mailer">
        <form action="send.php" method="post">
            Send Email <br>
            Subject <input type="text" name="subject" required> <br>
            Message <input type="text" name="message" required> <br>
            <button type="submit" name="send">Send</button>
        </form>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>
