document.addEventListener("DOMContentLoaded", function () {
    // Поиск пользователей
    const user_searchInput = document.getElementById("user-search-bar");
    const user_tableBody = document.querySelector(".user_table tbody");

    if (user_searchInput && user_tableBody) {
        user_searchInput.addEventListener("input", function () {
            const query = this.value.trim();

            fetch(`/search-users?q=${query}`)
                .then((response) => response.json())
                .then((data) => {
                    user_tableBody.innerHTML = ""; // Очищаем таблицу перед обновлением

                    if (data.length === 0) {
                        user_tableBody.innerHTML = `<tr>
                            <td colspan="3" class="text-center">No users found.</td>
                        </tr>`;
                        return;
                    }

                    data.forEach((user) => {
                        const row = document.createElement("tr");
                        row.setAttribute("data-id", user.id);
                        row.innerHTML = `
                            <td>${user.id}</td>
                            <td>${user.uname}</td>
                            <td>${user.role}</td>
                        `;
                        user_tableBody.appendChild(row);
                    });
                })
                .catch((error) => console.error("Error fetching users:", error));
        });
    } else {
        console.warn("User search input or table not found! (Этот код не выполняется на этой странице)");
    }

    // Поиск таблиц
    const table_searchInput = document.getElementById("table-search-bar");
    const table_tableBody = document.querySelector(".table_table tbody");

    if (table_searchInput && table_tableBody) {
        table_searchInput.addEventListener("input", function () {
            const query = this.value.trim();

            fetch(`/search-tables?q=${query}`)
                .then((response) => response.json())
                .then((data) => {
                    table_tableBody.innerHTML = ""; // Очищаем таблицу перед обновлением

                    if (data.length === 0) {
                        table_tableBody.innerHTML = `<tr>
                            <td colspan="3" class="text-center">No tables found.</td>
                        </tr>`;
                        return;
                    }

                    data.forEach((table) => {
                        const row = document.createElement("tr");
                        row.setAttribute("data-id", table.id);
                        row.innerHTML = `
                            <td>${table.id}</td>
                            <td>${table.name}</td>
                        `;
                        table_tableBody.appendChild(row);
                    });
                })
                .catch((error) => console.error("Error fetching tables:", error));
        });
    } else {
        console.warn("Table search input or table not found! (Этот код не выполняется на этой странице)");
    }
});
