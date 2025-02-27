document.addEventListener("DOMContentLoaded", function () {
    const user_searchInput = document.getElementById("user-search-bar");
    const user_tableBody = document.querySelector(".users_table tbody");


    if (!user_searchInput || !user_tableBody) {
        console.warn(
            "User search input or table not found! (Этот код не выполняется на этой странице)"
        );
        return;
    }

    user_searchInput.addEventListener("input", function () {
        const query = this.value.trim();

        fetch(`/search-users?q=${query}`)
            .then((response) => response.json())
            .then((data) => {
                user_tableBody.innerHTML = ""; // Очищаем таблицу перед обновлением

                if (data.length === 0) {
                    user_tableBody.innerHTML = `<tr>
                        <td colspan="4" class="text-center">No users found.</td>
                    </tr>`;
                    return;
                }

                data.forEach((user) => {
                    const row = document.createElement("tr");
                    row.setAttribute("data-id", user.id);
                    row.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.uname}</td>
                        <td>${user.association}</td>
                        <td>${user.role}</td>
                    `;
                    user_tableBody.appendChild(row);
                });
            })
            .catch((error) => console.error("Error fetching users:", error));
    });
});
