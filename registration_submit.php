<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Draft</title>
</head>

<body>

    <?php
    // Initialize error array before use.
    $err = array();

    // Read form data safely.
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validate required fields.
    if (trim($name) === '') {
        $err[] = 'Name is required.';
    }
    if (trim($username) === '') {
        $err[] = 'Username is required.';
    }
    if (empty($email)) {
        $err[] = "Email is required.";
    }

    if (strlen($password) < 6) {
        $err[] = 'Password must be at least 6 characters long.';
    }

    if ($role === 'teacher') {
        $teacher_key = $_POST['teacher_key'] ?? '';
        if ($teacher_key !== 'Teacher') {
            $err[] = 'Teacher validation key is incorrect.';
        }
    }

    if (empty($err)) {
        $file = fopen('user_database.txt', 'a');
        if ($file) {
            if (filesize("user_database.txt") === 0) {
                $header = implode("\t", [
                    "Name",
                    "Username",
                    "Email",
                    "Password",
                    "Role",
                ]) . "\n";

                fwrite($file, $header);
            }

            $user_data = implode("\t", [
                $name,
                $username,
                $email,
                $password,
                $role
            ]) . "\n";

            fwrite($file, $user_data);

            fclose($file);
            echo '<h2>Registration successful.</h2>';
        } else {
            $err[] = 'Unable to save registration data.';
        }
    }

    if (!empty($err)) {
        echo '<h2>Registration failed:</h2>';
        echo '<ul>';
        foreach ($err as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
    }
    ?>

</body>

</html>