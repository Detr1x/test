document.addEventListener("DOMContentLoaded", function () {
    const table_searchInput = document.getElementById("table-search-bar");
    const table_tableBody = document.querySelector(".tables_table tbody");

    if (!table_searchInput || !table_tableBody) {
        console.warn(
            "Table search input or table not found! (Этот код не выполняется на этой странице)"
        );
        return;
    }

    table_searchInput.addEventListener("input", function () {
        const query = this.value.trim();

        fetch(`/search-tables?q=${query}`)
            .then((response) => response.json())
            .then((data) => {
                table_tableBody.innerHTML = ""; // Очищаем таблицу перед обновлением

                if (data.length === 0) {
                    table_tableBody.innerHTML = `<tr>
                        <td colspan="4" class="text-center">No tables found.</td>
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
});
