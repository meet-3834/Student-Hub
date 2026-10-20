<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 13 - Student List</title>
    <style>
        body { max-width: 700px; margin: 40px auto; padding: 0 16px; font-family: Arial, sans-serif; }
        input, button { padding: 8px; margin: 4px 2px; }
        input { box-sizing: border-box; width: 100%; margin: 6px 0 12px; }
        form { max-width: 400px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 8px; border: 1px solid #bbb; text-align: left; }
        #message { min-height: 1.2em; }
    </style>
</head>
<body>
    <h1>Student List</h1>
    <p>Students are loaded and changed without reloading this page.</p>

    <form id="student-form">
        <input type="hidden" id="student-id">
        <label for="name">Name</label>
        <input id="name" name="name" required maxlength="100">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required maxlength="150">
        <button type="submit" id="save-button">Add student</button>
        <button type="button" id="cancel-button" hidden>Cancel edit</button>
    </form>

    <p id="message" role="status"></p>
    <table>
        <thead>
            <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
        </thead>
        <tbody id="student-list"></tbody>
    </table>

    <script>
        const form = document.querySelector('#student-form');
        const list = document.querySelector('#student-list');
        const message = document.querySelector('#message');
        const idInput = document.querySelector('#student-id');
        const nameInput = document.querySelector('#name');
        const emailInput = document.querySelector('#email');
        const saveButton = document.querySelector('#save-button');
        const cancelButton = document.querySelector('#cancel-button');

        async function requestApi(method, body) {
            const options = { method, headers: {} };
            if (body) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body);
            }

            const response = await fetch('api.php', options);
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'The request failed.');
            }
            return result;
        }

        function addCell(row, text) {
            const cell = document.createElement('td');
            cell.textContent = text;
            row.appendChild(cell);
            return cell;
        }

        function showStudents(students) {
            list.replaceChildren();

            if (students.length === 0) {
                const row = document.createElement('tr');
                const cell = addCell(row, 'No students yet.');
                cell.colSpan = 3;
                list.appendChild(row);
                return;
            }

            students.forEach((student) => {
                const row = document.createElement('tr');
                addCell(row, student.name);
                addCell(row, student.email);

                const actions = addCell(row, '');
                const editButton = document.createElement('button');
                editButton.textContent = 'Edit';
                editButton.type = 'button';
                editButton.addEventListener('click', () => startEdit(student));

                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Delete';
                deleteButton.type = 'button';
                deleteButton.addEventListener('click', () => deleteStudent(student.id));

                actions.append(editButton, deleteButton);
                list.appendChild(row);
            });
        }

        async function loadStudents(successMessage = '') {
            message.textContent = 'Loading students...';
            try {
                const result = await requestApi('GET');
                showStudents(result.data);
                message.textContent = successMessage;
            } catch (error) {
                message.textContent = error.message;
            }
        }

        function startEdit(student) {
            idInput.value = student.id;
            nameInput.value = student.name;
            emailInput.value = student.email;
            saveButton.textContent = 'Update student';
            cancelButton.hidden = false;
            nameInput.focus();
        }

        function resetForm() {
            form.reset();
            idInput.value = '';
            saveButton.textContent = 'Add student';
            cancelButton.hidden = true;
        }

        async function deleteStudent(id) {
            if (!confirm('Delete this student?')) return;

            try {
                const result = await requestApi('POST', { action: 'delete', id });
                await loadStudents(result.message);
            } catch (error) {
                message.textContent = error.message;
            }
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const isEditing = idInput.value !== '';
            const body = {
                action: isEditing ? 'update' : 'add',
                name: nameInput.value,
                email: emailInput.value
            };
            if (isEditing) body.id = Number(idInput.value);

            try {
                const result = await requestApi('POST', body);
                resetForm();
                await loadStudents(result.message);
            } catch (error) {
                message.textContent = error.message;
            }
        });

        cancelButton.addEventListener('click', resetForm);
        loadStudents();
    </script>
</body>
</html>
