# Private quiz storage setup

Quiz data is intentionally **not** stored inside the public GitHub repository.

Expected layout:

```
si350/
  QuizWebsite/
    create-quiz.html
    create-quiz-process.php
  quizwebsite-private/
    quizzes.txt
```

From the **QuizWebsite** directory on the PHP server, run:

```sh
mkdir -p ../quizwebsite-private
chmod 700 ../quizwebsite-private
test -e ../quizwebsite-private/quizzes.txt || printf '[]\n' > ../quizwebsite-private/quizzes.txt
chmod 600 ../quizwebsite-private/quizzes.txt
```

**Check your actual web server document root first.** The private folder must not be downloadable via HTTP. If the server exposes all of `~/si350`, choose a different location and update the `$path` variable in `create-quiz-process.php`.

The PHP web server process must have the necessary read/write permissions. These instructions assume PHP runs as your user; if it runs as another user, arrange narrowly scoped permissions through your system administrator instead of making the data world-writable.

The file contains a JSON array of quizzes. Only the code is tracked by Git; saved quizzes stay on the server. Do not put real answer keys or user information into GitHub commits.

The handler expects the authentication system to set `$_SESSION["user_id"]` and `$_SESSION["role"] = "teacher"`. Without those values, it returns 403. Add CSRF protection and robust storage recovery before using this in production.

After merging this PR, verify PHP syntax with:

```sh
php -l create-quiz-process.php
```
