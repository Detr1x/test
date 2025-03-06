document.addEventListener("DOMContentLoaded", function () {
    const addColumnBtn = document.getElementById("addColumn");
    const columnsShow = document.querySelector(".columns_show");
    const tablePreview = document.querySelector(".table-preview thead tr");
    let columnIndex = 0;

    addColumnBtn.addEventListener("click", function () {
        const nameInput = document.getElementById("name");
        const typeInput = document.getElementById("type");
        const name = nameInput.value.trim();
        const type = typeInput.value.trim();

        if (name === "" || type === "") {
            alert("Please fill in both fields.");
            return;
        }

        // Создание скрытых полей для формы
        let columnGroup = document.createElement("div");
        columnGroup.classList.add("column-group");
        columnGroup.innerHTML = `
            <input type="hidden" name="columns[${columnIndex}][name]" value="${name}">
            <input type="hidden" name="columns[${columnIndex}][type]" value="${type}">
            <input type="hidden" name="columns[${columnIndex}][s_number]" value="${
            columnIndex + 1
        }">
        `;
        columnsShow.appendChild(columnGroup);

        // Добавление нового столбца в таблицу-макет
        let th = document.createElement("th");
        th.textContent = `${name} (${type})`;
        tablePreview.appendChild(th);

        // Очистка инпутов
        nameInput.value = "";
        typeInput.value = "";

        columnIndex++;
    });
});
