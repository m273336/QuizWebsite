# Loading quizzes from private storage

This feature adds a single endpoint:

- `GET /get-quizzes.php` returns quiz IDs, titles, descriptions, and question counts.
- `GET /get-quizzes.php?id=<quiz-id>` returns one quiz and its questions/choices.

**Neither response contains the answer key.** Both routes require an authenticated student/teacher session, except for the explicitly enabled loopback-only development test mode. Do not use that test mode on a shared/public server, including when accessed through SSH port forwarding.

The endpoint reads `../quizwebsite-private/quizzes.txt` relative to the repository, the same location as `create-quiz-process.php`. This directory is created locally and is **not tracked in GitHub**.

To set up the private file, from the repository directory on your PHP server:

```sh
mkdir -p ../quizwebsite-private
chmod 700 ../quizwebsite-private
test -e ../quizwebsite-private/quizzes.txt || printf '[]\n' > ../quizwebsite-private/quizzes.txt
chmod 600 ../quizwebsite-private/quizzes.txt
```

Verify the directory is outside the web document root and accessible to the PHP process.

Local-only manual test, while no real login is available:

```sh
MID_QUIZ_TEST_MODE=1 php -S 127.0.0.1:8000
```

From a browser on the same host (or a carefully controlled SSH tunnel), open:

- `http://localhost:8000/student-dashboard.html`
- `http://localhost:8000/get-quizzes.php`
- `http://localhost:8000/quiz.html?id=YOUR_QUIZ_ID`

Populate the private file by creating a test quiz using `create-quiz.html` with test mode enabled. Check that none of the GET responses include `correct` or `created_by`. A nonexistent ID returns 404, and without login/test mode the endpoint returns 403.

This feature only **displays** quizzes. Submission, automatic grading, attempt tracking, and grade viewing are future features. The quiz submit button is intentionally disabled until `submit-quiz.php` exists.

Security note: loopback test mode is only for a controlled development environment; integrate proper login/session permissions and CSRF protection before deployment.
