document.getElementById("addColumn").addEventListener("click", function () {
    let container = document.querySelector(".columns_show");
    let index = document.querySelectorAll(".column-group").length + 1; // Учитываем первую колонку (id = 1)

    let div = document.createElement("div");
    div.classList.add("column-group");
    div.innerHTML = `
    <label>Name:</label>
    <input type="text" name="columns[${index}][name]" required>
    <label>Type:</label>
    <input type="text" name="columns[${index}][type]" required>
    <input type="hidden" name="columns[${index}][s_number]" value="${
        index + 1
    }">
`;

    container.appendChild(div);
});
