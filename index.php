<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target Send Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <main class="container py-4">
        <h1>Target Send Email</h1>

        <form action="send.php" method="post">
            <div class="mb-3">
                <label for="recipients" class="form-label">Recipients</label>
                <textarea class="form-control" id="recipients" name="recipients" rows="5" required placeholder="one@example.com, two@example.com"></textarea>
                <div class="form-text">Use commas or new lines to send to one or more email addresses.</div>
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input class="form-control" type="text" id="subject" name="subject" required>
            </div>

            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="8" required></textarea>
            </div>

            <button class="btn btn-primary" type="submit" name="send">Send</button>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
